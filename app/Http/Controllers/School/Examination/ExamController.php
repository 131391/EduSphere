<?php

namespace App\Http\Controllers\School\Examination;

use App\Http\Controllers\TenantController;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\FeeName;
use App\Models\Subject;
use App\Models\Student;
use App\Services\School\Examination\ResultService;
use Illuminate\Http\Request;

class ExamController extends TenantController
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        parent::__construct();
        $this->resultService = $resultService;
    }

    public function index()
    {
        $this->ensureSchoolActive();
        
        $exams = Exam::with(['academicYear', 'class', 'examType'])
            ->where('school_id', $this->getSchoolId())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $examTypes = ExamType::where('school_id', $this->getSchoolId())->get();
        $academicYears = AcademicYear::where('school_id', $this->getSchoolId())->get();
        
        $months = FeeName::where('school_id', $this->getSchoolId())
            ->where('is_active', true)
            ->pluck('name');

        return view('school.examination.exams.index', compact('exams', 'classes', 'examTypes', 'academicYears', 'months'));
    }

    public function store(Request $request)
    {
        $this->ensureSchoolActive();

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'month' => 'required|string',
        ]);

        $activeAcademicYear = AcademicYear::where('school_id', $this->getSchoolId())
            ->where('is_active', true)
            ->first();

        if (!$activeAcademicYear) {
            return back()->with('error', 'No active academic year found. Please set an active academic year first.');
        }

        try {
            $exam = Exam::create([
                'school_id' => $this->getSchoolId(),
                'academic_year_id' => $activeAcademicYear->id,
                'class_id' => $request->class_id,
                'exam_type_id' => $request->exam_type_id,
                'month' => $request->month,
                'status' => \App\Enums\ExamStatus::Scheduled,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam scheduled successfully!',
                    'data' => $exam->load(['academicYear', 'class', 'examType'])
                ]);
            }

            return redirect()->route('school.examination.exams.index')->with('success', 'Exam created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to schedule exam: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to schedule exam: ' . $e->getMessage());
        }
    }

    /**
     * Show selection form for mark entry
     */
    public function marksEntry()
    {
        $this->ensureSchoolActive();
        
        $exams = Exam::where('school_id', $this->getSchoolId())->get();
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $subjects = Subject::where('school_id', $this->getSchoolId())->get();

        return view('school.examination.marks.index', compact('exams', 'classes', 'subjects'));
    }

    /**
     * Show mark entry grid
     */
    public function enterMarks(Request $request)
    {
        $this->ensureSchoolActive();

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $exam = Exam::findOrFail($request->exam_id);
        $this->authorizeTenant($exam);

        $class = ClassModel::findOrFail($request->class_id);
        $subject = Subject::findOrFail($request->subject_id);
        
        $students = Student::where('school_id', $this->getSchoolId())
            ->where('class_id', $class->id)
            ->active()
            ->get();

        $results = \App\Models\Result::where('exam_id', $exam->id)
            ->where('subject_id', $subject->id)
            ->get()
            ->keyBy('student_id');

        return view('school.examination.marks.entry', compact('exam', 'class', 'subject', 'students', 'results'));
    }

    /**
     * Store bulk marks
     */
    public function storeMarks(Request $request)
    {
        $this->ensureSchoolActive();

        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'total_marks' => 'required|numeric|min:1',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.marks_obtained' => 'required|numeric|min:0|max:' . ($request->total_marks ?? 100),
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        try {
            $exam = Exam::findOrFail($validated['exam_id']);
            $this->authorizeTenant($exam);

            $result = $this->resultService->saveMarks($this->school, $validated);

            if ($result['success']) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $result['message']
                    ]);
                }
                return redirect()->route('school.examination.marks.index')->with('success', $result['message']);
            }

            throw new \Exception($result['message']);

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save marks: ' . $e->getMessage()
                ], 422);
            }
            return back()->with('error', 'Failed to save marks: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display tabulated results for an exam
     */
    public function tabulate(Exam $exam)
    {
        $this->authorizeTenant($exam);
        $this->ensureSchoolActive();

        $exam->load(['class', 'examType', 'academicYear']);
        
        // Get all subjects that have results for this exam
        $subjectIds = \App\Models\Result::where('exam_id', $exam->id)
            ->distinct()
            ->pluck('subject_id');
        
        $subjects = Subject::whereIn('id', $subjectIds)->get();

        // Get all students in this class
        $students = Student::where('class_id', $exam->class_id)
            ->where('school_id', $this->getSchoolId())
            ->active()
            ->get();

        // Get all results
        $results = \App\Models\Result::where('exam_id', $exam->id)
            ->get()
            ->groupBy('student_id');

        return view('school.examination.exams.tabulate', compact('exam', 'subjects', 'students', 'results'));
    }

    public function destroy(Exam $exam)
    {
        try {
            $exam->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam schedule removed successfully!'
                ]);
            }

            return redirect()->route('school.examination.exams.index')->with('success', 'Exam deleted successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove examination: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('school.examination.exams.index')->with('error', 'Failed to remove exam: ' . $e->getMessage());
        }
    }
}
