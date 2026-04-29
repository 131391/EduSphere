<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\TenantController;
use App\Models\Attendance;
use App\Models\ExamSubject;
use App\Models\Result;
use App\Models\Student;
use App\Models\Timetable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends TenantController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $schoolId = $this->getSchoolId();
        $today    = Carbon::today();
        $classIds = $teacher->classes()->pluck('classes.id');

        // ── Students ────────────────────────────────────────────────────────
        $studentCount = Student::where('school_id', $schoolId)
            ->whereIn('class_id', $classIds)
            ->count();

        // ── Today's attendance: how many of my classes are marked? ──────────
        $classesMarkedToday = $classIds->isEmpty()
            ? 0
            : Attendance::where('school_id', $schoolId)
                ->whereIn('class_id', $classIds)
                ->whereDate('date', $today)
                ->distinct('class_id')
                ->count('class_id');

        $todaysAttendanceTotals = $classIds->isEmpty()
            ? collect()
            : Attendance::where('school_id', $schoolId)
                ->whereIn('class_id', $classIds)
                ->whereDate('date', $today)
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status');

        $presentToday = (int) ($todaysAttendanceTotals[AttendanceStatus::Present->value] ?? 0);
        $absentToday  = (int) ($todaysAttendanceTotals[AttendanceStatus::Absent->value] ?? 0);

        // ── Mark entry: open assignments, and subjects with no results yet ──
        $assignments = ExamSubject::with(['exam.examType', 'exam.class', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->whereHas('exam', fn ($q) => $q
                ->where('school_id', $schoolId)
                ->whereNull('deleted_at'))
            ->get()
            ->filter(fn (ExamSubject $row) => $row->exam && $row->exam->isMarkEntryAllowed())
            ->values();

        $pendingMarks = $assignments->filter(function (ExamSubject $row) use ($schoolId) {
            $hasAny = Result::where('school_id', $schoolId)
                ->where('exam_id', $row->exam_id)
                ->where('subject_id', $row->subject_id)
                ->exists();

            return !$hasAny;
        })->count();

        // ── Today's timetable for this teacher ──────────────────────────────
        $todayKey = strtolower($today->format('l')); // monday, tuesday, ...
        $todaysSchedule = Timetable::with(['class', 'section', 'subject'])
            ->where('school_id', $schoolId)
            ->where('teacher_id', $teacher->id)
            ->where('day', $todayKey)
            ->orderBy('start_time')
            ->get();

        $stats = [
            'students'             => $studentCount,
            'classes_assigned'     => $classIds->count(),
            'classes_marked_today' => $classesMarkedToday,
            'present_today'        => $presentToday,
            'absent_today'         => $absentToday,
            'open_assignments'     => $assignments->count(),
            'pending_marks'        => $pendingMarks,
            'today_periods'        => $todaysSchedule->count(),
        ];

        return view('teacher.dashboard', [
            'title'           => 'Teacher Dashboard',
            'teacher'         => $teacher,
            'stats'           => $stats,
            'todaysSchedule'  => $todaysSchedule,
            'openAssignments' => $assignments->take(5),
        ]);
    }

    protected function currentTeacherOrFail()
    {
        $teacher = optional(Auth::user())->teacher;

        if (!$teacher || (int) $teacher->school_id !== (int) $this->getSchoolId()) {
            abort(403, 'Teacher profile not found for the current school.');
        }

        return $teacher;
    }
}
