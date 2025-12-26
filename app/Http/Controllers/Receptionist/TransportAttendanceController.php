<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\TransportAttendance;
use App\Models\StudentTransportAssignment;
use App\Models\Vehicle;
use App\Models\TransportRoute;
use App\Models\AcademicYear;
use App\Enums\TransportAttendanceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        return view('receptionist.transport-attendance.index', compact(
            'vehicles',
            'attendanceTypes',
            'currentAcademicYear'
        ));
    }

    /**
     * Get routes for a selected vehicle (AJAX).
     */
    public function getRoutes(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        
        // Verify tenant ownership
        if ($vehicle->school_id !== $schoolId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get routes for this vehicle
        $routes = TransportRoute::where('school_id', $schoolId)
            ->where('vehicle_id', $request->vehicle_id)
            ->where('status', TransportRoute::STATUS_ACTIVE)
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
    }

    /**
     * Get students for a selected route (AJAX).
     */
    public function getStudents(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'route_id' => 'required|exists:transport_routes,id',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        $route = TransportRoute::findOrFail($request->route_id);
        
        // Verify tenant ownership
        if ($vehicle->school_id !== $schoolId || $route->school_id !== $schoolId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verify route belongs to vehicle
        if ($route->vehicle_id !== $vehicle->id) {
            return response()->json(['error' => 'Route does not belong to the selected vehicle'], 400);
        }

        // Get current academic year
        $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        if (!$currentAcademicYear) {
            $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                ->latest('start_date')
                ->first();
        }

        if (!$currentAcademicYear) {
            return response()->json([
                'success' => true,
                'students' => [],
                'message' => 'No academic year found',
            ]);
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
    }

    /**
     * Store transport attendance records.
     */
    public function store(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'route_id' => 'required|exists:transport_routes,id',
            'attendance_type' => ['required', 'integer', Rule::enum(TransportAttendanceType::class)],
            'attendance_date' => 'required|date',
            'students' => 'required|array|min:1',
            'students.*' => 'required|exists:students,id',
        ]);

        // Verify tenant ownership
        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);
        $route = TransportRoute::findOrFail($validated['route_id']);

        if ($vehicle->school_id !== $schoolId || $route->school_id !== $schoolId) {
            abort(403, 'Unauthorized access');
        }

        // Verify route belongs to vehicle
        if ($route->vehicle_id !== $vehicle->id) {
            return back()->withErrors(['route_id' => 'Route does not belong to the selected vehicle.'])->withInput();
        }

        // Get current academic year
        $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first();

        if (!$currentAcademicYear) {
            $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
                ->latest('start_date')
                ->first();
        }

        if (!$currentAcademicYear) {
            return back()->withErrors(['academic_year' => 'No academic year found. Please create an academic year first.'])->withInput();
        }

        // Get student assignments to verify they belong to the route
        $assignments = StudentTransportAssignment::where('school_id', $schoolId)
            ->where('route_id', $validated['route_id'])
            ->where('academic_year_id', $currentAcademicYear->id)
            ->whereIn('student_id', $validated['students'])
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('student_id');

        // Check if all students are assigned to this route
        $invalidStudents = array_diff($validated['students'], $assignments->pluck('student_id')->toArray());
        if (!empty($invalidStudents)) {
            return back()->withErrors(['students' => 'Some selected students are not assigned to this route.'])->withInput();
        }

        // Get students that were checked (present) vs unchecked (absent)
        $checkedStudents = $request->input('checked_students', []);
        // Convert to integers for comparison
        $checkedStudents = array_map('intval', $checkedStudents);
        $isPresentMap = [];
        foreach ($validated['students'] as $studentId) {
            $isPresentMap[$studentId] = in_array((int)$studentId, $checkedStudents, true);
        }

        // Use transaction to ensure data consistency
        DB::beginTransaction();
        try {
            $attendanceRecords = [];
            $markedBy = auth()->id();

            foreach ($validated['students'] as $studentId) {
                try {
                    // Check if attendance already exists for this student, date, and type
                    $existing = TransportAttendance::where('school_id', $schoolId)
                        ->where('student_id', $studentId)
                        ->where('attendance_date', $validated['attendance_date'])
                        ->where('attendance_type', (int)$validated['attendance_type'])
                        ->first();

                    if ($existing) {
                        // Update existing record
                        $existing->update([
                            'is_present' => $isPresentMap[$studentId] ?? false,
                            'marked_by' => $markedBy,
                        ]);
                        $attendanceRecords[] = $existing;
                    } else {
                        // Create new record
                        $attendanceRecords[] = TransportAttendance::create([
                            'school_id' => $schoolId,
                            'student_id' => $studentId,
                            'vehicle_id' => $validated['vehicle_id'],
                            'route_id' => $validated['route_id'],
                            'academic_year_id' => $currentAcademicYear->id,
                            'attendance_date' => $validated['attendance_date'],
                            'attendance_type' => (int)$validated['attendance_type'], // Ensure integer
                            'is_present' => $isPresentMap[$studentId] ?? false,
                            'marked_by' => $markedBy,
                        ]);
                    }
                } catch (\Exception $e) {
                    throw $e; // Re-throw to trigger rollback
                }
            }

            DB::commit();

            $presentCount = count(array_filter($isPresentMap));
            $absentCount = count($isPresentMap) - $presentCount;

            return redirect()->route('receptionist.transport-attendance.index')
                ->with('success', "Attendance marked successfully. Present: {$presentCount}, Absent: {$absentCount}");

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Show user-friendly error message
            $errorMessage = 'Failed to save attendance. ';
            if (str_contains($e->getMessage(), 'Table') && str_contains($e->getMessage(), "doesn't exist")) {
                $errorMessage .= 'Database table not found. Please contact administrator.';
            } elseif (str_contains($e->getMessage(), 'SQLSTATE')) {
                $errorMessage .= 'Database error occurred. Please try again.';
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            return back()->withErrors(['error' => $errorMessage])->withInput();
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
        $selectedMonth = $request->get('month', date('Y-m'));
        $attendanceData = [];
        $students = [];

        // If filters are provided, fetch data
        if ($request->filled('vehicle_id') && $request->filled('route_id') && $request->filled('month')) {
            $vehicleId = $request->get('vehicle_id');
            $routeId = $request->get('route_id');
            $month = $request->get('month');

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
        $schoolId = $this->getSchoolId();
        
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        
        // Verify tenant ownership
        if ($vehicle->school_id !== $schoolId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get routes for this vehicle
        $routes = TransportRoute::where('school_id', $schoolId)
            ->where('vehicle_id', $request->vehicle_id)
            ->where('status', TransportRoute::STATUS_ACTIVE)
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
    }
}

