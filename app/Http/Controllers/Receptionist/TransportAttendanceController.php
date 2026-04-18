<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\TransportAttendance;
use App\Models\StudentTransportAssignment;
use App\Models\Vehicle;
use App\Models\TransportRoute;
use App\Models\AcademicYear;
use App\Enums\TransportAttendanceType;
use App\Enums\RouteStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransportAttendanceController extends TenantController
{
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

    /**
     * Get students for a selected route (AJAX).
     */
    public function getStudents(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();
            
            $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'route_id' => 'required|exists:transport_routes,id',
            ]);

            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            $route = TransportRoute::findOrFail($request->route_id);
            
            // Verify tenant ownership
            if ($vehicle->school_id !== $schoolId || $route->school_id !== $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['vehicle_id' => ['Unauthorized data access detected.']]
                ], 422);
            }

            // Verify route belongs to vehicle
            if ($route->vehicle_id !== $vehicle->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Topology mismatch',
                    'errors' => ['route_id' => ['The selected route does not belong to the selected vehicle manifest.']]
                ], 422);
            }

            // Get current academic year
            $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                ->where('is_current', true)
                ->first() ?: AcademicYear::where('school_id', $schoolId)->latest('start_date')->first();

            if (!$currentAcademicYear) {
                return response()->json([
                    'success' => false,
                    'message' => 'Temporal context missing',
                    'errors' => ['academic_year' => ['No active academic session found. Please establish an academic year before verifying boarding.']]
                ], 422);
            }

            // Get students assigned to this route in the current academic year
            $students = StudentTransportAssignment::with([
                    'student.class',
                    'student.section',
                    'busStop'
                ])
                ->where('school_id', $schoolId)
                ->where('route_id', $request->route_id)
                ->where('academic_year_id', $currentAcademicYear->id)
                ->whereNull('deleted_at') // Only active assignments
                ->get()
                ->map(function ($assignment) {
                    return [
                        'id' => $assignment->student_id,
                        'assignment_id' => $assignment->id,
                        'admission_no' => $assignment->student->admission_no,
                        'name' => trim($assignment->student->first_name . ' ' . $assignment->student->middle_name . ' ' . $assignment->student->last_name),
                        'class' => $assignment->student->class->name ?? 'N/A',
                        'section' => $assignment->student->section->name ?? 'N/A',
                        'bus_stop_name' => $assignment->busStop->bus_stop_name ?? 'N/A',
                    ];
                });

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
            $schoolId = $this->getSchoolId();
            
            $validated = $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'route_id' => 'required|exists:transport_routes,id',
                'attendance_type' => ['required', 'integer', Rule::enum(TransportAttendanceType::class)],
                'attendance_date' => 'required|date',
                'students' => 'required|array|min:1',
                'students.*' => 'required|exists:students,id',
                'checked_students' => 'nullable|array',
            ]);

            // Verify tenant ownership
            $vehicle = Vehicle::where('id', $validated['vehicle_id'])->where('school_id', $schoolId)->firstOrFail();
            $route = TransportRoute::where('id', $validated['route_id'])->where('school_id', $schoolId)->firstOrFail();

            if ($route->vehicle_id !== $vehicle->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Topology mismatch',
                    'errors' => ['route_id' => ['The selected route does not belong to the selected vehicle manifest.']]
                ], 422);
            }

            $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                ->where('is_current', true)
                ->first() ?: AcademicYear::where('school_id', $schoolId)->latest('start_date')->first();

            if (!$currentAcademicYear) {
                return response()->json([
                    'success' => false,
                    'message' => 'Temporal context missing',
                    'errors' => ['academic_year' => ['No active academic session found.']]
                ], 422);
            }

            // Get students that were checked (present)
            $checkedStudents = $request->input('checked_students', []);
            $checkedStudents = array_map('intval', $checkedStudents);
            
            DB::beginTransaction();
            
            $markedBy = auth()->id();
            $presentCount = 0;
            $absentCount = 0;

            foreach ($validated['students'] as $studentId) {
                $isPresent = in_array((int)$studentId, $checkedStudents, true);
                
                TransportAttendance::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'student_id' => $studentId,
                        'attendance_date' => $validated['attendance_date'],
                        'attendance_type' => (int)$validated['attendance_type'],
                    ],
                    [
                        'vehicle_id' => $validated['vehicle_id'],
                        'route_id' => $validated['route_id'],
                        'academic_year_id' => $currentAcademicYear->id,
                        'is_present' => $isPresent,
                        'marked_by' => $markedBy,
                    ]
                );

                if ($isPresent) $presentCount++; else $absentCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Attendance synchronized. Present: {$presentCount}, Absent: {$absentCount}",
                'stats' => ['present' => $presentCount, 'absent' => $absentCount]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
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

