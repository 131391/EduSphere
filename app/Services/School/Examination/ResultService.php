<?php

namespace App\Services\School\Examination;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Grade;
use App\Models\Result;
use App\Models\School;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultService
{
    /**
     * Save marks for multiple students in a specific exam and subject.
     *
     * Returns ['success' => bool, 'message' => string, 'saved' => int, 'skipped' => int].
     */
    public function saveMarks(School $school, array $data): array
    {
        $examId = (int) $data['exam_id'];
        $examSubjectId = (int) $data['exam_subject_id'];
        $marksData = $data['marks'];
        $userId = Auth::id();

        // Pre-flight checks must run BEFORE the DB transaction opens; otherwise a
        // bail-out path leaves an unclosed transaction and breaks the next request
        // (and `RefreshDatabase` test wrapping).
        /** @var Exam $exam */
        $exam = Exam::where('school_id', $school->id)->findOrFail($examId);

        if (!$exam->isMarkEntryAllowed()) {
            return [
                'success' => false,
                'message' => 'Mark entry is closed for this exam (status: ' . $exam->status->label() . ').',
                'saved' => 0,
                'skipped' => 0,
            ];
        }

        $exam->ensureSubjectSnapshot();

        /** @var ExamSubject $examSubject */
        $examSubject = $exam->examSubjects()->findOrFail($examSubjectId);

        if ($examSubject->subject_id === null) {
            return [
                'success' => false,
                'message' => 'The selected exam subject is no longer available for mark entry.',
                'saved' => 0,
                'skipped' => 0,
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
                'saved' => 0,
                'skipped' => 0,
            ];
        }

        DB::beginTransaction();
        try {

            $rows = [];
            $skippedCount = 0;
            $now = now();

            foreach ($marksData as $mark) {
                $rawMarks = $mark['marks_obtained'] ?? null;
                $remarks = array_key_exists('remarks', $mark) ? trim((string) $mark['remarks']) : null;
                $isAbsent = (bool) ($mark['is_absent'] ?? false);

                if (!$isAbsent && ($rawMarks === null || $rawMarks === '')) {
                    $skippedCount++;
                    continue;
                }

                $obtained = $isAbsent ? 0.0 : round((float) $rawMarks, 2);

                if (!$isAbsent && $obtained > $totalMarks) {
                    DB::rollBack();

                    return [
                        'success' => false,
                        'message' => "Marks for student ID {$mark['student_id']} exceed the configured full marks of {$totalMarks}.",
                        'saved' => 0,
                        'skipped' => 0,
                    ];
                }

                $percentage = ($totalMarks > 0 && !$isAbsent) ? ($obtained / $totalMarks) * 100 : 0.0;
                $grade = $isAbsent ? null : $this->calculateGrade($school, $percentage);

                $rows[] = [
                    'school_id' => $school->id,
                    'student_id' => (int) $mark['student_id'],
                    'exam_id' => $examId,
                    'subject_id' => $subjectId,
                    'class_id' => $classId,
                    'academic_year_id' => $academicYearId,
                    'marks_obtained' => $obtained,
                    'total_marks' => $totalMarks,
                    'percentage' => round($percentage, 2),
                    'grade' => $grade,
                    'remarks' => $remarks ?: null,
                    'is_absent' => $isAbsent,
                    'entered_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows === []) {
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Enter at least one mark before saving.',
                    'saved' => 0,
                    'skipped' => $skippedCount,
                ];
            }

            // Restore any soft-deleted rows for the same composite key first so the
            // upsert below is operating on the active row, not a tombstoned duplicate.
            Result::onlyTrashed()
                ->where('school_id', $school->id)
                ->where('exam_id', $examId)
                ->where('subject_id', $subjectId)
                ->whereIn('student_id', array_column($rows, 'student_id'))
                ->restore();

            Result::upsert(
                $rows,
                ['student_id', 'exam_id', 'subject_id'],
                [
                    'class_id',
                    'academic_year_id',
                    'marks_obtained',
                    'total_marks',
                    'percentage',
                    'grade',
                    'remarks',
                    'is_absent',
                    'entered_by',
                    'updated_at',
                ]
            );

            $exam->syncStatus();

            DB::commit();

            $savedCount = count($rows);
            $message = "Marks saved successfully for {$savedCount} student" . ($savedCount === 1 ? '' : 's') . '.';
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} blank entr" . ($skippedCount === 1 ? 'y was' : 'ies were') . ' skipped.';
            }

            return [
                'success' => true,
                'message' => $message,
                'saved' => $savedCount,
                'skipped' => $skippedCount,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Mark Entry Failed', [
                'school_id' => $school->id,
                'exam_id' => $examId,
                'exam_subject_id' => $examSubjectId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while saving marks: ' . $e->getMessage(),
                'saved' => 0,
                'skipped' => 0,
            ];
        }
    }

    /**
     * Calculate grade for a percentage. Returns null only when the school has
     * configured zero grade bands; otherwise the lowest band is the floor and
     * the highest band is the ceiling, so percentages outside the configured
     * range fall back to the nearest band rather than being silently ungraded.
     */
    public function calculateGrade(School $school, float $percentage): ?string
    {
        $bands = $this->cachedGradeBands($school);

        if ($bands->isEmpty()) {
            return null;
        }

        $direct = $bands->first(function (Grade $band) use ($percentage) {
            return $percentage >= $band->range_start && $percentage <= $band->range_end;
        });

        if ($direct) {
            return $direct->grade;
        }

        // Fall back: clamp to nearest band so we never silently emit `null` when
        // a school has bands configured but the percentage sits in a gap or
        // outside the [min, max] envelope.
        if ($percentage < $bands->min('range_start')) {
            return $bands->sortBy('range_start')->first()->grade;
        }

        return $bands->sortByDesc('range_end')->first()->grade;
    }

    /**
     * Get class result summary for an exam. Kept for external consumers.
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

    public function flushGradeCache(School $school): void
    {
        Cache::forget($this->gradeCacheKey($school));
    }

    /**
     * @return \Illuminate\Support\Collection<int, Grade>
     */
    protected function cachedGradeBands(School $school): \Illuminate\Support\Collection
    {
        return Cache::remember(
            $this->gradeCacheKey($school),
            now()->addMinutes(30),
            fn () => Grade::where('school_id', $school->id)
                ->orderBy('range_start')
                ->get()
        );
    }

    protected function gradeCacheKey(School $school): string
    {
        return 'examination:grades:' . $school->id;
    }
}
