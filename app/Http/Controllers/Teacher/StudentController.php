<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Teacher\Concerns\ResolvesTeacher;
use App\Http\Controllers\TenantController;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends TenantController
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

        $base = Student::where('school_id', $this->getSchoolId())
            ->whereIn('class_id', $classIds)
            ->active();

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
        $classes  = $teacher->classes()
            ->select('classes.*')
            ->distinct()
            ->orderBy('classes.name')
            ->get();

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

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('teacher:operate');
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $classIds = $teacher->classes()->pluck('classes.id');

        $query = Student::where('school_id', $this->getSchoolId())
            ->whereIn('class_id', $classIds)
            ->with(['class', 'section']);

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

        $students = $query->orderBy('class_id')->orderBy('first_name')->get();

        $filename = 'my-students-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($students) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel reads non-ASCII correctly.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Admission No', 'Roll No', 'Name', 'Class', 'Section', 'Gender', 'Mobile', 'Date of Birth']);

            foreach ($students as $s) {
                fputcsv($out, [
                    $s->admission_no,
                    $s->roll_no,
                    $s->full_name,
                    optional($s->class)->name,
                    optional($s->section)->name,
                    $s->gender?->label() ?? '',
                    $s->mobile_no,
                    optional($s->dob)->format('d-m-Y'),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function show($id)
    {
        $this->authorize('teacher:operate');
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $classIds = $teacher->classes()->pluck('classes.id')->unique()->values();

        $student = Student::where('school_id', $this->getSchoolId())
            ->whereIn('class_id', $classIds)
            ->with(['class', 'section'])
            ->findOrFail($id);

        // Pull attendance summary as aggregated counts rather than hydrating every row.
        $statusCounts = $student->attendance()
            ->where('school_id', $this->getSchoolId())
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

        // Paginated, filterable results (was previously eager-loaded all-at-once).
        $resultsQuery = $student->results()
            ->where('school_id', $this->getSchoolId())
            ->with(['exam.examType', 'subject'])
            ->latest('id');

        if (request()->filled('exam_id')) {
            $resultsQuery->where('exam_id', (int) request()->exam_id);
        }

        $results = $resultsQuery->paginate(15)->withQueryString();

        $examOptions = Exam::where('school_id', $this->getSchoolId())
            ->whereIn('id', $student->results()->where('school_id', $this->getSchoolId())->select('exam_id')->distinct())
            ->orderBy('id', 'desc')
            ->get(['id', 'name']);

        return view('teacher.students.show', compact(
            'student', 'attendanceSummary', 'teacher', 'results', 'examOptions'
        ));
    }
}
