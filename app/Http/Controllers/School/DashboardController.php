<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;

class DashboardController extends TenantController
{
    public function index()
    {
        $school = app('currentSchool');
        
        if (!$school) {
            abort(404, 'School not found');
        }

        // Calculate stats
        $totalCollection = \App\Models\Fee::where('school_id', $school->id)
            ->where('payment_status', 'paid')
            ->sum('paid_amount');
        
        $todayCollection = \App\Models\Fee::where('school_id', $school->id)
            ->where('payment_status', 'paid')
            ->whereDate('payment_date', today())
            ->sum('paid_amount');
        
        $totalAdmission = \App\Models\Student::where('school_id', $school->id)->count();
        $todayAdmission = \App\Models\Student::where('school_id', $school->id)
            ->whereDate('admission_date', today())
            ->count();
        
        $totalEnquiry = \App\Models\Registration::where('school_id', $school->id)->count();
        $todayEnquiry = \App\Models\Registration::where('school_id', $school->id)
            ->whereDate('registration_date', today())
            ->count();
        
        $runningClasses = \App\Models\ClassModel::where('school_id', $school->id)
            ->where('is_available', true)
            ->count();
        
        $totalSections = \App\Models\Section::where('school_id', $school->id)->count();
        
        $recentFees = \App\Models\Fee::where('school_id', $school->id)
            ->with('student')
            ->latest()
            ->take(5)
            ->get();
        
        return view('school.dashboard', [
            'school' => $school,
            'title' => 'School Dashboard',
            'totalCollection' => $totalCollection,
            'todayCollection' => $todayCollection,
            'totalAdmission' => $totalAdmission,
            'todayAdmission' => $todayAdmission,
            'totalEnquiry' => $totalEnquiry,
            'todayEnquiry' => $todayEnquiry,
            'runningClasses' => $runningClasses,
            'totalSections' => $totalSections,
            'recentFees' => $recentFees,
        ]);
    }
}

