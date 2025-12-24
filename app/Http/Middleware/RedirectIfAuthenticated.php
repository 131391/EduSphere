<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use App\Models\Role;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$guards
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                
                // Ensure role relation is loaded
                if (!$user->relationLoaded('role')) {
                    $user->load('role');
                }

                if (!$user->role) {
                    return redirect('/');
                }
                
                // Redirect based on role
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

        return $next($request);
    }
}
