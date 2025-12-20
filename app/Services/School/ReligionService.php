<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\Religion;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReligionService
{
    public function getPaginatedReligions(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = Religion::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createReligion(School $school, array $data): Religion
    {
        DB::beginTransaction();

        try {
            $religion = Religion::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'is_active' => true,
            ]);

            Log::info('Religion created', [
                'religion_id' => $religion->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $religion->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create religion', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateReligion(Religion $religion, array $data): Religion
    {
        DB::beginTransaction();

        try {
            $religion->update($data);

            Log::info('Religion updated', [
                'religion_id' => $religion->id,
            ]);

            DB::commit();

            return $religion->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteReligion(Religion $religion): bool
    {
        DB::beginTransaction();

        try {
            $religion->delete();

            Log::info('Religion deleted', [
                'religion_id' => $religion->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
