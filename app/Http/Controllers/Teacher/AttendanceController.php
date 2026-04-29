<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\TenantController;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Enums\AttendanceStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceController extends TenantController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $classIds = $teacher->classes()->pluck('classes.id')->unique();

        $date = $request->filled('date') ? $request->date : now()->toDateString();
        $classId = $request->filled('class_id') ? (int) $request->class_id : $classIds->first();

        // Reject class IDs the teacher isn't assigned to.
        if ($classId && !$classIds->contains($classId)) {
            $classId = $classIds->first();
        }

        $students = collect();
        $existingAttendance = collect();
        $classes = $teacher->classes()->get();

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
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $validated = $request->validate([
            'date'         => 'required|date|before_or_equal:today',
            'class_id'     => 'required|integer',
            'attendance'   => 'required|array|min:1',
            'attendance.*' => ['required', 'integer', 'in:' . implode(',', array_column(AttendanceStatus::cases(), 'value'))],
        ]);

        $date     = $validated['date'];
        $classId  = (int) $validated['class_id'];
        $schoolId = $this->getSchoolId();

        $classIds = $teacher->classes()->pluck('classes.id');
        if (!$classIds->contains($classId)) {
            return back()->with('error', 'You are not authorized to mark attendance for this class.');
        }

        $studentSections = Student::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->pluck('section_id', 'id');

        // Drop any submitted student IDs that don't belong to this class+school.
        $payload = collect($validated['attendance'])
            ->filter(fn ($status, $studentId) => $studentSections->has((int) $studentId));

        if ($payload->isEmpty()) {
            return back()->with('error', 'No valid students were submitted for this class.');
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

    protected function currentTeacherOrFail()
    {
        $teacher = optional(Auth::user())->teacher;

        if (!$teacher || (int) $teacher->school_id !== (int) $this->getSchoolId()) {
            abort(403, 'Teacher profile not found for the current school.');
        }

        return $teacher;
    }
}
