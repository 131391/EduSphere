<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $school = app('currentSchool');
        
        if (!$school) {
            abort(404, 'School not found');
        }
        
        return view('school.dashboard', [
            'school' => $school,
            'title' => 'School Dashboard',
        ]);
    }
}

