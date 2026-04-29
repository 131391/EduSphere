<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\TenantController;
use App\Models\Timetable;
use Illuminate\Support\Facades\Auth;

class TimetableController extends TenantController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $periods = Timetable::with(['class', 'section', 'subject'])
            ->where('school_id', $this->getSchoolId())
            ->where('teacher_id', $teacher->id)
            ->orderByRaw("FIELD(day, 'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")
            ->orderBy('start_time')
            ->get();

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $byDay = $periods->groupBy('day');

        $todayKey = strtolower(now()->format('l'));

        return view('teacher.timetable.index', [
            'teacher'  => $teacher,
            'days'     => $days,
            'byDay'    => $byDay,
            'todayKey' => $todayKey,
            'total'    => $periods->count(),
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
