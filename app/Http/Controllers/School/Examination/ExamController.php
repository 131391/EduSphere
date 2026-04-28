<?php

namespace App\Http\Controllers\School\Examination;

use App\Enums\ExamStatus;
use App\Http\Controllers\TenantController;
use App\Http\Requests\School\Examination\AssignExamSubjectTeacherRequest;
use App\Http\Requests\School\Examination\StoreExamRequest;
use App\Http\Requests\School\Examination\StoreMarksRequest;
use App\Http\Requests\School\Examination\UpdateExamRequest;
use App\Models\ExamSubject;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Result;
use App\Models\Student;
use App\Services\School\Examination\ExamService;
use App\Services\School\Examination\ResultService;
use App\Services\School\Examination\TabulationService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExamController extends TenantController
{
    use HasAjaxDataTable {
        handleAjaxTable as traitHandleAjaxTable;
    }

    public function __construct(
        protected ExamService $examService,
        protected ResultService $resultService,
        protected TabulationService $tabulationService,
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Exam::class);
        $this->ensureSchoolActive();

        $schoolId = $this->getSchoolId();
        // Status sync is now driven by the `exams:sync-statuses` scheduled command
        // (registered in routes/console.php). Listing endpoints stay read-only.

        $transformer = function (Exam $row) {
            return [
                'id' => $row->id ?? null,
                'assessment_name' => $row->display_name ?? 'N/A',
                'exam_type' => $row->examType?->name ?? 'N/A',
                'class_name' => $row->class?->name ?? 'N/A',
                'academic_year' => $row->academicYear?->name ?? 'N/A',
                'assessment_window' => $row->assessment_window ?? 'TBD',
                'status' => $row->status?->label() ?? 'N/A',
                'status_value' => $row->status?->value ?? null,
                'status_color' => $row->status?->color() ?? 'gray',
                'created_at' => $row->created_at?->format('M d, Y') ?? 'N/A',
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
        $schoolId = $this->getSchoolId();

        return [
            'total' => Exam::where('school_id', $schoolId)->count(),
            'scheduled' => Exam::where('school_id', $schoolId)->where('status', ExamStatus::Scheduled)->count(),
            'ongoing' => Exam::where('school_id', $schoolId)->where('status', ExamStatus::Ongoing)->count(),
            'completed' => Exam::where('school_id', $schoolId)->where('status', ExamStatus::Completed)->count(),
        ];
    }

    public function store(StoreExamRequest $request)
    {
        $this->authorize('create', Exam::class);
        $this->ensureSchoolActive();

        try {
            $exam = $this->examService->schedule($this->school, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam scheduled successfully!',
                    'data' => $exam,
                ]);
            }

            return redirect()->route('school.examination.exams.index')->with('success', 'Exam created successfully.');
        } catch (ValidationException $e) {
            throw $e;
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

    public function update(UpdateExamRequest $request, Exam $exam)
    {
        $this->authorize('update', $exam);
        $this->ensureSchoolActive();
        $this->authorizeTenant($exam);

        try {
            $exam = $this->examService->update($exam, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam updated successfully!',
                    'data' => $exam,
                ]);
            }

            return redirect()->route('school.examination.exams.index')->with('success', 'Exam updated successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update exam: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to update exam: ' . $e->getMessage());
        }
    }

    /**
     * Return exam data for edit modal (JSON).
     */
    public function edit(Exam $exam)
    {
        $this->authorize('view', $exam);
        $this->ensureSchoolActive();
        $this->authorizeTenant($exam);

        return response()->json([
            'id' => $exam->id,
            'exam_type_id' => $exam->exam_type_id,
            'class_id' => $exam->class_id,
            'name' => $exam->name,
            'start_date' => $exam->start_date ? \Carbon\Carbon::parse($exam->start_date)->format('Y-m-d') : null,
            'end_date' => $exam->end_date ? \Carbon\Carbon::parse($exam->end_date)->format('Y-m-d') : null,
            'status' => $exam->status->value,
            'status_label' => $exam->status->label(),
        ]);
    }

    public function cancel(Request $request, Exam $exam)
    {
        $this->authorize('cancel', $exam);
        $this->ensureSchoolActive();
        $this->authorizeTenant($exam);

        $this->examService->cancel($exam);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Exam cancelled successfully.',
            ]);
        }

        return redirect()->route('school.examination.exams.index')->with('success', 'Exam cancelled.');
    }

    public function lock(Request $request, Exam $exam)
    {
        $this->authorize('lock', $exam);
        $this->ensureSchoolActive();
        $this->authorizeTenant($exam);

        $this->examService->lock($exam);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Results published and locked.',
            ]);
        }

        return redirect()->route('school.examination.exams.index')->with('success', 'Results locked.');
    }

    public function marksEntry()
    {
        $this->ensureSchoolActive();

        // Read-only listing. Subject snapshot is ensured at scheduling time and on
        // the per-exam mark-entry endpoint, not here.
        $exams = Exam::with(['examType', 'class', 'examSubjects.subject'])
            ->where('school_id', $this->getSchoolId())
            ->orderByDesc('start_date')
            ->orderByDesc('created_at')
            ->get();

        return view('school.examination.marks.index', compact('exams'));
    }

    public function enterMarks(Request $request)
    {
        $this->ensureSchoolActive();

        $validated = $request->validate([
            'exam_id' => 'required|integer',
            'exam_subject_id' => 'required|integer',
        ]);

        $exam = Exam::with(['class', 'examType', 'examSubjects.subject'])
            ->where('school_id', $this->getSchoolId())
            ->findOrFail($validated['exam_id']);

        $this->authorize('enterMarks', $exam);
        $this->authorizeTenant($exam);

        // Snapshot + status are state-changing; only run on the actual entry path.
        $exam->ensureSubjectSnapshot();
        $exam->syncStatus();

        if (!$exam->isMarkEntryAllowed()) {
            return redirect()->route('school.examination.marks.index')
                ->with('error', 'Mark entry is closed for this exam (status: ' . $exam->status->label() . ').');
        }

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

    public function storeMarks(StoreMarksRequest $request)
    {
        $this->ensureSchoolActive();

        $validated = $request->validated();

        $exam = Exam::where('school_id', $this->getSchoolId())
            ->findOrFail($validated['exam_id']);

        $this->authorize('enterMarks', $exam);
        $this->authorizeTenant($exam);

        $examSubject = ExamSubject::where('exam_id', $exam->id)
            ->findOrFail($validated['exam_subject_id']);

        $this->authorize('enterSubjectMarks', [$exam, $examSubject]);

        try {
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

    /**
     * Assign or clear a teacher on a single exam subject row.
     */
    public function assignSubjectTeacher(AssignExamSubjectTeacherRequest $request, Exam $exam, ExamSubject $examSubject)
    {
        $this->authorize('update', $exam);
        $this->ensureSchoolActive();
        $this->authorizeTenant($exam);

        if ((int) $examSubject->exam_id !== (int) $exam->id) {
            abort(404, 'Exam subject does not belong to this exam.');
        }

        $examSubject->forceFill([
            'teacher_id' => $request->validated()['teacher_id'] ?? null,
        ])->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Teacher assignment updated.',
                'data' => $examSubject->fresh()->load('teacher'),
            ]);
        }

        return back()->with('success', 'Teacher assignment updated.');
    }

    public function tabulate(Exam $exam)
    {
        $this->authorize('view', $exam);
        $this->authorizeTenant($exam);
        $this->ensureSchoolActive();

        // Snapshot is idempotent and protects legacy exams that pre-date the snapshot
        // logic. Status sync runs from the scheduled command, not from this read.
        $exam->ensureSubjectSnapshot();

        $exam->load(['class', 'examType', 'academicYear', 'examSubjects.subject']);

        if (!$exam->class) {
            abort(422, 'This exam is missing its class assignment and cannot be tabulated.');
        }

        $rows = $this->tabulationService->tabulate($this->school, $exam);
        $examSubjects = $exam->examSubjects;
        $students = $rows->map(fn ($row) => $row['student'])->values();

        return view('school.examination.exams.tabulate', [
            'exam' => $exam,
            'examSubjects' => $examSubjects,
            'students' => $students,
            'rows' => $rows,
        ]);
    }

    public function destroy(Request $request, Exam $exam)
    {
        $this->authorize('delete', $exam);
        $this->ensureSchoolActive();
        $this->authorizeTenant($exam);

        try {
            $this->examService->delete($exam);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Cannot delete exam.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('school.examination.exams.index')->with('error', $message);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove examination: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('school.examination.exams.index')->with('error', 'Failed to remove exam: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Exam schedule removed successfully!',
            ]);
        }

        return redirect()->route('school.examination.exams.index')->with('success', 'Exam deleted successfully.');
    }
}
