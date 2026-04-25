<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\Student;
use App\Models\StudentTransportAssignment;
use App\Models\BusStop;
use App\Enums\GeneralStatus;
use App\Enums\YesNo;
use Illuminate\Support\Facades\DB;

class StudentTransportService
{
    /**
     * Assign transport to a student.
     *
     * @param School $school
     * @param Student $student
     * @param array $data
     * @return StudentTransportAssignment
     */
    public function assignTransport(School $school, Student $student, array $data): StudentTransportAssignment
    {
        return DB::transaction(function () use ($school, $student, $data) {
            // End current active assignments
            StudentTransportAssignment::where('student_id', $student->id)
                ->where('status', GeneralStatus::Active)
                ->update([
                    'status' => GeneralStatus::Inactive, 
                    'end_date' => now()
                ]);

            // Fetch bus stop details to capture the fee and vehicle info
            $busStop = BusStop::where('school_id', $school->id)->findOrFail($data['bus_stop_id']);

            // Create new assignment
            $assignment = StudentTransportAssignment::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'route_id' => $data['route_id'],
                'bus_stop_id' => $busStop->id,
                'vehicle_id' => $busStop->vehicle_id, // Default to the bus stop's vehicle
                'fee_per_month' => $busStop->charge_per_month,
                'academic_year_id' => $data['academic_year_id'],
                'start_date' => $data['start_date'],
                'status' => GeneralStatus::Active,
            ]);

            // Ensure the student flag is correctly set
            if ($student->is_transport_required !== YesNo::Yes) {
                $student->is_transport_required = YesNo::Yes;
                $student->save();
            }

            return $assignment;
        });
    }

    /**
     * Remove transport assignment from a student.
     *
     * @param Student $student
     * @return void
     */
    public function removeTransport(Student $student): void
    {
        DB::transaction(function () use ($student) {
            StudentTransportAssignment::where('student_id', $student->id)
                ->where('status', GeneralStatus::Active)
                ->update([
                    'status' => GeneralStatus::Inactive, 
                    'end_date' => now()
                ]);

            if ($student->is_transport_required === YesNo::Yes) {
                $student->is_transport_required = YesNo::No;
                $student->save();
            }
        });
    }
}
