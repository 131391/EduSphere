<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        // TODO: Implement
        return view('parent.children.index');
    }

    public function show($id)
    {
        // TODO: Implement
        return view('parent.children.show');
    }
}

