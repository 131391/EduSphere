<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreCategoryRequest;
use App\Http\Requests\School\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\School\CategoryService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class CategoryController extends TenantController
{
    use HasAjaxDataTable;

    protected CategoryService $service;

    public function __construct(CategoryService $service)
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
                'created_at' => $item->created_at?->format('M d, Y'),
            ];
        };

        $query = Category::where('school_id', $schoolId);

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
            'total' => Category::where('school_id', $schoolId)->count(),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, $stats);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => $stats,
        ]);

        return view('school.categories.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $this->service->createCategory(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category created successfully!',
                    'data' => $category
                ]);
            }

            return $this->redirectWithSuccess(
                'school.categories.index',
                'Category created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create category: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create category: ' . $e->getMessage());
        }
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $category = Category::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $category = $this->service->updateCategory($category, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category updated successfully!',
                    'data' => $category
                ]);
            }

            return $this->redirectWithSuccess(
                'school.categories.index',
                'Category updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update category: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update category: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteCategory($category);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.categories.index',
                'Category deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete category: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.categories.index',
                'Failed to delete category: ' . $e->getMessage()
            );
        }
    }
}
