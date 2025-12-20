<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index()
    {
        // TODO: Implement
        return view('student.results.index');
    }

    public function show($id)
    {
        // TODO: Implement
        return view('student.results.show');
    }
}

