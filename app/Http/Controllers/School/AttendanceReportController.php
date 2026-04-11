<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\ClassModel;
use App\Models\Section;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Services\School\AttendanceReportService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceReportController extends TenantController
{
    protected $reportService;

    public function __construct(AttendanceReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    /**
     * Monthly Class Attendance Report
     */
    public function monthly(Request $request)
    {
        $this->ensureSchoolActive();
        
        $classes = ClassModel::where('school_id', $this->getSchoolId())->get();
        $sections = Section::whereIn('class_id', $classes->pluck('id'))->get();
        
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $monthYear = $request->input('month', now()->format('Y-m'));
        
        $year = (int) date('Y', strtotime($monthYear));
        $month = (int) date('m', strtotime($monthYear));

        $reportData = null;
        if ($classId && $sectionId) {
            $reportData = $this->reportService->getMonthlyReport($classId, $sectionId, $year, $month);
        }

        return view('school.reports.attendance.monthly', compact('classes', 'sections', 'reportData', 'classId', 'sectionId', 'monthYear'));
    }

    /**
     * Student-wise Attendance History
     */
    public function student(Request $request)
    {
        $this->ensureSchoolActive();
        
        $students = Student::where('school_id', $this->getSchoolId())->active()->get();
        $academicYear = AcademicYear::where('school_id', $this->getSchoolId())->where('is_active', true)->first();
        
        $studentId = $request->input('student_id');
        $history = null;
        
        if ($studentId && $academicYear) {
            $history = $this->reportService->getStudentAttendanceHistory($studentId, $academicYear->id);
        }

        return view('school.reports.attendance.student', compact('students', 'history', 'studentId'));
    }

    /**
     * Daily Attendance Summary for Class
     */
    public function daily(Request $request)
    {
        $this->ensureSchoolActive();
        
        $date = $request->input('date', now()->format('Y-m-d'));
        $summary = $this->reportService->getDailySummary($date, $this->getSchoolId());

        return view('school.reports.attendance.daily', compact('summary', 'date'));
    }
}
