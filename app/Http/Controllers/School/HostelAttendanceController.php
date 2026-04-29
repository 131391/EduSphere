<?php

namespace App\Http\Controllers\School;

use App\Enums\GeneralStatus;
use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreHostelAttendanceRequest;
use App\Models\Hostel;
use App\Models\HostelAttendance;
use App\Models\HostelBedAssignment;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use App\Services\School\HostelAttendanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $hostels = Hostel::where('school_id', $schoolId)
            ->orderBy('hostel_name')
            ->get();

        $academicYears = \App\Models\AcademicYear::where('school_id', $schoolId)->get();

        $today = now()->toDateString();
        $stats = [
            'total_residents' => HostelBedAssignment::where('school_id', $schoolId)
                ->where('status', GeneralStatus::Active)
                ->whereNull('deleted_at')
                ->count(),
            'present_today' => HostelAttendance::where('school_id', $schoolId)
                ->where('attendance_date', $today)
                ->where('is_present', true)
                ->count(),
            'absent_today' => HostelAttendance::where('school_id', $schoolId)
                ->where('attendance_date', $today)
                ->where('is_present', false)
                ->count(),
        ];

        return view('school.hostel.attendance', compact('hostels', 'stats', 'academicYears'));
    }

    public function getFloors(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $floors = HostelFloor::where('school_id', $schoolId)
            ->where('hostel_id', $request->hostel_id)
            ->orderBy('floor_name')
            ->get();

        return response()->json($floors);
    }

    public function getRooms(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $rooms = HostelRoom::where('school_id', $schoolId)
            ->where('hostel_floor_id', $request->hostel_floor_id)
            ->orderBy('room_name')
            ->get();

        return response()->json($rooms);
    }

    public function getStudents(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();

            $request->validate([
                'hostel_id' => ['required', Rule::exists('hostels', 'id')->where('school_id', $schoolId)],
            ]);

            $assignments = HostelBedAssignment::where('school_id', $schoolId)
                ->where('hostel_id', $request->hostel_id)
                ->where('status', GeneralStatus::Active)
                ->with([
                    'student' => function ($query) {
                        $query->with(['class', 'section']);
                    },
                    'floor',
                    'room',
                ])
                ->get();

            $studentsArray = $assignments->map(function ($assignment) {
                $student = $assignment->student;
                if (!$student) {
                    return null;
                }

                return [
                    'id' => $student->id,
                    'student_id' => $student->id,
                    'admission_no' => $student->admission_no,
                    'name' => $student->full_name,
                    'class_name' => $student->class ? $student->class->name : 'N/A',
                    'floor_id' => $assignment->hostel_floor_id,
                    'room_id' => $assignment->hostel_room_id,
                    'bed_no' => $assignment->bed_no ?? 'N/A',
                    'floor_name' => $assignment->floor?->floor_name ?? 'N/A',
                    'room_name' => $assignment->room?->room_name ?? 'N/A',
                    'remarks' => '',
                ];
            })->filter()->values()->toArray();

            return response()->json([
                'success' => true,
                'students' => $studentsArray,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve resident list: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreHostelAttendanceRequest $request)
    {
        try {
            $this->attendanceService->markBulkAttendance(
                $this->getSchool(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Hostel attendance marked successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function report(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $hostels = Hostel::where('school_id', $schoolId)
            ->orderBy('hostel_name')
            ->get();

        $query = HostelAttendance::where('school_id', $schoolId)
            ->with([
                'student' => function ($q) {
                    $q->with(['class', 'section']);
                },
                'hostel',
                'markedBy',
            ]);

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('attendance_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('attendance_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search, $schoolId) {
                $q->where('school_id', $schoolId)
                    ->where(function ($sub) use ($search) {
                        $sub->where('admission_no', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $stats = [
            'total_logs' => (clone $query)->count(),
            'compliance_present' => (clone $query)->where('is_present', true)->count(),
            'compliance_percentage' => 0,
        ];

        if ($stats['total_logs'] > 0) {
            $stats['compliance_percentage'] = round(($stats['compliance_present'] / $stats['total_logs']) * 100);
        }

        $sortColumn = $request->input('sort', 'attendance_date');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSortColumns = ['attendance_date', 'admission_no', 'hostel_name'];

        if (in_array($sortColumn, $allowedSortColumns, true)) {
            if ($sortColumn === 'admission_no') {
                $query->join('students', 'hostel_attendances.student_id', '=', 'students.id')
                    ->orderBy('students.admission_no', $sortDirection)
                    ->select('hostel_attendances.*');
            } elseif ($sortColumn === 'hostel_name') {
                $query->join('hostels', 'hostel_attendances.hostel_id', '=', 'hostels.id')
                    ->orderBy('hostels.hostel_name', $sortDirection)
                    ->select('hostel_attendances.*');
            } else {
                $query->orderBy('hostel_attendances.attendance_date', $sortDirection);
            }
        } else {
            $query->orderBy('hostel_attendances.attendance_date', 'desc');
        }

        if ($request->has('export') && $request->export === 'excel') {
            $exportQuery = clone $query;
            return $this->exportToCsv($exportQuery->get());
        }

        $perPage = $request->input('per_page', 15);
        $attendances = $query->paginate($perPage)->withQueryString();

        $attendances->load([
            'student' => fn($q) => $q->with(['class', 'section']),
            'hostel',
        ]);

        $studentIds = $attendances->pluck('student_id')->unique();
        $hostelIds = $attendances->pluck('hostel_id')->unique();

        $bedAssignments = HostelBedAssignment::where('school_id', $schoolId)
            ->whereIn('student_id', $studentIds)
            ->whereIn('hostel_id', $hostelIds)
            ->whereNull('deleted_at')
            ->with(['floor', 'room'])
            ->get()
            ->keyBy(fn($a) => $a->student_id . '_' . $a->hostel_id);

        $attendances->getCollection()->transform(function ($attendance) use ($bedAssignments) {
            $assignment = $bedAssignments->get($attendance->student_id . '_' . $attendance->hostel_id);
            if ($assignment) {
                $attendance->bed_no = $assignment->bed_no;
                $attendance->floor_name = $assignment->floor?->floor_name;
                $attendance->room_name = $assignment->room?->room_name;
            }
            return $attendance;
        });

        $selectedHostel = null;
        if ($request->filled('hostel_id')) {
            $selectedHostel = Hostel::find($request->hostel_id);
        }

        return view('school.hostel.attendance-report', compact(
            'hostels',
            'attendances',
            'selectedHostel',
            'stats'
        ));
    }

    public function monthWiseReport(Request $request)
    {
        return $this->report($request);
    }

    private function exportToCsv($attendances)
    {
        $filename = 'hostel_attendance_report_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'SR NO',
            'ADMISSION NO',
            'STUDENT NAME',
            'CLASS',
            'HOSTEL',
            'FLOOR',
            'ROOM',
            'BED NO',
            'ATTENDANCE',
            'ATTENDANCE DATE',
            'REMARKS',
        ];

        $callback = function () use ($attendances, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            $srNo = 1;
            foreach ($attendances as $attendance) {
                $student = $attendance->student;
                $rowData = [
                    $srNo++,
                    $student->admission_no ?? '',
                    trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                    $student->class ? $student->class->name : 'N/A',
                    $attendance->hostel ? $attendance->hostel->hostel_name : 'N/A',
                    $attendance->floor_name ?? 'N/A',
                    $attendance->room_name ?? 'N/A',
                    $attendance->bed_no ?? 'N/A',
                    $attendance->is_present ? 'Present' : 'Absent',
                    $attendance->attendance_date ? $attendance->attendance_date->format('d/m/Y') : '',
                    $attendance->remarks ?? '',
                ];
                fputcsv($file, $rowData);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
