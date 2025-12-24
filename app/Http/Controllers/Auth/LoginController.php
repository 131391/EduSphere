<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use App\Models\Role;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Update last login (use try-catch to avoid errors if update fails)
            try {
                $user->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail login
                \Log::warning('Failed to update last login: ' . $e->getMessage());
            }

            // Redirect based on role
            return $this->redirectByRole($user);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redirect user based on their role.
     */
    protected function redirectByRole($user)
    {
        // Ensure role relation is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        if (!$user->role) {
            return redirect('/');
        }

        return match ($user->role->slug) {
            Role::SUPER_ADMIN => redirect()->route('admin.dashboard'),
            Role::SCHOOL_ADMIN => redirect()->route('school.dashboard'),
            Role::RECEPTIONIST => redirect()->route('receptionist.dashboard'),
            Role::TEACHER => redirect()->route('teacher.dashboard'),
            Role::STUDENT => redirect()->route('student.dashboard'),
            Role::PARENT => redirect()->route('parent.dashboard'),
            default => redirect('/'),
        };
    }
}

