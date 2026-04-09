<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Enums\AttendanceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Teacher profile not found. Please contact the administrator.');
        }

        // Get classes assigned to this teacher via class_subject pivot
        $classIds = $teacher->classes()->pluck('classes.id')->unique();

        $date = $request->filled('date') ? $request->date : now()->toDateString();
        $classId = $request->filled('class_id') ? $request->class_id : $classIds->first();

        $students = collect();
        $existingAttendance = collect();
        $classes = $teacher->classes()->get();

        if ($classId) {
            $students = Student::where('school_id', $teacher->school_id)
                ->where('class_id', $classId)
                ->active()
                ->with('section')
                ->orderBy('first_name')
                ->get();

            $existingAttendance = Attendance::where('school_id', $teacher->school_id)
                ->where('class_id', $classId)
                ->where('date', $date)
                ->pluck('status', 'student_id');
        }

        $statuses = AttendanceStatus::cases();

        return view('teacher.attendance.index', compact(
            'teacher', 'classes', 'students', 'existingAttendance', 'date', 'classId', 'statuses'
        ));
    }

    public function store(Request $request)
    {
        $teacher = Auth::user()->teacher;

        if (!$teacher) {
            return back()->with('error', 'Teacher profile not found.');
        }

        $request->validate([
            'date'       => 'required|date|before_or_equal:today',
            'class_id'   => 'required|integer',
            'attendance' => 'required|array|min:1',
            'attendance.*' => 'required|integer|in:1,2,3,4,5',
        ]);

        $date    = $request->date;
        $classId = $request->class_id;
        $schoolId = $teacher->school_id;

        DB::transaction(function () use ($request, $date, $classId, $schoolId, $teacher) {
            foreach ($request->attendance as $studentId => $statusValue) {
                Attendance::updateOrCreate(
                    [
                        'school_id'  => $schoolId,
                        'student_id' => $studentId,
                        'class_id'   => $classId,
                        'date'       => $date,
                    ],
                    [
                        'section_id'      => Student::find($studentId)?->section_id,
                        'academic_year_id'=> AcademicYear::where('school_id', $schoolId)->where('is_current', true)->value('id'),
                        'status'          => $statusValue,
                        'marked_by'       => Auth::id(),
                    ]
                );
            }
        });

        return redirect()->route('teacher.attendance.index', [
            'date'     => $date,
            'class_id' => $classId,
        ])->with('success', 'Attendance saved successfully for ' . \Carbon\Carbon::parse($date)->format('d M Y') . '.');
    }
}
