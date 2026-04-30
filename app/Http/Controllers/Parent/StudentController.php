<?php

namespace App\Http\Controllers\Parent;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Parent\Concerns\ResolvesParent;
use App\Http\Controllers\TenantController;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Student;

class StudentController extends TenantController
{
    use ResolvesParent;

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        $studentIds = $this->ownedStudentIds($parentProfile);

        $children = Student::whereIn('id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->with(['class:id,name', 'section:id,name', 'academicYear:id,name'])
            ->get();

        // Per-child attendance aggregate — one query, indexed by student_id.
        $attendanceByStudent = Attendance::whereIn('student_id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->selectRaw('student_id, COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as present', [AttendanceStatus::Present->value])
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        // Per-child fee aggregate — one query.
        $feesByStudent = Fee::whereIn('student_id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->selectRaw('student_id, SUM(due_amount) as due, SUM(paid_amount) as paid')
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        // Decorate children with the precomputed numbers so the view doesn't iterate
        // collections. These are non-persisted attributes, just for rendering.
        foreach ($children as $child) {
            $att = $attendanceByStudent->get($child->id);
            $child->attendance_total   = (int) ($att->total ?? 0);
            $child->attendance_present = (int) ($att->present ?? 0);
            $child->attendance_pct     = $child->attendance_total > 0
                ? round(($child->attendance_present / $child->attendance_total) * 100, 1)
                : 0;

            $fee = $feesByStudent->get($child->id);
            $child->fees_due  = (float) ($fee->due ?? 0);
            $child->fees_paid = (float) ($fee->paid ?? 0);
        }

        $totalDue   = (float) $feesByStudent->sum('due');
        $totalDays  = (int) $attendanceByStudent->sum('total');
        $totalPres  = (int) $attendanceByStudent->sum('present');

        $stats = [
            'total_children' => $children->count(),
            'total_due'      => $totalDue,
            'avg_attendance' => $totalDays > 0 ? round(($totalPres / $totalDays) * 100, 1) : 0,
        ];

        return view('parent.children.index', compact('children', 'parentProfile', 'stats'));
    }

    public function show($id)
    {
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        $studentIds = $this->ownedStudentIds($parentProfile);

        $student = Student::whereIn('id', $studentIds)
            ->where('school_id', $this->getSchoolId())
            ->with(['class:id,name', 'section:id,name'])
            ->findOrFail($id);

        // Attendance summary via aggregate — same pattern as teacher portal.
        $statusCounts = $student->attendance()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total   = (int) $statusCounts->sum();
        $present = (int) ($statusCounts[AttendanceStatus::Present->value] ?? 0);
        $absent  = (int) ($statusCounts[AttendanceStatus::Absent->value] ?? 0);

        $attendanceSummary = [
            'total'      => $total,
            'present'    => $present,
            'absent'     => $absent,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
        ];

        $feeSums = Fee::where('student_id', $student->id)
            ->where('school_id', $this->getSchoolId())
            ->selectRaw('SUM(due_amount) as due, SUM(paid_amount) as paid')
            ->first();

        $feeSummary = [
            'total_due'  => (float) ($feeSums->due ?? 0),
            'total_paid' => (float) ($feeSums->paid ?? 0),
        ];

        // Bounded recent-results list for the profile page.
        $recentResults = $student->results()
            ->where('school_id', $this->getSchoolId())
            ->with(['exam:id,name,exam_type_id', 'subject:id,name'])
            ->latest('id')
            ->limit(10)
            ->get();

        return view('parent.children.show', compact(
            'student', 'parentProfile', 'attendanceSummary', 'feeSummary', 'recentResults'
        ));
    }
}
