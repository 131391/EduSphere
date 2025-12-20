<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\BloodGroup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BloodGroupService
{
    public function getPaginatedGroups(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = BloodGroup::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createGroup(School $school, array $data): BloodGroup
    {
        DB::beginTransaction();

        try {
            $group = BloodGroup::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'is_active' => true,
            ]);

            Log::info('Blood group created', [
                'group_id' => $group->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $group->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create blood group', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateGroup(BloodGroup $group, array $data): BloodGroup
    {
        DB::beginTransaction();

        try {
            $group->update($data);

            Log::info('Blood group updated', [
                'group_id' => $group->id,
            ]);

            DB::commit();

            return $group->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteGroup(BloodGroup $group): bool
    {
        DB::beginTransaction();

        try {
            $group->delete();

            Log::info('Blood group deleted', [
                'group_id' => $group->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
