<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends TenantController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        $this->ensureSchoolActive();

        return view('school.profile.show', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $this->ensureSchoolActive();
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->route('school.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function password()
    {
        $this->ensureSchoolActive();

        return view('school.profile.password');
    }

    public function updatePassword(Request $request)
    {
        $this->ensureSchoolActive();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->route('school.profile.show')
            ->with('success', 'Password changed successfully.');
    }
}
