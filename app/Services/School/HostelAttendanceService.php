<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\HostelAttendance;
use App\Models\HostelBedAssignment;
use App\Enums\GeneralStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HostelAttendanceService
{
    /**
     * Mark hostel attendance in bulk.
     *
     * @param School $school
     * @param array $data
     * @return void
     */
    public function markBulkAttendance(School $school, array $data): void
    {
        DB::transaction(function () use ($school, $data) {
            $userId = Auth::id();
            $now = now();

            foreach ($data['attendance_data'] as $studentData) {
                HostelAttendance::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'student_id' => $studentData['student_id'],
                        'attendance_date' => $data['attendance_date'],
                        'academic_year_id' => $data['academic_year_id'],
                    ],
                    [
                        'hostel_id' => $data['hostel_id'],
                        'hostel_floor_id' => $data['hostel_floor_id'],
                        'hostel_room_id' => $data['hostel_room_id'],
                        'is_present' => $studentData['is_present'],
                        'time' => $now,
                        'remarks' => $studentData['remarks'] ?? null,
                        'marked_by' => $userId,
                    ]
                );
            }
        });
    }

    /**
     * Get month-wise attendance report for a hostel room.
     *
     * @param School $school
     * @param int $hostelId
     * @param int $floorId
     * @param int $roomId
     * @param string $month Y-m
     * @return array
     */
    public function getMonthWiseReport(School $school, int $hostelId, int $floorId, int $roomId, string $month): array
    {
        $year = (int)substr($month, 0, 4);
        $monthNum = (int)substr($month, 5, 2);
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthNum, $year);

        $startDate = "{$year}-{$monthNum}-01";
        $endDate = "{$year}-{$monthNum}-{$daysInMonth}";

        // Get current academic year
        $academicYear = \App\Models\AcademicYear::where('school_id', $school->id)
            ->where('is_current', true)
            ->first() ?: \App\Models\AcademicYear::where('school_id', $school->id)->latest('start_date')->first();

        if (!$academicYear) {
            return [];
        }

        // Get all students assigned to this room
        $assignments = HostelBedAssignment::with(['student'])
            ->where('school_id', $school->id)
            ->where('hostel_id', $hostelId)
            ->where('hostel_floor_id', $floorId)
            ->where('hostel_room_id', $roomId)
            ->where('status', GeneralStatus::Active)
            ->whereNull('deleted_at')
            ->get();

        $studentIds = $assignments->pluck('student_id')->toArray();

        // Get all attendance records
        $attendances = HostelAttendance::where('school_id', $school->id)
            ->where('hostel_id', $hostelId)
            ->whereIn('student_id', $studentIds)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('is_present', true)
            ->get();

        $report = [];
        foreach ($assignments as $assignment) {
            $student = $assignment->student;
            $studentData = [
                'id' => $student->id,
                'admission_no' => $student->admission_no,
                'name' => $student->full_name,
                'days' => array_fill(1, $daysInMonth, false)
            ];

            $studentAttendances = $attendances->where('student_id', $student->id);
            foreach ($studentAttendances as $attendance) {
                $day = (int)$attendance->attendance_date->format('d');
                if ($day >= 1 && $day <= $daysInMonth) {
                    $studentData['days'][$day] = true;
                }
            }

            $report[] = $studentData;
        }

        return [
            'students' => $report,
            'days_in_month' => $daysInMonth,
            'month_name' => date('F Y', strtotime($startDate))
        ];
    }
}
