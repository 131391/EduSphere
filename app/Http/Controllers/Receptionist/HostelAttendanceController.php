<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\HostelAttendance;
use App\Models\HostelBedAssignment;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

        return view('receptionist.hostel-attendance.index', compact('hostels'));
    }

    /**
     * Get students for a selected hostel (AJAX).
     */
    public function getStudents(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
        ]);

        $hostel = Hostel::findOrFail($request->hostel_id);
        
        // Verify tenant ownership
        if ($hostel->school_id !== $schoolId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
                'class_name' => $student->class && $student->class->class_name ? $student->class->class_name : 'N/A',
                'section_name' => $student->section && $student->section->section_name ? $student->section->section_name : '',
                'floor_name' => $assignment->floor ? $assignment->floor->floor_name : 'N/A',
                'room_name' => $assignment->room ? $assignment->room->room_name : 'N/A',
                'bed_no' => $assignment->bed_no ?? 'N/A',
            ];
        })->filter()->values()->toArray();

        return response()->json([
            'success' => true,
            'students' => $studentsArray,
        ]);
    }

    /**
     * Store hostel attendance records.
     */
    public function store(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $validated = $request->validate([
            'hostel_id' => 'required|exists:hostels,id',
            'attendance_date' => 'required|date',
            'students' => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.is_present' => 'required|boolean',
            'students.*.remarks' => 'nullable|string|max:500',
        ]);

        // Verify hostel belongs to school
        $hostel = Hostel::findOrFail($validated['hostel_id']);
        if ($hostel->school_id !== $schoolId) {
            return back()->withErrors(['hostel_id' => 'Invalid hostel selected.'])->withInput();
        }

        // Verify all students belong to the selected hostel
        $studentIds = collect($validated['students'])->pluck('student_id')->toArray();
        $validAssignments = HostelBedAssignment::where('school_id', $schoolId)
            ->where('hostel_id', $validated['hostel_id'])
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id')
            ->toArray();

        $invalidStudents = array_diff($studentIds, $validAssignments);
        if (!empty($invalidStudents)) {
            return back()->withErrors(['students' => 'Some selected students are not assigned to this hostel.'])->withInput();
        }

        // Process attendance in a transaction
        DB::beginTransaction();
        try {
            foreach ($validated['students'] as $studentData) {
                $studentId = $studentData['student_id'];
                $isPresent = $studentData['is_present'] ?? true;
                $remarks = $studentData['remarks'] ?? null;

                // Check if attendance already exists for this student and date
                $existingAttendance = HostelAttendance::where('school_id', $schoolId)
                    ->where('student_id', $studentId)
                    ->where('attendance_date', $validated['attendance_date'])
                    ->first();

                if ($existingAttendance) {
                    // Update existing record
                    $existingAttendance->update([
                        'is_present' => $isPresent,
                        'remarks' => $remarks,
                        'marked_by' => Auth::id(),
                    ]);
                } else {
                    // Create new record
                    HostelAttendance::create([
                        'school_id' => $schoolId,
                        'student_id' => $studentId,
                        'hostel_id' => $validated['hostel_id'],
                        'attendance_date' => $validated['attendance_date'],
                        'is_present' => $isPresent,
                        'remarks' => $remarks,
                        'marked_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('receptionist.hostel-attendance.index')
                ->with('success', 'Hostel attendance marked successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to save attendance: ' . $e->getMessage()])->withInput();
        }
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
            $query->whereHas('student', function($q) use ($search) {
                $q->where('admission_no', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'attendance_date');
        $sortDirection = $request->get('direction', 'desc');
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
                $query->orderBy('attendance_date', $sortDirection);
            }
        } else {
            $query->orderBy('attendance_date', 'desc');
        }

        // Export functionality
        if ($request->has('export') && $request->export === 'excel') {
            // For export, we need to load relationships
            $exportQuery = clone $query;
            return $this->exportToExcel($exportQuery->get());
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $attendances = $query->paginate($perPage)->withQueryString();

        // Load bed assignments for all students in the current page
        $studentIds = $attendances->pluck('student_id')->unique();
        $hostelIds = $attendances->pluck('hostel_id')->unique();
        
        $bedAssignments = HostelBedAssignment::where('school_id', $schoolId)
            ->whereIn('student_id', $studentIds)
            ->whereIn('hostel_id', $hostelIds)
            ->whereNull('deleted_at')
            ->with(['floor', 'room'])
            ->get()
            ->keyBy(function($assignment) {
                return $assignment->student_id . '_' . $assignment->hostel_id;
            });

        // Attach bed assignments to attendance records
        $attendances->getCollection()->transform(function($attendance) use ($bedAssignments) {
            $key = $attendance->student_id . '_' . $attendance->hostel_id;
            $assignment = $bedAssignments->get($key);
            if ($assignment) {
                $attendance->bed_assignment = $assignment;
                $attendance->bed_no = $assignment->bed_no;
                $attendance->floor_name = $assignment->floor ? $assignment->floor->floor_name : null;
                $attendance->room_name = $assignment->room ? $assignment->room->room_name : null;
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
            'selectedHostel'
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
                    $student->class ? $student->class->class_name : 'N/A',
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
