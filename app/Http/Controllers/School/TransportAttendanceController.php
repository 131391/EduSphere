<?php

namespace App\Http\Controllers\School;

use App\Enums\TransportAttendanceType;
use App\Http\Controllers\TenantController;
use App\Models\AcademicYear;
use App\Models\TransportAttendance;
use App\Models\TransportRoute;
use App\Models\Vehicle;
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

    public function index()
    {
        $schoolId = $this->getSchoolId();
        $currentAcademicYear = AcademicYear::where('school_id', $schoolId)
            ->where('is_current', true)
            ->first()
            ?: AcademicYear::where('school_id', $schoolId)->latest('start_date')->first();

        $vehicles = Vehicle::where('school_id', $schoolId)
            ->where('is_active', true)
            ->orderBy('vehicle_no')
            ->get();

        $today = now()->toDateString();
        $stats = [
            'total_students' => $currentAcademicYear
                ? \App\Models\StudentTransportAssignment::where('school_id', $schoolId)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->where('status', \App\Enums\GeneralStatus::Active)
                    ->count()
                : 0,
            'boarded_today' => TransportAttendance::where('school_id', $schoolId)
                ->where('attendance_date', $today)
                ->where('is_present', true)
                ->count(),
            'absent_today' => TransportAttendance::where('school_id', $schoolId)
                ->where('attendance_date', $today)
                ->where('is_present', false)
                ->count(),
        ];

        return view('school.transport.attendance', [
            'vehicles' => $vehicles,
            'attendanceTypes' => TransportAttendanceType::cases(),
            'stats' => $stats,
        ]);
    }

    public function getRoutes(Request $request)
    {
        try {
            $validated = $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
            ]);

            $routes = $this->attendanceService->getRoutesForVehicle(
                $this->getSchool(),
                (int) $validated['vehicle_id']
            );

            return response()->json([
                'success' => true,
                'routes' => $routes->values(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load routes: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getStudents(Request $request)
    {
        try {
            $validated = $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'route_id' => 'required|exists:transport_routes,id',
                'academic_year_id' => 'nullable|exists:academic_years,id',
            ]);

            $students = $this->attendanceService->getStudentsForRoute(
                $this->getSchool(),
                (int) $validated['vehicle_id'],
                (int) $validated['route_id'],
                isset($validated['academic_year_id']) ? (int) $validated['academic_year_id'] : null
            );

            return response()->json([
                'success' => true,
                'students' => $students->values(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load students: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'vehicle_id' => 'required|exists:vehicles,id',
                'route_id' => 'required|exists:transport_routes,id',
                'academic_year_id' => 'nullable|exists:academic_years,id',
                'attendance_date' => 'required|date|before_or_equal:today',
                'attendance_type' => ['required', Rule::enum(TransportAttendanceType::class)],
                'attendance_data' => 'nullable|array|min:1',
                'attendance_data.*.student_id' => 'required_with:attendance_data|exists:students,id',
                'attendance_data.*.is_present' => 'required_with:attendance_data|boolean',
                'attendance_data.*.remarks' => 'nullable|string|max:255',
                'students' => 'nullable|array|min:1',
                'students.*' => 'required_with:students|exists:students,id',
                'checked_students' => 'nullable|array',
                'checked_students.*' => 'integer',
            ]);

            if (empty($validated['attendance_data']) && empty($validated['students'])) {
                throw ValidationException::withMessages([
                    'students' => ['Select at least one student before marking attendance.'],
                ]);
            }

            $summary = $this->attendanceService->markBulkAttendance(
                $this->getSchool(),
                $validated
            );

            $message = "Attendance synchronized. Present: {$summary['present']}, Absent: {$summary['absent']}";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'stats' => $summary,
                ]);
            }

            return back()->with('success', $message);
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark attendance: ' . $e->getMessage(),
                ], 500);
            }

            return $this->backWithError('Failed to mark attendance: ' . $e->getMessage());
        }
    }

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
            $selectedVehicle = Vehicle::where('school_id', $schoolId)->findOrFail($request->vehicle_id);
            $selectedRoute = TransportRoute::where('school_id', $schoolId)->findOrFail($request->route_id);
            $reportData = $this->attendanceService->getMonthWiseReport(
                $this->getSchool(),
                (int) $request->vehicle_id,
                (int) $request->route_id,
                $request->month
            );
        }

        return view('school.transport.attendance-report', compact(
            'vehicles',
            'selectedVehicle',
            'selectedRoute',
            'selectedMonth',
            'reportData'
        ));
    }
}
