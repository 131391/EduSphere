<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index()
    {
        $parentProfile = Auth::user()->parent;

        if (!$parentProfile) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found. Please contact the administrator.');
        }

        $children = $parentProfile->students()
            ->with(['class', 'section', 'academicYear'])
            ->get();

        return view('parent.children.index', compact('children', 'parentProfile'));
    }

    public function show($id)
    {
        $parentProfile = Auth::user()->parent;

        if (!$parentProfile) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found.');
        }

        $studentIds = $parentProfile->students()->pluck('students.id');

        $student = Student::whereIn('id', $studentIds)
            ->with(['class', 'section', 'attendance', 'fees', 'results.exam', 'results.subject'])
            ->findOrFail($id);

        $attendanceSummary = [
            'total'   => $student->attendance->count(),
            'present' => $student->attendance->filter(fn($a) => $a->status?->value === 1)->count(),
            'absent'  => $student->attendance->filter(fn($a) => $a->status?->value === 2)->count(),
        ];
        $attendanceSummary['percentage'] = $attendanceSummary['total'] > 0
            ? round(($attendanceSummary['present'] / $attendanceSummary['total']) * 100, 1)
            : 0;

        $feeSummary = [
            'total_due'  => $student->fees->sum('due_amount'),
            'total_paid' => $student->fees->sum('paid_amount'),
        ];

        return view('parent.children.show', compact('student', 'parentProfile', 'attendanceSummary', 'feeSummary'));
    }
}
