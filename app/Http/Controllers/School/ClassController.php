<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreClassRequest;
use App\Http\Requests\School\UpdateClassRequest;
use App\Models\ClassModel;
use App\Services\School\ClassService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class ClassController extends TenantController
{
    use HasAjaxDataTable;

    protected ClassService $classService;

    public function __construct(ClassService $classService)
    {
        parent::__construct();
        $this->classService = $classService;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($class) {
            return [
                'id' => $class->id,
                'name' => $class->name,
                'order' => $class->order,
                'is_available' => (bool)$class->is_available,
                'created_at' => $class->created_at?->format('M d, Y'),
            ];
        };

        $query = ClassModel::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $sort = $request->input('sort', 'order');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (\in_array($sort, ['id', 'name', 'order', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('order', 'asc');
        }

        $stats = $this->classService->getClassStatistics($this->getSchool());

        if ($request->expectsJson() || $request->ajax() || $request->has('page') || $request->filled('filters')) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.classes.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreClassRequest $request)
    {
        try {
            $class = $this->classService->createClass(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Class "' . $class->name . '" created successfully!',
                    'data' => $class
                ]);
            }

            return $this->redirectWithSuccess(
                'school.classes.index',
                'Class "' . $class->name . '" created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create class: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create class: ' . $e->getMessage());
        }
    }

    public function update(UpdateClassRequest $request, $id)
    {
        try {
            $class = ClassModel::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $class = $this->classService->updateClass($class, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Class "' . $class->name . '" updated successfully!',
                    'data' => $class
                ]);
            }

            return $this->redirectWithSuccess(
                'school.classes.index',
                'Class "' . $class->name . '" updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update class: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update class: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $class = ClassModel::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $className = $class->name;
            $this->classService->deleteClass($class);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Class "' . $className . '" deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.classes.index',
                'Class "' . $className . '" deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete class: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.classes.index',
                'Failed to delete class: ' . $e->getMessage()
            );
        }
    }
}
