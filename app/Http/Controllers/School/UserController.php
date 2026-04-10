<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Enums\SchoolStatus;
use App\Enums\UserStatus;

class UserController extends TenantController
{
    public function index()
    {
        $school = auth()->user()->school;
        $users = User::with('role')
            ->where('school_id', $school->id)
            ->whereHas('role', function($q) {
                $q->whereIn('slug', ['teacher', 'receptionist', 'accountant', 'librarian']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $roles = [
            'teacher' => 'Teacher',
            'receptionist' => 'Receptionist',
            'accountant' => 'Accountant',
            'librarian' => 'Librarian',
        ];

        return view('school.users.index', compact('users', 'roles'));
    }

    public function store(\App\Http\Requests\School\StoreUserRequest $request)
    {
        $validated = $request->validated();
        $school = auth()->user()->school;

        $role = \App\Models\Role::where('slug', $validated['role'])->firstOrFail();

        $user = User::create([
            'school_id' => $school->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
            'phone' => $validated['phone'],
            'status' => UserStatus::Active,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully.',
                'user' => $user
            ]);
        }

        return redirect()->route('school.users.index')->with('success', 'User created successfully.');
    }

    public function update(\App\Http\Requests\School\UpdateUserRequest $request, User $user)
    {
        $this->authorizeTenant($user);
        $validated = $request->validated();

        $role = \App\Models\Role::where('slug', $validated['role'])->firstOrFail();

        $statusMap = [
            'active' => UserStatus::Active,
            'inactive' => UserStatus::Inactive,
            'suspended' => UserStatus::Suspended,
        ];

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $role->id,
            'phone' => $validated['phone'],
            'status' => $statusMap[$validated['status']] ?? UserStatus::Active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully.',
                'user' => $user->load('role')
            ]);
        }

        return redirect()->route('school.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorizeTenant($user);
        
        // Prevent deleting school admin
        if ($user->isSchoolAdmin()) {
            return back()->with('error', 'Cannot delete school admin user.');
        }

        $user->delete();

        return redirect()->route('school.users.index')->with('success', 'User deleted successfully.');
    }
}
