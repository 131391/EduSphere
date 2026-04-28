<?php

namespace App\Services\School;

use App\Enums\GeneralStatus;
use App\Enums\TransportAttendanceType;
use App\Models\School;
use App\Models\StudentTransportAssignment;
use App\Models\TransportAttendance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransportAttendanceService
{
    public function __construct(
        protected TransportIntegrityService $transportIntegrityService
    ) {
    }

    public function markBulkAttendance(School $school, array $data): array
    {
        return DB::transaction(function () use ($school, $data) {
            $academicYear = $this->transportIntegrityService->getAcademicYearForSchool(
                $school,
                isset($data['academic_year_id']) ? (int) $data['academic_year_id'] : null,
                true
            );

            $route = $this->transportIntegrityService->getRouteForSchool($school, (int) $data['route_id'], true);
            $vehicle = $this->transportIntegrityService->getVehicleForSchool($school, (int) $data['vehicle_id'], true);

            if ((int) $route->vehicle_id !== (int) $vehicle->id) {
                throw ValidationException::withMessages([
                    'route_id' => ['The selected route does not belong to the selected vehicle.'],
                ]);
            }

            $attendanceDate = $data['attendance_date'];
            $records = $this->normalizeAttendanceRecords($data);
            $studentIds = $records->pluck('student_id')->all();

            $eligibleStudentIds = StudentTransportAssignment::query()
                ->where('school_id', $school->id)
                ->where('route_id', $route->id)
                ->where('vehicle_id', $vehicle->id)
                ->where('academic_year_id', $academicYear->id)
                ->where('status', GeneralStatus::Active)
                ->where(function ($query) use ($attendanceDate) {
                    $query->whereNull('start_date')
                        ->orWhereDate('start_date', '<=', $attendanceDate);
                })
                ->where(function ($query) use ($attendanceDate) {
                    $query->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $attendanceDate);
                })
                ->pluck('student_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $unknownStudentIds = array_values(array_diff($studentIds, $eligibleStudentIds));
            if ($unknownStudentIds !== []) {
                throw ValidationException::withMessages([
                    'students' => ['One or more students are not actively assigned to the selected route and vehicle for the selected academic year.'],
                ]);
            }

            $userId = Auth::id();
            $now = now();
            $presentCount = 0;
            $absentCount = 0;

            foreach ($records as $studentData) {
                TransportAttendance::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'student_id' => $studentData['student_id'],
                        'attendance_date' => $attendanceDate,
                        'attendance_type' => $data['attendance_type'],
                        'academic_year_id' => $academicYear->id,
                    ],
                    [
                        'vehicle_id' => $vehicle->id,
                        'route_id' => $route->id,
                        'is_present' => $studentData['is_present'],
                        'time' => $now,
                        'remarks' => $studentData['remarks'],
                        'marked_by' => $userId,
                    ]
                );

                if ($studentData['is_present']) {
                    $presentCount++;
                } else {
                    $absentCount++;
                }
            }

            return [
                'present' => $presentCount,
                'absent' => $absentCount,
            ];
        });
    }

    public function getRoutesForVehicle(School $school, int $vehicleId): Collection
    {
        $vehicle = $this->transportIntegrityService->getVehicleForSchool($school, $vehicleId, true);

        return $vehicle->routes()
            ->where('status', \App\Enums\RouteStatus::Active)
            ->orderBy('route_name')
            ->get(['id', 'route_name']);
    }

    public function getStudentsForRoute(
        School $school,
        int $vehicleId,
        int $routeId,
        ?int $academicYearId = null
    ): Collection {
        $academicYear = $this->transportIntegrityService->getAcademicYearForSchool($school, $academicYearId, true);
        $route = $this->transportIntegrityService->getRouteForSchool($school, $routeId, true);
        $vehicle = $this->transportIntegrityService->getVehicleForSchool($school, $vehicleId, true);

        if ((int) $route->vehicle_id !== (int) $vehicle->id) {
            throw ValidationException::withMessages([
                'route_id' => ['The selected route does not belong to the selected vehicle.'],
            ]);
        }

        return StudentTransportAssignment::query()
            ->with(['student.class', 'student.section', 'busStop'])
            ->where('school_id', $school->id)
            ->where('route_id', $route->id)
            ->where('vehicle_id', $vehicle->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('status', GeneralStatus::Active)
            ->get()
            ->sortBy(fn ($assignment) => $assignment->busStop?->distance_from_institute ?? 9999)
            ->values()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->student_id,
                    'assignment_id' => $assignment->id,
                    'admission_no' => $assignment->student?->admission_no,
                    'name' => trim(collect([
                        $assignment->student?->first_name,
                        $assignment->student?->middle_name,
                        $assignment->student?->last_name,
                    ])->filter()->implode(' ')),
                    'class' => $assignment->student?->class?->name ?? 'N/A',
                    'section' => $assignment->student?->section?->name ?? 'N/A',
                    'bus_stop_name' => $assignment->busStop?->bus_stop_name ?? 'N/A',
                ];
            });
    }

    public function getMonthWiseReport(School $school, int $vehicleId, int $routeId, string $month): array
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            throw ValidationException::withMessages([
                'month' => ['The selected month format is invalid.'],
            ]);
        }

        $year = (int) substr($month, 0, 4);
        $monthNum = (int) substr($month, 5, 2);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        $startDate = sprintf('%04d-%02d-01', $year, $monthNum);
        $endDate = sprintf('%04d-%02d-%02d', $year, $monthNum, $daysInMonth);
        $academicYear = $this->transportIntegrityService->getAcademicYearForSchool($school, null, true);
        $route = $this->transportIntegrityService->getRouteForSchool($school, $routeId, false);
        $vehicle = $this->transportIntegrityService->getVehicleForSchool($school, $vehicleId, false);

        if ((int) $route->vehicle_id !== (int) $vehicle->id) {
            throw ValidationException::withMessages([
                'route_id' => ['The selected route does not belong to the selected vehicle.'],
            ]);
        }

        $assignments = StudentTransportAssignment::withTrashed()
            ->with(['student'])
            ->where('school_id', $school->id)
            ->where('route_id', $route->id)
            ->where('vehicle_id', $vehicle->id)
            ->where('academic_year_id', $academicYear->id)
            ->where(function ($query) use ($endDate) {
                $query->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $endDate);
            })
            ->where(function ($query) use ($startDate) {
                $query->whereNull('end_date')
                    ->orWhereDate('end_date', '>=', $startDate);
            })
            ->get();

        $studentIds = $assignments->pluck('student_id')->unique()->values()->all();

        $attendances = TransportAttendance::query()
            ->where('school_id', $school->id)
            ->where('vehicle_id', $vehicle->id)
            ->where('route_id', $route->id)
            ->whereIn('student_id', $studentIds)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('is_present', true)
            ->get();

        $report = [];
        foreach ($assignments as $assignment) {
            $student = $assignment->student;
            if (!$student) {
                continue;
            }

            $studentData = [
                'id' => $student->id,
                'admission_no' => $student->admission_no,
                'name' => trim(collect([$student->first_name, $student->middle_name, $student->last_name])->filter()->implode(' ')),
                'days' => array_fill(1, $daysInMonth, []),
            ];

            $studentAttendances = $attendances->where('student_id', $student->id);
            foreach ($studentAttendances as $attendance) {
                $day = (int) $attendance->attendance_date->format('d');
                if ($day >= 1 && $day <= $daysInMonth) {
                    $studentData['days'][$day][] = $this->getAttendanceTypeCode($attendance->attendance_type);
                }
            }

            $report[] = $studentData;
        }

        return [
            'students' => $report,
            'days_in_month' => $daysInMonth,
            'month_name' => date('F Y', strtotime($startDate)),
        ];
    }

    private function normalizeAttendanceRecords(array $data): Collection
    {
        if (!empty($data['attendance_data'])) {
            return collect($data['attendance_data'])->map(function ($studentData) {
                return [
                    'student_id' => (int) $studentData['student_id'],
                    'is_present' => (bool) $studentData['is_present'],
                    'remarks' => $studentData['remarks'] ?? null,
                ];
            })->unique('student_id')->values();
        }

        $checkedStudents = collect($data['checked_students'] ?? [])
            ->map(fn ($studentId) => (int) $studentId)
            ->values()
            ->all();

        return collect($data['students'] ?? [])->map(function ($studentId) use ($checkedStudents) {
            $studentId = (int) $studentId;

            return [
                'student_id' => $studentId,
                'is_present' => in_array($studentId, $checkedStudents, true),
                'remarks' => null,
            ];
        })->unique('student_id')->values();
    }

    private function getAttendanceTypeCode($type): string
    {
        if ($type instanceof TransportAttendanceType) {
            return match ($type) {
                TransportAttendanceType::PickupFromBusStop => 'PBS',
                TransportAttendanceType::DropAtSchoolCampus => 'DSC',
                TransportAttendanceType::PickupFromSchoolCampus => 'PSC',
                TransportAttendanceType::DropAtBusStop => 'DBS',
            };
        }

        return match ((int) $type) {
            1 => 'PBS',
            2 => 'DSC',
            3 => 'PSC',
            4 => 'DBS',
            default => '??',
        };
    }
}
