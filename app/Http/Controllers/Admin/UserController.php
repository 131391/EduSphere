<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\School;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use \App\Traits\HasAjaxDataTable;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $query = User::with(['role', 'school']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by school
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->whereHas('role', fn($q) => $q->where('slug', $request->role));
        }

        // Filter by status
        if ($request->filled('status') && is_numeric($request->status)) {
            $query->where('status', (int)$request->status);
        }

        // Sorting
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        
        // Define allowable sort columns to prevent SQL injection
        $allowedSorts = ['id', 'name', 'email', 'status', 'last_login_at', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Stats
        $stats = [
            'total'     => User::count(),
            'active'    => User::where('status', \App\Enums\UserStatus::Active)->count(),
            'inactive'  => User::where('status', \App\Enums\UserStatus::Inactive)->count(),
            'suspended' => User::where('status', \App\Enums\UserStatus::Suspended)->count(),
            'pending'   => User::where('status', \App\Enums\UserStatus::Pending)->count(),
        ];

        // Export Functionality
        if ($request->input('export') === 'csv') {
            return $this->exportToCsv($query);
        }

        $users = $query->paginate($perPage);
        $schools = School::orderBy('name')->get(['id', 'name']);
        $roles = Role::orderBy('name')->get(['id', 'name', 'slug']);

        // Transformer
        $transformer = function ($user) {
            $status = $user->status;
            // Config array for frontend rendering equivalent
            $config = match ($status?->value) {
                1 => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-100', 'icon' => 'fa-check-circle'],
                2 => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-100', 'icon' => 'fa-ban'],
                3 => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-100', 'icon' => 'fa-clock'],
                default => ['bg' => 'bg-gray-50', 'text' => 'text-gray-700', 'border' => 'border-gray-100', 'icon' => 'fa-pause-circle'],
            };

            return [
                'id'            => $user->id,
                'name'          => $user->name,
                'initials'      => strtoupper(substr($user->name, 0, 2)),
                'email'         => $user->email,
                'role'          => $user->role->name ?? 'N/A',
                'school'        => $user->school->name ?? 'EduSphere Global',
                'status_value'  => $status?->value,
                'status_label'  => $status?->label() ?? 'Unknown',
                'status_config' => $config,
                'last_login'    => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
            ];
        };

        if ($request->ajax()) {
            return $this->ajaxResponse($users, $stats, $transformer);
        }

        $initialData = [
            'rows' => $users->getCollection()->map($transformer)->values(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
                'from'         => $users->firstItem(),
                'to'           => $users->lastItem(),
            ],
            'stats' => $stats
        ];

        return view('admin.users.index', compact('users', 'schools', 'roles', 'stats', 'initialData'));
    }

    private function exportToCsv($query)
    {
        $filename = 'users_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, ['ID', 'Name', 'Email', 'Role', 'School', 'Status', 'Last Login', 'Created At']);
            
            // CSV Data using Cursor to prevent memory exhaustion
            foreach ($query->cursor() as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role->name ?? 'N/A',
                    $user->school->name ?? 'EduSphere Global',
                    $user->status->label(),
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
