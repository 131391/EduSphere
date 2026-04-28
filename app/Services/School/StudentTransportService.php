<?php

namespace App\Services\School;

use App\Enums\GeneralStatus;
use App\Enums\YesNo;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentTransportAssignment;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentTransportService
{
    public function __construct(
        protected TransportIntegrityService $transportIntegrityService
    ) {
    }

    public function assignTransport(School $school, Student $student, array $data): StudentTransportAssignment
    {
        return DB::transaction(function () use ($school, $student, $data) {
            $academicYear = $this->transportIntegrityService->getAcademicYearForSchool(
                $school,
                isset($data['academic_year_id']) ? (int) $data['academic_year_id'] : null,
                true
            );

            $resolved = $this->transportIntegrityService->resolveRouteBusStopVehicle(
                $school,
                (int) $data['route_id'],
                (int) $data['bus_stop_id'],
                isset($data['vehicle_id']) ? (int) $data['vehicle_id'] : null
            );

            $route = $resolved['route'];
            $busStop = $resolved['busStop'];
            $vehicle = $resolved['vehicle'];
            $startDate = $data['start_date'] ?? now()->toDateString();
            $feePerMonth = array_key_exists('fee_per_month', $data) && $data['fee_per_month'] !== null
                ? $data['fee_per_month']
                : ($busStop->charge_per_month ?? 0);

            $existingActiveAssignment = StudentTransportAssignment::query()
                ->where('school_id', $school->id)
                ->where('student_id', $student->id)
                ->where('status', GeneralStatus::Active)
                ->lockForUpdate()
                ->first();

            if (
                $existingActiveAssignment
                && (int) $existingActiveAssignment->route_id === (int) $route->id
                && (int) $existingActiveAssignment->bus_stop_id === (int) $busStop->id
                && (int) $existingActiveAssignment->vehicle_id === (int) $vehicle->id
                && (int) $existingActiveAssignment->academic_year_id === (int) $academicYear->id
            ) {
                $existingActiveAssignment->update([
                    'fee_per_month' => $feePerMonth,
                    'start_date' => $startDate,
                    'end_date' => null,
                ]);

                $this->syncStudentTransportFlag($student);

                return $existingActiveAssignment->fresh();
            }

            $this->assertVehicleCapacityAvailable(
                $vehicle,
                $school->id,
                $academicYear->id,
                excludeStudentId: $student->id
            );

            $this->closeActiveAssignments($school, $student, $startDate);

            $assignment = StudentTransportAssignment::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'route_id' => $route->id,
                'bus_stop_id' => $busStop->id,
                'vehicle_id' => $vehicle->id,
                'fee_per_month' => $feePerMonth,
                'academic_year_id' => $academicYear->id,
                'start_date' => $startDate,
                'end_date' => null,
                'status' => GeneralStatus::Active,
            ]);

            $this->syncStudentTransportFlag($student);

            return $assignment;
        });
    }

    public function updateAssignment(StudentTransportAssignment $assignment, array $data): StudentTransportAssignment
    {
        return DB::transaction(function () use ($assignment, $data) {
            $school = $assignment->school;
            $originalStudent = $assignment->student;
            $student = $originalStudent;

            if (isset($data['student_id']) && (int) $data['student_id'] !== (int) $assignment->student_id) {
                $student = Student::withoutGlobalScopes()
                    ->where('school_id', $school->id)
                    ->find($data['student_id']);

                if (!$student) {
                    throw ValidationException::withMessages([
                        'student_id' => ['The selected student does not belong to this school.'],
                    ]);
                }

                $conflictingAssignmentExists = StudentTransportAssignment::query()
                    ->where('school_id', $school->id)
                    ->where('student_id', $student->id)
                    ->where('status', GeneralStatus::Active)
                    ->where('id', '!=', $assignment->id)
                    ->exists();

                if ($conflictingAssignmentExists) {
                    throw ValidationException::withMessages([
                        'student_id' => ['The selected student already has an active transport assignment.'],
                    ]);
                }
            }

            $academicYear = $this->transportIntegrityService->getAcademicYearForSchool(
                $school,
                isset($data['academic_year_id']) ? (int) $data['academic_year_id'] : (int) $assignment->academic_year_id,
                true
            );

            $resolved = $this->transportIntegrityService->resolveRouteBusStopVehicle(
                $school,
                (int) ($data['route_id'] ?? $assignment->route_id),
                (int) ($data['bus_stop_id'] ?? $assignment->bus_stop_id),
                isset($data['vehicle_id']) ? (int) $data['vehicle_id'] : (int) $assignment->vehicle_id
            );

            $route = $resolved['route'];
            $busStop = $resolved['busStop'];
            $vehicle = $resolved['vehicle'];

            $this->assertVehicleCapacityAvailable(
                $vehicle,
                $school->id,
                $academicYear->id,
                excludeAssignmentId: $assignment->id,
                excludeStudentId: $student->id
            );

            $status = isset($data['status']) ? (int) $data['status'] : (int) $assignment->status->value;
            $isActive = $status === GeneralStatus::Active->value;
            $feePerMonth = array_key_exists('fee_per_month', $data) && $data['fee_per_month'] !== null
                ? $data['fee_per_month']
                : ($busStop->charge_per_month ?? 0);

            $assignment->update([
                'student_id' => $student->id,
                'route_id' => $route->id,
                'bus_stop_id' => $busStop->id,
                'vehicle_id' => $vehicle->id,
                'fee_per_month' => $feePerMonth,
                'academic_year_id' => $academicYear->id,
                'start_date' => $data['start_date'] ?? $assignment->start_date,
                'end_date' => $isActive ? null : ($data['end_date'] ?? now()->toDateString()),
                'status' => $status,
            ]);

            $this->syncStudentTransportFlag($originalStudent);
            if ($student->id !== $originalStudent->id) {
                $this->syncStudentTransportFlag($student);
            }

            return $assignment->fresh();
        });
    }

    public function removeTransport(Student $student): void
    {
        DB::transaction(function () use ($student) {
            $school = $student->school;
            $this->closeActiveAssignments($school, $student, now()->toDateString());
            $this->syncStudentTransportFlag($student);
        });
    }

    public function deleteAssignment(StudentTransportAssignment $assignment): void
    {
        DB::transaction(function () use ($assignment) {
            $student = $assignment->student;

            $assignment->update([
                'status' => GeneralStatus::Inactive,
                'end_date' => $assignment->end_date ?? now()->toDateString(),
            ]);

            $assignment->delete();

            $this->syncStudentTransportFlag($student);
        });
    }

    private function closeActiveAssignments(School $school, Student $student, string $endDate): void
    {
        StudentTransportAssignment::query()
            ->where('school_id', $school->id)
            ->where('student_id', $student->id)
            ->where('status', GeneralStatus::Active)
            ->update([
                'status' => GeneralStatus::Inactive,
                'end_date' => $endDate,
            ]);
    }

    private function assertVehicleCapacityAvailable(
        Vehicle $vehicle,
        int $schoolId,
        int $academicYearId,
        ?int $excludeAssignmentId = null,
        ?int $excludeStudentId = null
    ): void {
        if (!$vehicle->capacity) {
            return;
        }

        $query = StudentTransportAssignment::query()
            ->where('school_id', $schoolId)
            ->where('vehicle_id', $vehicle->id)
            ->where('academic_year_id', $academicYearId)
            ->where('status', GeneralStatus::Active);

        if ($excludeAssignmentId !== null) {
            $query->where('id', '!=', $excludeAssignmentId);
        }

        if ($excludeStudentId !== null) {
            $query->where('student_id', '!=', $excludeStudentId);
        }

        $currentCount = $query->count();

        if ($currentCount >= $vehicle->capacity) {
            throw ValidationException::withMessages([
                'vehicle_id' => ["Cannot assign transport. Vehicle ({$vehicle->vehicle_no}) has reached its maximum capacity of {$vehicle->capacity}."],
            ]);
        }
    }

    private function syncStudentTransportFlag(Student $student): void
    {
        $hasActiveAssignment = StudentTransportAssignment::query()
            ->where('school_id', $student->school_id)
            ->where('student_id', $student->id)
            ->where('status', GeneralStatus::Active)
            ->exists();

        $expectedFlag = $hasActiveAssignment ? YesNo::Yes : YesNo::No;

        if ($student->is_transport_required !== $expectedFlag) {
            $student->is_transport_required = $expectedFlag;
            $student->save();
        }
    }
}
