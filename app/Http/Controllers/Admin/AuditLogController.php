<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer');

        // Filter by causer (user)
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)->where('causer_type', 'App\\Models\\User');
        }

        // Filter by description/action
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        // Stats
        $stats = [
            'total'     => Activity::count(),
            'today'     => Activity::whereDate('created_at', now()->toDateString())->count(),
            'created'   => Activity::where('description', 'created')->count(),
            'updated'   => Activity::where('description', 'updated')->count(),
            'deleted'   => Activity::where('description', 'deleted')->count(),
        ];

        return view('admin.audit-logs.index', compact('logs', 'stats'));
    }
}
