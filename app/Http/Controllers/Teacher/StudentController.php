<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found. Please contact the administrator.');
        }

        $classIds = $teacher->classes()->pluck('classes.id');

        $query = Student::where('school_id', $teacher->school_id)
            ->whereIn('class_id', $classIds)
            ->with(['class', 'section']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('admission_no', 'like', "%{$s}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $students = $query->orderBy('first_name')->paginate(20)->withQueryString();
        $classes  = $teacher->classes()->get();

        return view('teacher.students.index', compact('students', 'classes', 'teacher'));
    }

    public function show($id)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found.');
        }

        $classIds = $teacher->classes()->pluck('classes.id');

        $student = Student::where('school_id', $teacher->school_id)
            ->whereIn('class_id', $classIds)
            ->with(['class', 'section', 'attendance', 'results.exam', 'results.subject'])
            ->findOrFail($id);

        $attendanceSummary = [
            'total'   => $student->attendance->count(),
            'present' => $student->attendance->filter(fn($a) => $a->status?->value === 1)->count(),
            'absent'  => $student->attendance->filter(fn($a) => $a->status?->value === 2)->count(),
        ];
        $attendanceSummary['percentage'] = $attendanceSummary['total'] > 0
            ? round(($attendanceSummary['present'] / $attendanceSummary['total']) * 100, 1)
            : 0;

        return view('teacher.students.show', compact('student', 'attendanceSummary', 'teacher'));
    }
}
