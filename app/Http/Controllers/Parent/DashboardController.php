<?php

namespace App\Http\Controllers\Parent;

use App\Enums\AttendanceStatus;
use App\Enums\ExamStatus;
use App\Http\Controllers\Parent\Concerns\ResolvesParent;
use App\Http\Controllers\TenantController;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Result;
use App\Models\Student;

class DashboardController extends TenantController
{
    use ResolvesParent;

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->authorize('parent:operate');
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        $studentIds = $this->ownedStudentIds($parentProfile);

        // Children list — light load, just relationships needed for display.
        $children = Student::whereIn('id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->with(['class:id,name', 'section:id,name'])
            ->get();

        // Aggregate stats via DB-level COUNT/SUM rather than collection iteration.
        $totalDue = (float) Fee::whereIn('student_id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->sum('due_amount');

        $attendanceCounts = Attendance::whereIn('student_id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $totalDays   = (int) $attendanceCounts->sum();
        $presentDays = (int) ($attendanceCounts[AttendanceStatus::Present->value] ?? 0);
        $avgAttendance = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

        // Upcoming dues — bounded list, eager-loaded.
        $upcomingFees = Fee::whereIn('student_id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->where('due_amount', '>', 0)
            ->with(['feeName:id,name', 'student:id,first_name,last_name'])
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->map(function ($fee) {
                $fee->student_name = $fee->student?->full_name;
                return $fee;
            });

        // Recent results from completed exams — bounded, eager-loaded.
        $recentResults = Result::whereIn('student_id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->whereHas('exam', fn ($q) => $q->where('status', ExamStatus::Completed))
            ->with(['exam:id,name,exam_type_id', 'exam.examType:id,name', 'subject:id,name', 'student:id,first_name,last_name'])
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(function ($result) {
                $result->student_name = $result->student?->full_name;
                return $result;
            });

        $stats = [
            'total_children' => $children->count(),
            'total_due'      => $totalDue,
            'avg_attendance' => $avgAttendance,
            'recent_results' => $recentResults,
            'upcoming_fees'  => $upcomingFees,
        ];

        return view('parent.dashboard', compact('parentProfile', 'children', 'stats'));
    }
}
