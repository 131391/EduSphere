<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;
use App\Enums\StudentStatus;

class StudentController extends TenantController
{
    public function index(Request $request)
    {
        $school = $this->getSchool();
        
        $query = \App\Models\Student::where('school_id', $this->getSchoolId())
            ->with(['class', 'section']);

        // Filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(20);

        // Stats
        $stats = [
            'total'    => \App\Models\Student::where('school_id', $this->getSchoolId())->count(),
            'active'   => \App\Models\Student::where('school_id', $this->getSchoolId())->where('status', StudentStatus::Active)->count(),
            'inactive' => \App\Models\Student::where('school_id', $this->getSchoolId())->where('status', StudentStatus::Inactive)->count(),
            'admissions_this_month' => \App\Models\Student::where('school_id', $this->getSchoolId())
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $classes = \App\Models\ClassModel::where('school_id', $this->getSchoolId())->get();
        $sections = $request->filled('class_id') 
            ? \App\Models\Section::where('class_id', $request->class_id)->get()
            : collect();

        return view('school.students.index', compact('students', 'classes', 'sections', 'request', 'stats'));
    }

    public function create()
    {
        // Admission and Registration flows handle student creation.
        // Direct creation can be added here if needed.
        return redirect()->route('school.admission.index')
            ->with('info', 'Please use the Admission module to register new students.');
    }

    public function show($id)
    {
        $school = $this->getSchool();
        $student = \App\Models\Student::where('school_id', $this->getSchoolId())
            ->with(['class', 'section', 'fees' => function($q) {
                $q->latest()->take(10);
            }, 'attendance' => function($q) {
                $q->latest()->take(30);
            }])
            ->findOrFail($id);

        return view('school.students.show', compact('student'));
    }

    public function edit($id)
    {
        $school = $this->getSchool();
        $student = \App\Models\Student::where('school_id', $this->getSchoolId())->findOrFail($id);
        $classes = \App\Models\ClassModel::where('school_id', $this->getSchoolId())->get();
        $sections = \App\Models\Section::where('class_id', $student->class_id)->get();
        
        return view('school.students.edit', compact('student', 'classes', 'sections'));
    }

    public function update(Request $request, $id)
    {
        $school = $this->getSchool();
        $student = \App\Models\Student::where('school_id', $this->getSchoolId())->findOrFail($id);

        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:20',
            'class_id'    => ['required', \Illuminate\Validation\Rule::exists('classes', 'id')->where('school_id', $this->getSchoolId())],
            'section_id'  => ['required', \Illuminate\Validation\Rule::exists('sections', 'id')->where('school_id', $this->getSchoolId())],
            'status'      => ['required', \Illuminate\Validation\Rule::in(array_column(StudentStatus::cases(), 'value'))],
            'address'     => 'nullable|string|max:500',
        ]);

        // Ensure section belongs to the selected class
        $sectionValid = \App\Models\Section::where('id', $validated['section_id'])
            ->where('class_id', $validated['class_id'])
            ->where('school_id', $this->getSchoolId())
            ->exists();
        if (!$sectionValid) {
            return back()->withErrors(['section_id' => 'The selected section does not belong to the chosen class.'])->withInput();
        }

        $student->update($validated);

        return redirect()->route('school.students.index')
            ->with('success', 'Student information updated successfully.');
    }

    public function destroy($id)
    {
        $school = $this->getSchool();
        $student = \App\Models\Student::where('school_id', $this->getSchoolId())->findOrFail($id);
        
        $student->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Student record moved to archives successfully.'
            ]);
        }

        return redirect()->route('school.students.index')
            ->with('success', 'Student record moved to archives.');
    }
}

