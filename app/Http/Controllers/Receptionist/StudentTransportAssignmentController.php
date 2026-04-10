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
use Illuminate\Validation\ValidationException;

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
        $perPage = $request->input('per_page', 15);
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
        try {
            $schoolId = $this->getSchoolId();
            $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                ->where('is_current', true)
                ->first() ?: AcademicYear::where('school_id', $schoolId)->latest('start_date')->first();

            if (!$currentAcademicYear) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Temporal context missing',
                    'errors' => ['academic_year' => ['No active academic session found. Please establish an academic year before mapping transport.']]
                ], 422);
            }

            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'route_id' => 'required|exists:transport_routes,id',
                'bus_stop_id' => 'required|exists:bus_stops,id',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'fee_per_month' => 'required|numeric|min:0',
            ]);

            // Verify tenant ownership
            $student = Student::where('id', $validated['student_id'])->where('school_id', $schoolId)->first();
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['student_id' => ['The selected student is not part of this institutional registry.']]
                ], 422);
            }

            $route = TransportRoute::where('id', $validated['route_id'])->where('school_id', $schoolId)->first();
            if (!$route) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['route_id' => ['The selected route does not belong to your institutional fleet.']]
                ], 422);
            }

            $busStop = BusStop::where('id', $validated['bus_stop_id'])->where('school_id', $schoolId)->with('route')->first();
            if (!$busStop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['bus_stop_id' => ['The selected bus stop is not part of this institutional network.']]
                ], 422);
            }

            if ($busStop->route_id !== $route->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Topology mismatch',
                    'errors' => ['bus_stop_id' => ['Network node "' . $busStop->bus_stop_name . '" is mapping to route "' . ($busStop->route->route_name ?? 'N/A') . '", not the selected route.']]
                ], 422);
            }

            $validated['school_id'] = $schoolId;
            $validated['academic_year_id'] = $currentAcademicYear->id;

            $assignment = StudentTransportAssignment::create($validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transport facility assigned to student successfully.',
                    'assignment' => $assignment->load(['student.class', 'route', 'busStop', 'vehicle'])
                ]);
            }

            return redirect()->route('receptionist.transport-assignments.index')->with('success', 'Transport facility assigned to student successfully.');
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System exception: ' . $e->getMessage()
            ], 500);
        }
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

        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'route_id' => 'required|exists:transport_routes,id',
                'bus_stop_id' => 'required|exists:bus_stops,id',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'fee_per_month' => 'required|numeric|min:0',
            ]);

            $schoolId = $this->getSchoolId();

            // Verify tenant ownership
            $student = Student::where('id', $validated['student_id'])->where('school_id', $schoolId)->first();
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['student_id' => ['The selected student is not part of this institutional registry.']]
                ], 422);
            }

            $route = TransportRoute::where('id', $validated['route_id'])->where('school_id', $schoolId)->first();
            if (!$route) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['route_id' => ['The selected route does not belong to your institutional fleet.']]
                ], 422);
            }

            $busStop = BusStop::where('id', $validated['bus_stop_id'])->where('school_id', $schoolId)->with('route')->first();
            if (!$busStop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['bus_stop_id' => ['The selected bus stop is not part of this institutional network.']]
                ], 422);
            }

            if ($busStop->route_id !== $route->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Topology mismatch',
                    'errors' => ['bus_stop_id' => ['Network node "' . $busStop->bus_stop_name . '" is mapping to route "' . ($busStop->route->route_name ?? 'N/A') . '", not the selected route.']]
                ], 422);
            }

            $transportAssignment->update($validated);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student transit mapping updated successfully.',
                    'assignment' => $transportAssignment->load(['student.class', 'route', 'busStop', 'vehicle'])
                ]);
            }

            return redirect()->route('receptionist.transport-assignments.index')->with('success', 'Student transit mapping updated successfully.');
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, StudentTransportAssignment $transportAssignment)
    {
        $this->authorizeTenant($transportAssignment);

        try {
            $transportAssignment->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Student transit registry entry struck successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'System exception: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display transport assignment history (including soft-deleted records).
     */
    public function history(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $query = StudentTransportAssignment::withTrashed()
            ->with([
                'student.class',
                'route',
                'busStop',
                'vehicle',
                'academicYear'
            ])
            ->where('school_id', $schoolId);

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
        $perPage = $request->input('per_page', 15);
        $assignments = $query->latest()->paginate($perPage)->withQueryString();

        // Get data for dropdowns
        $routes = TransportRoute::where('school_id', $schoolId)->get();
        $vehicles = Vehicle::where('school_id', $schoolId)->get();
        $busStops = BusStop::where('school_id', $schoolId)->get();
        
        // Get classes for filter
        $classes = ClassModel::where('school_id', $schoolId)
            ->orderBy('name')
            ->get();

        return view('receptionist.transport-assign-history.index', compact(
            'assignments',
            'routes',
            'vehicles',
            'busStops',
            'classes'
        ));
    }
}
