<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use App\Models\HostelBedAssignment;
use App\Models\Student;
use App\Models\ClassModel;
use App\Services\School\StudentHostelService;
use App\Traits\HasAjaxDataTable;
use App\Enums\GeneralStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $transformer = function ($assignment) {
            return [
                'id'                => $assignment->id,
                'student_name'      => $assignment->student?->full_name,
                'admission_no'      => $assignment->student?->admission_no,
                'class_name'        => $assignment->student?->class?->name,
                'hostel_name'       => $assignment->hostel?->hostel_name,
                'floor_name'        => $assignment->floor?->floor_name,
                'room_name'         => $assignment->room?->room_name,
                'bed_no'            => $assignment->bed_no ?? 'N/A',
                'rent'              => $assignment->rent ? '₹' . number_format($assignment->rent, 2) : 'N/A',
                'status'            => $assignment->status->value,
                'raw' => [
                    'student_id'      => $assignment->student_id,
                    'hostel_id'       => $assignment->hostel_id,
                    'hostel_floor_id' => $assignment->hostel_floor_id,
                    'hostel_room_id'  => $assignment->hostel_room_id,
                    'bed_no'          => $assignment->bed_no,
                    'rent'            => $assignment->rent,
                    'starting_month'  => $assignment->starting_month,
                    'start_date'      => $assignment->start_date ? $assignment->start_date->format('Y-m-d') : '',
                ],
            ];
        };

        $query = HostelBedAssignment::with(['student.class', 'hostel', 'floor', 'room'])
            ->where('school_id', $schoolId);

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        $hostels = Hostel::where('school_id', $schoolId)->orderBy('hostel_name')->get();
        $classes = ClassModel::where('school_id', $schoolId)->orderBy('name')->get();
        
        $initialData = $this->getHydrationData($query, $transformer, [
            'hostels' => $hostels,
            'classes' => $classes,
            'stats' => [
                'total_residents' => HostelBedAssignment::where('school_id', $schoolId)->where('status', GeneralStatus::Active)->count(),
                'total_capacity'  => (int) Hostel::where('school_id', $schoolId)->sum('capability'),
            ]
        ]);

        return view('school.hostel.assignments', [
            'initialData' => $initialData,
            'hostels' => $hostels,
            'classes' => $classes,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id'      => 'required|exists:students,id,school_id,' . $this->getSchoolId(),
                'hostel_id'       => 'required|exists:hostels,id,school_id,' . $this->getSchoolId(),
                'hostel_floor_id' => 'required|exists:hostel_floors,id,school_id,' . $this->getSchoolId(),
                'hostel_room_id'  => 'required|exists:hostel_rooms,id,school_id,' . $this->getSchoolId(),
                'bed_no'          => 'nullable|string|max:255',
                'rent'            => 'nullable|numeric|min:0',
                'starting_month'  => 'nullable|string|max:255',
                'start_date'      => 'required|date',
            ]);

            $student = Student::where('school_id', $this->getSchoolId())->findOrFail($validated['student_id']);
            
            $assignment = $this->hostelService->assignHostel($this->getSchool(), $student, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Student assigned to hostel successfully.',
                'data' => $assignment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign hostel: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $assignment = HostelBedAssignment::where('school_id', $this->getSchoolId())->findOrFail($id);

            $validated = $request->validate([
                'hostel_id'       => 'required|exists:hostels,id,school_id,' . $this->getSchoolId(),
                'hostel_floor_id' => 'required|exists:hostel_floors,id,school_id,' . $this->getSchoolId(),
                'hostel_room_id'  => 'required|exists:hostel_rooms,id,school_id,' . $this->getSchoolId(),
                'bed_no'          => 'nullable|string|max:255',
                'rent'            => 'nullable|numeric|min:0',
                'starting_month'  => 'nullable|string|max:255',
                'start_date'      => 'required|date',
                'status'          => 'required|string|in:active,inactive',
            ]);

            $assignment->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully.',
                'data' => $assignment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $assignment = HostelBedAssignment::where('school_id', $this->getSchoolId())->findOrFail($id);
            $assignment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Assignment removed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove assignment: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($assignment) {
            return [
                'id'                => $assignment->id,
                'student_name'      => $assignment->student?->full_name,
                'admission_no'      => $assignment->student?->admission_no,
                'hostel_name'       => $assignment->hostel?->hostel_name,
                'floor_name'        => $assignment->floor?->floor_name,
                'room_name'         => $assignment->room?->room_name,
                'bed_no'            => $assignment->bed_no ?? 'N/A',
                'status'            => $assignment->status->value,
                'start_date'        => $assignment->start_date ? $assignment->start_date->format('d M, Y') : 'N/A',
                'end_date'          => $assignment->end_date ? $assignment->end_date->format('d M, Y') : 'Ongoing',
            ];
        };

        $query = HostelBedAssignment::withTrashed()
            ->with(['student', 'hostel', 'floor', 'room'])
            ->where('school_id', $schoolId)
            ->whereNotNull('deleted_at')
            ->orWhere('status', GeneralStatus::Inactive);

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer);
        }

        $initialData = $this->getHydrationData($query, $transformer, []);

        return view('school.hostel.history', [
            'initialData' => $initialData,
        ]);
    }
}
