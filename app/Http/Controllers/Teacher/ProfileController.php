<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Teacher\Concerns\ResolvesTeacher;
use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends TenantController
{
    use ResolvesTeacher;

    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        $this->authorize('teacher:operate');
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();

        return view('teacher.profile.show', [
            'teacher' => $teacher->fresh(),
            'user'    => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('teacher:operate');
        $this->ensureSchoolActive();
        $teacher = $this->currentTeacherOrFail();
        $user    = Auth::user();

        $validated = $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'nullable|string|max:100',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:500',
            'qualification'    => 'nullable|string|max:255',
            'date_of_birth'    => 'nullable|date|before:today',
            'photo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $photoPath = $teacher->photo;
        if ($request->hasFile('photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('teachers/photos', 'public');
        }

        DB::transaction(function () use ($teacher, $user, $validated, $photoPath) {
            $teacher->update([
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'] ?? null,
                'phone'         => $validated['phone'] ?? null,
                'address'       => $validated['address'] ?? null,
                'qualification' => $validated['qualification'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'email'         => $validated['email'],
                'photo'         => $photoPath,
            ]);

            $user->update([
                'name'  => trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')),
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);
        });

        return redirect()->route('teacher.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function password()
    {
        $this->authorize('teacher:operate');
        $this->ensureSchoolActive();
        $this->currentTeacherOrFail();

        return view('teacher.profile.password');
    }

    public function updatePassword(Request $request)
    {
        $this->authorize('teacher:operate');
        $this->ensureSchoolActive();
        $this->currentTeacherOrFail();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('teacher.profile.show')
            ->with('success', 'Password changed successfully.');
    }
}
