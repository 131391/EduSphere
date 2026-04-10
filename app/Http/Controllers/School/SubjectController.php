<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends TenantController
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

        $subject = Subject::create([
            'school_id' => $school->id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subject created successfully!',
                'data' => $subject
            ]);
        }

        return redirect()->route('school.subjects.index')->with('success', 'Subject created successfully.');
    }

    public function update(Request $request, Subject $subject)
    {
        $this->authorizeTenant($subject);

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

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subject updated successfully!',
                'data' => $subject
            ]);
        }

        return redirect()->route('school.subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $this->authorizeTenant($subject);
        
        // Check if subject is assigned to any classes
        if ($subject->classes()->exists()) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete subject as it is assigned to one or more classes.'
                ], 422);
            }
            return back()->with('error', 'Cannot delete subject as it is assigned to one or more classes.');
        }

        $subject->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subject deleted successfully!'
            ]);
        }

        return redirect()->route('school.subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
