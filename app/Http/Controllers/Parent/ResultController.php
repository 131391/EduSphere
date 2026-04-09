<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Result;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $parentProfile = Auth::user()->parent;

        if (!$parentProfile) {
            return redirect()->route('parent.dashboard')
                ->with('error', 'Parent profile not found. Please contact the administrator.');
        }

        $children = $parentProfile->students()->with(['class', 'section'])->get();

        $selectedChildId = $request->filled('student_id') ? $request->student_id : optional($children->first())->id;

        $results = collect();
        if ($selectedChildId && $children->contains('id', $selectedChildId)) {
            $results = Result::where('student_id', $selectedChildId)
                ->with(['exam', 'subject'])
                ->orderByDesc('created_at')
                ->get()
                ->groupBy(fn($r) => optional($r->exam)->name ?? 'Unknown Exam');
        }

        return view('parent.results.index', compact('children', 'results', 'selectedChildId', 'parentProfile'));
    }
}
