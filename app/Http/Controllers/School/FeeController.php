<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $school = app('currentSchool');
        
        if (!$school) {
            abort(404, 'School not found');
        }
        
        return view('school.fees.index', [
            'school' => $school,
        ]);
    }

    public function create()
    {
        // TODO: Implement
        return view('school.fees.create');
    }

    public function store(Request $request)
    {
        // TODO: Implement
    }

    public function show($id)
    {
        // TODO: Implement
    }
}

