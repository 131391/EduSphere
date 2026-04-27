<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Http\Requests\Receptionist\StoreHostelAttendanceRequest;
use App\Models\HostelAttendance;
use App\Models\HostelBedAssignment;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HostelAttendanceController extends TenantController
{
    /**
     * Display the hostel attendance form.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        // Get all hostels for the school
        $hostels = Hostel::where('school_id', $schoolId)
            ->orderBy('hostel_name')
            ->get();

        // Calculate Global Stats for today
        $today = now()->toDateString();
        $stats = [
            'total_residents' => HostelBedAssignment::where('school_id', $schoolId)
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

        return view('receptionist.hostel-attendance.index', compact('hostels', 'stats'));
    }

    /**
     * Get students for a selected hostel (AJAX).
     */
    public function getStudents(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();
            
            $request->validate([
                'hostel_id' => ['required', \Illuminate\Validation\Rule::exists('hostels', 'id')->where('school_id', $this->getSchoolId())],
            ]);

            $hostel = Hostel::findOrFail($request->hostel_id);

            // Get active bed assignments for this hostel
            $assignments = HostelBedAssignment::where('school_id', $schoolId)
                ->where('hostel_id', $request->hostel_id)
                ->with([
                    'student' => function($query) {
                        $query->with(['class', 'section']);
                    },
                    'floor', 
                    'room'
                ])
                ->has('student') // Only get assignments with valid students
                ->get();

            $studentsArray = $assignments->map(function($assignment) {
                $student = $assignment->student;
                if (!$student) {
                    return null;
                }
                
                return [
                    'id' => $student->id,
                    'admission_no' => $student->admission_no,
                    'name' => trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')),
                    'class_name' => $student->class ? $student->class->name : 'N/A',
                    'section_name' => $student->section ? $student->section->name : '',
                    'floor_name' => $assignment->floor ? $assignment->floor->floor_name : 'N/A',
                    'room_name' => $assignment->room ? $assignment->room->room_name : 'N/A',
                    'bed_no' => $assignment->bed_no ?? 'N/A',
                ];
            })->filter()->values()->toArray();

            return response()->json([
                'success' => true,
                'students' => $studentsArray,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Manifest retrieval failure: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(StoreHostelAttendanceRequest $request)
    {
        $schoolId = $this->getSchoolId();
        $validated = $request->validated();

        // Verify all submitted students are actually assigned to this hostel.
        $studentIds = collect($validated['students'])->pluck('student_id')->toArray();
        $validAssignmentsCount = HostelBedAssignment::where('school_id', $schoolId)
            ->where('hostel_id', $validated['hostel_id'])
            ->whereIn('student_id', $studentIds)
            ->count();

        if ($validAssignmentsCount !== count(array_unique($studentIds))) {
            return response()->json([
                'success' => false,
                'message' => 'Some students are not assigned to the selected hostel.',
                'errors'  => ['students' => ['Student residency mismatch detected.']],
            ], 422);
        }

        return DB::transaction(function () use ($validated, $schoolId) {
            foreach ($validated['students'] as $studentData) {
                HostelAttendance::updateOrCreate(
                    [
                        'school_id'       => $schoolId,
                        'student_id'      => $studentData['student_id'],
                        'attendance_date' => $validated['attendance_date'],
                    ],
                    [
                        'hostel_id'  => $validated['hostel_id'],
                        'is_present' => $studentData['is_present'],
                        'remarks'    => $studentData['remarks'] ?? null,
                        'marked_by'  => Auth::id(),
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance saved for ' . count($validated['students']) . ' residents.',
            ]);
        });
    }

    /**
     * Display hostel attendance report.
     */
    public function report(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        // Get all hostels for the school
        $hostels = Hostel::where('school_id', $schoolId)
            ->orderBy('hostel_name')
            ->get();

        // Build query with eager loading - use whereHas for filtering to preserve relationships
        $query = HostelAttendance::where('school_id', $schoolId)
            ->with([
                'student' => function($q) {
                    $q->with(['class', 'section']);
                },
                'hostel', 
                'markedBy'
            ]);

        // Filter by hostel
        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('attendance_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('attendance_date', '<=', $request->date_to);
        }

        // Search by student name or admission number using whereHas
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search, $schoolId) {
                $q->where('school_id', $schoolId)
                  ->where(function($sub) use ($search) {
                    $sub->where('admission_no', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Calculate Stats based on filters (before pagination)
        $stats = [
            'total_logs' => (clone $query)->count(),
            'compliance_present' => (clone $query)->where('is_present', true)->count(),
            'compliance_percentage' => 0
        ];
        if ($stats['total_logs'] > 0) {
            $stats['compliance_percentage'] = round(($stats['compliance_present'] / $stats['total_logs']) * 100);
        }

        // Sorting
        $sortColumn   = $request->input('sort', 'attendance_date');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSortColumns = ['attendance_date', 'admission_no', 'hostel_name'];
        if (in_array($sortColumn, $allowedSortColumns)) {
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

        // Export functionality
        if ($request->has('export') && $request->export === 'excel') {
            // For export, we need to load relationships
            $exportQuery = clone $query;
            return $this->exportToExcel($exportQuery->get());
        }

        // Pagination
        $perPage     = $request->input('per_page', 15);
        $attendances = $query->paginate($perPage)->withQueryString();

        $attendances->load([
            'student' => fn($q) => $q->with(['class', 'section']),
            'hostel',
        ]);

        // Attach bed assignment data (floor, room, bed) to each attendance record
        $studentIds = $attendances->pluck('student_id')->unique();
        $hostelIds  = $attendances->pluck('hostel_id')->unique();

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
                $attendance->bed_no     = $assignment->bed_no;
                $attendance->floor_name = $assignment->floor?->floor_name;
                $attendance->room_name  = $assignment->room?->room_name;
            }
            return $attendance;
        });

        // Get selected hostel for display
        $selectedHostel = null;
        if ($request->filled('hostel_id')) {
            $selectedHostel = Hostel::find($request->hostel_id);
        }

        return view('receptionist.hostel-attendance.report', compact(
            'hostels',
            'attendances',
            'selectedHostel',
            'stats'
        ));
    }

    /**
     * Export hostel attendance report to Excel.
     */
    private function exportToExcel($attendances)
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
            
            // Write headers
            fputcsv($file, $headers);
            
            // Write data
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
