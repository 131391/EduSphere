<?php

namespace App\Http\Controllers\School\Examination;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use Illuminate\Http\Request;

class ExamTypeController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;
        $examTypes = ExamType::where('school_id', $school->id)
            ->orderBy('name')
            ->paginate(15);

        return view('school.examination.exam-types.index', compact('examTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $school = auth()->user()->school;

        ExamType::create([
            'school_id' => $school->id,
            'name' => $request->name,
        ]);

        return redirect()->route('school.examination.exam-types.index')->with('success', 'Exam type created successfully.');
    }

    public function update(Request $request, ExamType $examType)
    {
        $this->authorizeAccess($examType);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $examType->update([
            'name' => $request->name,
        ]);

        return redirect()->route('school.examination.exam-types.index')->with('success', 'Exam type updated successfully.');
    }

    public function destroy(ExamType $examType)
    {
        $this->authorizeAccess($examType);
        
        // Check if exam type is used in any exams (to be implemented later when Exam model is ready)
        // For now, just delete
        $examType->delete();

        return redirect()->route('school.examination.exam-types.index')->with('success', 'Exam type deleted successfully.');
    }

    protected function authorizeAccess(ExamType $examType)
    {
        if ($examType->school_id !== auth()->user()->school_id) {
            abort(403);
        }
    }
}
