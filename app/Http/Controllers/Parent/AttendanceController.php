<?php

namespace App\Http\Controllers\Parent;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Parent\Concerns\ResolvesParent;
use App\Http\Controllers\TenantController;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends TenantController
{
    use ResolvesParent;

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        $children = $parentProfile->students()
            ->where('students.school_id', $this->getSchoolId())
            ->with(['class:id,name', 'section:id,name'])
            ->get();

        $selectedChildId = $request->filled('student_id') ? (int) $request->student_id : optional($children->first())->id;

        $attendanceLogs = collect();
        $stats = [
            'total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0,
            'excused' => 0, 'half_day' => 0, 'percentage' => 0,
        ];

        if ($selectedChildId && $children->contains('id', $selectedChildId)) {
            // Aggregate counts in DB rather than counting filtered collections.
            $counts = Attendance::where('student_id', $selectedChildId)
                ->where('school_id', $this->getSchoolId())
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status');

            $stats['total']    = (int) $counts->sum();
            $stats['present']  = (int) ($counts[AttendanceStatus::Present->value] ?? 0);
            $stats['absent']   = (int) ($counts[AttendanceStatus::Absent->value]  ?? 0);
            $stats['late']     = (int) ($counts[AttendanceStatus::Late->value]    ?? 0);
            $stats['excused']  = (int) ($counts[AttendanceStatus::Excused->value] ?? 0);
            $stats['half_day'] = (int) ($counts[AttendanceStatus::HalfDay->value] ?? 0);

            $stats['percentage'] = $stats['total'] > 0
                ? round((($stats['present'] + $stats['late'] + ($stats['half_day'] * 0.5)) / $stats['total']) * 100, 1)
                : 0;

            // Paginate the list — typical school year is 200+ days per child.
            $attendanceLogs = Attendance::where('student_id', $selectedChildId)
                ->where('school_id', $this->getSchoolId())
                ->orderByDesc('date')
                ->paginate(30)
                ->withQueryString();
        }

        return view('parent.attendance.index', compact('parentProfile', 'children', 'selectedChildId', 'attendanceLogs', 'stats'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        $validated = $request->validate([
            'student_id' => 'required|integer',
            'from'       => 'nullable|date',
            'to'         => 'nullable|date|after_or_equal:from',
        ]);

        $studentIds = $this->ownedStudentIds($parentProfile);

        if (!$studentIds->contains((int) $validated['student_id'])) {
            abort(403, 'You do not own this student.');
        }

        $from = $validated['from'] ?? now()->subYear()->toDateString();
        $to   = $validated['to']   ?? now()->toDateString();

        $rows = Attendance::where('student_id', $validated['student_id'])
            ->where('school_id', $this->getSchoolId())
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->get(['date', 'status', 'remarks']);

        $filename = 'attendance-' . $validated['student_id'] . '-' . $from . '-to-' . $to . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Day', 'Status', 'Remarks']);

            foreach ($rows as $r) {
                $date = \Carbon\Carbon::parse($r->date);
                fputcsv($out, [
                    $date->format('Y-m-d'),
                    $date->format('l'),
                    $r->status?->label() ?? '—',
                    $r->remarks ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
