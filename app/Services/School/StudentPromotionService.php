<?php

namespace App\Services\School;

use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\School;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentPromotion;
use App\Enums\StudentStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentPromotionService
{
    /**
     * Preview what will happen when promoting — no DB writes.
     */
    public function preview(School $school, int $fromYearId, int $toYearId): array
    {
        $fromYear = AcademicYear::where('school_id', $school->id)->findOrFail($fromYearId);
        $toYear   = AcademicYear::where('school_id', $school->id)->findOrFail($toYearId);

        $classes = ClassModel::where('school_id', $school->id)
            ->where('is_available', true)
            ->orderBy('order')
            ->get();

        $maxOrder = $classes->max('order');
        $preview  = [];

        // Load all students for the from-year in one query to avoid N+1
        $allStudents = Student::withoutGlobalScope('school')
            ->where('school_id', $school->id)
            ->where('academic_year_id', $fromYearId)
            ->where('status', StudentStatus::Active)
            ->with('section')
            ->get()
            ->groupBy('class_id');

        foreach ($classes as $class) {
            $students = $allStudents->get($class->id, collect());

            if ($students->isEmpty()) continue;

            $nextClass = $classes->where('order', $class->order + 1)->first();

            $preview[] = [
                'class_id'        => $class->id,
                'class_name'      => $class->name,
                'class_order'     => $class->order,
                'is_final_class'  => $class->order >= $maxOrder,
                'next_class_id'   => $nextClass?->id,
                'next_class_name' => $nextClass?->name ?? 'Graduate',
                'student_count'   => $students->count(),
                'students'        => $students->map(fn($s) => [
                    'id'           => $s->id,
                    'name'         => $s->full_name,
                    'admission_no' => $s->admission_no,
                    'section'      => $s->section?->name,
                    'result'       => $class->order >= $maxOrder
                        ? StudentPromotion::RESULT_GRADUATED
                        : StudentPromotion::RESULT_PROMOTED,
                ])->values(),
            ];
        }

        return [
            'from_year'      => $fromYear,
            'to_year'        => $toYear,
            'classes'        => $preview,
            'total_students' => collect($preview)->sum('student_count'),
        ];
    }

    /**
     * Execute bulk promotion for all active students.
     * Each class entry in $promotionData:
     *   class_id, students: [{ student_id, result, to_class_id, to_section_id }]
     */
    public function promote(School $school, int $fromYearId, int $toYearId, array $promotionData): array
    {
        $fromYear = AcademicYear::where('school_id', $school->id)->findOrFail($fromYearId);
        $toYear   = AcademicYear::where('school_id', $school->id)->findOrFail($toYearId);

        $classes   = ClassModel::where('school_id', $school->id)->orderBy('order')->get()->keyBy('id');
        $maxOrder  = $classes->max('order');
        $promotedBy = Auth::id();

        $promoted    = 0;
        $graduated   = 0;
        $detained    = 0;
        $transferred = 0;
        $errors      = [];

        DB::beginTransaction();
        try {
            foreach ($promotionData as $classEntry) {
                $fromClassId = $classEntry['class_id'];
                $fromClass   = $classes->get($fromClassId);

                if (!$fromClass) continue;

                foreach ($classEntry['students'] as $studentData) {
                    $student = Student::where('school_id', $school->id)
                        ->where('id', $studentData['student_id'])
                        ->where('academic_year_id', $fromYearId)
                        ->lockForUpdate()
                        ->first();

                    if (!$student) {
                        $errors[] = "Student ID {$studentData['student_id']} not found in from-year.";
                        continue;
                    }

                    // Guard: skip if already promoted for this year pair
                    $alreadyPromoted = StudentPromotion::withoutGlobalScope('school')
                        ->where('school_id', $school->id)
                        ->where('student_id', $student->id)
                        ->where('from_academic_year_id', $fromYearId)
                        ->where('to_academic_year_id', $toYearId)
                        ->exists();

                    if ($alreadyPromoted) {
                        $errors[] = "Student {$student->admission_no} already promoted for this year transition.";
                        continue;
                    }

                    $result      = (int) ($studentData['result'] ?? StudentPromotion::RESULT_PROMOTED);
                    $toClassId   = $studentData['to_class_id'] ?? null;
                    $toSectionId = $studentData['to_section_id'] ?? null;

                    // Resolve default next class if not explicitly set
                    if (!$toClassId && $result === StudentPromotion::RESULT_PROMOTED) {
                        $nextClass = $classes->where('order', $fromClass->order + 1)->first();
                        $toClassId = $nextClass?->id;
                    }

                    // Validate to_class belongs to school
                    if ($toClassId && !$classes->has($toClassId)) {
                        $errors[] = "Invalid target class {$toClassId} for student {$student->admission_no}.";
                        continue;
                    }

                    // Validate to_section belongs to to_class
                    if ($toSectionId && $toClassId) {
                        $sectionValid = Section::where('id', $toSectionId)
                            ->where('class_id', $toClassId)
                            ->where('school_id', $school->id)
                            ->exists();
                        if (!$sectionValid) {
                            $toSectionId = Section::where('class_id', $toClassId)
                                ->where('school_id', $school->id)
                                ->value('id');
                        }
                    } elseif ($toClassId) {
                        // Auto-assign first section of target class
                        $toSectionId = Section::where('class_id', $toClassId)
                            ->where('school_id', $school->id)
                            ->value('id');
                    }

                    // Record promotion audit
                    StudentPromotion::create([
                        'school_id'            => $school->id,
                        'student_id'           => $student->id,
                        'from_academic_year_id' => $fromYearId,
                        'to_academic_year_id'   => $toYearId,
                        'from_class_id'         => $student->class_id,
                        'to_class_id'           => $toClassId,
                        'from_section_id'       => $student->section_id,
                        'to_section_id'         => $toSectionId,
                        'result'                => $result,
                        'promoted_by'           => $promotedBy,
                    ]);

                    // Update student record
                    $updateData = ['academic_year_id' => $toYearId];

                    if ($result === StudentPromotion::RESULT_GRADUATED) {
                        $updateData['status']     = StudentStatus::Graduated;
                        $updateData['class_id']   = null;
                        $updateData['section_id'] = null;
                        $graduated++;
                    } elseif ($result === StudentPromotion::RESULT_DETAINED) {
                        // Detained: stays in same class, just moves to new year
                        $updateData['class_id']   = $student->class_id;
                        $updateData['section_id'] = $student->section_id;
                        $detained++;
                    } elseif ($result === StudentPromotion::RESULT_TRANSFERRED) {
                        $updateData['status'] = StudentStatus::Transferred;
                        if ($toClassId)   $updateData['class_id']   = $toClassId;
                        if ($toSectionId) $updateData['section_id'] = $toSectionId;
                        $transferred++;
                    } else {
                        // Promoted
                        if ($toClassId)   $updateData['class_id']   = $toClassId;
                        if ($toSectionId) $updateData['section_id'] = $toSectionId;
                        $promoted++;
                    }

                    // Sync section current_strength
                    $oldSectionId = $student->section_id;
                    $newSectionId = $updateData['section_id'] ?? null;

                    if ($oldSectionId && $oldSectionId !== $newSectionId) {
                        Section::withoutGlobalScope('school')
                            ->where('id', $oldSectionId)
                            ->decrement('current_strength');
                    }
                    if ($newSectionId && $newSectionId !== $oldSectionId) {
                        Section::withoutGlobalScope('school')
                            ->where('id', $newSectionId)
                            ->increment('current_strength');
                    }

                    $student->update($updateData);
                }
            }

            DB::commit();

            Log::info('Student promotion completed', [
                'school_id'   => $school->id,
                'from_year'   => $fromYearId,
                'to_year'     => $toYearId,
                'promoted'    => $promoted,
                'graduated'   => $graduated,
                'detained'    => $detained,
                'transferred' => $transferred,
                'promoted_by' => $promotedBy,
            ]);

            return [
                'success'     => true,
                'promoted'    => $promoted,
                'graduated'   => $graduated,
                'detained'    => $detained,
                'transferred' => $transferred,
                'errors'      => $errors,
                'message'     => "Promotion complete. Promoted: {$promoted}, Graduated: {$graduated}, Detained: {$detained}, Transferred: {$transferred}."
                    . (count($errors) ? ' Errors: ' . count($errors) : ''),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student promotion failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get promotion history for a school or student.
     */
    public function getHistory(School $school, ?int $studentId = null, ?int $academicYearId = null)
    {
        $query = StudentPromotion::where('school_id', $school->id)
            ->with(['student', 'fromAcademicYear', 'toAcademicYear', 'fromClass', 'toClass', 'promotedBy']);

        if ($studentId)     $query->where('student_id', $studentId);
        if ($academicYearId) $query->where('to_academic_year_id', $academicYearId);

        return $query->latest()->paginate(25);
    }
}
