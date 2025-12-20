<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        // TODO: Implement
        return view('parent.fees.index');
    }

    public function show($id)
    {
        // TODO: Implement
        return view('parent.fees.show');
    }
}

