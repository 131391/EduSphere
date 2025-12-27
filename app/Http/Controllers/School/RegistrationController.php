<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function index()
    {
        // TODO: Implement
        return view('school.student-registrations.index');
    }

    public function import(Request $request)
    {
        // TODO: Implement
    }

    public function downloadTemplate()
    {
        // TODO: Implement
    }
}

