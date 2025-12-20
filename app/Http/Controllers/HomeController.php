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
        if (Auth::check()) {
            $user = Auth::user();
            return match ($user->role) {
                'super_admin' => redirect()->route('admin.dashboard'),
                'school_admin' => redirect()->route('school.dashboard'),
                'teacher' => redirect()->route('teacher.dashboard'),
                'student' => redirect()->route('student.dashboard'),
                'parent' => redirect()->route('parent.dashboard'),
                default => view('welcome'),
            };
        }

        return redirect()->route('login');
    }
}

