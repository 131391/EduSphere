<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $parentProfile = Auth::user()->parent;

        if (!$parentProfile) {
            return redirect()->route('parent.dashboard')->with('error', 'Parent profile not found.');
        }

        $children = $parentProfile->students()->with(['class', 'section'])->get();
        $selectedChildId = $request->filled('student_id') ? $request->student_id : optional($children->first())->id;

        $attendanceLogs = collect();
        $stats = [
            'total' => 0,
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'half_day' => 0,
            'percentage' => 0
        ];

        if ($selectedChildId && $children->contains('id', $selectedChildId)) {
            $attendanceLogs = Attendance::where('student_id', $selectedChildId)
                ->with(['academicYear'])
                ->orderByDesc('date')
                ->get();

            $stats['total'] = $attendanceLogs->count();
            $stats['present'] = $attendanceLogs->filter(fn($a) => $a->status?->value === 1)->count();
            $stats['absent'] = $attendanceLogs->filter(fn($a) => $a->status?->value === 2)->count();
            $stats['late'] = $attendanceLogs->filter(fn($a) => $a->status?->value === 3)->count();
            $stats['half_day'] = $attendanceLogs->filter(fn($a) => $a->status?->value === 4)->count();
            
            $stats['percentage'] = $stats['total'] > 0 
                ? round((($stats['present'] + $stats['late'] + ($stats['half_day'] * 0.5)) / $stats['total']) * 100, 1) 
                : 0;
        }

        return view('parent.attendance.index', compact('parentProfile', 'children', 'selectedChildId', 'attendanceLogs', 'stats'));
    }
}

