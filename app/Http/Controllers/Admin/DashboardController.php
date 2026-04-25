<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Enums\SchoolStatus;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        // Cache dashboard stats for 5 minutes
        $stats = Cache::remember('admin.dashboard.stats', 300, function () {
            return [
                'totalSchools' => School::withTrashed()->count(),
                'activeSchools' => School::where('status', SchoolStatus::Active)->count(),
                'inactiveSchools' => School::where('status', SchoolStatus::Inactive)->count(),
                'suspendedSchools' => School::where('status', SchoolStatus::Suspended)->count(),
                'totalUsers' => User::count(),
                'totalStudents' => Student::count(),
                'totalTeachers' => Teacher::count(),
            ];
        });

        // Schools with subscription expiring in the next 30 days
        $expiringSchools = School::where('status', SchoolStatus::Active)
            ->whereNotNull('subscription_end_date')
            ->whereBetween('subscription_end_date', [now(), now()->addDays(30)])
            ->orderBy('subscription_end_date')
            ->get();

        // Recent activity logs (last 15)
        $recentActivity = Activity::with('causer')
            ->latest()
            ->take(15)
            ->get();

        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $dateSelect = $driver === 'sqlite' 
            ? "strftime('%Y-%m', created_at) as month" 
            : 'DATE_FORMAT(created_at, "%Y-%m") as month';

        $schoolGrowth = School::withTrashed()
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw($dateSelect . ', COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        return view('admin.dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'expiringSchools' => $expiringSchools,
            'recentActivity' => $recentActivity,
            'schoolGrowth' => $schoolGrowth,
        ]);
    }
}
