<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SchoolAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        // Super admin can access all schools
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user belongs to current school
        $currentSchool = app('currentSchool');
        
        if (!$currentSchool) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'School not found.'], 404);
            }

            abort(404, 'School not found');
        }

        if (!$user->canAccessSchool($currentSchool->id)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have access to this school.'], 403);
            }

            abort(403, 'You do not have access to this school');
        }

        return $next($request);
    }
}
