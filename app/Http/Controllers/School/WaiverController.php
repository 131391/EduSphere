<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;

class WaiverController extends TenantController
{
    public function index(Request $request)
    {
        $school = $this->getSchool(); // Consistent with other controllers
        $query = \App\Models\Waiver::where('school_id', $school->id)
            ->with(['student', 'academicYear']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $waivers = $query->latest()->paginate(20);
        $students = \App\Models\Student::where('school_id', $school->id)->get();
        $academicYears = \App\Models\AcademicYear::where('school_id', $school->id)->get();
        $academicYear = \App\Models\AcademicYear::where('school_id', $school->id)->where('is_current', 1)->first() 
                      ?? \App\Models\AcademicYear::where('school_id', $school->id)->latest()->first();

        return view('school.waivers.index', compact('waivers', 'students', 'academicYears', 'academicYear'));
    }

    public function create()
    {
        $school = $this->getSchool();
        $students = \App\Models\Student::where('school_id', $school->id)->get();
        $academicYears = \App\Models\AcademicYear::where('school_id', $school->id)->get();
        
        return view('school.waivers.create', compact('students', 'academicYears'));
    }

    public function store(\App\Http\Requests\School\StoreWaiverRequest $request)
    {
        $validated = $request->validated();
        $validated['school_id'] = $this->getSchoolId();

        try {
            $waiver = \App\Models\Waiver::create($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Waiver applied successfully!',
                    'data' => $waiver
                ]);
            }

            return redirect()->route('school.waivers.index')
                ->with('success', 'Waiver applied successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Waiver Store Error: " . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to apply waiver: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to apply waiver: ' . $e->getMessage());
        }
    }

    public function update(\App\Http\Requests\School\UpdateWaiverRequest $request, \App\Models\Waiver $waiver)
    {
        try {
            $this->authorizeTenant($waiver);
            $waiver->update($request->validated());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Waiver updated successfully!',
                    'data' => $waiver
                ]);
            }

            return back()->with('success', 'Waiver updated successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Waiver Update Error: " . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update waiver: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update waiver: ' . $e->getMessage());
        }
    }

    public function destroy(\App\Models\Waiver $waiver)
    {
        try {
            $this->authorizeTenant($waiver);
            $waiver->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Waiver removed successfully!'
                ]);
            }

            return back()->with('success', 'Waiver removed successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Waiver Delete Error: " . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to remove waiver: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to remove waiver: ' . $e->getMessage());
        }
    }
}

