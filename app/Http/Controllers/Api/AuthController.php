<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // TODO: Implement API login
        return response()->json(['message' => 'API login not implemented yet'], 501);
    }

    public function register(Request $request)
    {
        // TODO: Implement API registration
        return response()->json(['message' => 'API registration not implemented yet'], 501);
    }
}

