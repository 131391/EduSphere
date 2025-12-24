<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Enums\SchoolStatus;

class UserController extends Controller
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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:teacher,receptionist,accountant,librarian',
            'phone' => 'nullable|string|max:20',
        ]);

        $school = auth()->user()->school;

        $role = \App\Models\Role::where('slug', $request->role)->firstOrFail();

        User::create([
            'school_id' => $school->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
            'phone' => $request->phone,
            'status' => User::STATUS_ACTIVE,
        ]);

        return redirect()->route('school.users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAccess($user);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:teacher,receptionist,accountant,librarian',
            'phone' => 'nullable|string|max:20',
            'status' => ['required', 'integer', Rule::enum(SchoolStatus::class)],
        ]);

        $role = \App\Models\Role::where('slug', $request->role)->firstOrFail();

        $statusMap = [
            'active' => \App\Models\User::STATUS_ACTIVE,
            'inactive' => \App\Models\User::STATUS_INACTIVE,
            'suspended' => \App\Models\User::STATUS_SUSPENDED,
        ];

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $role->id,
            'phone' => $request->phone,
            'status' => $statusMap[$request->status] ?? \App\Models\User::STATUS_ACTIVE,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('school.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorizeAccess($user);
        
        // Prevent deleting school admin
        // Prevent deleting school admin
        if ($user->hasRole('school_admin')) {
            return back()->with('error', 'Cannot delete school admin user.');
        }

        $user->delete();

        return redirect()->route('school.users.index')->with('success', 'User deleted successfully.');
    }

    protected function authorizeAccess(User $user)
    {
        if ($user->school_id !== auth()->user()->school_id) {
            abort(403);
        }
    }
}
