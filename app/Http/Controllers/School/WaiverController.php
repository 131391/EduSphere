<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;

use App\Traits\HasAjaxDataTable;

class WaiverController extends TenantController
{
    use HasAjaxDataTable {
        handleAjaxTable as traitHandleAjaxTable;
    }


    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $schoolId = $this->getSchoolId();

        $transformer = function($row) {
            return [
                'id' => $row->id,
                'student_name' => $row->student?->full_name ?? 'N/A',
                'admission_no' => $row->student?->admission_no ?? 'N/A',
                'fee_period' => $row->fee_period,
                'actual_fee' => number_format($row->actual_fee, 2),
                'waiver_percentage' => $row->waiver_percentage . '%',
                'waiver_amount' => number_format($row->waiver_amount, 2),
                'reason' => $row->reason ?? 'N/A',
            ];
        };

        $query = \App\Models\Waiver::where('school_id', $schoolId)
            ->with(['student', 'academicYear']);

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        $stats = $this->getTableStats();

        if ($request->expectsJson() || $request->ajax()) {
            return $this->traitHandleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.waivers.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
            'students' => \App\Models\Student::where('school_id', $schoolId)
                ->with(['class', 'section'])
                ->active()
                ->orderBy('first_name')
                ->get(),
            'academicYears' => \App\Models\AcademicYear::where('school_id', $schoolId)->get(),
            'academicYear' => \App\Models\AcademicYear::where('school_id', $schoolId)->where('is_current', 1)->first() 
                          ?? \App\Models\AcademicYear::where('school_id', $schoolId)->latest()->first(),
        ]);
    }




    protected function getTableStats()
    {
        return [
            'total_waivers' => \App\Models\Waiver::where('school_id', $this->getSchoolId())->count(),
            'total_amount_waived' => number_format(\App\Models\Waiver::where('school_id', $this->getSchoolId())->sum('waiver_amount'), 2),
            'unique_students' => \App\Models\Waiver::where('school_id', $this->getSchoolId())->distinct('student_id')->count('student_id'),
        ];
    }

    public function create()
    {
        $school = $this->getSchool();
        $students = \App\Models\Student::where('school_id', $school->id)
            ->with(['class', 'section'])
            ->active()
            ->orderBy('first_name')
            ->get();
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

