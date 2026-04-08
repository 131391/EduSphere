<?php

namespace App\Services\School\Examination;

use App\Models\Result;
use App\Models\Grade;
use App\Models\School;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultService
{
    /**
     * Save marks for multiple students in a specific exam and subject
     */
    public function saveMarks(School $school, array $data): array
    {
        $examId = $data['exam_id'];
        $subjectId = $data['subject_id'];
        $classId = $data['class_id'];
        $academicYearId = $data['academic_year_id'];
        $totalMarks = $data['total_marks'];
        $marksData = $data['marks']; // Array of ['student_id' => X, 'marks_obtained' => Y]

        DB::beginTransaction();
        try {
            foreach ($marksData as $mark) {
                $obtained = floatval($mark['marks_obtained']);
                $percentage = ($totalMarks > 0) ? ($obtained / $totalMarks) * 100 : 0;
                $grade = $this->calculateGrade($school, $percentage);

                Result::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'student_id' => $mark['student_id'],
                        'exam_id' => $examId,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'class_id' => $classId,
                        'academic_year_id' => $academicYearId,
                        'marks_obtained' => $obtained,
                        'total_marks' => $totalMarks,
                        'percentage' => $percentage,
                        'grade' => $grade,
                        'remarks' => $mark['remarks'] ?? null,
                    ]
                );
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Marks saved successfully.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark Entry Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while saving marks: ' . $e->getMessage()
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
