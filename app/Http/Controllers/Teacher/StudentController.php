<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\TenantController;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends TenantController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $classIds = $teacher->classes()->pluck('classes.id');

        $base = Student::where('school_id', $this->getSchoolId())
            ->whereIn('class_id', $classIds);

        $query = (clone $base)->with(['class', 'section']);

        if ($request->filled('search')) {
            $s = trim($request->string('search')->toString());
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('admission_no', 'like', "%{$s}%")
                  ->orWhere('roll_no', 'like', "%{$s}%");
            });
        }

        if ($request->filled('class_id') && $classIds->contains((int) $request->class_id)) {
            $query->where('class_id', (int) $request->class_id);
        }

        $students = $query->orderBy('first_name')->paginate(20)->withQueryString();
        $classes  = $teacher->classes()->get();

        // One aggregated query for stats instead of 3 separate counts.
        $genderCounts = (clone $base)
            ->selectRaw('gender, COUNT(*) as c')
            ->groupBy('gender')
            ->pluck('c', 'gender');

        $stats = [
            'total'   => (int) $genderCounts->sum(),
            'male'    => (int) ($genderCounts[\App\Enums\Gender::Male->value] ?? 0),
            'female'  => (int) ($genderCounts[\App\Enums\Gender::Female->value] ?? 0),
            'classes' => $classIds->count(),
        ];

        return view('teacher.students.index', compact('students', 'classes', 'teacher', 'stats'));
    }

    public function show($id)
    {
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $classIds = $teacher->classes()->pluck('classes.id');

        $student = Student::where('school_id', $this->getSchoolId())
            ->whereIn('class_id', $classIds)
            ->with(['class', 'section', 'results.exam', 'results.subject'])
            ->findOrFail($id);

        // Pull attendance summary as aggregated counts rather than hydrating every row.
        $statusCounts = $student->attendance()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total   = (int) $statusCounts->sum();
        $present = (int) ($statusCounts[\App\Enums\AttendanceStatus::Present->value] ?? 0);
        $absent  = (int) ($statusCounts[\App\Enums\AttendanceStatus::Absent->value] ?? 0);

        $attendanceSummary = [
            'total'      => $total,
            'present'    => $present,
            'absent'     => $absent,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
        ];

        return view('teacher.students.show', compact('student', 'attendanceSummary', 'teacher'));
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
