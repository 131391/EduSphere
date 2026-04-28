<?php

namespace App\Http\Controllers\School\Examination;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\Examination\StoreGradeRequest;
use App\Http\Requests\School\Examination\UpdateGradeRequest;
use App\Models\Grade;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GradeController extends TenantController
{
    use HasAjaxDataTable {
        handleAjaxTable as traitHandleAjaxTable;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Grade::class);
        $this->ensureSchoolActive();
        $schoolId = $this->getSchoolId();

        $transformer = function ($row) {
            return [
                'id' => $row->id,
                'grade' => $row->grade,
                'range_start' => $row->range_start,
                'range_end' => $row->range_end,
                'range_display' => "{$row->range_start}% - {$row->range_end}%",
            ];
        };

        $query = Grade::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('grade', 'like', '%' . $request->search . '%');
        }

        $stats = $this->getTableStats();
        $coverage = $this->coverageReport();

        if ($request->expectsJson() || $request->ajax()) {
            return $this->traitHandleAjaxTable($query, $transformer, array_merge($stats, ['coverage' => $coverage]));
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.examination.grades.index', array_merge($initialData, [
            'initialData' => $initialData,
            'stats' => $stats,
            'coverage' => $coverage,
        ]));
    }

    protected function getTableStats()
    {
        return [
            'total_grades' => Grade::where('school_id', $this->getSchoolId())->count(),
        ];
    }

    /**
     * Returns information about whether the configured bands cover 0–100%.
     *
     * Shape: ['is_complete' => bool, 'gaps' => array<int, array{from:int,to:int}>].
     * Used by the UI to surface a banner and by callers/tests to assert coverage.
     *
     * Accepts an optional `$schoolId` so it can be invoked outside the request
     * lifecycle (e.g. tests, console). When omitted it falls back to the
     * tenant context if bound.
     */
    public function coverageReport(?int $schoolId = null): array
    {
        if ($schoolId === null) {
            $schoolId = app()->bound('currentSchool')
                ? optional(app('currentSchool'))->id
                : null;
        }

        if ($schoolId === null) {
            return [
                'is_complete' => false,
                'gaps' => [['from' => 0, 'to' => 100]],
            ];
        }

        $bands = Grade::where('school_id', $schoolId)
            ->orderBy('range_start')
            ->get(['range_start', 'range_end']);

        if ($bands->isEmpty()) {
            return [
                'is_complete' => false,
                'gaps' => [['from' => 0, 'to' => 100]],
            ];
        }

        $gaps = [];
        $cursor = 0;

        foreach ($bands as $band) {
            $start = (int) $band->range_start;
            $end = (int) $band->range_end;

            if ($start > $cursor) {
                $gaps[] = ['from' => $cursor, 'to' => $start - 1];
            }

            $cursor = max($cursor, $end + 1);
        }

        if ($cursor <= 100) {
            $gaps[] = ['from' => $cursor, 'to' => 100];
        }

        return [
            'is_complete' => $gaps === [],
            'gaps' => $gaps,
        ];
    }

    public function store(StoreGradeRequest $request)
    {
        $this->authorize('create', Grade::class);
        $this->ensureSchoolActive();

        $validated = $request->validated();

        $this->guardAgainstOverlappingGradeRange(
            (int) $validated['range_start'],
            (int) $validated['range_end']
        );

        try {
            $grade = Grade::create([
                'school_id' => $this->getSchoolId(),
                'range_start' => $validated['range_start'],
                'range_end' => $validated['range_end'],
                'grade' => $validated['grade'],
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grade created successfully!',
                    'data' => $grade,
                ]);
            }

            return redirect()->route('school.examination.grades.index')->with('success', 'Grade created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create grade: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to create grade: ' . $e->getMessage());
        }
    }

    public function update(UpdateGradeRequest $request, Grade $grade)
    {
        $this->authorize('update', $grade);
        $this->ensureSchoolActive();
        $this->authorizeTenant($grade);

        $validated = $request->validated();

        $this->guardAgainstOverlappingGradeRange(
            (int) $validated['range_start'],
            (int) $validated['range_end'],
            $grade->id
        );

        try {
            $grade->update([
                'range_start' => $validated['range_start'],
                'range_end' => $validated['range_end'],
                'grade' => $validated['grade'],
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grade updated successfully!',
                    'data' => $grade,
                ]);
            }

            return redirect()->route('school.examination.grades.index')->with('success', 'Grade updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update grade: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to update grade: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, Grade $grade)
    {
        $this->authorize('delete', $grade);
        $this->ensureSchoolActive();
        $this->authorizeTenant($grade);

        try {
            $grade->delete();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grade deleted successfully!',
                ]);
            }

            return redirect()->route('school.examination.grades.index')->with('success', 'Grade deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete grade: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('school.examination.grades.index')->with('error', 'Failed to delete grade: ' . $e->getMessage());
        }
    }

    protected function guardAgainstOverlappingGradeRange(int $rangeStart, int $rangeEnd, ?int $ignoreId = null): void
    {
        $query = Grade::where('school_id', $this->getSchoolId())
            ->where(function ($gradeQuery) use ($rangeStart, $rangeEnd) {
                $gradeQuery
                    ->whereBetween('range_start', [$rangeStart, $rangeEnd])
                    ->orWhereBetween('range_end', [$rangeStart, $rangeEnd])
                    ->orWhere(function ($nestedQuery) use ($rangeStart, $rangeEnd) {
                        $nestedQuery->where('range_start', '<=', $rangeStart)
                            ->where('range_end', '>=', $rangeEnd);
                    });
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'range_start' => ['This grade range overlaps with an existing grade band.'],
            ]);
        }
    }
}
