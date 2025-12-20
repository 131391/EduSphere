<?php

namespace App\Services\School;

use App\Models\Section;
use App\Models\School;
use App\Models\ClassModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SectionService
{
    public function getPaginatedSections(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = Section::where('school_id', $school->id)
            ->with(['class'])
            ->withCount(['students']);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhereHas('class', function($classQuery) use ($filters) {
                      $classQuery->where('name', 'like', '%' . $filters['search'] . '%');
                  });
            });
        }

        if (isset($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $allowedSortColumns = ['id', 'name', 'class_id', 'capacity', 'created_at'];
        if (in_array($sortColumn, $allowedSortColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        return $query->paginate($perPage);
    }

    public function createSection(School $school, array $data): Section
    {
        DB::beginTransaction();

        try {
            $section = Section::create([
                'school_id' => $school->id,
                'class_id' => $data['class_id'],
                'name' => $data['name'],
                'capacity' => $data['capacity'],
                'current_strength' => 0,
            ]);

            Log::info('Section created', [
                'section_id' => $section->id,
                'school_id' => $school->id,
                'class_id' => $section->class_id,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return $section->fresh(['class']);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create section', [
                'school_id' => $school->id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function updateSection(Section $section, array $data): Section
    {
        DB::beginTransaction();

        try {
            $section->update([
                'class_id' => $data['class_id'] ?? $section->class_id,
                'name' => $data['name'] ?? $section->name,
                'capacity' => $data['capacity'] ?? $section->capacity,
            ]);

            Log::info('Section updated', [
                'section_id' => $section->id,
                'changes' => $section->getChanges(),
            ]);

            DB::commit();

            return $section->fresh(['class']);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update section', [
                'section_id' => $section->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function deleteSection(Section $section): bool
    {
        DB::beginTransaction();

        try {
            if ($section->students()->count() > 0) {
                throw new \Exception('Cannot delete section with enrolled students');
            }

            $section->delete();

            Log::info('Section deleted', [
                'section_id' => $section->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getSectionStatistics(School $school): array
    {
        return [
            'total_sections' => Section::where('school_id', $school->id)->count(),
            'total_capacity' => Section::where('school_id', $school->id)->sum('capacity'),
            'total_students' => Section::where('school_id', $school->id)->sum('current_strength'),
        ];
    }
}
