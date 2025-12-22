<?php

namespace App\Http\Controllers\School\Examination;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\FeeName;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;
        
        $exams = Exam::with(['academicYear', 'class', 'examType'])
            ->where('school_id', $school->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $classes = ClassModel::where('school_id', $school->id)->get();
        $examTypes = ExamType::where('school_id', $school->id)->get();
        $academicYears = AcademicYear::where('school_id', $school->id)->get();
        
        $months = FeeName::where('school_id', $school->id)
            ->where('is_active', true)
            ->pluck('name');

        return view('school.examination.exams.index', compact('exams', 'classes', 'examTypes', 'academicYears', 'months'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'month' => 'required|string',
        ]);

        $school = auth()->user()->school;
        $activeAcademicYear = AcademicYear::where('school_id', $school->id)
            ->where('is_active', true)
            ->first();

        if (!$activeAcademicYear) {
            return back()->with('error', 'No active academic year found. Please set an active academic year first.');
        }

        Exam::create([
            'school_id' => $school->id,
            'academic_year_id' => $activeAcademicYear->id,
            'class_id' => $request->class_id,
            'exam_type_id' => $request->exam_type_id,
            'month' => $request->month,
            'status' => 'scheduled',
        ]);

        return redirect()->route('school.examination.exams.index')->with('success', 'Exam created successfully.');
    }

    public function destroy(Exam $exam)
    {
        $this->authorizeAccess($exam);
        
        $exam->delete();

        return redirect()->route('school.examination.exams.index')->with('success', 'Exam deleted successfully.');
    }

    protected function authorizeAccess(Exam $exam)
    {
        if ($exam->school_id !== auth()->user()->school_id) {
            abort(403);
        }
    }
}
