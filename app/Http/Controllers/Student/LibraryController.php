<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BookIssue;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    public function index()
    {
        $this->authorize('student:operate');

        $student = Auth::user()->student;

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Student profile not found. Please contact the administrator.');
        }

        $issues = BookIssue::where('school_id', $student->school_id)
            ->where('student_id', $student->id)
            ->with([
                'book' => fn($q) => $q->withTrashed()->select('id', 'title', 'author', 'isbn'),
            ])
            ->orderByDesc('issue_date')
            ->get();

        $active = $issues->where('status', 'issued');
        $summary = [
            'active_count'      => $active->count(),
            'overdue_count'     => $active->filter(fn($i) => $i->isOverdue())->count(),
            'fines_outstanding' => (float) $issues
                ->where('fine_amount', '>', 0)
                ->whereNull('fine_paid_at')
                ->sum('fine_amount'),
            'total_history'     => $issues->whereIn('status', ['returned', 'lost'])->count(),
        ];

        return view('student.library.index', [
            'student' => $student,
            'issues'  => $issues,
            'summary' => $summary,
        ]);
    }
}
