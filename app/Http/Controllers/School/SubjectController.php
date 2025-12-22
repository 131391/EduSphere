<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;
        $subjects = Subject::where('school_id', $school->id)
            ->orderBy('name')
            ->paginate(15);

        return view('school.subjects.index', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $school = auth()->user()->school;

        Subject::create([
            'school_id' => $school->id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('school.subjects.index')->with('success', 'Subject created successfully.');
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorizeAccess($subject);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $subject->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
        ]);

        return redirect()->route('school.subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $this->authorizeAccess($subject);
        
        // Check if subject is assigned to any classes
        if ($subject->classes()->exists()) {
            return back()->with('error', 'Cannot delete subject as it is assigned to one or more classes.');
        }

        $subject->delete();

        return redirect()->route('school.subjects.index')->with('success', 'Subject deleted successfully.');
    }

    protected function authorizeAccess(Subject $subject)
    {
        if ($subject->school_id !== auth()->user()->school_id) {
            abort(403);
        }
    }
}
