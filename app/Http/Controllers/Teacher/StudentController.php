<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        // TODO: Implement
        return view('teacher.students.index');
    }

    public function show($id)
    {
        // TODO: Implement
        return view('teacher.students.show');
    }
}

