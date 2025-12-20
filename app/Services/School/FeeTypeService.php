<?php

namespace App\Services\School;

use App\Models\FeeType;
use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeeTypeService
{
    public function getPaginatedFeeTypes(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = FeeType::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createFeeType(School $school, array $data): FeeType
    {
        DB::beginTransaction();

        try {
            $feeType = FeeType::create([
                'school_id' => $school->id,
                'name' => $data['name'],
            ]);

            Log::info('Fee type created', [
                'fee_type_id' => $feeType->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $feeType->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create fee type', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateFeeType(FeeType $feeType, array $data): FeeType
    {
        DB::beginTransaction();

        try {
            $feeType->update($data);

            Log::info('Fee type updated', [
                'fee_type_id' => $feeType->id,
            ]);

            DB::commit();

            return $feeType->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteFeeType(FeeType $feeType): bool
    {
        DB::beginTransaction();

        try {
            $feeType->delete();

            Log::info('Fee type deleted', [
                'fee_type_id' => $feeType->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
