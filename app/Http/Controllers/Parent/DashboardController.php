<?php

namespace App\Http\Controllers\Parent;

use App\Enums\ExamStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $parentProfile = Auth::user()->parent;

        if (!$parentProfile) {
            return redirect()->route('login')->with('error', 'Parent profile not found.');
        }

        $children = $parentProfile->students()
            ->with([
                'class',
                'section',
                'attendance',
                'fees.feeName',
                'results' => fn ($query) => $query
                    ->whereHas('exam', fn ($examQuery) => $examQuery->where('status', ExamStatus::Completed))
                    ->with(['exam', 'subject']),
            ])
            ->get();

        $stats = [
            'total_children' => $children->count(),
            'total_due' => 0,
            'avg_attendance' => 0,
            'recent_results' => collect(),
            'upcoming_fees' => collect(),
        ];

        if ($stats['total_children'] > 0) {
            // Financials
            $stats['total_due'] = $children->sum(function($c) {
                return $c->fees->sum('due_amount');
            });

            $stats['upcoming_fees'] = $children->flatMap(function($c) {
                return $c->fees->where('due_amount', '>', 0)->map(function($f) use ($c) {
                    $f->student_name = $c->first_name;
                    return $f;
                });
            })->sortBy('due_date')->take(5);

            // Attendance
            $totalDays = $children->sum(function($c) { return $c->attendance->count(); });
            $presentDays = $children->sum(function($c) {
                return $c->attendance->filter(fn($a) => $a->status?->value === 1)->count();
            });
            $stats['avg_attendance'] = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0;

            // Results
            $stats['recent_results'] = $children->flatMap(function($c) {
                return $c->results->map(function($r) use ($c) {
                    $r->student_name = $c->first_name;
                    return $r;
                });
            })->sortByDesc('created_at')->take(5);
        }

        return view('parent.dashboard', compact('parentProfile', 'children', 'stats'));
    }
}
