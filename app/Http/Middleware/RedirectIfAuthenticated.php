<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
                
                // Redirect based on role
                return match ($user->role) {
                    'super_admin' => redirect()->route('admin.dashboard'),
                    'school_admin' => redirect()->route('school.dashboard'),
                    'teacher' => redirect()->route('teacher.dashboard'),
                    'student' => redirect()->route('student.dashboard'),
                    'parent' => redirect()->route('parent.dashboard'),
                    default => redirect('/'),
                };
            }
        }

        return $next($request);
    }
}
