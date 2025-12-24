<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Show the application home page.
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Redirect to the appropriate dashboard based on role.
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Ensure role relation is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        if (!$user->role) {
            return redirect('/');
        }

        return match ($user->role->slug) {
            \App\Models\Role::SUPER_ADMIN => redirect()->route('admin.dashboard'),
            \App\Models\Role::SCHOOL_ADMIN => redirect()->route('school.dashboard'),
            \App\Models\Role::TEACHER => redirect()->route('teacher.dashboard'),
            \App\Models\Role::STUDENT => redirect()->route('student.dashboard'),
            \App\Models\Role::PARENT => redirect()->route('parent.dashboard'),
            \App\Models\Role::RECEPTIONIST => redirect()->route('receptionist.dashboard'),
            default => redirect('/'),
        };
    }
}

