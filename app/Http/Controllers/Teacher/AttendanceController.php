<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        // TODO: Implement
        return view('teacher.attendance.index');
    }

    public function store(Request $request)
    {
        // TODO: Implement
    }
}

