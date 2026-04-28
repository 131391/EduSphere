<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreHostelAttendanceRequest;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use App\Models\AcademicYear;
use App\Models\HostelBedAssignment;
use App\Services\School\HostelAttendanceService;
use App\Enums\GeneralStatus;
use Illuminate\Http\Request;

class HostelAttendanceController extends TenantController
{
    protected HostelAttendanceService $attendanceService;

    public function __construct(HostelAttendanceService $attendanceService)
    {
        parent::__construct();
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->get();
        
        $students = collect();
        if ($request->filled('hostel_room_id') && $request->filled('academic_year_id')) {
            $students = HostelBedAssignment::with(['student'])
                ->where('school_id', $schoolId)
                ->where('hostel_room_id', $request->hostel_room_id)
                ->where('status', GeneralStatus::Active)
                ->get();
        }

        return view('school.hostel.attendance', compact('hostels', 'academicYears', 'students'));
    }

    public function store(StoreHostelAttendanceRequest $request)
    {
        try {
            $this->attendanceService->markBulkAttendance(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Hostel attendance marked successfully!',
                ]);
            }

            return back()->with('success', 'Hostel attendance marked successfully!');
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

    public function getResidents(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $students = HostelBedAssignment::with(['student'])
            ->where('school_id', $schoolId)
            ->where('hostel_room_id', $request->hostel_room_id)
            ->where('status', GeneralStatus::Active)
            ->get()
            ->sortBy(function ($assignment) {
                return $assignment->student?->full_name;
            })
            ->values();

        return response()->json($students);
    }

    public function monthWiseReport(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();
        $selectedHostel = null;
        $selectedFloor = null;
        $selectedRoom = null;
        $selectedMonth = $request->input('month', date('Y-m'));
        $reportData = ['students' => [], 'days_in_month' => 0];

        if ($request->filled('hostel_id') && $request->filled('hostel_floor_id') && $request->filled('hostel_room_id') && $request->filled('month')) {
            $selectedHostel = Hostel::where('school_id', $schoolId)->findOrFail($request->hostel_id);
            $selectedFloor = HostelFloor::where('school_id', $schoolId)->findOrFail($request->hostel_floor_id);
            $selectedRoom = HostelRoom::where('school_id', $schoolId)->findOrFail($request->hostel_room_id);

            $reportData = $this->attendanceService->getMonthWiseReport(
                $this->getSchool(),
                $request->hostel_id,
                $request->hostel_floor_id,
                $request->hostel_room_id,
                $request->month
            );
        }

        return view('school.hostel.attendance-report', compact(
            'hostels',
            'selectedHostel',
            'selectedFloor',
            'selectedRoom',
            'selectedMonth',
            'reportData'
        ));
    }
}
