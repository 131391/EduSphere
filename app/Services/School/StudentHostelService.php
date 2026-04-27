<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\Student;
use App\Models\HostelBedAssignment;
use App\Models\Hostel;
use App\Enums\GeneralStatus;
use Illuminate\Support\Facades\DB;
use Exception;

class StudentHostelService
{
    /**
     * Assign hostel to a student.
     *
     * @param School $school
     * @param Student $student
     * @param array $data
     * @return HostelBedAssignment
     * @throws Exception
     */
    public function assignHostel(School $school, Student $student, array $data): HostelBedAssignment
    {
        return DB::transaction(function () use ($school, $student, $data) {
            $hostel = Hostel::where('school_id', $school->id)->findOrFail($data['hostel_id']);

            // Validate capacity
            if ($hostel->capability) {
                $currentCount = HostelBedAssignment::where('school_id', $school->id)
                    ->where('hostel_id', $hostel->id)
                    ->where('status', GeneralStatus::Active)
                    ->count();

                if ($currentCount >= $hostel->capability) {
                    throw new Exception("Cannot assign student. Hostel '{$hostel->hostel_name}' has reached its maximum capacity of {$hostel->capability} beds.");
                }
            }

            // End current active assignments for the student
            HostelBedAssignment::where('student_id', $student->id)
                ->where('status', GeneralStatus::Active)
                ->update([
                    'status' => GeneralStatus::Inactive, 
                    'end_date' => now()
                ]);

            // Create new assignment
            $assignment = HostelBedAssignment::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'hostel_id' => $data['hostel_id'],
                'hostel_floor_id' => $data['hostel_floor_id'],
                'hostel_room_id' => $data['hostel_room_id'],
                'bed_no' => $data['bed_no'] ?? null,
                'rent' => $data['rent'] ?? null,
                'starting_month' => $data['starting_month'] ?? null,
                'hostel_assign_date' => $data['hostel_assign_date'] ?? $data['start_date'] ?? now()->format('Y-m-d'),
                'start_date' => $data['start_date'] ?? now()->format('Y-m-d'),
                'status' => GeneralStatus::Active,
            ]);

            return $assignment;
        });
    }

    /**
     * Remove hostel assignment from a student.
     *
     * @param Student $student
     * @return void
     */
    public function removeHostel(Student $student): void
    {
        DB::transaction(function () use ($student) {
            HostelBedAssignment::where('student_id', $student->id)
                ->where('status', GeneralStatus::Active)
                ->update([
                    'status' => GeneralStatus::Inactive, 
                    'end_date' => now()
                ]);
        });
    }
}
