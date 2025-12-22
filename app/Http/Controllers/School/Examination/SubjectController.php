<?php

namespace App\Http\Controllers\School\Examination;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;
        
        // Get all classes for the school
        $classes = ClassModel::where('school_id', $school->id)->get();
        
        // Get all subjects for the school
        $subjects = Subject::where('school_id', $school->id)->get();
        
        // Get subjects assigned to classes with full marks
        $classSubjects = DB::table('class_subject')
            ->join('classes', 'class_subject.class_id', '=', 'classes.id')
            ->join('subjects', 'class_subject.subject_id', '=', 'subjects.id')
            ->where('classes.school_id', $school->id)
            ->select(
                'class_subject.id',
                'classes.name as class_name',
                'subjects.name as subject_name',
                'class_subject.full_marks'
            )
            ->orderBy('classes.name')
            ->orderBy('subjects.name')
            ->paginate(15);

        return view('school.examination.subjects.index', compact('classes', 'subjects', 'classSubjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'full_marks' => 'required|integer|min:1',
        ]);

        $class = ClassModel::findOrFail($request->class_id);
        
        // Check if already assigned
        $exists = DB::table('class_subject')
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'This subject is already assigned to the selected class.');
        }

        $class->subjects()->attach($request->subject_id, [
            'full_marks' => $request->full_marks,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('school.examination.subjects.index')->with('success', 'Subject added successfully.');
    }

    public function destroy($id)
    {
        DB::table('class_subject')->where('id', $id)->delete();
        
        return redirect()->route('school.examination.subjects.index')->with('success', 'Subject removed successfully.');
    }
}
