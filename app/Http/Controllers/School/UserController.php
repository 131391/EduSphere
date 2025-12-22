<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;
        $users = User::where('school_id', $school->id)
            ->whereIn('role', ['teacher', 'receptionist', 'accountant', 'librarian'])
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

        User::create([
            'school_id' => $school->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => 'active',
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
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'status' => $request->status,
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
        if ($user->role === 'school_admin') {
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
