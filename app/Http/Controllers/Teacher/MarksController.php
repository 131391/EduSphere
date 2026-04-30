<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Teacher\Concerns\ResolvesTeacher;
use App\Http\Controllers\TenantController;
use App\Http\Requests\School\Examination\StoreMarksRequest;
use App\Models\Exam;
use App\Models\ExamSubject;
use App\Models\Result;
use App\Models\Student;
use App\Services\School\Examination\ResultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class MarksController extends TenantController
{
    use ResolvesTeacher;

    public function __construct(protected ResultService $resultService)
    {
        parent::__construct();
    }

    /**
     * List exam-subjects assigned to the current teacher across exams that
     * still accept mark entry.
     */
    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $assignments = ExamSubject::with(['exam.examType', 'exam.class', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->whereHas('exam', fn ($q) => $q
                ->where('school_id', $this->getSchoolId())
                ->whereNull('deleted_at'))
            ->get()
            ->filter(fn (ExamSubject $row) => $row->exam && $row->exam->isMarkEntryAllowed())
            ->values();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $assignments->map(fn (ExamSubject $row) => [
                    'exam_subject_id' => $row->id,
                    'exam_id' => $row->exam_id,
                    'exam_name' => $row->exam?->display_name,
                    'class_name' => $row->exam?->class?->name,
                    'subject' => $row->resolved_name,
                    'full_marks' => $row->full_marks,
                    'status' => $row->exam?->status?->label(),
                ])->all(),
            ]);
        }

        return view('teacher.marks.index', [
            'assignments' => $assignments,
        ]);
    }

    public function entry(Request $request)
    {
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $validated = $request->validate([
            'exam_id' => 'required|integer',
            'exam_subject_id' => 'required|integer',
        ]);

        $exam = Exam::with(['class', 'examType', 'examSubjects.subject'])
            ->where('school_id', $this->getSchoolId())
            ->findOrFail($validated['exam_id']);

        $examSubject = ExamSubject::where('exam_id', $exam->id)
            ->where('teacher_id', $teacher->id)
            ->findOrFail($validated['exam_subject_id']);

        $this->authorize('enterMarks', $exam);
        $this->authorize('enterSubjectMarks', [$exam, $examSubject]);

        if (!$exam->isMarkEntryAllowed()) {
            return redirect()->route('teacher.marks.index')
                ->with('error', 'Mark entry is closed for this exam.');
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

        return view('teacher.marks.entry', [
            'exam' => $exam,
            'class' => $class,
            'examSubject' => $examSubject,
            'students' => $students,
            'results' => $results,
            'fullMarks' => $examSubject->full_marks,
        ]);
    }

    public function store(StoreMarksRequest $request)
    {
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        $validated = $request->validated();

        $exam = Exam::where('school_id', $this->getSchoolId())
            ->findOrFail($validated['exam_id']);

        $examSubject = ExamSubject::where('exam_id', $exam->id)
            ->where('teacher_id', $teacher->id)
            ->findOrFail($validated['exam_subject_id']);

        $this->authorize('enterMarks', $exam);
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

                return redirect()->route('teacher.marks.index')->with('success', $result['message']);
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
}
