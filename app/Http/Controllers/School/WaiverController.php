<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;

class WaiverController extends TenantController
{
    public function index(Request $request)
    {
        $school = app('currentSchool');
        $query = \App\Models\Waiver::where('school_id', $school->id)
            ->with(['student', 'academicYear']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $waivers = $query->latest()->paginate(20);
        $students = \App\Models\Student::where('school_id', $school->id)->get();

        return view('school.waivers.index', compact('waivers', 'students'));
    }

    public function create()
    {
        $school = app('currentSchool');
        $students = \App\Models\Student::where('school_id', $school->id)->get();
        $academicYears = \App\Models\AcademicYear::where('school_id', $school->id)->get();
        
        return view('school.waivers.create', compact('students', 'academicYears'));
    }

    public function store(\App\Http\Requests\School\StoreWaiverRequest $request)
    {
        $validated = $request->validated();
        $validated['school_id'] = auth()->user()->school_id;

        \App\Models\Waiver::create($validated);

        return redirect()->route('school.waivers.index')
            ->with('success', 'Waiver applied successfully.');
    }
}

