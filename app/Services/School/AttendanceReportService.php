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
    public function getMonthlyReport($classId, $sectionId, $year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $students = Student::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->active()
            ->orderBy('full_name')
            ->get();

        $attendanceData = Attendance::where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('student_id');

        $report = [];
        foreach ($students as $student) {
            $studentAttendance = $attendanceData->get($student->id, collect());
            $days = [];
            $presentCount = 0;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $startDate->copy()->day($day)->format('Y-m-d');
                $status = $studentAttendance->firstWhere('date', $date);
                $days[$day] = $status ? $status->status : null;
                
                if ($status && $status->status->value === 'present') {
                    $presentCount++;
                }
            }

            $report[] = [
                'student' => $student,
                'days' => $days,
                'present_count' => $presentCount,
                'percentage' => $daysInMonth > 0 ? round(($presentCount / $daysInMonth) * 100, 2) : 0,
            ];
        }

        return [
            'report' => $report,
            'daysInMonth' => $daysInMonth,
            'monthName' => $startDate->format('F Y'),
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
        return ClassModel::where('school_id', $schoolId)
            ->with(['sections' => function($query) use ($date) {
                $query->withCount(['students' => function($q) {
                    $q->active();
                }]);
            }])
            ->get()
            ->map(function($class) use ($date) {
                $sectionsInfo = $class->sections->map(function($section) use ($date) {
                    $attendance = Attendance::where('section_id', $section->id)
                        ->where('date', $date)
                        ->get();
                    
                    return [
                        'section_name' => $section->name,
                        'total_students' => $section->students_count,
                        'present' => $attendance->where('status.value', 'present')->count(),
                        'absent' => $attendance->where('status.value', 'absent')->count(),
                        'leave' => $attendance->where('status.value', 'leave')->count(),
                    ];
                });

                return [
                    'class_name' => $class->name,
                    'sections' => $sectionsInfo
                ];
            });
    }
}
