<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\TenantController;
use App\Models\Visitor;
use App\Models\Student;
use App\Models\FeeCollection;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends TenantController
{
    public function index()
    {
        $schoolId = $this->getSchoolId();
        $today = Carbon::today();

        // Statistics
        $stats = [
            'total_collection' => $this->getTotalCollection($schoolId),
            'today_collection' => $this->getTodayCollection($schoolId, $today),
            'total_admission' => Student::where('school_id', $schoolId)->count(),
            'today_admission' => Student::where('school_id', $schoolId)
                ->whereDate('created_at', $today)->count(),
            'total_enquiry' => Visitor::where('school_id', $schoolId)
                ->whereIn('visit_purpose', ['Admission Enquiry', 'General Enquiry'])->count(),
            'today_enquiry' => Visitor::where('school_id', $schoolId)
                ->whereIn('visit_purpose', ['Admission Enquiry', 'General Enquiry'])
                ->whereDate('created_at', $today)->count(),
            'running_classes' => \App\Models\ClassModel::where('school_id', $schoolId)->count(),
            'total_sections' => \App\Models\Section::where('school_id', $schoolId)->count(),
        ];

        // Visitor Statistics
        $visitorStats = [
            'total' => Visitor::where('school_id', $schoolId)->count(),
            'online' => Visitor::where('school_id', $schoolId)
                ->where('meeting_type', 'online')->count(),
            'offline' => Visitor::where('school_id', $schoolId)
                ->where('meeting_type', 'offline')->count(),
            'office' => Visitor::where('school_id', $schoolId)
                ->where('meeting_type', 'office')->count(),
            'cancelled' => Visitor::where('school_id', $schoolId)
                ->where('status', 'cancelled')->count(),
        ];

        // Recent Activity (Latest visitors)
        $recentVisitors = Visitor::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('receptionist.dashboard.index', compact('stats', 'visitorStats', 'recentVisitors'));
    }

    private function getTotalCollection($schoolId)
    {
        // This would integrate with your fee collection system
        // For now, returning 0 as placeholder
        return 0;
    }

    private function getTodayCollection($schoolId, $date)
    {
        // This would integrate with your fee collection system
        // For now, returning 0 as placeholder
        return 0;
    }
}
