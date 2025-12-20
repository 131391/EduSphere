<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\CorrespondingRelative;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CorrespondingRelativeService
{
    public function getPaginatedRelatives(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = CorrespondingRelative::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createRelative(School $school, array $data): CorrespondingRelative
    {
        DB::beginTransaction();

        try {
            $relative = CorrespondingRelative::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'is_active' => true,
            ]);

            Log::info('Corresponding relative created', [
                'relative_id' => $relative->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $relative->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create corresponding relative', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateRelative(CorrespondingRelative $relative, array $data): CorrespondingRelative
    {
        DB::beginTransaction();

        try {
            $relative->update($data);

            Log::info('Corresponding relative updated', [
                'relative_id' => $relative->id,
            ]);

            DB::commit();

            return $relative->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteRelative(CorrespondingRelative $relative): bool
    {
        DB::beginTransaction();

        try {
            $relative->delete();

            Log::info('Corresponding relative deleted', [
                'relative_id' => $relative->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
