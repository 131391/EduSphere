<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Parent\Concerns\ResolvesParent;
use App\Http\Controllers\TenantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends TenantController
{
    use ResolvesParent;

    public function __construct()
    {
        parent::__construct();
    }

    public function show()
    {
        $this->authorize('parent:operate');
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        return view('parent.profile.show', [
            'parentProfile' => $parentProfile->fresh(),
            'user'          => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('parent:operate');
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();
        $user          = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'      => 'nullable|string|max:20',
            'occupation' => 'nullable|string|max:100',
            'address'    => 'nullable|string|max:500',
            'photo'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $photoPath = $parentProfile->photo;
        if ($request->hasFile('photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('parents/photos', 'public');
        }

        DB::transaction(function () use ($parentProfile, $user, $validated, $photoPath) {
            $parentProfile->update([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'] ?? null,
                'email'      => $validated['email'],
                'phone'      => $validated['phone'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'address'    => $validated['address'] ?? null,
                'photo'      => $photoPath,
            ]);

            $user->update([
                'name'  => trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')),
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);
        });

        return redirect()->route('parent.profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    public function password()
    {
        $this->authorize('parent:operate');
        $this->ensureSchoolActive();
        $this->currentParentOrFail();

        return view('parent.profile.password');
    }

    public function updatePassword(Request $request)
    {
        $this->authorize('parent:operate');
        $this->ensureSchoolActive();
        $this->currentParentOrFail();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('parent.profile.show')
            ->with('success', 'Password changed successfully.');
    }
}
