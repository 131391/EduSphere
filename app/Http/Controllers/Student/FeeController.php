<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        // TODO: Implement
        return view('student.fees.index');
    }

    public function show($id)
    {
        // TODO: Implement
        return view('student.fees.show');
    }
}

