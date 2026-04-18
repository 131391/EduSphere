<?php

namespace App\Services\School;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceReportService
{
    /**
     * Get monthly attendance summary for a class
     */
    public function getMonthlyReport($classId, $sectionId, $year, $month, $schoolId)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $students = Student::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->active()
            ->orderBy('first_name')
            ->get();

        $attendanceData = Attendance::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('student_id');

        // Working days = days that have at least one attendance record for this class
        $workingDays = Attendance::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereBetween('date', [$startDate, $endDate])
            ->distinct('date')
            ->count('date');
        $workingDays = max($workingDays, 1); // avoid division by zero

        $report = [];
        foreach ($students as $student) {
            $studentAttendance = $attendanceData->get($student->id, collect());
            $days = [];
            $presentCount = 0;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date   = $startDate->copy()->day($day)->format('Y-m-d');
                $record = $studentAttendance->firstWhere('date', $date);
                $days[$day] = $record ? $record->status : null;

                // WHY compare to the enum case directly:
                // AttendanceStatus is int-backed (Present = 1). Comparing ->value === 'present'
                // would always be false because the value is an integer, not a string.
                if ($record && $record->status === \App\Enums\AttendanceStatus::Present) {
                    $presentCount++;
                }
            }

            $report[] = [
                'student'       => $student,
                'days'          => $days,
                'present_count' => $presentCount,
                'percentage'    => round(($presentCount / $workingDays) * 100, 2),
            ];
        }

        return [
            'report'      => $report,
            'daysInMonth' => $daysInMonth,
            'workingDays' => $workingDays,
            'monthName'   => $startDate->format('F Y'),
        ];
    }

    /**
     * Get attendance history for a specific student
     */
    public function getStudentAttendanceHistory($studentId, $academicYearId)
    {
        return Attendance::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get Daily Attendance Summary for all classes
     */
    public function getDailySummary($date, $schoolId)
    {
        // Pre-load all attendance for the date in one query to avoid N+1
        $allAttendance = Attendance::where('school_id', $schoolId)
            ->where('date', $date)
            ->get()
            ->groupBy('section_id');

        return ClassModel::where('school_id', $schoolId)
            ->with(['sections' => function ($query) {
                $query->withCount(['students' => fn($q) => $q->active()]);
            }])
            ->get()
            ->map(function ($class) use ($allAttendance) {
                $sectionsInfo = $class->sections->map(function ($section) use ($allAttendance) {
                    $attendance = $allAttendance->get($section->id, collect());

                    return [
                        'section_name'    => $section->name,
                        'total_students'  => $section->students_count,
                        // WHY use enum case comparison:
                        // AttendanceStatus::Present->value === 1 (integer), not 'present' (string).
                        // Using the enum case directly is type-safe and refactor-proof.
                        'present' => $attendance->filter(fn($a) => $a->status === \App\Enums\AttendanceStatus::Present)->count(),
                        'absent'  => $attendance->filter(fn($a) => $a->status === \App\Enums\AttendanceStatus::Absent)->count(),
                        'leave'   => $attendance->filter(fn($a) => $a->status === \App\Enums\AttendanceStatus::Excused)->count(),
                    ];
                });

                return ['class_name' => $class->name, 'sections' => $sectionsInfo];
            });
    }
}
