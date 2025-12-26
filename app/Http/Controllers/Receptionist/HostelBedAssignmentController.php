<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\HostelBedAssignment;
use App\Models\Student;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use App\Models\FeeName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HostelBedAssignmentController extends TenantController
{
    /**
     * Display a listing of hostel bed assignments.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $query = HostelBedAssignment::where('school_id', $schoolId)
            ->with(['student.class', 'student.section', 'hostel', 'floor', 'room']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bed_no', 'like', "%{$search}%")
                  ->orWhereHas('student', function($studentQuery) use ($search) {
                      $studentQuery->where('admission_no', 'like', "%{$search}%")
                                   ->orWhere('first_name', 'like', "%{$search}%")
                                   ->orWhere('middle_name', 'like', "%{$search}%")
                                   ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('hostel', function($hostelQuery) use ($search) {
                      $hostelQuery->where('hostel_name', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $assignments = $query->paginate($perPage)->withQueryString();

        // Get hostels for the dropdown
        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();

        return view('receptionist.hostel-bed-assignments.index', compact('assignments', 'hostels'));
    }

    /**
     * Store a newly created hostel bed assignment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'hostel_id' => 'required|exists:hostels,id',
            'hostel_floor_id' => 'required|exists:hostel_floors,id',
            'hostel_room_id' => 'required|exists:hostel_rooms,id',
            'bed_no' => 'nullable|string|max:255',
            'rent' => 'nullable|numeric|min:0',
            'hostel_assign_date' => 'nullable|date',
            'starting_month' => 'nullable|string|max:255',
        ]);

        $schoolId = $this->getSchoolId();
        
        // Verify student belongs to same school
        $student = Student::findOrFail($validated['student_id']);
        if ($student->school_id !== $schoolId) {
            return back()->withErrors(['student_id' => 'Invalid student selected.'])->withInput();
        }

        // Verify hostel belongs to same school
        $hostel = Hostel::findOrFail($validated['hostel_id']);
        if ($hostel->school_id !== $schoolId) {
            return back()->withErrors(['hostel_id' => 'Invalid hostel selected.'])->withInput();
        }

        // Verify floor belongs to same school and hostel
        $floor = HostelFloor::where('id', $validated['hostel_floor_id'])
            ->where('school_id', $schoolId)
            ->where('hostel_id', $validated['hostel_id'])
            ->first();
            
        if (!$floor) {
            return back()->withErrors(['hostel_floor_id' => 'The selected floor does not belong to the selected hostel.'])->withInput();
        }

        // Verify room belongs to same school, hostel, and floor
        $room = HostelRoom::where('id', $validated['hostel_room_id'])
            ->where('school_id', $schoolId)
            ->where('hostel_id', $validated['hostel_id'])
            ->where('hostel_floor_id', $validated['hostel_floor_id'])
            ->first();
            
        if (!$room) {
            return back()->withErrors(['hostel_room_id' => 'The selected room does not belong to the selected floor.'])->withInput();
        }

        // Check if student already has an active assignment
        $existingAssignment = HostelBedAssignment::where('student_id', $validated['student_id'])
            ->where('hostel_id', $validated['hostel_id'])
            ->whereNull('deleted_at')
            ->first();
            
        if ($existingAssignment) {
            return back()->withErrors(['student_id' => 'This student already has an active assignment for this hostel.'])->withInput();
        }

        $validated['school_id'] = $schoolId;

        try {
            HostelBedAssignment::create($validated);
            return redirect()->route('receptionist.hostel-bed-assignments.index')->with('success', 'Hostel bed assigned successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to assign hostel bed. Please try again.'])->withInput();
        }
    }

    /**
     * Update the specified hostel bed assignment.
     */
    public function update(Request $request, HostelBedAssignment $hostelBedAssignment)
    {
        $this->authorizeTenant($hostelBedAssignment);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'hostel_id' => 'required|exists:hostels,id',
            'hostel_floor_id' => 'required|exists:hostel_floors,id',
            'hostel_room_id' => 'required|exists:hostel_rooms,id',
            'bed_no' => 'nullable|string|max:255',
            'rent' => 'nullable|numeric|min:0',
            'hostel_assign_date' => 'nullable|date',
            'starting_month' => 'nullable|string|max:255',
        ]);

        $schoolId = $this->getSchoolId();
        
        // Verify student belongs to same school
        $student = Student::findOrFail($validated['student_id']);
        if ($student->school_id !== $schoolId) {
            return back()->withErrors(['student_id' => 'Invalid student selected.'])->withInput();
        }

        // Verify hostel belongs to same school
        $hostel = Hostel::findOrFail($validated['hostel_id']);
        if ($hostel->school_id !== $schoolId) {
            return back()->withErrors(['hostel_id' => 'Invalid hostel selected.'])->withInput();
        }

        // Verify floor belongs to same school and hostel
        $floor = HostelFloor::where('id', $validated['hostel_floor_id'])
            ->where('school_id', $schoolId)
            ->where('hostel_id', $validated['hostel_id'])
            ->first();
            
        if (!$floor) {
            return back()->withErrors(['hostel_floor_id' => 'The selected floor does not belong to the selected hostel.'])->withInput();
        }

        // Verify room belongs to same school, hostel, and floor
        $room = HostelRoom::where('id', $validated['hostel_room_id'])
            ->where('school_id', $schoolId)
            ->where('hostel_id', $validated['hostel_id'])
            ->where('hostel_floor_id', $validated['hostel_floor_id'])
            ->first();
            
        if (!$room) {
            return back()->withErrors(['hostel_room_id' => 'The selected room does not belong to the selected floor.'])->withInput();
        }

        // Check if another student already has an active assignment for this hostel (excluding current)
        $existingAssignment = HostelBedAssignment::where('student_id', $validated['student_id'])
            ->where('hostel_id', $validated['hostel_id'])
            ->where('id', '!=', $hostelBedAssignment->id)
            ->whereNull('deleted_at')
            ->first();
            
        if ($existingAssignment) {
            return back()->withErrors(['student_id' => 'This student already has an active assignment for this hostel.'])->withInput();
        }

        try {
            $hostelBedAssignment->update($validated);
            return redirect()->route('receptionist.hostel-bed-assignments.index')->with('success', 'Hostel bed assignment updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update hostel bed assignment. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified hostel bed assignment.
     */
    public function destroy(HostelBedAssignment $hostelBedAssignment)
    {
        $this->authorizeTenant($hostelBedAssignment);

        $hostelBedAssignment->delete();

        return redirect()->route('receptionist.hostel-bed-assignments.index')->with('success', 'Hostel bed assignment deleted successfully.');
    }

    /**
     * Search students by admission number or name (AJAX).
     */
    public function searchStudents(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $request->validate([
            'search' => 'required|string|min:2',
        ]);

        $search = $request->search;
        
        $students = Student::where('school_id', $schoolId)
            ->where(function($query) use ($search) {
                $query->where('admission_no', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
            })
            ->with('class')
            ->limit(10)
            ->get();

        $studentsArray = $students->map(function($student) {
            return [
                'id' => $student->id,
                'admission_no' => $student->admission_no,
                'name' => trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name),
                'class_id' => $student->class_id,
                'class_name' => $student->class->name ?? 'N/A',
                'section_id' => $student->section_id,
                'section_name' => $student->section->name ?? 'N/A',
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'students' => $studentsArray,
        ]);
    }

    /**
     * Get floors for a selected hostel (AJAX).
     */
    public function getFloors(Request $request)
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

        // Get floors for this hostel
        $floors = HostelFloor::where('school_id', $schoolId)
            ->where('hostel_id', $request->hostel_id)
            ->orderBy('floor_name')
            ->get(['id', 'floor_name']);
        
        $floorsArray = $floors->map(function($floor) {
                return [
                    'id' => $floor->id,
                    'floor_name' => $floor->floor_name,
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'success' => true,
            'floors' => $floorsArray,
        ]);
    }

    /**
     * Get rooms for a selected floor (AJAX).
     */
    public function getRooms(Request $request)
    {
        $schoolId = $this->getSchoolId();
        
        $request->validate([
            'hostel_floor_id' => 'required|exists:hostel_floors,id',
        ]);

        $floor = HostelFloor::findOrFail($request->hostel_floor_id);
        
        // Verify tenant ownership
        if ($floor->school_id !== $schoolId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get rooms for this floor
        $rooms = HostelRoom::where('school_id', $schoolId)
            ->where('hostel_floor_id', $request->hostel_floor_id)
            ->orderBy('room_name')
            ->get(['id', 'room_name']);
        
        $roomsArray = $rooms->map(function($room) {
                return [
                    'id' => $room->id,
                    'room_name' => $room->room_name,
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'success' => true,
            'rooms' => $roomsArray,
        ]);
    }

    /**
     * Get fee names list for starting month dropdown.
     */
    public function getMonths()
    {
        try {
            // Get school ID safely - try multiple methods
            $school = null;
            
            // Method 1: Try getting from service container (set by middleware)
            if (app()->bound('currentSchool')) {
                $school = app('currentSchool');
            }
            
            // Method 2: Try getting from authenticated user
            if (!$school && Auth::check()) {
                $user = Auth::user();
                if ($user && $user->school) {
                    $school = $user->school;
                }
            }
            
            // Method 3: Try getSchool() from TenantController (only if school is set)
            if (!$school && isset($this->school) && $this->school) {
                $school = $this->school;
            }
            
            if (!$school) {
                return response()->json([
                    'success' => false,
                    'error' => 'School not found',
                    'months' => [],
                ], 404);
            }
            
            $schoolId = $school->id;
            
            // Get active fee names for the school
            $feeNames = FeeName::where('school_id', $schoolId)
                ->active()
                ->orderBy('name')
                ->get(['id', 'name']);

            $feeNamesArray = $feeNames->map(function($feeName) {
                return [
                    'value' => $feeName->id,
                    'label' => $feeName->name,
                ];
            })->values()->toArray();

            return response()->json([
                'success' => true,
                'months' => $feeNamesArray, // Keep 'months' key for backward compatibility with frontend
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load fee names',
                'months' => [],
            ], 500);
        }
    }
}

