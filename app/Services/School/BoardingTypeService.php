<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\BoardingType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BoardingTypeService
{
    public function getPaginatedTypes(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = BoardingType::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createType(School $school, array $data): BoardingType
    {
        DB::beginTransaction();

        try {
            $type = BoardingType::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'is_active' => true,
            ]);

            Log::info('Boarding type created', [
                'type_id' => $type->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $type->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create boarding type', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateType(BoardingType $type, array $data): BoardingType
    {
        DB::beginTransaction();

        try {
            $type->update($data);

            Log::info('Boarding type updated', [
                'type_id' => $type->id,
            ]);

            DB::commit();

            return $type->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteType(BoardingType $type): bool
    {
        DB::beginTransaction();

        try {
            $type->delete();

            Log::info('Boarding type deleted', [
                'type_id' => $type->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
