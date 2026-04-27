<?php

namespace App\Services\School\Examination;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Grade;
use App\Models\Result;
use App\Models\School;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultService
{
    /**
     * Save marks for multiple students in a specific exam and subject
     */
    public function saveMarks(School $school, array $data): array
    {
        $examId = (int) $data['exam_id'];
        $examSubjectId = (int) $data['exam_subject_id'];
        $marksData = $data['marks'];

        DB::beginTransaction();
        try {
            /** @var Exam $exam */
            $exam = Exam::where('school_id', $school->id)->findOrFail($examId);
            $exam->ensureSubjectSnapshot();

            /** @var ExamSubject $examSubject */
            $examSubject = $exam->examSubjects()->findOrFail($examSubjectId);

            if ($examSubject->subject_id === null) {
                return [
                    'success' => false,
                    'message' => 'The selected exam subject is no longer available for mark entry.',
                ];
            }

            $classId = (int) $exam->class_id;
            $academicYearId = (int) $exam->academic_year_id;
            $subjectId = (int) $examSubject->subject_id;
            $totalMarks = (float) $examSubject->full_marks;

            $validStudentIds = Student::where('school_id', $school->id)
                ->where('class_id', $classId)
                ->active()
                ->pluck('id')
                ->toArray();

            $providedStudentIds = collect($marksData)
                ->pluck('student_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $invalidStudentIds = array_values(array_diff($providedStudentIds, $validStudentIds));

            if ($invalidStudentIds !== []) {
                return [
                    'success' => false,
                    'message' => 'One or more selected students do not belong to this exam class.',
                ];
            }

            $savedCount = 0;
            $skippedCount = 0;

            foreach ($marksData as $mark) {
                $rawMarks = $mark['marks_obtained'] ?? null;
                $remarks = array_key_exists('remarks', $mark) ? trim((string) $mark['remarks']) : null;

                if ($rawMarks === null || $rawMarks === '') {
                    $skippedCount++;
                    continue;
                }

                $obtained = round((float) $rawMarks, 2);

                if ($obtained > $totalMarks) {
                    return [
                        'success' => false,
                        'message' => "Marks for student ID {$mark['student_id']} exceed the configured full marks of {$totalMarks}.",
                    ];
                }

                $percentage = ($totalMarks > 0) ? ($obtained / $totalMarks) * 100 : 0;
                $grade = $this->calculateGrade($school, $percentage);

                $result = Result::withTrashed()->firstOrNew(
                    [
                        'school_id' => $school->id,
                        'student_id' => $mark['student_id'],
                        'exam_id' => $examId,
                        'subject_id' => $subjectId,
                    ]
                );

                $result->fill([
                    'class_id' => $classId,
                    'academic_year_id' => $academicYearId,
                    'marks_obtained' => $obtained,
                    'total_marks' => $totalMarks,
                    'percentage' => round($percentage, 2),
                    'grade' => $grade,
                    'remarks' => $remarks ?: null,
                ]);

                $result->save();

                if ($result->trashed()) {
                    $result->restore();
                }

                $savedCount++;
            }

            if ($savedCount === 0) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Enter at least one mark before saving.',
                ];
            }

            $exam->syncStatus();

            DB::commit();

            $message = "Marks saved successfully for {$savedCount} student" . ($savedCount === 1 ? '' : 's') . '.';
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} blank entr" . ($skippedCount === 1 ? 'y was' : 'ies were') . ' skipped.';
            }

            return [
                'success' => true,
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Mark Entry Failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while saving marks: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate grade based on percentage
     */
    public function calculateGrade(School $school, float $percentage): ?string
    {
        $grade = Grade::where('school_id', $school->id)
            ->where('range_start', '<=', $percentage)
            ->where('range_end', '>=', $percentage)
            ->orderByDesc('range_start')
            ->first();

        return $grade ? $grade->grade : null;
    }

    /**
     * Get class result summary for an exam
     */
    public function getClassExamSummary(School $school, $examId, $classId)
    {
        return Result::where('school_id', $school->id)
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->with(['student', 'subject'])
            ->get()
            ->groupBy('student_id');
    }
}
