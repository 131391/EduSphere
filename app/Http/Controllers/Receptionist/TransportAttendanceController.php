<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\TransportAttendance;
use App\Models\StudentTransportAssignment;
use App\Models\Vehicle;
use App\Models\TransportRoute;
use App\Models\AcademicYear;
use App\Enums\RouteStatus;
use App\Enums\TransportAttendanceType;
use App\Services\School\TransportAttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransportAttendanceController extends TenantController
{
    public function __construct(
        protected TransportAttendanceService $attendanceService
    ) {
        parent::__construct();
    }

    /**
     * Display the transport attendance form.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        // Get current academic year
        $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        if (!$currentAcademicYear) {
            $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                ->latest('start_date')
                ->first();
        }

        // Get all vehicles for the school
        $vehicles = Vehicle::where('school_id', $schoolId)
            ->orderBy('vehicle_no')
            ->get();

        // Get attendance types
        $attendanceTypes = TransportAttendanceType::cases();

        // Calculate Global Stats for today
        $today = now()->toDateString();
        $stats = [
            'total_students' => $currentAcademicYear ? StudentTransportAssignment::where('school_id', $schoolId)
                ->where('academic_year_id', $currentAcademicYear->id)
                ->where('status', \App\Enums\GeneralStatus::Active)
                ->count() : 0,
            'boarded_today' => TransportAttendance::where('school_id', $schoolId)
                ->where('attendance_date', $today)
                ->where('is_present', true)
                ->count(),
            'absent_today' => TransportAttendance::where('school_id', $schoolId)
                ->where('attendance_date', $today)
                ->where('is_present', false)
                ->count(),
        ];

        return view('receptionist.transport-attendance.index', compact(
            'vehicles',
            'attendanceTypes',
            'currentAcademicYear',
            'stats'
        ));
    }

    /**
     * Get routes for a selected vehicle (AJAX).
     */
    public function getRoutes(Request $request)
    {
        try {
            $validated = $request->validate([
                'vehicle_id' => [
                    'required',
                    Rule::exists('vehicles', 'id')->where('school_id', $this->getSchoolId()),
                ],
            ]);

            $routesArray = $this->attendanceService->getRoutesForVehicle(
                $this->getSchool(),
                (int) $validated['vehicle_id']
            )->values()->toArray();

            return response()->json([
                'success' => true,
                'routes' => $routesArray,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Receptionist transport getRoutes failed', [
                'exception' => $e,
                'school_id' => $this->getSchoolId(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load routes. Please try again.',
            ], 500);
        }
    }

    /**
     * Get students for a selected route (AJAX).
     */
    public function getStudents(Request $request)
    {
        try {
            $validated = $request->validate([
                'vehicle_id' => [
                    'required',
                    Rule::exists('vehicles', 'id')->where('school_id', $this->getSchoolId()),
                ],
                'route_id' => [
                    'required',
                    Rule::exists('transport_routes', 'id')->where('school_id', $this->getSchoolId()),
                ],
                'academic_year_id' => [
                    'nullable',
                    Rule::exists('academic_years', 'id')->where('school_id', $this->getSchoolId()),
                ],
            ]);

            $students = $this->attendanceService->getStudentsForRoute(
                $this->getSchool(),
                (int) $validated['vehicle_id'],
                (int) $validated['route_id'],
                isset($validated['academic_year_id']) ? (int) $validated['academic_year_id'] : null
            );

            return response()->json([
                'success' => true,
                'students' => $students,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Receptionist transport getStudents failed', [
                'exception' => $e,
                'school_id' => $this->getSchoolId(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load students. Please try again.',
            ], 500);
        }
    }

    /**
     * Store transport attendance records.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'vehicle_id' => [
                    'required',
                    Rule::exists('vehicles', 'id')->where('school_id', $this->getSchoolId()),
                ],
                'route_id' => [
                    'required',
                    Rule::exists('transport_routes', 'id')->where('school_id', $this->getSchoolId()),
                ],
                'academic_year_id' => [
                    'nullable',
                    Rule::exists('academic_years', 'id')->where('school_id', $this->getSchoolId()),
                ],
                'attendance_type' => ['required', 'integer', Rule::enum(TransportAttendanceType::class)],
                'attendance_date' => 'required|date|before_or_equal:today',
                'students' => 'required|array|min:1',
                'students.*' => [
                    'required',
                    Rule::exists('students', 'id')->where('school_id', $this->getSchoolId()),
                ],
                'checked_students' => 'nullable|array',
                'checked_students.*' => 'integer',
            ]);
            $summary = $this->attendanceService->markBulkAttendance($this->getSchool(), $validated);

            return response()->json([
                'success' => true,
                'message' => "Attendance synchronized. Present: {$summary['present']}, Absent: {$summary['absent']}",
                'stats' => $summary,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Receptionist transport mark attendance failed', [
                'exception' => $e,
                'school_id' => $this->getSchoolId(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to save attendance. Please try again.',
            ], 500);
        }
    }

    /**
     * Display transport attendance month-wise report.
     */
    public function monthWiseReport(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $vehicles = Vehicle::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('vehicle_no')
            ->get();

        $selectedVehicle = null;
        $selectedRoute = null;
        $selectedMonth = $request->input('month', date('Y-m'));
        $reportData = ['students' => [], 'days_in_month' => 0];

        if ($request->filled('vehicle_id') && $request->filled('route_id') && $request->filled('month')) {
            $selectedVehicle = Vehicle::where('school_id', $schoolId)->findOrFail($request->input('vehicle_id'));
            $selectedRoute = TransportRoute::where('school_id', $schoolId)->findOrFail($request->input('route_id'));

            try {
                $reportData = $this->attendanceService->getMonthWiseReport(
                    $this->getSchool(),
                    (int) $request->input('vehicle_id'),
                    (int) $request->input('route_id'),
                    (string) $request->input('month')
                );
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors());
            }
        }

        $students = $reportData['students'];

        return view('receptionist.transport-attendance.month-wise-report', compact(
            'vehicles',
            'selectedVehicle',
            'selectedRoute',
            'selectedMonth',
            'students',
            'reportData'
        ));
    }

    /**
     * Get routes for a selected vehicle (AJAX) - for month-wise report.
     */
    public function getRoutesForReport(Request $request)
    {
        try {
            $request->validate([
                'vehicle_id' => [
                    'required',
                    Rule::exists('vehicles', 'id')->where('school_id', $this->getSchoolId()),
                ],
            ]);

            $schoolId = $this->getSchoolId();

            $routes = TransportRoute::where('school_id', $schoolId)
                ->where('vehicle_id', $request->input('vehicle_id'))
                ->where('status', RouteStatus::Active)
                ->orderBy('route_name')
                ->get(['id', 'route_name']);

            return response()->json([
                'success' => true,
                'routes' => $routes->map(fn ($route) => [
                    'id' => $route->id,
                    'route_name' => $route->route_name,
                ])->values()->all(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Receptionist transport getRoutesForReport failed', [
                'exception' => $e,
                'school_id' => $this->getSchoolId(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load routes. Please try again.',
            ], 500);
        }
    }
}
