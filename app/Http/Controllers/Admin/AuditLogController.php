<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    use HasAjaxDataTable;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 25);
        $query = Activity::with('causer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event')) {
            $query->where('description', $request->event);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $sort = in_array($request->input('sort'), ['created_at', 'description']) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction);

        $stats = [
            'total'   => Activity::count(),
            'today'   => Activity::whereDate('created_at', now()->toDateString())->count(),
            'created' => Activity::where('description', 'created')->count(),
            'updated' => Activity::where('description', 'updated')->count(),
            'deleted' => Activity::where('description', 'deleted')->count(),
        ];

        $transformer = function ($log) {
            $desc = $log->description;
            $config = match ($desc) {
                'created' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-100', 'icon' => 'fa-plus-circle'],
                'updated' => ['bg' => 'bg-blue-50',  'text' => 'text-blue-700',  'border' => 'border-blue-100',  'icon' => 'fa-edit'],
                'deleted' => ['bg' => 'bg-rose-50',  'text' => 'text-rose-700',  'border' => 'border-rose-100',  'icon' => 'fa-trash-alt'],
                default   => ['bg' => 'bg-gray-50',  'text' => 'text-gray-700',  'border' => 'border-gray-100',  'icon' => 'fa-info-circle'],
            };

            return [
                'id'            => $log->id,
                'date'          => $log->created_at->format('M d, Y'),
                'time'          => $log->created_at->format('H:i:s'),
                'diff'          => $log->created_at->diffForHumans(),
                'causer_name'   => $log->causer?->name ?? 'System Process',
                'causer_role'   => $log->causer?->role?->name ?? 'Core',
                'causer_initials' => strtoupper(substr($log->causer?->name ?? 'SP', 0, 2)),
                'event'         => ucfirst($desc),
                'event_config'  => $config,
                'model'         => class_basename($log->subject_type ?? 'N/A'),
                'subject_id'    => $log->subject_id ? "#{$log->subject_id}" : '',
                'has_delta'     => $log->properties && $log->properties->has('attributes'),
                'old_state'     => $log->properties?->has('old') ? json_encode($log->properties['old'], JSON_PRETTY_PRINT) : null,
                'new_state'     => $log->properties?->has('attributes') ? json_encode($log->properties['attributes'], JSON_PRETTY_PRINT) : null,
            ];
        };

        $logs = $query->paginate($perPage);

        if ($request->ajax()) {
            return $this->ajaxResponse($logs, $stats, $transformer);
        }

        $initialData = [
            'rows' => $logs->getCollection()->map($transformer)->values(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page'    => $logs->lastPage(),
                'per_page'     => $logs->perPage(),
                'total'        => $logs->total(),
                'from'         => $logs->firstItem(),
                'to'           => $logs->lastItem(),
            ],
            'stats' => $stats,
        ];

        return view('admin.audit-logs.index', compact('stats', 'initialData'));
    }
}
