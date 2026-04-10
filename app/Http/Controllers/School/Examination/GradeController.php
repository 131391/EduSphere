<?php

namespace App\Http\Controllers\School\Examination;

use App\Http\Controllers\TenantController;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends TenantController
{
    public function index()
    {
        $school = auth()->user()->school;
        $grades = Grade::where('school_id', $school->id)
            ->orderBy('range_start', 'desc')
            ->paginate(15);

        return view('school.examination.grades.index', compact('grades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'range_start' => 'required|integer|min:0|max:100',
            'range_end' => 'required|integer|min:0|max:100|gte:range_start',
            'grade' => 'required|string|max:10',
        ]);

        $school = auth()->user()->school;

        try {
            $grade = Grade::create([
                'school_id' => $this->getSchoolId(),
                'range_start' => $request->range_start,
                'range_end' => $request->range_end,
                'grade' => $request->grade,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grade created successfully!',
                    'data' => $grade
                ]);
            }

            return redirect()->route('school.examination.grades.index')->with('success', 'Grade created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create grade: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to create grade: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Grade $grade)
    {
        $this->authorizeTenant($grade);

        $request->validate([
            'range_start' => 'required|integer|min:0|max:100',
            'range_end' => 'required|integer|min:0|max:100|gte:range_start',
            'grade' => 'required|string|max:10',
        ]);

        try {
            $grade->update([
                'range_start' => $request->range_start,
                'range_end' => $request->range_end,
                'grade' => $request->grade,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grade updated successfully!',
                    'data' => $grade
                ]);
            }

            return redirect()->route('school.examination.grades.index')->with('success', 'Grade updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update grade: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update grade: ' . $e->getMessage());
        }
    }

    public function destroy(Grade $grade)
    {
        $this->authorizeTenant($grade);
        
        try {
            $grade->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grade deleted successfully!'
                ]);
            }

            return redirect()->route('school.examination.grades.index')->with('success', 'Grade deleted successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete grade: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('school.examination.grades.index')->with('error', 'Failed to delete grade: ' . $e->getMessage());
        }
    }
}
