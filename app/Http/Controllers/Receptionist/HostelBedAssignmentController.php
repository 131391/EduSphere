<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Http\Requests\Receptionist\StoreHostelBedAssignmentRequest;
use App\Http\Requests\Receptionist\UpdateHostelBedAssignmentRequest;
use App\Services\School\StudentHostelService;
use App\Models\HostelBedAssignment;
use App\Models\Student;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use App\Models\FeeName;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HostelBedAssignmentController extends TenantController
{
    use HasAjaxDataTable;

    protected StudentHostelService $hostelService;

    public function __construct(StudentHostelService $hostelService)
    {
        parent::__construct();
        $this->hostelService = $hostelService;
    }

    /**
     * Display a listing of hostel bed assignments.
     */
    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($row) {
            $student = $row->student;
            $name = $student ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) : 'N/A';
            return [
                'id'                 => $row->id,
                'student_id'         => $row->student_id,
                'student_name'       => $name,
                'initials'           => collect(explode(' ', $name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join(''),
                'admission_no'       => $student->admission_no ?? 'N/A',
                'class_name'         => $student->class->name ?? 'N/A',
                'hostel_id'          => $row->hostel_id,
                'hostel_name'        => $row->hostel->hostel_name ?? 'N/A',
                'hostel_floor_id'    => $row->hostel_floor_id,
                'floor_name'         => $row->floor->floor_name ?? 'N/A',
                'hostel_room_id'     => $row->hostel_room_id,
                'room_name'          => $row->room->room_name ?? 'N/A',
                'bed_no'             => $row->bed_no ?? 'N/A',
                'rent'               => $row->rent ?? 0,
                'rent_label'         => '₹' . number_format($row->rent ?? 0, 2),
                'hostel_assign_date' => $row->hostel_assign_date ? $row->hostel_assign_date->format('d M, Y') : 'N/A',
                'starting_month'     => $row->starting_month ?? '',
            ];
        };

        $query = HostelBedAssignment::where('school_id', $schoolId)
            ->with(['student.class', 'hostel', 'floor', 'room']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bed_no', 'like', "%{$search}%")
                  ->orWhereHas('student', fn($sq) => $sq->where('admission_no', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%"))
                  ->orWhereHas('hostel', fn($hq) => $hq->where('hostel_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        if ($request->has('export')) {
            return $this->exportToCsv($query);
        }

        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();

        $stats = [
            'total_assignments' => HostelBedAssignment::where('school_id', $schoolId)->whereNull('deleted_at')->count(),
            'total_hostels'     => Hostel::where('school_id', $schoolId)->count(),
            'total_rooms'       => HostelRoom::where('school_id', $schoolId)->count(),
            'total_rent'        => (float) HostelBedAssignment::where('school_id', $schoolId)->whereNull('deleted_at')->sum('rent'),
        ];

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('receptionist.hostel-bed-assignments.index', [
            'initialData' => $initialData,
            'stats'       => $stats,
            'hostels'     => $hostels,
        ]);
    }

    private function exportToCsv($query)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="hostel_assignments_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Student', 'Admission No', 'Class', 'Hostel', 'Floor', 'Room', 'Bed No', 'Rent', 'Assigned On']);
            $query->orderBy('created_at', 'desc')->cursor()->each(function ($row) use ($file) {
                $student = $row->student;
                $name = $student ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) : 'N/A';
                fputcsv($file, [
                    $name,
                    $student->admission_no ?? 'N/A',
                    $student->class->name ?? 'N/A',
                    $row->hostel->hostel_name ?? 'N/A',
                    $row->floor->floor_name ?? 'N/A',
                    $row->room->room_name ?? 'N/A',
                    $row->bed_no ?? '',
                    $row->rent ?? 0,
                    $row->hostel_assign_date ? $row->hostel_assign_date->format('Y-m-d') : '',
                ]);
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function export()
    {
        return redirect()->route('receptionist.hostel-bed-assignments.index', ['export' => 'csv']);
    }

    /**
     * Store a newly created hostel bed assignment.
     */
    public function store(StoreHostelBedAssignmentRequest $request)
    {
        try {
            $validated = $request->validated();
            $schoolId = $this->getSchoolId();

            $student = Student::where('id', $validated['student_id'])->where('school_id', $schoolId)->firstOrFail();

            // Check if student already has an active assignment
            $existingAssignment = HostelBedAssignment::where('student_id', $validated['student_id'])
                ->whereNull('deleted_at')
                ->where('status', \App\Enums\GeneralStatus::Active)
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Concurrent assignment detected',
                    'errors' => ['student_id' => ['This student already has an active assignment in another room.']]
                ], 422);
            }

            $this->hostelService->assignHostel($this->getSchool(), $student, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Residential unit assigned successfully to ' . $student->full_name
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
                'message' => 'Database exception during assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified hostel bed assignment.
     */
    public function update(UpdateHostelBedAssignmentRequest $request, HostelBedAssignment $hostelBedAssignment)
    {
        $this->authorizeTenant($hostelBedAssignment);

        try {
            $validated = $request->validated();
            $schoolId = $this->getSchoolId();

            $student = Student::where('id', $validated['student_id'])->where('school_id', $schoolId)->firstOrFail();

            // Check if another student already has an active assignment for this hostel (excluding current)
            $existingAssignment = HostelBedAssignment::where('student_id', $validated['student_id'])
                ->where('id', '!=', $hostelBedAssignment->id)
                ->whereNull('deleted_at')
                ->where('status', \App\Enums\GeneralStatus::Active)
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Concurrent residency detected',
                    'errors' => ['student_id' => ['This student is currently assigned to another residential unit.']]
                ], 422);
            }

            // We do a manual update here because the service handles *new* assignments
            \Illuminate\Support\Facades\DB::transaction(function () use ($hostelBedAssignment, $validated) {
                $hostelBedAssignment->update($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Residential assignment updated successfully.'
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
                'message' => 'Failed to synchronize assignment revision: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified hostel bed assignment.
     */
    public function destroy(HostelBedAssignment $hostelBedAssignment)
    {
        $this->authorizeTenant($hostelBedAssignment);

        try {
            $hostelBedAssignment->delete();
            return response()->json([
                'success' => true,
                'message' => 'Assignment struck successfully from records.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Operational failure during record deletion.'
            ], 500);
        }
    }

    /**
     * Search students by admission number or name (AJAX).
     */
    public function searchStudents(Request $request)
    {
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registry search failure: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get floors for a selected hostel (AJAX).
     */
    public function getFloors(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();
            
            $request->validate([
                'hostel_id' => 'required|exists:hostels,id',
            ]);

            $hostel = Hostel::findOrFail($request->hostel_id);
            
            // Verify tenant ownership
            if ($hostel->school_id !== $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['hostel_id' => ['Unauthorized hostel selection.']]
                ], 422);
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Structural retrieval failure: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rooms for a selected floor (AJAX).
     */
    public function getRooms(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();
            
            $request->validate([
                'hostel_floor_id' => 'required|exists:hostel_floors,id',
            ]);

            $floor = HostelFloor::findOrFail($request->hostel_floor_id);
            
            // Verify tenant ownership
            if ($floor->school_id !== $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['hostel_floor_id' => ['Unauthorized floor selection.']]
                ], 422);
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory retrieval failure: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fee names list for starting month dropdown.
     */
    public function getMonths()
    {
        try {
            $schoolId = $this->getSchoolId();
            
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
                'months' => $feeNamesArray, // Keep 'months' key for backward compatibility
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lifecycle loading failure: ' . $e->getMessage(),
                'months' => [],
            ], 500);
        }
    }
}

