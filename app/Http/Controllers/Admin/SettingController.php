<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{


    public function changePassword()
    {
        return view('admin.change-password');
    }

    public function profile()
    {
        return view('admin.profile');
    }
}

