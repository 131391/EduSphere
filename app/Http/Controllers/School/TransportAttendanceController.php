<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreTransportAttendanceRequest;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use App\Models\AcademicYear;
use App\Models\StudentTransportAssignment;
use App\Services\School\TransportAttendanceService;
use App\Enums\GeneralStatus;
use App\Enums\TransportAttendanceType;
use Illuminate\Http\Request;

class TransportAttendanceController extends TenantController
{
    protected TransportAttendanceService $attendanceService;

    public function __construct(TransportAttendanceService $attendanceService)
    {
        parent::__construct();
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $routes = TransportRoute::where('school_id', $schoolId)->where('status', \App\Enums\RouteStatus::Active)->get();
        $vehicles = Vehicle::where('school_id', $schoolId)->where('is_active', true)->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->get();
        
        $attendanceTypes = TransportAttendanceType::options();

        // If filtering for a specific route/vehicle to mark attendance
        $students = collect();
        if ($request->filled('route_id') && $request->filled('academic_year_id')) {
            $students = StudentTransportAssignment::with(['student', 'busStop'])
                ->where('school_id', $schoolId)
                ->where('route_id', $request->route_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('status', GeneralStatus::Active)
                ->get()
                ->sortBy(function ($assignment) {
                    return $assignment->busStop?->distance_from_institute ?? 9999;
                });
        }

        return view('school.transport.attendance', compact('routes', 'vehicles', 'academicYears', 'attendanceTypes', 'students'));
    }

    public function store(StoreTransportAttendanceRequest $request)
    {
        try {
            $this->attendanceService->markBulkAttendance(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transport attendance marked successfully!',
                ]);
            }

            return back()->with('success', 'Transport attendance marked successfully!');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to mark attendance: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to mark attendance: ' . $e->getMessage());
        }
    }

    public function getStudents(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $students = StudentTransportAssignment::with(['student', 'busStop'])
            ->where('school_id', $schoolId)
            ->where('route_id', $request->route_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('status', GeneralStatus::Active)
            ->get()
            ->sortBy(function ($assignment) {
                return $assignment->busStop?->distance_from_institute ?? 9999;
            })
            ->values();

        return response()->json($students);
    }
}
