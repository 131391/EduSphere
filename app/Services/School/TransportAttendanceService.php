<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\TransportAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransportAttendanceService
{
    /**
     * Mark transport attendance in bulk.
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
                TransportAttendance::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'student_id' => $studentData['student_id'],
                        'attendance_date' => $data['attendance_date'],
                        'attendance_type' => $data['attendance_type'],
                        'academic_year_id' => $data['academic_year_id'],
                    ],
                    [
                        'vehicle_id' => $data['vehicle_id'],
                        'route_id' => $data['route_id'],
                        'is_present' => $studentData['is_present'],
                        'time' => $now,
                        'remarks' => $studentData['remarks'] ?? null,
                        'marked_by' => $userId,
                    ]
                );
            }
        });
    }
}
