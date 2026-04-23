<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

use App\Models\Role;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Maximum login attempts before lockout.
     */
    protected int $maxAttempts = 5;

    /**
     * Lockout duration in seconds.
     */
    protected int $decaySeconds = 60;

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login', [
            'school' => app()->bound('currentSchool') ? app('currentSchool') : null,
        ]);
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

        // Rate limiting: prevent brute-force attacks
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, $this->maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => ["Too many login attempts. Please try again in {$seconds} seconds."],
            ]);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Subdomain cross-login check
            $host = $request->getHost();
            $subdomain = explode('.', $host)[0];
            
            // Only enforce school isolation on actual school subdomains
            if (!in_array($subdomain, ['www', 'admin', 'api'])) {
                $school = \App\Models\School::where('subdomain', $subdomain)->first();
                if ($school && !$user->canAccessSchool($school->id)) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    throw ValidationException::withMessages([
                        'email' => ['These credentials do not match our records for this school.'],
                    ]);
                }
            }

            // Check user status — reject inactive, suspended, and pending users
            if (!$user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $statusLabel = $user->status_label ?? 'inactive';

                throw ValidationException::withMessages([
                    'email' => ["Your account is {$statusLabel}. Please contact the administrator."],
                ]);
            }

            // Clear rate limiter on successful login
            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();

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

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Login successful.',
                    'redirect' => $this->redirectPathByRole($user),
                ]);
            }

            // Redirect based on role
            return $this->redirectByRole($user);
        }

        // Increment rate limiter on failed attempt
        RateLimiter::hit($throttleKey, $this->decaySeconds);

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
        return redirect($this->redirectPathByRole($user));
    }

    /**
     * Resolve redirect path based on user role.
     */
    protected function redirectPathByRole($user): string
    {
        // Ensure role relation is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        if (!$user->role) {
            return '/';
        }

        return match ($user->role->slug) {
            Role::SUPER_ADMIN => route('admin.dashboard'),
            Role::SCHOOL_ADMIN => route('school.dashboard'),
            Role::RECEPTIONIST => route('receptionist.dashboard'),
            Role::TEACHER => route('teacher.dashboard'),
            Role::STUDENT => route('student.dashboard'),
            Role::PARENT => route('parent.dashboard'),
            default => '/',
        };
    }
}
