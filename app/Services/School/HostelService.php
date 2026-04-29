<?php

namespace App\Services\School;

use App\Models\Hostel;
use App\Models\HostelFloor;
use App\Models\HostelRoom;
use App\Models\HostelBedAssignment;
use App\Models\Student;
use App\Models\School;
use App\Enums\GeneralStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Hostel Management Service
 * 
 * Handles all business logic for hostel management including
 * bed assignments, capacity management, and occupancy tracking.
 */
class HostelService
{
    /**
     * Assign a student to a hostel bed
     *
     * @param School $school
     * @param Student $student
     * @param array $data
     * @return HostelBedAssignment
     * @throws \Exception
     */
    public function assignHostel(School $school, Student $student, array $data): HostelBedAssignment
    {
        return DB::transaction(function () use ($school, $student, $data) {
            // Validate capacity
            $this->validateCapacity($school->id, $data['hostel_id'], $data['hostel_room_id'] ?? null);

            // Check if student already has active assignment
            $existingAssignment = HostelBedAssignment::where('school_id', $school->id)
                ->where('student_id', $student->id)
                ->where('status', GeneralStatus::Active)
                ->whereNull('deleted_at')
                ->first();

            if ($existingAssignment) {
                throw new \Exception('Student already has an active hostel assignment.');
            }

            // Check bed collision
            if (!empty($data['bed_no'])) {
                $collision = HostelBedAssignment::where('school_id', $school->id)
                    ->where('hostel_room_id', $data['hostel_room_id'])
                    ->where('bed_no', $data['bed_no'])
                    ->active()
                    ->exists();

                if ($collision) {
                    throw new \Exception("Bed number '{$data['bed_no']}' is already assigned to another student in this room.");
                }
            }

            // Create assignment
            $assignment = HostelBedAssignment::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'hostel_id' => $data['hostel_id'],
                'hostel_floor_id' => $data['hostel_floor_id'],
                'hostel_room_id' => $data['hostel_room_id'],
                'bed_no' => $data['bed_no'] ?? null,
                'rent' => $data['rent'] ?? 0,
                'hostel_assign_date' => now(),
                'starting_month' => $data['starting_month'] ?? now()->format('F Y'),
                'start_date' => $data['start_date'] ?? now(),
                'status' => GeneralStatus::Active,
            ]);

            Log::info('Student assigned to hostel', [
                'school_id' => $school->id,
                'student_id' => $student->id,
                'hostel_id' => $data['hostel_id'],
                'assignment_id' => $assignment->id,
            ]);

            return $assignment;
        });
    }

    /**
     * Update an existing hostel assignment
     *
     * @param HostelBedAssignment $assignment
     * @param array $data
     * @return HostelBedAssignment
     * @throws \Exception
     */
    public function updateAssignment(HostelBedAssignment $assignment, array $data): HostelBedAssignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            // If changing rooms, validate capacity
            if (isset($data['hostel_room_id']) && $data['hostel_room_id'] != $assignment->hostel_room_id) {
                $this->validateCapacity($assignment->school_id, $data['hostel_id'] ?? $assignment->hostel_id, $data['hostel_room_id']);
            }

            // Check bed collision
            $roomId = $data['hostel_room_id'] ?? $assignment->hostel_room_id;
            $bedNo = $data['bed_no'] ?? $assignment->bed_no;
            
            if (!empty($bedNo)) {
                $collision = HostelBedAssignment::where('school_id', $assignment->school_id)
                    ->where('hostel_room_id', $roomId)
                    ->where('bed_no', $bedNo)
                    ->where('id', '!=', $assignment->id)
                    ->active()
                    ->exists();

                if ($collision) {
                    throw new \Exception("Bed number '{$bedNo}' is already assigned to another student in this room.");
                }
            }

            $assignment->update($data);

            Log::info('Hostel assignment updated', [
                'assignment_id' => $assignment->id,
                'updates' => $data,
            ]);

            return $assignment;
        });
    }

    /**
     * Terminate a hostel assignment
     *
     * @param HostelBedAssignment $assignment
     * @param string|null $endDate
     * @return HostelBedAssignment
     */
    public function terminateAssignment(HostelBedAssignment $assignment, ?string $endDate = null): HostelBedAssignment
    {
        return DB::transaction(function () use ($assignment, $endDate) {
            $assignment->update([
                'status' => GeneralStatus::Inactive,
                'end_date' => $endDate ?? now(),
            ]);

            Log::info('Hostel assignment terminated', [
                'assignment_id' => $assignment->id,
                'end_date' => $endDate ?? now(),
            ]);

            return $assignment;
        });
    }

    /**
     * Validate hostel room capacity
     *
     * @param int $schoolId
     * @param int $hostelId
     * @param int|null $roomId
     * @throws \Exception
     */
    public function validateCapacity(int $schoolId, int $hostelId, ?int $roomId = null): void
    {
        $hostel = Hostel::where('school_id', $schoolId)->findOrFail($hostelId);
        
        // 1. Validate Hostel Capacity
        $currentOccupancy = HostelBedAssignment::where('school_id', $schoolId)
            ->where('hostel_id', $hostelId)
            ->where('status', GeneralStatus::Active)
            ->whereNull('deleted_at')
            ->count();

        $capacity = $hostel->capability ?? 0;

        if ($capacity > 0 && $currentOccupancy >= $capacity) {
            throw new \Exception("Hostel {$hostel->hostel_name} has reached its maximum capacity of {$capacity} beds.");
        }

        // 2. Validate Room Capacity
        if ($roomId) {
            $room = HostelRoom::where('school_id', $schoolId)->findOrFail($roomId);
            if (!$room->hasAvailableBeds()) {
                throw new \Exception("Room {$room->room_name} has reached its maximum capacity of {$room->no_of_beds} beds.");
            }
        }
    }

    /**
     * Get hostel occupancy statistics
     *
     * @param int $schoolId
     * @param int|null $hostelId
     * @return array
     */
    public function getOccupancyStats(int $schoolId, ?int $hostelId = null): array
    {
        $query = Hostel::where('school_id', $schoolId);
        
        if ($hostelId) {
            $query->where('id', $hostelId);
        }

        $hostels = $query->withCount(['bedAssignments' => function ($q) {
            $q->where('status', GeneralStatus::Active);
        }])->get();

        $stats = [
            'total_hostels' => $hostels->count(),
            'total_capacity' => $hostels->sum('capability'),
            'total_occupancy' => $hostels->sum('bed_assignments_count'),
            'hostels' => [],
        ];

        foreach ($hostels as $hostel) {
            $stats['hostels'][] = [
                'id' => $hostel->id,
                'name' => $hostel->hostel_name,
                'capacity' => $hostel->capability,
                'occupancy' => $hostel->bed_assignments_count,
                'available' => max(0, ($hostel->capability ?? 0) - $hostel->bed_assignments_count),
                'occupancy_percentage' => $hostel->capability ? 
                    round(($hostel->bed_assignments_count / $hostel->capability) * 100, 2) : 0,
            ];
        }

        return $stats;
    }

    /**
     * Get available rooms in a hostel
     *
     * @param int $schoolId
     * @param int $hostelId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailableRooms(int $schoolId, int $hostelId)
    {
        return HostelRoom::where('school_id', $schoolId)
            ->where('hostel_id', $hostelId)
            ->withCount('assignments')
            ->get()
            ->filter(function ($room) {
                return $room->assignments_count > 0; // Has at least one occupant
            });
    }

    /**
     * Get students without hostel assignment
     *
     * @param int $schoolId
     * @param int|null $classId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnassignedStudents(int $schoolId, ?int $classId = null)
    {
        $assignedStudentIds = HostelBedAssignment::where('school_id', $schoolId)
            ->where('status', GeneralStatus::Active)
            ->whereNull('deleted_at')
            ->pluck('student_id')
            ->toArray();

        $query = Student::where('school_id', $schoolId)
            ->where('status', GeneralStatus::Active)
            ->whereNotIn('id', $assignedStudentIds);

        if ($classId) {
            $query->where('class_id', $classId);
        }

        return $query->orderBy('first_name')->orderBy('last_name')->get();
    }

    /**
     * Bulk assign students to hostel
     *
     * @param School $school
     * @param array $studentIds
     * @param array $hostelData
     * @return array
     */
    public function bulkAssign(School $school, array $studentIds, array $hostelData): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($studentIds as $studentId) {
            try {
                $student = Student::where('school_id', $school->id)->findOrFail($studentId);
                $assignment = $this->assignHostel($school, $student, array_merge($hostelData, [
                    'student_id' => $studentId,
                ]));
                $results['success'][] = [
                    'student_id' => $studentId,
                    'assignment_id' => $assignment->id,
                ];
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'student_id' => $studentId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get room occupancy details
     *
     * @param int $roomId
     * @return array
     */
    public function getRoomOccupancy(int $roomId): array
    {
        $room = HostelRoom::with('hostel', 'floor')->findOrFail($roomId);
        
        $assignments = HostelBedAssignment::where('hostel_room_id', $roomId)
            ->where('status', GeneralStatus::Active)
            ->with('student')
            ->get();

        return [
            'room' => $room,
            'assignments' => $assignments,
            'total_beds' => $assignments->count(),
        ];
    }
}