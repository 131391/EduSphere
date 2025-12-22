<?php

namespace App\Http\Controllers\School\Examination;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
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

        Grade::create([
            'school_id' => $school->id,
            'range_start' => $request->range_start,
            'range_end' => $request->range_end,
            'grade' => $request->grade,
        ]);

        return redirect()->route('school.examination.grades.index')->with('success', 'Grade created successfully.');
    }

    public function update(Request $request, Grade $grade)
    {
        $this->authorizeAccess($grade);

        $request->validate([
            'range_start' => 'required|integer|min:0|max:100',
            'range_end' => 'required|integer|min:0|max:100|gte:range_start',
            'grade' => 'required|string|max:10',
        ]);

        $grade->update([
            'range_start' => $request->range_start,
            'range_end' => $request->range_end,
            'grade' => $request->grade,
        ]);

        return redirect()->route('school.examination.grades.index')->with('success', 'Grade updated successfully.');
    }

    public function destroy(Grade $grade)
    {
        $this->authorizeAccess($grade);
        
        $grade->delete();

        return redirect()->route('school.examination.grades.index')->with('success', 'Grade deleted successfully.');
    }

    protected function authorizeAccess(Grade $grade)
    {
        if ($grade->school_id !== auth()->user()->school_id) {
            abort(403);
        }
    }
}
