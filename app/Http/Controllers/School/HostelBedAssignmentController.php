<?php

namespace App\Http\Controllers\School;

use App\Enums\GeneralStatus;
use App\Http\Controllers\TenantController;
use App\Http\Requests\Receptionist\StoreHostelBedAssignmentRequest;
use App\Http\Requests\Receptionist\UpdateHostelBedAssignmentRequest;
use App\Models\Hostel;
use App\Models\HostelBedAssignment;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use App\Models\Student;
use App\Services\School\StudentHostelService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class HostelBedAssignmentController extends TenantController
{
    use HasAjaxDataTable;

    protected StudentHostelService $hostelService;

    public function __construct(StudentHostelService $hostelService)
    {
        parent::__construct();
        $this->hostelService = $hostelService;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($row) {
            $student = $row->student;
            $name = $student ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) : 'N/A';

            return [
                'id' => $row->id,
                'student_id' => $row->student_id,
                'student_name' => $name,
                'initials' => collect(explode(' ', $name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join(''),
                'admission_no' => $student->admission_no ?? 'N/A',
                'class_name' => $student->class->name ?? 'N/A',
                'hostel_id' => $row->hostel_id,
                'hostel_name' => $row->hostel->hostel_name ?? 'N/A',
                'hostel_floor_id' => $row->hostel_floor_id,
                'floor_name' => $row->floor->floor_name ?? 'N/A',
                'hostel_room_id' => $row->hostel_room_id,
                'room_name' => $row->room->room_name ?? 'N/A',
                'bed_no' => $row->bed_no ?? 'N/A',
                'rent' => $row->rent ?? 0,
                'rent_label' => '₹' . number_format($row->rent ?? 0, 2),
                'hostel_assign_date' => $row->hostel_assign_date ? $row->hostel_assign_date->format('d M, Y') : 'N/A',
                'starting_month' => $row->starting_month ?? '',
                'status' => $row->status->value,
                'raw' => [
                    'student_id' => $row->student_id,
                    'hostel_id' => $row->hostel_id,
                    'hostel_floor_id' => $row->hostel_floor_id,
                    'hostel_room_id' => $row->hostel_room_id,
                    'bed_no' => $row->bed_no,
                    'rent' => $row->rent,
                    'hostel_assign_date' => $row->hostel_assign_date ? $row->hostel_assign_date->format('Y-m-d') : '',
                    'starting_month' => $row->starting_month,
                    'status' => $row->status->value,
                ],
            ];
        };

        $query = HostelBedAssignment::where('school_id', $schoolId)
            ->with(['student.class', 'student.section', 'hostel', 'floor', 'room']);

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
            'total_hostels' => Hostel::where('school_id', $schoolId)->count(),
            'total_rooms' => HostelRoom::where('school_id', $schoolId)->count(),
            'total_rent' => (float) HostelBedAssignment::where('school_id', $schoolId)->whereNull('deleted_at')->sum('rent'),
        ];

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.hostel.assignments', [
            'initialData' => $initialData,
            'stats' => $stats,
            'hostels' => $hostels,
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
        return redirect()->route('school.hostel.assignments.index', ['export' => 'csv']);
    }

    public function store(StoreHostelBedAssignmentRequest $request)
    {
        try {
            $validated = $request->validated();
            $schoolId = $this->getSchoolId();

            $student = Student::where('id', $validated['student_id'])->where('school_id', $schoolId)->firstOrFail();

            $existingAssignment = HostelBedAssignment::where('student_id', $validated['student_id'])
                ->whereNull('deleted_at')
                ->where('status', GeneralStatus::Active)
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Concurrent assignment detected',
                    'errors' => ['student_id' => ['This student already has an active assignment in another room.']],
                ], 422);
            }

            $this->hostelService->assignHostel($this->getSchool(), $student, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Residential unit assigned successfully to ' . $student->full_name,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database exception during assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateHostelBedAssignmentRequest $request, HostelBedAssignment $assignment)
    {
        $this->authorizeTenant($assignment);

        try {
            $validated = $request->validated();
            $schoolId = $this->getSchoolId();

            $student = Student::where('id', $validated['student_id'])->where('school_id', $schoolId)->firstOrFail();

            $existingAssignment = HostelBedAssignment::where('student_id', $validated['student_id'])
                ->where('id', '!=', $assignment->id)
                ->whereNull('deleted_at')
                ->where('status', GeneralStatus::Active)
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Concurrent residency detected',
                    'errors' => ['student_id' => ['This student is currently assigned to another residential unit.']],
                ], 422);
            }

            $assignment->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Residential assignment updated successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize assignment revision: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(HostelBedAssignment $assignment)
    {
        $this->authorizeTenant($assignment);

        try {
            $assignment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Assignment struck successfully from records.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Operational failure during record deletion.',
            ], 500);
        }
    }

    public function searchStudents(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();

            $request->validate([
                'search' => 'required|string|min:2',
            ]);

            $search = $request->search;

            $students = Student::where('school_id', $schoolId)
                ->where(function ($query) use ($search) {
                    $query->where('admission_no', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->with(['class', 'section'])
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'students' => $students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'admission_no' => $student->admission_no,
                        'name' => trim($student->first_name . ' ' . $student->middle_name . ' ' . $student->last_name),
                        'class_id' => $student->class_id,
                        'class_name' => $student->class->name ?? 'N/A',
                        'section_id' => $student->section_id,
                        'section_name' => $student->section->name ?? 'N/A',
                    ];
                })->values()->toArray(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registry search failure: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getFloors(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();

            $request->validate([
                'hostel_id' => 'required|exists:hostels,id',
            ]);

            $hostel = Hostel::findOrFail($request->hostel_id);
            if ($hostel->school_id !== $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['hostel_id' => ['Unauthorized hostel selection.']],
                ], 422);
            }

            $floors = HostelFloor::where('school_id', $schoolId)
                ->where('hostel_id', $request->hostel_id)
                ->orderBy('floor_name')
                ->get(['id', 'floor_name']);

            return response()->json([
                'success' => true,
                'floors' => $floors->map(fn($floor) => ['id' => $floor->id, 'floor_name' => $floor->floor_name])->values()->toArray(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Structural retrieval failure: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getRooms(Request $request)
    {
        try {
            $schoolId = $this->getSchoolId();

            $request->validate([
                'hostel_floor_id' => 'required|exists:hostel_floors,id',
            ]);

            $floor = HostelFloor::findOrFail($request->hostel_floor_id);
            if ($floor->school_id !== $schoolId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Integrity violation',
                    'errors' => ['hostel_floor_id' => ['Unauthorized floor selection.']],
                ], 422);
            }

            $rooms = HostelRoom::where('school_id', $schoolId)
                ->where('hostel_floor_id', $request->hostel_floor_id)
                ->orderBy('room_name')
                ->get(['id', 'room_name']);

            return response()->json([
                'success' => true,
                'rooms' => $rooms->map(fn($room) => ['id' => $room->id, 'room_name' => $room->room_name])->values()->toArray(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unit lookup failure: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getMonths()
    {
        $months = collect(range(0, 11))->map(function ($offset) {
            $date = now()->startOfMonth()->addMonths($offset);
            return [
                'value' => $date->format('F Y'),
                'label' => $date->format('F Y'),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'months' => $months,
        ]);
    }

    public function history(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($row) {
            $student = $row->student;
            $name = $student ? trim(($student->first_name ?? '') . ' ' . ($student->middle_name ?? '') . ' ' . ($student->last_name ?? '')) : 'N/A';

            return [
                'id' => $row->id,
                'student_name' => $name,
                'admission_no' => $student->admission_no ?? 'N/A',
                'class_name' => $student->class->name ?? 'N/A',
                'hostel_name' => $row->hostel->hostel_name ?? 'N/A',
                'floor_name' => $row->floor->floor_name ?? 'N/A',
                'room_name' => $row->room->room_name ?? 'N/A',
                'bed_no' => $row->bed_no ?? 'N/A',
                'status' => $row->status->value,
                'start_date' => $row->start_date ? $row->start_date->format('d M, Y') : 'N/A',
                'end_date' => $row->end_date ? $row->end_date->format('d M, Y') : 'Ongoing',
            ];
        };

        $query = HostelBedAssignment::withTrashed()
            ->with(['student.class', 'hostel', 'floor', 'room'])
            ->where('school_id', $schoolId)
            ->where(function ($q) {
                $q->whereNotNull('deleted_at')
                    ->orWhere('status', GeneralStatus::Inactive);
            });

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        $initialData = $this->getHydrationData($query, $transformer, []);

        return view('school.hostel.history', [
            'initialData' => $initialData,
        ]);
    }
}
