<?php

namespace App\Http\Controllers\Student;

use App\Enums\ExamStatus;
use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Result;
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
            ->whereHas('exam', fn ($query) => $query->where('status', ExamStatus::Completed))
            ->with(['exam', 'subject'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('exam_id');

        $summary = [
            'total_exams' => $results->count(),
            'average' => round((float) Result::where('student_id', $student->id)
                ->where('school_id', $student->school_id)
                ->whereHas('exam', fn ($query) => $query->where('status', ExamStatus::Completed))
                ->avg('percentage'), 2),
            'highest' => round((float) Result::where('student_id', $student->id)
                ->where('school_id', $student->school_id)
                ->whereHas('exam', fn ($query) => $query->where('status', ExamStatus::Completed))
                ->max('percentage'), 2),
            'lowest' => round((float) Result::where('student_id', $student->id)
                ->where('school_id', $student->school_id)
                ->whereHas('exam', fn ($query) => $query->where('status', ExamStatus::Completed))
                ->min('percentage'), 2),
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

        $exam = Exam::where('school_id', $student->school_id)
            ->where('status', ExamStatus::Completed)
            ->findOrFail($id);

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
