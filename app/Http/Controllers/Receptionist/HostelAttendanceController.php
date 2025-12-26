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
}
