<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found. Please contact the administrator.');
        }

        $records = Attendance::where('student_id', $student->id)
            ->where('school_id', $student->school_id)
            ->orderByDesc('date')
            ->get();

        $summary = [
            'total'   => $records->count(),
            'present' => $records->where('status.value', 1)->count(),
            'absent'  => $records->where('status.value', 2)->count(),
            'late'    => $records->where('status.value', 3)->count(),
        ];

        // Re-count using enum comparison
        $summary['present'] = $records->filter(fn($r) => $r->status?->value === 1)->count();
        $summary['absent']  = $records->filter(fn($r) => $r->status?->value === 2)->count();
        $summary['late']    = $records->filter(fn($r) => $r->status?->value === 3)->count();
        $summary['percentage'] = $summary['total'] > 0
            ? round(($summary['present'] / $summary['total']) * 100, 1)
            : 0;

        $monthly = $records->groupBy(fn($r) => $r->date->format('F Y'));

        return view('student.attendance.index', compact('records', 'summary', 'monthly', 'student'));
    }
}
