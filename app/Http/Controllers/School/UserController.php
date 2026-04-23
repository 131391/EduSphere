<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\User;
use App\Models\Role;
use App\Enums\UserStatus;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends TenantController
{
    use HasAjaxDataTable;

    private array $allowedRoleSlugs = ['teacher', 'receptionist', 'accountant', 'librarian'];

    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        $schoolId = $this->getSchoolId();

        $transformer = function ($user) {
            $status = $user->status instanceof UserStatus ? $user->status : null;
            $color = $status?->color() ?? 'gray';

            $colorClass = match ($color) {
                'green' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                'red' => 'bg-rose-50 text-rose-700 border-rose-100',
                'yellow' => 'bg-amber-50 text-amber-700 border-amber-100',
                default => 'bg-slate-50 text-slate-700 border-slate-100',
            };

            $statusConfig = [
                'bg' => "bg-{$color}-50",
                'text' => "text-{$color}-700",
                'border' => "border-{$color}-100",
                'icon' => match ($status) {
                    UserStatus::Active => 'fa-check-circle',
                    UserStatus::Inactive => 'fa-minus-circle',
                    UserStatus::Suspended => 'fa-ban',
                    UserStatus::Pending => 'fa-clock',
                    default => 'fa-question-circle',
                },
                'class' => $colorClass,
            ];

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?: '-',
                'initials' => collect(explode(' ', $user->name))
                    ->map(fn($n) => mb_substr($n, 0, 1))
                    ->take(2)
                    ->join(''),
                'role_slug' => $user->role?->slug,
                'role_name' => $user->role?->name ?? ucfirst($user->role?->slug ?? ''),
                'status' => $user->status?->value,
                'status_label' => $status?->label() ?? 'N/A',
                'status_config' => $statusConfig,
                'created_at' => $user->created_at?->format('d M, Y'),
                'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
            ];
        };

        $query = User::with('role')
            ->where('school_id', $schoolId)
            ->whereHas('role', fn($q) => $q->whereIn('slug', $this->allowedRoleSlugs));

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $roleSlug = $request->role;
            $query->whereHas('role', fn($q) => $q->where('slug', $roleSlug));
        }

        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', (int) $request->status);
        }

        if ($request->expectsJson() || $request->ajax() || $request->filled('filters') || $request->has('page')) {
            return $this->handleAjaxTable($query, $transformer, $this->getStats($schoolId));
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $this->getStats($schoolId),
        ]);

        $roles = [
            'teacher' => 'Teacher',
            'receptionist' => 'Receptionist',
            'accountant' => 'Accountant',
            'librarian' => 'Librarian',
        ];

        $statuses = [
            (string) UserStatus::Active->value => UserStatus::Active->label(),
            (string) UserStatus::Inactive->value => UserStatus::Inactive->label(),
            (string) UserStatus::Suspended->value => UserStatus::Suspended->label(),
            (string) UserStatus::Pending->value => UserStatus::Pending->label(),
        ];

        return view('school.users.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
            'roles' => $roles,
            'statuses' => $statuses,
        ]);
    }

    private function getStats(int $schoolId): array
    {
        $base = User::where('school_id', $schoolId)
            ->whereHas('role', fn($q) => $q->whereIn('slug', $this->allowedRoleSlugs));

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', UserStatus::Active)->count(),
            'inactive' => (clone $base)->where('status', UserStatus::Inactive)->count(),
            'suspended' => (clone $base)->where('status', UserStatus::Suspended)->count(),
            'pending' => (clone $base)->where('status', UserStatus::Pending)->count(),
        ];
    }

    public function store(\App\Http\Requests\School\StoreUserRequest $request)
    {
        try {
            $this->authorize('create', User::class);
            $validated = $request->validated();
            $school = auth()->user()->school;

            $role = Role::where('slug', $validated['role'])->firstOrFail();

            $user = User::create([
                'school_id' => $school->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role_id' => $role->id,
                'phone' => $validated['phone'] ?? null,
                'status' => UserStatus::Active,
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully.',
                    'data' => $user->load('role'),
                ]);
            }

            return redirect()->route('school.users.index')->with('success', 'User created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create user: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function update(\App\Http\Requests\School\UpdateUserRequest $request, User $user)
    {
        $this->authorizeTenant($user);
        $this->authorize('update', $user);

        try {
            $validated = $request->validated();
            $role = Role::where('slug', $validated['role'])->firstOrFail();

            $statusMap = [
                'active' => UserStatus::Active,
                'inactive' => UserStatus::Inactive,
                'suspended' => UserStatus::Suspended,
            ];

            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role_id' => $role->id,
                'phone' => $validated['phone'] ?? null,
                'status' => $statusMap[$validated['status']] ?? UserStatus::Active,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully.',
                    'data' => $user->load('role'),
                ]);
            }

            return redirect()->route('school.users.index')->with('success', 'User updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, User $user)
    {
        $this->authorizeTenant($user);
        $this->authorize('delete', $user);

        if ($user->isSchoolAdmin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete school admin user.',
                ], 422);
            }
            return back()->with('error', 'Cannot delete school admin user.');
        }

        try {
            $user->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully.',
                ]);
            }

            return redirect()->route('school.users.index')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
