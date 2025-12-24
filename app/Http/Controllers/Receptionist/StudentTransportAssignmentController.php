<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\StudentTransportAssignment;
use App\Models\Student;
use App\Models\TransportRoute;
use App\Models\BusStop;
use App\Models\Vehicle;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class StudentTransportAssignmentController extends TenantController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schoolId = $this->getSchoolId();
        $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        $assignments = StudentTransportAssignment::with([
            'student.class',
            'route',
            'busStop',
            'vehicle'
        ])
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $currentAcademicYear->id)
            ->latest()
            ->get();

        // Get data for dropdowns
        $students = Student::where('school_id', $schoolId)
            ->where('status', 'active')
            ->with('class')
            ->get();
        
        $routes = TransportRoute::where('school_id', $schoolId)->get();
        $vehicles = Vehicle::where('school_id', $schoolId)->get();
        $busStops = BusStop::where('school_id', $schoolId)->get();

        // Statistics
        $totalAssigned = $assignments->count();
        $activeRoutes = $assignments->pluck('route_id')->unique()->count();
        $totalFees = $assignments->sum('fee_per_month');

        return view('receptionist.transport-assignments.index', compact(
            'assignments',
            'students',
            'routes',
            'vehicles',
            'busStops',
            'totalAssigned',
            'activeRoutes',
            'totalFees',
            'currentAcademicYear'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'route_id' => 'required|exists:routes,id',
            'bus_stop_id' => 'required|exists:bus_stops,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'fee_per_month' => 'required|numeric|min:0',
        ]);

        // Verify tenant ownership
        $student = Student::findOrFail($validated['student_id']);
        $route = TransportRoute::findOrFail($validated['route_id']);
        $busStop = BusStop::findOrFail($validated['bus_stop_id']);

        if ($student->school_id !== $schoolId || 
            $route->school_id !== $schoolId || 
            $busStop->school_id !== $schoolId) {
            abort(403, 'Unauthorized access');
        }

        // Verify bus stop belongs to the selected route
        if ($busStop->route_id !== $route->id) {
            return back()->withErrors(['bus_stop_id' => 'Selected bus stop does not belong to the selected route.']);
        }

        $validated['school_id'] = $schoolId;
        $validated['academic_year_id'] = $currentAcademicYear->id;

        StudentTransportAssignment::create($validated);

        return redirect()->route('receptionist.transport-assignments.index')
            ->with('success', 'Transport assignment created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentTransportAssignment $transportAssignment)
    {
        $this->authorizeTenant($transportAssignment);

        return response()->json($transportAssignment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StudentTransportAssignment $transportAssignment)
    {
        $this->authorizeTenant($transportAssignment);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'route_id' => 'required|exists:routes,id',
            'bus_stop_id' => 'required|exists:bus_stops,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'fee_per_month' => 'required|numeric|min:0',
        ]);

        $schoolId = $this->getSchoolId();

        // Verify tenant ownership
        $student = Student::findOrFail($validated['student_id']);
        $route = TransportRoute::findOrFail($validated['route_id']);
        $busStop = BusStop::findOrFail($validated['bus_stop_id']);

        if ($student->school_id !== $schoolId || 
            $route->school_id !== $schoolId || 
            $busStop->school_id !== $schoolId) {
            abort(403, 'Unauthorized access');
        }

        // Verify bus stop belongs to the selected route
        if ($busStop->route_id !== $route->id) {
            return back()->withErrors(['bus_stop_id' => 'Selected bus stop does not belong to the selected route.']);
        }

        $transportAssignment->update($validated);

        return redirect()->route('receptionist.transport-assignments.index')
            ->with('success', 'Transport assignment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentTransportAssignment $transportAssignment)
    {
        $this->authorizeTenant($transportAssignment);

        $transportAssignment->delete();

        return redirect()->route('receptionist.transport-assignments.index')
            ->with('success', 'Transport assignment deleted successfully.');
    }
}
