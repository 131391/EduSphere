<?php

namespace App\Http\Controllers\School;

use App\Enums\GeneralStatus;
use App\Enums\StudentStatus;
use App\Http\Controllers\TenantController;
use App\Models\BusStop;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentTransportAssignment;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use App\Services\School\StudentTransportService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StudentTransportAssignmentController extends TenantController
{
    use HasAjaxDataTable;

    public function __construct(
        protected StudentTransportService $transportService
    ) {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($assignment) {
            $studentName = trim(collect([
                $assignment->student?->first_name,
                $assignment->student?->middle_name,
                $assignment->student?->last_name,
            ])->filter()->implode(' '));

            return [
                'id' => $assignment->id,
                'student_id' => $assignment->student_id,
                'student_name' => $studentName ?: 'N/A',
                'admission_no' => $assignment->student?->admission_no ?? 'N/A',
                'class_name' => $assignment->student?->class?->name ?? 'N/A',
                'route_id' => $assignment->route_id,
                'route_name' => $assignment->route?->route_name ?? 'N/A',
                'bus_stop_id' => $assignment->bus_stop_id,
                'bus_stop_name' => $assignment->busStop?->bus_stop_name ?? 'N/A',
                'bus_stop_no' => $assignment->busStop?->bus_stop_no ?? 'N/A',
                'vehicle_id' => $assignment->vehicle_id,
                'vehicle_no' => $assignment->vehicle?->vehicle_no ?? 'N/A',
                'fee_per_month' => (float) $assignment->fee_per_month,
                'fee_formatted' => '₹' . number_format((float) $assignment->fee_per_month, 2),
                'status' => $assignment->status->value,
                'created_at' => $assignment->created_at?->format('M d, Y'),
            ];
        };

        $query = StudentTransportAssignment::with(['student.class', 'route', 'busStop', 'vehicle'])
            ->where('school_id', $schoolId)
            ->where('status', GeneralStatus::Active);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function ($studentQuery) use ($search) {
                $studentQuery->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('middle_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('admission_no', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($studentQuery) use ($request) {
                $studentQuery->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        $stats = [
            'total' => StudentTransportAssignment::where('school_id', $schoolId)
                ->where('status', GeneralStatus::Active)
                ->count(),
            'active_routes' => StudentTransportAssignment::where('school_id', $schoolId)
                ->where('status', GeneralStatus::Active)
                ->distinct('route_id')
                ->count('route_id'),
            'total_revenue' => (float) StudentTransportAssignment::where('school_id', $schoolId)
                ->where('status', GeneralStatus::Active)
                ->sum('fee_per_month'),
            'fleet_count' => StudentTransportAssignment::where('school_id', $schoolId)
                ->where('status', GeneralStatus::Active)
                ->whereNotNull('vehicle_id')
                ->distinct('vehicle_id')
                ->count('vehicle_id'),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $classes = ClassModel::where('school_id', $schoolId)->orderBy('name')->get();
        $routes = TransportRoute::where('school_id', $schoolId)->orderBy('route_name')->get();
        $students = Student::where('school_id', $schoolId)
            ->where('status', StudentStatus::Active)
            ->orderBy('first_name')
            ->get();

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.transport.assignments', [
            'initialData' => $initialData,
            'classes' => $classes,
            'routes' => $routes,
            'students' => $students,
            'stats' => $stats,
            'title' => 'Transport Assignments',
        ]);
    }

    public function edit($id)
    {
        $assignment = StudentTransportAssignment::where('school_id', $this->getSchoolId())
            ->findOrFail($id);

        return response()->json($assignment);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'route_id' => 'required|exists:transport_routes,id',
                'bus_stop_id' => 'required|exists:bus_stops,id',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'fee_per_month' => 'nullable|numeric|min:0',
                'start_date' => 'nullable|date',
                'academic_year_id' => 'nullable|exists:academic_years,id',
            ]);

            $student = Student::where('school_id', $this->getSchoolId())
                ->findOrFail($validated['student_id']);

            $assignment = $this->transportService->assignTransport(
                $this->getSchool(),
                $student,
                $validated
            );

            return response()->json([
                'success' => true,
                'message' => 'Transport assigned successfully!',
                'data' => $assignment->load(['student.class', 'route', 'busStop', 'vehicle']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to assign transport', [
                'exception' => $e,
                'school_id' => $this->getSchoolId(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to assign transport. Please try again.',
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $assignment = StudentTransportAssignment::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'route_id' => 'required|exists:transport_routes,id',
                'bus_stop_id' => 'required|exists:bus_stops,id',
                'vehicle_id' => 'nullable|exists:vehicles,id',
                'fee_per_month' => 'nullable|numeric|min:0',
                'start_date' => 'nullable|date',
                'academic_year_id' => 'nullable|exists:academic_years,id',
            ]);

            $assignment = $this->transportService->updateAssignment($assignment, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully!',
                'data' => $assignment->load(['student.class', 'route', 'busStop', 'vehicle']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to update transport assignment', [
                'exception' => $e,
                'school_id' => $this->getSchoolId(),
                'assignment_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update assignment. Please try again.',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $assignment = StudentTransportAssignment::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->transportService->deleteAssignment($assignment);

            return response()->json([
                'success' => true,
                'message' => 'Assignment removed successfully!',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Failed to remove transport assignment', [
                'exception' => $e,
                'school_id' => $this->getSchoolId(),
                'assignment_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to remove assignment. Please try again.',
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $query = StudentTransportAssignment::onlyTrashed()
            ->with(['student.class', 'academicYear', 'route', 'busStop', 'vehicle'])
            ->where('school_id', $schoolId)
            ->latest('deleted_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('student', function ($studentQuery) use ($search) {
                $studentQuery->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('middle_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('admission_no', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('student', function ($studentQuery) use ($request) {
                $studentQuery->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->route_id);
        }

        $assignments = $query->paginate(15)->withQueryString();
        $classes = ClassModel::where('school_id', $schoolId)->orderBy('name')->get();
        $vehicles = Vehicle::where('school_id', $schoolId)->orderBy('vehicle_no')->get();
        $routes = TransportRoute::where('school_id', $schoolId)->orderBy('route_name')->get();

        return view('school.transport.history', compact('assignments', 'classes', 'vehicles', 'routes'));
    }
}
