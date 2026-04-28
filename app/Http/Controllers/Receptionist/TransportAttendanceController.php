<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\TransportAttendance;
use App\Models\StudentTransportAssignment;
use App\Models\Vehicle;
use App\Models\TransportRoute;
use App\Models\AcademicYear;
use App\Enums\TransportAttendanceType;
use App\Services\School\TransportAttendanceService;
use Illuminate\Http\Request;
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
            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
            ]);

            $routesArray = $this->attendanceService->getRoutesForVehicle(
                $this->getSchool(),
                (int) $request->vehicle_id
            )->values()->toArray();

            return response()->json([
                'success' => true,
                'routes' => $routesArray,
            ]);
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
     * Get students for a selected route (AJAX).
     */
    public function getStudents(Request $request)
    {
        try {
            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'route_id' => 'required|exists:transport_routes,id',
            ]);

            $students = $this->attendanceService->getStudentsForRoute(
                $this->getSchool(),
                (int) $request->vehicle_id,
                (int) $request->route_id
            );

            return response()->json([
                'success' => true,
                'students' => $students,
            ]);
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
     * Store transport attendance records.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'route_id' => 'required|exists:transport_routes,id',
                'attendance_type' => ['required', 'integer', Rule::enum(TransportAttendanceType::class)],
                'attendance_date' => 'required|date',
                'students' => 'required|array|min:1',
                'students.*' => 'required|exists:students,id',
                'checked_students' => 'nullable|array',
            ]);
            $summary = $this->attendanceService->markBulkAttendance($this->getSchool(), $validated);

            return response()->json([
                'success' => true,
                'message' => "Attendance synchronized. Present: {$summary['present']}, Absent: {$summary['absent']}",
                'stats' => $summary
            ]);

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
     * Display transport attendance month-wise report.
     */
    public function monthWiseReport(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        // Get all vehicles for the school
        $vehicles = Vehicle::where('school_id', $schoolId)
            ->orderBy('vehicle_no')
            ->get();

        $selectedVehicle = null;
        $selectedRoute = null;
        $selectedMonth = $request->input('month', date('Y-m'));
        $attendanceData = [];
        $students = [];

        // If filters are provided, fetch data
        if ($request->filled('vehicle_id') && $request->filled('route_id') && $request->filled('month')) {
            $vehicleId = $request->input('vehicle_id');
            $routeId = $request->input('route_id');
            $month = $request->input('month');

            // Validate vehicle and route
            $selectedVehicle = Vehicle::where('school_id', $schoolId)
                ->where('id', $vehicleId)
                ->firstOrFail();

            $selectedRoute = TransportRoute::where('school_id', $schoolId)
                ->where('id', $routeId)
                ->where('vehicle_id', $vehicleId)
                ->firstOrFail();

            // Get current academic year
            $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                ->where('is_current', true)
                ->first();

            if (!$currentAcademicYear) {
                $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                    ->latest('start_date')
                    ->first();
            }

            if ($currentAcademicYear) {
                // Get all students assigned to this route
                $assignments = StudentTransportAssignment::with(['student'])
                    ->where('school_id', $schoolId)
                    ->where('route_id', $routeId)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->whereNull('deleted_at')
                    ->get();

                $studentIds = $assignments->pluck('student_id')->toArray();

                // Parse month (format: Y-m)
                $year = (int)substr($month, 0, 4);
                $monthNum = (int)substr($month, 5, 2);
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

                // Get all attendance records for this route, month, and students
                $startDate = "{$year}-{$monthNum}-01";
                $endDate = "{$year}-{$monthNum}-{$daysInMonth}";

                $attendances = TransportAttendance::with(['student'])
                    ->where('school_id', $schoolId)
                    ->where('vehicle_id', $vehicleId)
                    ->where('route_id', $routeId)
                    ->whereIn('student_id', $studentIds)
                    ->whereBetween('attendance_date', [$startDate, $endDate])
                    ->where('is_present', true)
                    ->get();

                // Build student list with attendance data
                foreach ($assignments as $assignment) {
                    $student = $assignment->student;
                    $studentData = [
                        'id' => $student->id,
                        'admission_no' => $student->admission_no,
                        'name' => trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name),
                        'days' => []
                    ];

                    // Initialize all days as empty
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $studentData['days'][$day] = [];
                    }

                    // Fill in attendance data
                    $studentAttendances = $attendances->where('student_id', $student->id);
                    foreach ($studentAttendances as $attendance) {
                        $day = (int)$attendance->attendance_date->format('d');
                        if ($day >= 1 && $day <= $daysInMonth) {
                            // Get attendance type code
                            $typeCode = $this->getAttendanceTypeCode($attendance->attendance_type);
                            $studentData['days'][$day][] = $typeCode;
                        }
                    }

                    $students[] = $studentData;
                }
            }
        }

        return view('receptionist.transport-attendance.month-wise-report', compact(
            'vehicles',
            'selectedVehicle',
            'selectedRoute',
            'selectedMonth',
            'students',
            'attendanceData'
        ));
    }

    /**
     * Get attendance type code (PBS, DSC, PSC, DBS).
     */
    private function getAttendanceTypeCode(TransportAttendanceType $type): string
    {
        return match($type) {
            TransportAttendanceType::PickupFromBusStop => 'PBS',
            TransportAttendanceType::DropAtSchoolCampus => 'DSC',
            TransportAttendanceType::PickupFromSchoolCampus => 'PSC',
            TransportAttendanceType::DropAtBusStop => 'DBS',
        };
    }

    /**
     * Get routes for a selected vehicle (AJAX) - for month-wise report.
     */
    public function getRoutesForReport(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();
            
            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
            ]);

            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            
            // Verify tenant ownership
            if ($vehicle->school_id !== $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['vehicle_id' => ['The selected vehicle is not part of this institutional registry.']]
                ], 422);
            }

            // Get routes for this vehicle
            $routes = TransportRoute::where('school_id', $schoolId)
                ->where('vehicle_id', $request->vehicle_id)
                ->where('status', RouteStatus::Active)
                ->orderBy('route_name')
                ->get(['id', 'route_name']);
            
            $routesArray = $routes->map(function($route) {
                    return [
                        'id' => $route->id,
                        'route_name' => $route->route_name,
                    ];
                })
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'routes' => $routesArray,
            ]);
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
}
