<?php

namespace App\Http\Controllers\Parent;

use App\Enums\ExamStatus;
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
        $stats = [
            'total_exams' => 0,
            'avg_pct'     => 0,
            'best_subject' => 'N/A'
        ];

        if ($selectedChildId && $children->contains('id', $selectedChildId)) {
            $allResults = Result::where('student_id', $selectedChildId)
                ->whereHas('exam', fn ($query) => $query->where('status', ExamStatus::Completed))
                ->with(['exam', 'subject'])
                ->orderByDesc('created_at')
                ->get();

            if ($allResults->isNotEmpty()) {
                $stats['total_exams'] = $allResults->groupBy('exam_id')->count();
                $stats['avg_pct']     = round($allResults->avg('percentage'), 1);
                $bestResult = $allResults->sortByDesc('percentage')->first();
                $stats['best_subject'] = optional($bestResult->subject)->name ?? 'N/A';
            }

            $results = $allResults->groupBy('exam_id');
        }

        return view('parent.results.index', compact('children', 'results', 'selectedChildId', 'parentProfile', 'stats'));
    }
}
