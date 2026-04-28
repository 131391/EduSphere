<?php

namespace App\Services\School\Examination;

use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\School;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ExamImportService
{
    public function __construct(protected ResultService $resultService)
    {
    }

    /**
     * Generate a CSV template for mark entry.
     */
    public function generateTemplate(Exam $exam, ExamSubject $examSubject): string
    {
        $exam->load('class');
        $students = Student::where('school_id', $exam->school_id)
            ->where('class_id', $exam->class_id)
            ->active()
            ->orderByRaw('COALESCE(roll_no, 999999999)')
            ->orderBy('first_name')
            ->get();

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Student ID', 'Roll No', 'Student Name', 'Marks Obtained (Max: ' . $examSubject->full_marks . ')', 'Is Absent (1/0)', 'Remarks']);

        foreach ($students as $student) {
            fputcsv($handle, [
                $student->id,
                $student->roll_no ?? 'N/A',
                $student->full_name,
                '',
                '0',
                ''
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Process an uploaded marks CSV.
     */
    public function import(School $school, Exam $exam, ExamSubject $examSubject, $filePath): array
    {
        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle); // Skip header

        $marks = [];
        $line = 2;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) continue;

            $studentId = $row[0];
            $marksObtained = $row[3];
            $isAbsent = (bool) $row[4];
            $remarks = $row[5] ?? '';

            $marks[] = [
                'student_id' => $studentId,
                'marks_obtained' => $marksObtained,
                'is_absent' => $isAbsent,
                'remarks' => $remarks,
            ];

            $line++;
        }

        fclose($handle);

        if (empty($marks)) {
            throw ValidationException::withMessages(['file' => ['The uploaded file contains no data.']]);
        }

        return $this->resultService->saveMarks($school, [
            'exam_id' => $exam->id,
            'exam_subject_id' => $examSubject->id,
            'marks' => $marks
        ]);
    }
}
