<?php

namespace App\Services\School\Examination;

use App\Models\Exam;
use App\Models\School;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class ReportCardService
{
    public function __construct(protected TabulationService $tabulationService)
    {
    }

    /**
     * Generate a PDF report card for a single student in an exam.
     */
    public function generateForStudent(School $school, Exam $exam, Student $student)
    {
        $tabulation = $this->tabulationService->tabulate($school, $exam);
        $studentData = $tabulation->get($student->id);

        if (!$studentData) {
            throw new \Exception("Result data not found for student.");
        }

        $pdf = Pdf::loadView('pdf.examination.report-card', [
            'school' => $school,
            'exam' => $exam,
            'student' => $student,
            'data' => $studentData,
            'examSubjects' => $exam->examSubjects()->with('subject')->get(),
        ]);

        $pdf->setPaper('a4', 'portrait');
        
        return $pdf;
    }

    /**
     * Generate a bulk PDF containing report cards for all students in an exam.
     */
    public function generateBulk(School $school, Exam $exam)
    {
        $tabulation = $this->tabulationService->tabulate($school, $exam);
        $examSubjects = $exam->examSubjects()->with('subject')->get();

        $pdf = Pdf::loadView('pdf.examination.bulk-report-cards', [
            'school' => $school,
            'exam' => $exam,
            'tabulation' => $tabulation,
            'examSubjects' => $examSubjects,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }
}
