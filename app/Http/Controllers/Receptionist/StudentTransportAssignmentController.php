<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\StudentTransportAssignment;
use App\Models\Student;
use App\Models\TransportRoute;
use App\Models\BusStop;
use App\Models\Vehicle;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use Illuminate\Http\Request;

class StudentTransportAssignmentController extends TenantController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        // If no current academic year, get the latest one or return empty
        if (!$currentAcademicYear) {
            $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                ->latest('start_date')
                ->first();
        }

        $query = StudentTransportAssignment::with([
            'student.class',
            'route',
            'busStop',
            'vehicle',
            'academicYear'
        ])
            ->where('school_id', $schoolId);

        // Only filter by academic year if one exists
        if ($currentAcademicYear) {
            $query->where('academic_year_id', $currentAcademicYear->id);
        } else {
            // If no academic year exists, return empty collection
            $query->whereRaw('1 = 0'); // This ensures no results
        }

        // Apply filters
        if ($request->filled('class_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->filled('bus_stop_id')) {
            $query->where('bus_stop_id', $request->bus_stop_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('student', function($sq) use ($search) {
                    $sq->where('first_name', 'like', "%{$search}%")
                       ->orWhere('middle_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('admission_no', 'like', "%{$search}%");
                });
            });
        }

        // Paginate the results
        $perPage = $request->get('per_page', 15);
        $assignments = $query->latest()->paginate($perPage)->withQueryString();

        // Calculate statistics from full query (before pagination, but after filters)
        $statsQuery = StudentTransportAssignment::with([
            'student.class',
            'route',
            'busStop',
            'vehicle',
            'academicYear'
        ])
            ->where('school_id', $schoolId);

        // Apply same filters as main query
        if ($currentAcademicYear) {
            $statsQuery->where('academic_year_id', $currentAcademicYear->id);
        } else {
            $statsQuery->whereRaw('1 = 0');
        }

        if ($request->filled('class_id')) {
            $statsQuery->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('vehicle_id')) {
            $statsQuery->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('route_id')) {
            $statsQuery->where('route_id', $request->route_id);
        }

        if ($request->filled('bus_stop_id')) {
            $statsQuery->where('bus_stop_id', $request->bus_stop_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $statsQuery->where(function($q) use ($search) {
                $q->whereHas('student', function($sq) use ($search) {
                    $sq->where('first_name', 'like', "%{$search}%")
                       ->orWhere('middle_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('admission_no', 'like', "%{$search}%");
                });
            });
        }

        $allAssignments = $statsQuery->get();
        $totalAssigned = $allAssignments->count();
        $activeRoutes = $allAssignments->pluck('route_id')->unique()->count();
        $totalFees = $allAssignments->sum('fee_per_month');

        // Get data for dropdowns
        $students = Student::where('school_id', $schoolId)
            ->where('status', \App\Enums\StudentStatus::Active)
            ->with('class')
            ->get();
        
        $routes = TransportRoute::where('school_id', $schoolId)->get();
        $vehicles = Vehicle::where('school_id', $schoolId)->get();
        $busStops = BusStop::where('school_id', $schoolId)->get();
        
        // Get classes for filter
        $classes = ClassModel::where('school_id', $schoolId)
            ->orderBy('name')
            ->get();

        return view('receptionist.transport-assignments.index', compact(
            'assignments',
            'students',
            'routes',
            'vehicles',
            'busStops',
            'classes',
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

        // If no current academic year, get the latest one
        if (!$currentAcademicYear) {
            $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                ->latest('start_date')
                ->first();
        }

        // If still no academic year exists, return error
        if (!$currentAcademicYear) {
            return back()->withErrors(['academic_year' => 'No academic year found. Please create an academic year first.']);
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'route_id' => 'required|exists:transport_routes,id',
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
            return back()->withErrors(['bus_stop_id' => 'Selected bus stop does not belong to the selected route.'])->withInput();
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
            'route_id' => 'required|exists:transport_routes,id',
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
            return back()->withErrors(['bus_stop_id' => 'Selected bus stop does not belong to the selected route.'])->withInput();
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
