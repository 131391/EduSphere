<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StoreCategoryRequest;
use App\Http\Requests\School\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\School\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends TenantController
{
    protected CategoryService $service;

    public function __construct(CategoryService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'sort' => $request->input('sort', 'id'),
                'direction' => $request->input('direction', 'asc'),
            ];

            $categories = $this->service->getPaginatedCategories(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.categories.index', compact('categories'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load categories.');
        }
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
