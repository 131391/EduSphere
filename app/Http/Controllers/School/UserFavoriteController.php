<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserFavoriteController extends Controller
{
    public function index()
    {
        // Return empty array for now
        return response()->json([]);
    }

    public function toggle(Request $request)
    {
        // Mock success response
        return response()->json(['status' => 'added']);
    }

    public function check(Request $request)
    {
        // Mock false response
        return response()->json(['is_favorite' => false]);
    }

    public function destroy($id)
    {
        // Mock success
        return response()->json(['status' => 'removed']);
    }
}
