<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TimetableController extends Controller
{
    public function index()
    {
        $this->authorize('student:operate');

        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found. Please contact the administrator.');
        }

        // Load class and section for display context
        $student->load(['class', 'section']);

        return view('student.timetable.index', compact('student'));
    }
}
