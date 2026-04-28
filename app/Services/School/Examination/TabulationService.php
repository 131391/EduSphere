<?php

namespace App\Services\School\Examination;

use App\Models\Exam;
use App\Models\Result;
use App\Models\School;
use App\Models\Student;
use Illuminate\Support\Collection;

class TabulationService
{
    public function __construct(protected ResultService $resultService)
    {
    }

    /**
     * Build a per-student tabulation row for a given exam.
     *
     * Returns a Collection keyed by student id with the shape:
     *   [
     *     'student' => Student,
     *     'subjects' => Collection<int, ?Result>,   // keyed by exam_subject_id
     *     'total_obtained' => float,
     *     'total_max' => float,
     *     'percentage' => float,
     *     'grade' => ?string,
     *     'has_missing' => bool,
     *     'is_absent_overall' => bool,
     *   ]
     */
    public function tabulate(School $school, Exam $exam): Collection
    {
        $exam->ensureSubjectSnapshot();
        $exam->loadMissing(['examSubjects.subject', 'class', 'examType', 'academicYear']);

        $examSubjects = $exam->examSubjects;

        $students = Student::where('school_id', $school->id)
            ->where('class_id', $exam->class_id)
            ->active()
            ->orderByRaw('COALESCE(roll_no, 999999999)')
            ->orderBy('first_name')
            ->get();

        $resultsByStudent = Result::where('school_id', $school->id)
            ->where('exam_id', $exam->id)
            ->get()
            ->groupBy('student_id');

        $expectedSubjectIds = $examSubjects->pluck('subject_id')->filter()->all();

        return $students->keyBy('id')->map(function (Student $student) use ($examSubjects, $resultsByStudent, $school, $expectedSubjectIds) {
            $studentResults = $resultsByStudent->get($student->id) ?? collect();

            $subjects = $examSubjects->mapWithKeys(function ($examSubject) use ($studentResults) {
                $result = $studentResults->firstWhere('subject_id', $examSubject->subject_id);

                return [$examSubject->id => $result];
            });

            $countedResults = $studentResults->where('is_absent', false);
            $totalObtained = (float) $countedResults->sum('marks_obtained');

            // Total max should always reference the configured exam-subject snapshot,
            // not just the subjects this student has results for. Otherwise a student
            // missing one paper appears to have scored 100% of a smaller denominator.
            $totalMax = (float) $examSubjects->sum('full_marks');

            $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0.0;
            $grade = $this->resultService->calculateGrade($school, $percentage);

            $recordedSubjectIds = $studentResults->pluck('subject_id')->filter()->all();
            $hasMissing = count(array_diff($expectedSubjectIds, $recordedSubjectIds)) > 0;

            $isAbsentOverall = $studentResults->isNotEmpty()
                && $studentResults->every(fn ($r) => (bool) $r->is_absent);

            return [
                'student' => $student,
                'subjects' => $subjects,
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $percentage,
                'grade' => $grade,
                'has_missing' => $hasMissing,
                'is_absent_overall' => $isAbsentOverall,
            ];
        });
    }
}
