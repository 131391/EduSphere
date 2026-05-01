<?php

namespace App\Http\Controllers\Parent;

use App\Enums\ExamStatus;
use App\Http\Controllers\Parent\Concerns\ResolvesParent;
use App\Http\Controllers\TenantController;
use App\Models\Result;
use Illuminate\Http\Request;

class ResultController extends TenantController
{
    use ResolvesParent;

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $this->authorize('parent:operate');
        $this->ensureSchoolActive();
        $parentProfile = $this->currentParentOrFail();

        $children = $parentProfile->students()
            ->where('students.school_id', $this->getSchoolId())
            ->with(['class:id,name', 'section:id,name'])
            ->get();

        $selectedChildId = $request->filled('student_id')
            ? (int) $request->student_id
            : optional($children->first())->id;

        $results = collect();
        $stats = [
            'total_exams'  => 0,
            'avg_pct'      => 0,
            'best_subject' => 'N/A',
        ];

        if ($selectedChildId && $children->contains('id', $selectedChildId)) {
            $allResults = Result::where('student_id', $selectedChildId)
                ->where('school_id', $this->getSchoolId())
                ->whereHas('exam', fn ($q) => $q->where('status', ExamStatus::Completed))
                ->with(['exam:id,name,exam_type_id', 'exam.examType:id,name', 'subject:id,name'])
                ->orderByDesc('id')
                ->get();

            if ($allResults->isNotEmpty()) {
                $stats['total_exams'] = $allResults->groupBy('exam_id')->count();
                $stats['avg_pct']     = round((float) $allResults->avg('percentage'), 1);
                $bestResult           = $allResults->sortByDesc('percentage')->first();
                $stats['best_subject'] = optional($bestResult->subject)->name ?? 'N/A';
            }

            $results = $allResults->groupBy('exam_id');
        }

        return view('parent.results.index', compact(
            'children', 'results', 'selectedChildId', 'parentProfile', 'stats'
        ));
    }
}
