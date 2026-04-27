<?php

namespace App\Http\Controllers\School\Examination;

use App\Http\Controllers\TenantController;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use App\Traits\HasAjaxDataTable;

class GradeController extends TenantController
{
    use HasAjaxDataTable {
        handleAjaxTable as traitHandleAjaxTable;
    }

    public function index(Request $request)
    {
        $this->ensureSchoolActive();
        $schoolId = $this->getSchoolId();

        $transformer = function($row) {
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

        if ($request->expectsJson() || $request->ajax()) {
            return $this->traitHandleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.examination.grades.index', array_merge($initialData, [
            'initialData' => $initialData,
            'stats' => $stats,
        ]));
    }

    protected function getTableStats()
    {
        return [
            'total_grades' => Grade::where('school_id', $this->getSchoolId())->count(),
        ];
    }

    public function store(Request $request)
    {
        $this->ensureSchoolActive();

        $request->validate([
            'range_start' => 'required|integer|min:0|max:100',
            'range_end' => 'required|integer|min:0|max:100|gte:range_start',
            'grade' => 'required|string|max:10',
        ]);

        $this->guardAgainstOverlappingGradeRange(
            (int) $request->range_start,
            (int) $request->range_end
        );

        try {
            $grade = Grade::create([
                'school_id' => $this->getSchoolId(),
                'range_start' => $request->range_start,
                'range_end' => $request->range_end,
                'grade' => $request->grade,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grade created successfully!',
                    'data' => $grade
                ]);
            }

            return redirect()->route('school.examination.grades.index')->with('success', 'Grade created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create grade: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to create grade: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Grade $grade)
    {
        $this->ensureSchoolActive();
        $this->authorizeTenant($grade);

        $request->validate([
            'range_start' => 'required|integer|min:0|max:100',
            'range_end' => 'required|integer|min:0|max:100|gte:range_start',
            'grade' => 'required|string|max:10',
        ]);

        $this->guardAgainstOverlappingGradeRange(
            (int) $request->range_start,
            (int) $request->range_end,
            $grade->id
        );

        try {
            $grade->update([
                'range_start' => $request->range_start,
                'range_end' => $request->range_end,
                'grade' => $request->grade,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grade updated successfully!',
                    'data' => $grade
                ]);
            }

            return redirect()->route('school.examination.grades.index')->with('success', 'Grade updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update grade: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Failed to update grade: ' . $e->getMessage());
        }
    }

    public function destroy(Grade $grade)
    {
        $this->ensureSchoolActive();
        $this->authorizeTenant($grade);
        
        try {
            $grade->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Grade deleted successfully!'
                ]);
            }

            return redirect()->route('school.examination.grades.index')->with('success', 'Grade deleted successfully.');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete grade: ' . $e->getMessage()
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
