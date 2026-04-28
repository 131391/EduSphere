<?php

namespace App\Http\Controllers\School\Examination;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\Examination\StoreExamTypeRequest;
use App\Http\Requests\School\Examination\UpdateExamTypeRequest;
use App\Models\Exam;
use App\Models\ExamType;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class ExamTypeController extends TenantController
{
    use HasAjaxDataTable {
        handleAjaxTable as traitHandleAjaxTable;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ExamType::class);
        $this->ensureSchoolActive();
        $schoolId = $this->getSchoolId();

        $transformer = function ($row) {
            return [
                'id' => $row->id,
                'name' => $row->name,
                'updated_at' => $row->updated_at->diffForHumans(),
            ];
        };

        $query = ExamType::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $stats = $this->getTableStats();

        if ($request->expectsJson() || $request->ajax()) {
            return $this->traitHandleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.examination.exam-types.index', [
            'initialData' => $initialData,
            'stats' => $stats,
        ]);
    }

    protected function getTableStats()
    {
        return [
            'total_types' => ExamType::where('school_id', $this->getSchoolId())->count(),
        ];
    }

    public function store(StoreExamTypeRequest $request)
    {
        $this->authorize('create', ExamType::class);
        $this->ensureSchoolActive();

        try {
            $examType = ExamType::create([
                'school_id' => $this->getSchoolId(),
                'name' => $request->validated()['name'],
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam type created successfully!',
                    'data' => $examType,
                ]);
            }

            return redirect()->route('school.examination.exam-types.index')->with('success', 'Exam type created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create exam type: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to create exam type: ' . $e->getMessage());
        }
    }

    public function update(UpdateExamTypeRequest $request, ExamType $examType)
    {
        $this->authorize('update', $examType);
        $this->ensureSchoolActive();
        $this->authorizeTenant($examType);

        try {
            $examType->update([
                'name' => $request->validated()['name'],
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam type updated successfully!',
                    'data' => $examType,
                ]);
            }

            return redirect()->route('school.examination.exam-types.index')->with('success', 'Exam type updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update exam type: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Failed to update exam type: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, ExamType $examType)
    {
        $this->authorize('delete', $examType);
        $this->ensureSchoolActive();
        $this->authorizeTenant($examType);

        if (Exam::withTrashed()->where('exam_type_id', $examType->id)->exists()) {
            $message = 'This exam type is already used by one or more exams and cannot be deleted.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('school.examination.exam-types.index')->with('error', $message);
        }

        try {
            $examType->delete();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Exam type deleted successfully!',
                ]);
            }

            return redirect()->route('school.examination.exam-types.index')->with('success', 'Exam type deleted successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete exam type: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('school.examination.exam-types.index')->with('error', 'Failed to delete exam type: ' . $e->getMessage());
        }
    }
}
