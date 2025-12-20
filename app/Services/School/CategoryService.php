<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function getPaginatedCategories(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = Category::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createCategory(School $school, array $data): Category
    {
        DB::beginTransaction();

        try {
            $category = Category::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'is_active' => true,
            ]);

            Log::info('Category created', [
                'category_id' => $category->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $category->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create category', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateCategory(Category $category, array $data): Category
    {
        DB::beginTransaction();

        try {
            $category->update($data);

            Log::info('Category updated', [
                'category_id' => $category->id,
            ]);

            DB::commit();

            return $category->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteCategory(Category $category): bool
    {
        DB::beginTransaction();

        try {
            $category->delete();

            Log::info('Category deleted', [
                'category_id' => $category->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
