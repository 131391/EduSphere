<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Teacher\Concerns\ResolvesTeacher;
use App\Http\Controllers\TenantController;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Enums\AttendanceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends TenantController
{
    use ResolvesTeacher;

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->authorize('teacher:operate');
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $classIds = $teacher->classes()->pluck('classes.id')->unique()->values();

        $date = $request->filled('date') ? $request->date : now()->toDateString();
        $classId = $request->filled('class_id') ? (int) $request->class_id : $classIds->first();

        // Reject class IDs the teacher isn't assigned to.
        if ($classId && !$classIds->contains($classId)) {
            $classId = $classIds->first();
        }

        $students = collect();
        $existingAttendance = collect();
        $classes = $teacher->classes()
            ->select('classes.*')
            ->distinct()
            ->orderBy('classes.name')
            ->get();

        if ($classId) {
            $students = Student::where('school_id', $this->getSchoolId())
                ->where('class_id', $classId)
                ->active()
                ->with('section')
                ->orderByRaw('COALESCE(roll_no, 999999999)')
                ->orderBy('first_name')
                ->get();

            $existingAttendance = Attendance::where('school_id', $this->getSchoolId())
                ->where('class_id', $classId)
                ->where('date', $date)
                ->pluck('status', 'student_id');
        }

        $statuses = AttendanceStatus::cases();

        // Stats: status is cast to AttendanceStatus enum, so unwrap with ?->value.
        $statusCounts = $existingAttendance->countBy(fn ($s) => $s?->value);
        $stats = [
            'present'  => (int) ($statusCounts[AttendanceStatus::Present->value] ?? 0),
            'absent'   => (int) ($statusCounts[AttendanceStatus::Absent->value] ?? 0),
            'others'   => $existingAttendance->filter(fn ($s) => $s?->value > 2)->count(),
            'total'    => $students->count(),
            'unmarked' => max(0, $students->count() - $existingAttendance->count()),
        ];

        return view('teacher.attendance.index', compact(
            'teacher', 'classes', 'students', 'existingAttendance', 'date', 'classId', 'statuses', 'stats'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('teacher:operate');
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $validated = $request->validate([
            'date'         => 'required|date|before_or_equal:today',
            'class_id'     => 'required|integer',
            'attendance'   => 'required|array|min:1',
            // Allow blank entries: an unticked student is simply skipped, not crashed.
            'attendance.*' => ['nullable', 'integer', 'in:' . implode(',', array_column(AttendanceStatus::cases(), 'value'))],
        ]);

        $date     = $validated['date'];
        $classId  = (int) $validated['class_id'];
        $schoolId = $this->getSchoolId();

        $classIds = $teacher->classes()->pluck('classes.id')->unique()->values();
        if (!$classIds->contains($classId)) {
            return back()->with('error', 'You are not authorized to mark attendance for this class.');
        }

        $studentSections = Student::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->pluck('section_id', 'id');

        // Skip students the teacher didn't pick a status for, then drop any
        // submitted IDs that don't belong to this class+school.
        $payload = collect($validated['attendance'])
            ->filter(fn ($status) => $status !== null && $status !== '')
            ->filter(fn ($status, $studentId) => $studentSections->has((int) $studentId));

        if ($payload->isEmpty()) {
            return back()->with('error', 'Please mark at least one student before saving.');
        }

        $academicYearId = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->value('id');

        try {
            DB::transaction(function () use ($payload, $date, $classId, $schoolId, $studentSections, $academicYearId) {
                foreach ($payload as $studentId => $statusValue) {
                    Attendance::updateOrCreate(
                        [
                            'school_id'  => $schoolId,
                            'student_id' => $studentId,
                            'class_id'   => $classId,
                            'date'       => $date,
                        ],
                        [
                            'section_id'       => $studentSections[$studentId] ?? null,
                            'academic_year_id' => $academicYearId,
                            'status'           => $statusValue,
                            'marked_by'        => Auth::id(),
                        ]
                    );
                }
            });
        } catch (\Throwable $e) {
            Log::error('Teacher attendance save failed', [
                'teacher_id' => $teacher->id,
                'class_id'   => $classId,
                'date'       => $date,
                'error'      => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to save attendance. Please try again.');
        }

        return redirect()->route('teacher.attendance.index', [
            'date'     => $date,
            'class_id' => $classId,
        ])->with('success', 'Attendance saved successfully for ' . \Carbon\Carbon::parse($date)->format('d M Y') . '.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('teacher:operate');
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $validated = $request->validate([
            'class_id'   => 'required|integer',
            'from'       => 'required|date',
            'to'         => 'required|date|after_or_equal:from',
        ]);

        $classIds = $teacher->classes()->pluck('classes.id')->unique()->values();
        $classId  = (int) $validated['class_id'];

        if (!$classIds->contains($classId)) {
            abort(403, 'You are not authorized to export this class.');
        }

        $schoolId = $this->getSchoolId();
        $from     = $validated['from'];
        $to       = $validated['to'];

        $students = Student::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->active()
            ->orderByRaw('COALESCE(roll_no, 999999999)')
            ->orderBy('first_name')
            ->get();

        // Pull all attendance rows in range, keyed by [student_id][YYYY-MM-DD].
        $rows = Attendance::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereBetween('date', [$from, $to])
            ->get(['student_id', 'date', 'status']);

        $byStudent = [];
        foreach ($rows as $r) {
            $key = \Illuminate\Support\Carbon::parse($r->date)->toDateString();
            $byStudent[$r->student_id][$key] = $r->status;
        }

        $period = \Carbon\CarbonPeriod::create($from, $to);
        $dates  = collect($period)->map(fn ($d) => $d->toDateString());

        $filename = sprintf('attendance-%d-%s-to-%s.csv', $classId, $from, $to);

        return response()->streamDownload(function () use ($students, $dates, $byStudent) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            $header = ['Roll No', 'Admission No', 'Name'];
            foreach ($dates as $d) {
                $header[] = \Illuminate\Support\Carbon::parse($d)->format('d-M');
            }
            $header[] = 'Present';
            $header[] = 'Absent';
            fputcsv($out, $header);

            foreach ($students as $s) {
                $line = [$s->roll_no, $s->admission_no, $s->full_name];
                $present = 0;
                $absent  = 0;
                foreach ($dates as $d) {
                    $status = $byStudent[$s->id][$d] ?? null;
                    if ($status === null) {
                        $line[] = '-';
                        continue;
                    }
                    $val = $status?->value ?? $status;
                    $line[] = match ((int) $val) {
                        AttendanceStatus::Present->value => 'P',
                        AttendanceStatus::Absent->value  => 'A',
                        default                          => (string) $val,
                    };
                    if ((int) $val === AttendanceStatus::Present->value) $present++;
                    if ((int) $val === AttendanceStatus::Absent->value)  $absent++;
                }
                $line[] = $present;
                $line[] = $absent;
                fputcsv($out, $line);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
