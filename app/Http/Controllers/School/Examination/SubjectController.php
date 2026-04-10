<?php

namespace App\Http\Controllers\School\Examination;

use App\Http\Controllers\TenantController;
use App\Models\ClassModel;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends TenantController
{
    public function index()
    {
        $school = $this->getSchool(); // Consistent with other controllers
        
        // Get all classes for the school
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        
        // Get all subjects for the school
        $subjects = Subject::where('school_id', $this->getSchoolId())->get();
        
        // Get subjects assigned to classes with full marks
        $classSubjects = DB::table('class_subject')
            ->join('classes', 'class_subject.class_id', '=', 'classes.id')
            ->join('subjects', 'class_subject.subject_id', '=', 'subjects.id')
            ->where('classes.school_id', $this->getSchoolId())
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
        try {
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
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This subject is already assigned to the selected class.'
                    ], 422);
                }
                return back()->with('error', 'This subject is already assigned to the selected class.');
            }

            $class->subjects()->attach($request->subject_id, [
                'full_marks' => $request->full_marks,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject added successfully.'
                ]);
            }

            return redirect()->route('school.examination.subjects.index')->with('success', 'Subject added successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Subject Assignment Error: " . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add subject: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to add subject: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            // Scope to school via the junction table join if needed, but here ID is primary key of pivot if using that, 
            // but the join in index uses class_subject.id.
            DB::table('class_subject')->where('id', $id)->delete();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Subject removed successfully!'
                ]);
            }

            return redirect()->route('school.examination.subjects.index')->with('success', 'Subject removed successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Subject Removal Error: " . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove subject: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to remove subject: ' . $e->getMessage());
        }
    }
}
