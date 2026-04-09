<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found. Please contact the administrator.');
        }

        $results = Result::where('student_id', $student->id)
            ->where('school_id', $student->school_id)
            ->with(['exam', 'subject'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn($r) => optional($r->exam)->name ?? 'Unknown Exam');

        $summary = [
            'total_exams' => $results->count(),
            'average'     => round((float) Result::where('student_id', $student->id)->avg('percentage'), 2),
            'highest'     => round((float) Result::where('student_id', $student->id)->max('percentage'), 2),
            'lowest'      => round((float) Result::where('student_id', $student->id)->min('percentage'), 2),
        ];

        return view('student.results.index', compact('results', 'summary', 'student'));
    }

    public function show($id)
    {
        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found.');
        }

        $exam = Exam::where('school_id', $student->school_id)->findOrFail($id);

        $results = Result::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->where('school_id', $student->school_id)
            ->with(['subject'])
            ->get();

        $totalMarks    = $results->sum('total_marks');
        $obtainedMarks = $results->sum('marks_obtained');
        $percentage    = $totalMarks > 0 ? round(($obtainedMarks / $totalMarks) * 100, 2) : 0;

        return view('student.results.show', compact('exam', 'results', 'student', 'totalMarks', 'obtainedMarks', 'percentage'));
    }
}
