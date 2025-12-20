<?php

namespace App\Services\School;

use App\Models\FeeName;
use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeeNameService
{
    public function getPaginatedFeeNames(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = FeeName::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createFeeName(School $school, array $data): FeeName
    {
        DB::beginTransaction();

        try {
            $feeName = FeeName::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ]);

            Log::info('Fee name created', [
                'fee_name_id' => $feeName->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $feeName->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create fee name', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateFeeName(FeeName $feeName, array $data): FeeName
    {
        DB::beginTransaction();

        try {
            $feeName->update($data);

            Log::info('Fee name updated', [
                'fee_name_id' => $feeName->id,
            ]);

            DB::commit();

            return $feeName->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteFeeName(FeeName $feeName): bool
    {
        DB::beginTransaction();

        try {
            $feeName->delete();

            Log::info('Fee name deleted', [
                'fee_name_id' => $feeName->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
