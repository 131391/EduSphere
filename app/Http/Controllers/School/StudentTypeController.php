<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreStudentTypeRequest;
use App\Http\Requests\School\UpdateStudentTypeRequest;
use App\Models\StudentType;
use App\Services\School\StudentTypeService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class StudentTypeController extends TenantController
{
    use HasAjaxDataTable;

    protected StudentTypeService $service;

    public function __construct(StudentTypeService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'is_default' => (bool)$item->is_default,
                'created_at' => $item->created_at?->format('M d, Y'),
            ];
        };

        $query = StudentType::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (\in_array($sort, ['id', 'name', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        $stats = [
            'total' => StudentType::where('school_id', $schoolId)->count(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.student-types.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreStudentTypeRequest $request)
    {
        try {
            $type = $this->service->createType(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student type created successfully!',
                    'data' => $type
                ]);
            }

            return $this->redirectWithSuccess(
                'school.student-types.index',
                'Student type created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create student type: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create student type: ' . $e->getMessage());
        }
    }

    public function update(UpdateStudentTypeRequest $request, $id)
    {
        try {
            $type = StudentType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateType($type, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student type updated successfully!',
                    'data' => $type
                ]);
            }

            return $this->redirectWithSuccess(
                'school.student-types.index',
                'Student type updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update student type: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update student type: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $type = StudentType::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteType($type);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Student type deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.student-types.index',
                'Student type deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete student type: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.student-types.index',
                'Failed to delete student type: ' . $e->getMessage()
            );
        }
    }
}
