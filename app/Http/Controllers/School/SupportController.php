<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;

class SupportController extends TenantController
{
    public function index()
    {
        return view('school.support.index');
    }
}
