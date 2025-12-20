<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserFavoriteController extends Controller
{
    public function index()
    {
        $favorites = UserFavorite::where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json($favorites);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string',
        ]);

        $favorite = UserFavorite::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'url' => $request->url,
            ],
            [
                'title' => $request->title,
            ]
        );

        return response()->json([
            'message' => 'Favorite saved successfully',
            'favorite' => $favorite,
        ]);
    }

    public function destroy(UserFavorite $userFavorite)
    {
        if ($userFavorite->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $userFavorite->delete();

        return response()->json(['message' => 'Favorite removed successfully']);
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string',
        ]);

        $favorite = UserFavorite::where('user_id', Auth::id())
            ->where('url', $request->url)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed']);
        }

        $favorite = UserFavorite::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'url' => $request->url,
        ]);

        return response()->json(['status' => 'added', 'favorite' => $favorite]);
    }

    public function check(Request $request)
    {
        $isFavorite = UserFavorite::where('user_id', Auth::id())
            ->where('url', $request->url)
            ->exists();

        return response()->json(['is_favorite' => $isFavorite]);
    }
}
