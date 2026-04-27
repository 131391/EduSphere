<?php

namespace App\Http\Controllers\School\Examination;

use App\Enums\ExamStatus;
use App\Http\Controllers\TenantController;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Result;
use App\Models\Student;
use App\Services\School\Examination\ResultService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ExamController extends TenantController
{
    use HasAjaxDataTable {
        handleAjaxTable as traitHandleAjaxTable;
    }

    public function __construct(protected ResultService $resultService)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $schoolId = $this->getSchoolId();

        $this->syncExamStatuses($schoolId);

        $transformer = function (Exam $row) {
            return [
                'id' => $row->id,
                'assessment_name' => $row->display_name,
                'exam_type' => $row->examType?->name ?? 'N/A',
                'class_name' => $row->class?->name ?? 'N/A',
                'academic_year' => $row->academicYear?->name ?? 'N/A',
                'assessment_window' => $row->assessment_window ?? 'TBD',
                'status' => $row->status->label(),
                'status_value' => $row->status->value,
                'status_color' => $row->status->color(),
                'created_at' => $row->created_at->format('M d, Y'),
            ];
        };

        $query = Exam::with(['academicYear', 'class', 'examType'])
            ->where('school_id', $schoolId);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->integer('class_id'));
        }

        if ($request->filled('exam_type_id')) {
            $query->where('exam_type_id', $request->integer('exam_type_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->integer('status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('month', 'like', "%{$search}%")
                    ->orWhereHas('examType', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('class', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $stats = $this->getTableStats();

        if ($request->expectsJson() || $request->ajax()) {
            return $this->traitHandleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.examination.exams.index', [
            'initialData' => $initialData,
            'stats' => $stats,
            'classes' => ClassModel::where('school_id', $schoolId)->get(),
            'examTypes' => ExamType::where('school_id', $schoolId)->orderBy('name')->get(),
        ]);
    }

    protected function getTableStats(): array
    {
        return [
            'total' => Exam::where('school_id', $this->getSchoolId())->count(),
            'scheduled' => Exam::where('school_id', $this->getSchoolId())->where('status', ExamStatus::Scheduled)->count(),
            'ongoing' => Exam::where('school_id', $this->getSchoolId())->where('status', ExamStatus::Ongoing)->count(),
            'completed' => Exam::where('school_id', $this->getSchoolId())->where('status', ExamStatus::Completed)->count(),
        ];
    }

    public function store(Request $request)
    {
        $this->ensureSchoolActive();

        $validated = $request->validate([
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($query) => $query
                    ->where('school_id', $this->getSchoolId())
                    ->whereNull('deleted_at')),
            ],
            'exam_type_id' => [
                'required',
                Rule::exists('exam_types', 'id')->where(fn ($query) => $query
                    ->where('school_id', $this->getSchoolId())),
            ],
            'name' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $activeAcademicYear = AcademicYear::where('school_id', $this->getSchoolId())
            ->where('is_current', true)
            ->first();

        if (!$activeAcademicYear) {
            return $this->validationErrorResponse(
                $request,
                'No current academic year is configured. Please activate an academic year first.'
            );
        }

        $class = ClassModel::where('school_id', $this->getSchoolId())
            ->findOrFail($validated['class_id']);
        $examType = ExamType::where('school_id', $this->getSchoolId())
            ->findOrFail($validated['exam_type_id']);

        if (!$class->subjects()->exists()) {
            return $this->validationErrorResponse(
                $request,
                'Assign at least one subject to the selected class before scheduling an exam.'
            );
        }

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        $duplicateExists = Exam::where('school_id', $this->getSchoolId())
            ->where('academic_year_id', $activeAcademicYear->id)
            ->where('class_id', $class->id)
            ->where('exam_type_id', $examType->id)
            ->whereDate('start_date', $startDate->toDateString())
            ->whereDate('end_date', $endDate->toDateString())
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages([
                'start_date' => ['An exam with the same class, type, and schedule already exists.'],
            ]);
        }

        try {
            $exam = DB::transaction(function () use ($activeAcademicYear, $class, $examType, $validated, $startDate, $endDate) {
                $window = $this->buildAssessmentWindow($startDate, $endDate);

                $exam = Exam::create([
                    'school_id' => $this->getSchoolId(),
                    'academic_year_id' => $activeAcademicYear->id,
                    'class_id' => $class->id,
                    'exam_type_id' => $examType->id,
                    'name' => trim((string) (($validated['name'] ?? null) ?: "{$examType->name} ({$window})")),
                    'month' => $window,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'status' => $startDate->isPast() || $startDate->isToday()
                        ? ExamStatus::Ongoing
                        : ExamStatus::Scheduled,
                ]);

                $exam->ensureSubjectSnapshot();

                return $exam->load(['academicYear', 'class', 'examType', 'examSubjects']);
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam scheduled successfully!',
                    'data' => $exam,
                ]);
            }

            return redirect()->route('school.examination.exams.index')->with('success', 'Exam created successfully.');
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to schedule exam: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to schedule exam: ' . $e->getMessage());
        }
    }

    public function marksEntry()
    {
        $this->ensureSchoolActive();

        $this->syncExamStatuses($this->getSchoolId());

        $exams = Exam::with(['examType', 'class', 'examSubjects.subject'])
            ->where('school_id', $this->getSchoolId())
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();

        $exams->each(function (Exam $exam) {
            $exam->ensureSubjectSnapshot();
        });

        $exams->load(['examType', 'class', 'examSubjects.subject']);

        return view('school.examination.marks.index', compact('exams'));
    }

    public function enterMarks(Request $request)
    {
        $this->ensureSchoolActive();

        $validated = $request->validate([
            'exam_id' => [
                'required',
                Rule::exists('exams', 'id')->where(fn ($query) => $query
                    ->where('school_id', $this->getSchoolId())
                    ->whereNull('deleted_at')),
            ],
            'exam_subject_id' => 'required|exists:exam_subjects,id',
        ]);

        $exam = Exam::with(['class', 'examType', 'examSubjects.subject'])
            ->where('school_id', $this->getSchoolId())
            ->findOrFail($validated['exam_id']);
        $this->authorizeTenant($exam);

        $exam->ensureSubjectSnapshot();
        $exam->syncStatus();

        $examSubject = $exam->examSubjects()
            ->with('subject')
            ->findOrFail($validated['exam_subject_id']);

        if ($examSubject->subject_id === null) {
            throw ValidationException::withMessages([
                'exam_subject_id' => ['This exam subject is no longer available for mark entry.'],
            ]);
        }

        $class = $exam->class;

        if (!$class) {
            throw ValidationException::withMessages([
                'exam_id' => ['This exam is missing its class assignment and cannot accept marks.'],
            ]);
        }

        $students = Student::where('school_id', $this->getSchoolId())
            ->where('class_id', $class->id)
            ->active()
            ->orderByRaw('COALESCE(roll_no, 999999999)')
            ->orderBy('first_name')
            ->get();

        $results = Result::where('school_id', $this->getSchoolId())
            ->where('exam_id', $exam->id)
            ->where('subject_id', $examSubject->subject_id)
            ->where('class_id', $class->id)
            ->get()
            ->keyBy('student_id');

        return view('school.examination.marks.entry', [
            'exam' => $exam,
            'class' => $class,
            'examSubject' => $examSubject,
            'students' => $students,
            'results' => $results,
            'fullMarks' => $examSubject->full_marks,
        ]);
    }

    public function storeMarks(Request $request)
    {
        $this->ensureSchoolActive();

        $validated = $request->validate([
            'exam_id' => [
                'required',
                Rule::exists('exams', 'id')->where(fn ($query) => $query
                    ->where('school_id', $this->getSchoolId())
                    ->whereNull('deleted_at')),
            ],
            'exam_subject_id' => 'required|exists:exam_subjects,id',
            'marks' => 'required|array|min:1',
            'marks.*.student_id' => [
                'required',
                Rule::exists('students', 'id')->where(fn ($query) => $query
                    ->where('school_id', $this->getSchoolId())
                    ->whereNull('deleted_at')),
            ],
            'marks.*.marks_obtained' => 'nullable|numeric|min:0|max:999999.99',
            'marks.*.remarks' => 'nullable|string|max:500',
        ]);

        try {
            $exam = Exam::where('school_id', $this->getSchoolId())
                ->findOrFail($validated['exam_id']);
            $this->authorizeTenant($exam);

            $exam->ensureSubjectSnapshot();

            if (!$exam->examSubjects()->whereKey($validated['exam_subject_id'])->exists()) {
                throw ValidationException::withMessages([
                    'exam_subject_id' => ['The selected subject does not belong to this exam.'],
                ]);
            }

            $result = $this->resultService->saveMarks($this->school, $validated);

            if ($result['success']) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $result['message'],
                    ]);
                }

                return redirect()->route('school.examination.marks.index')->with('success', $result['message']);
            }

            throw ValidationException::withMessages([
                'marks' => [$result['message']],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save marks: ' . $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Failed to save marks: ' . $e->getMessage())->withInput();
        }
    }

    public function tabulate(Exam $exam)
    {
        $this->authorizeTenant($exam);
        $this->ensureSchoolActive();

        $exam->ensureSubjectSnapshot();
        $exam->syncStatus();

        $exam->load(['class', 'examType', 'academicYear', 'examSubjects.subject']);

        if (!$exam->class) {
            abort(422, 'This exam is missing its class assignment and cannot be tabulated.');
        }

        $examSubjects = $exam->examSubjects;

        $students = Student::where('class_id', $exam->class_id)
            ->where('school_id', $this->getSchoolId())
            ->active()
            ->orderByRaw('COALESCE(roll_no, 999999999)')
            ->orderBy('first_name')
            ->get();

        $results = Result::where('school_id', $this->getSchoolId())
            ->where('exam_id', $exam->id)
            ->get()
            ->groupBy('student_id');

        return view('school.examination.exams.tabulate', compact('exam', 'examSubjects', 'students', 'results'));
    }

    public function destroy(Exam $exam)
    {
        $this->ensureSchoolActive();
        $this->authorizeTenant($exam);

        if (Result::withTrashed()->where('exam_id', $exam->id)->exists()) {
            $message = 'This exam already has recorded marks and cannot be removed.';

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('school.examination.exams.index')->with('error', $message);
        }

        try {
            $exam->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam schedule removed successfully!',
                ]);
            }

            return redirect()->route('school.examination.exams.index')->with('success', 'Exam deleted successfully.');
        } catch (\Throwable $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove examination: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('school.examination.exams.index')->with('error', 'Failed to remove exam: ' . $e->getMessage());
        }
    }

    protected function syncExamStatuses(int $schoolId): void
    {
        Exam::with(['class', 'examSubjects'])
            ->where('school_id', $schoolId)
            ->get()
            ->each(function (Exam $exam) {
                $exam->ensureSubjectSnapshot();
                $exam->syncStatus();
            });
    }

    protected function buildAssessmentWindow(Carbon $startDate, Carbon $endDate): string
    {
        if ($startDate->isSameMonth($endDate)) {
            return $startDate->format('F Y');
        }

        if ($startDate->isSameYear($endDate)) {
            return $startDate->format('M') . ' - ' . $endDate->format('M Y');
        }

        return $startDate->format('M Y') . ' - ' . $endDate->format('M Y');
    }

    protected function validationErrorResponse(Request $request, string $message)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->with('error', $message);
    }
}
