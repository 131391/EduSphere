<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public function changePassword()
    {
        return view('admin.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Password changed successfully.']);
        }

        return back()->with('success', 'Password changed successfully.');
    }

    public function profile()
    {
        return view('admin.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        if ($request->ajax()) {
            return response()->json(['message' => 'Profile updated successfully.']);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Display the system settings page.
     */
    public function systemSettings()
    {
        $settings = \App\Models\GlobalSetting::all()->groupBy('group');
        return view('admin.settings.system', compact('settings'));
    }

    /**
     * Update global system settings.
     */
    public function updateSystemSettings(Request $request)
    {
        $settings = $request->except('_token');

        foreach ($settings as $key => $value) {
            \App\Models\GlobalSetting::where('key', $key)->update(['value' => $value]);
        }

        return back()->with('success', 'System settings updated successfully.');
    }
}

