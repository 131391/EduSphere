<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Handle the global search.
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search Schools
        $schools = School::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->take(5)
            ->get();
        
        foreach ($schools as $school) {
            $results[] = [
                'type' => 'School',
                'title' => $school->name,
                'subtitle' => $school->code . ' - ' . $school->city?->name,
                'url' => route('admin.schools.show', $school->id),
                'icon' => 'fa-school',
                'color' => 'blue'
            ];
        }

        // Search Users
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->take(5)
            ->get();
            
        foreach ($users as $user) {
            $results[] = [
                'type' => 'User',
                'title' => $user->name,
                'subtitle' => $user->email . ' (' . $user->role->name . ')',
                'url' => route('admin.users.index', ['search' => $user->email]),
                'icon' => 'fa-user-cog',
                'color' => 'purple'
            ];
        }

        return response()->json(['results' => $results]);
    }
}
