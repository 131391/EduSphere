<?php

namespace App\Services\School;

use App\Models\ClassModel;
use App\Models\School;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClassService
{
    /**
     * Get all classes for a school
     */
    public function getAllClasses(School $school, array $filters = []): Collection
    {
        $query = ClassModel::where('school_id', $school->id)
            ->with(['sections', 'students'])
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc');

        // Apply filters
        if (isset($filters['is_available'])) {
            $query->where('is_available', $filters['is_available']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->get();
    }

    /**
     * Get paginated classes
     */
    public function getPaginatedClasses(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = ClassModel::where('school_id', $school->id)
            ->withCount(['sections', 'students']);

        // Apply search
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        // Apply availability filter
        if (isset($filters['is_available'])) {
            $query->where('is_available', $filters['is_available']);
        }

        // Apply sorting
        $sortColumn = $filters['sort'] ?? 'order';
        $sortDirection = $filters['direction'] ?? 'asc';

        $allowedSortColumns = ['id', 'name', 'order', 'created_at'];
        if (in_array($sortColumn, $allowedSortColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('order', 'asc')->orderBy('name', 'asc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new class
     */
    public function createClass(School $school, array $data): ClassModel
    {
        DB::beginTransaction();

        try {
            // Auto-assign order if not provided
            if (!isset($data['order'])) {
                $maxOrder = ClassModel::where('school_id', $school->id)->max('order') ?? 0;
                $data['order'] = $maxOrder + 1;
            }

            // Ensure is_available is set
            $data['is_available'] = $data['is_available'] ?? true;

            // Create class
            $class = ClassModel::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'order' => $data['order'],
                'is_available' => $data['is_available'],
            ]);

            Log::info('Class created', [
                'class_id' => $class->id,
                'school_id' => $school->id,
                'name' => $class->name,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $class->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create class', [
                'school_id' => $school->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Update a class
     */
    public function updateClass(ClassModel $class, array $data): ClassModel
    {
        DB::beginTransaction();

        try {
            $class->update([
                'name' => $data['name'] ?? $class->name,
                'order' => $data['order'] ?? $class->order,
                'is_available' => $data['is_available'] ?? $class->is_available,
            ]);

            Log::info('Class updated', [
                'class_id' => $class->id,
                'school_id' => $class->school_id,
                'changes' => $class->getChanges(),
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $class->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update class', [
                'class_id' => $class->id,
                'data' => $data,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete a class
     */
    public function deleteClass(ClassModel $class): bool
    {
        DB::beginTransaction();

        try {
            // Check if class has students
            if ($class->students()->count() > 0) {
                throw new \Exception('Cannot delete class with enrolled students');
            }

            // Check if class has sections
            if ($class->sections()->count() > 0) {
                throw new \Exception('Cannot delete class with sections. Delete sections first.');
            }

            $classId = $class->id;
            $schoolId = $class->school_id;

            $class->delete();

            Log::info('Class deleted', [
                'class_id' => $classId,
                'school_id' => $schoolId,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete class', [
                'class_id' => $class->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }

    /**
     * Toggle class availability
     */
    public function toggleAvailability(ClassModel $class): ClassModel
    {
        $class->update([
            'is_available' => !$class->is_available,
        ]);

        Log::info('Class availability toggled', [
            'class_id' => $class->id,
            'is_available' => $class->is_available,
            'user_id' => auth()->id(),
        ]);

        return $class->fresh();
    }

    /**
     * Get class statistics
     */
    public function getClassStatistics(School $school): array
    {
        return [
            'total_classes' => ClassModel::where('school_id', $school->id)->count(),
            'available_classes' => ClassModel::where('school_id', $school->id)
                ->where('is_available', true)
                ->count(),
            'unavailable_classes' => ClassModel::where('school_id', $school->id)
                ->where('is_available', false)
                ->count(),
            'total_students' => DB::table('students')
                ->join('classes', 'students.class_id', '=', 'classes.id')
                ->where('classes.school_id', $school->id)
                ->count(),
        ];
    }

    /**
     * Reorder classes
     */
    public function reorderClasses(School $school, array $orderedIds): bool
    {
        DB::beginTransaction();

        try {
            foreach ($orderedIds as $order => $classId) {
                ClassModel::where('id', $classId)
                    ->where('school_id', $school->id)
                    ->update(['order' => $order + 1]);
            }

            Log::info('Classes reordered', [
                'school_id' => $school->id,
                'order' => $orderedIds,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to reorder classes', [
                'school_id' => $school->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }
    }
}
