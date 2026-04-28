<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\StudentTransportAssignment;
use App\Models\Student;
use App\Models\TransportRoute;
use App\Models\BusStop;
use App\Models\Vehicle;
use App\Models\AcademicYear;
use App\Models\ClassModel;
use App\Services\School\StudentTransportService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class StudentTransportAssignmentController extends TenantController
{
    use HasAjaxDataTable;

    protected StudentTransportService $transportService;

    public function __construct(StudentTransportService $transportService)
    {
        parent::__construct();
        $this->transportService = $transportService;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($assignment) {
            return [
                'id' => $assignment->id,
                'student_name' => $assignment->student?->full_name,
                'admission_no' => $assignment->student?->admission_no,
                'class_name' => $assignment->student?->class?->name,
                'route_name' => $assignment->route?->route_name,
                'bus_stop_name' => $assignment->busStop?->bus_stop_name,
                'vehicle_no' => $assignment->vehicle?->vehicle_no ?? 'N/A',
                'fee' => '₹' . number_format($assignment->fee_per_month, 2),
                'status' => $assignment->status->value,
                'created_at' => $assignment->created_at?->format('M d, Y'),
            ];
        };

        $query = StudentTransportAssignment::with(['student.class', 'route', 'busStop', 'vehicle'])
            ->where('school_id', $schoolId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('admission_no', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, []);
        }

        $classes = ClassModel::where('school_id', $schoolId)->orderBy('name')->get();
        $routes = TransportRoute::where('school_id', $schoolId)->get();
        $vehicles = Vehicle::where('school_id', $schoolId)->get();
        $busStops = BusStop::where('school_id', $schoolId)->get();

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => [
                'total' => StudentTransportAssignment::where('school_id', $schoolId)->count(),
                'active' => StudentTransportAssignment::where('school_id', $schoolId)->where('status', 'active')->count(),
                'total_revenue' => '₹' . number_format(StudentTransportAssignment::where('school_id', $schoolId)->where('status', 'active')->sum('fee_per_month'), 2),
            ],
            'classes' => $classes,
            'routes' => $routes,
            'vehicles' => $vehicles,
            'busStops' => $busStops,
        ]);

        return view('school.transport.assignments', [
            'initialData' => $initialData,
            'classes' => $classes,
            'routes' => $routes,
            'title' => 'Transport Assignments'
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'route_id' => 'required|exists:transport_routes,id',
                'bus_stop_id' => 'required|exists:bus_stops,id',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'fee_per_month' => 'required|numeric|min:0',
            ]);

            $student = Student::where('school_id', $this->getSchoolId())->findOrFail($validated['student_id']);
            
            $assignment = $this->transportService->assignTransport(
                $this->getSchool(),
                $student,
                array_merge($validated, ['action' => 'assign'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Transport assigned successfully!',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign transport: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $assignment = StudentTransportAssignment::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $validated = $request->validate([
                'route_id' => 'required|exists:transport_routes,id',
                'bus_stop_id' => 'required|exists:bus_stops,id',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'fee_per_month' => 'required|numeric|min:0',
                'status' => 'required|string|in:active,inactive'
            ]);

            $assignment->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully!',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $assignment = StudentTransportAssignment::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $assignment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Assignment removed successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($assignment) {
            return [
                'id' => $assignment->id,
                'student_name' => $assignment->student?->full_name,
                'admission_no' => $assignment->student?->admission_no,
                'route_name' => $assignment->route?->route_name,
                'bus_stop_name' => $assignment->busStop?->bus_stop_name,
                'status' => $assignment->status->value,
                'deleted_at' => $assignment->deleted_at?->format('M d, Y'),
                'created_at' => $assignment->created_at?->format('M d, Y'),
            ];
        };

        $query = StudentTransportAssignment::onlyTrashed()
            ->with(['student', 'route', 'busStop'])
            ->where('school_id', $schoolId);

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, []);
        }

        $initialData = $this->getHydrationData($query, $transformer, []);

        return view('school.transport.history', [
            'initialData' => $initialData,
            'title' => 'Transport Assignment History'
        ]);
    }
}
