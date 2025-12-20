<?php

namespace App\Services\School;

use App\Models\MiscellaneousFee;
use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MiscellaneousFeeService
{
    public function getPaginatedFees(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = MiscellaneousFee::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createFee(School $school, array $data): MiscellaneousFee
    {
        DB::beginTransaction();

        try {
            $fee = MiscellaneousFee::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ]);

            Log::info('Miscellaneous fee created', [
                'fee_id' => $fee->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $fee->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create miscellaneous fee', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateFee(MiscellaneousFee $fee, array $data): MiscellaneousFee
    {
        DB::beginTransaction();

        try {
            $fee->update($data);

            Log::info('Miscellaneous fee updated', [
                'fee_id' => $fee->id,
            ]);

            DB::commit();

            return $fee->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteFee(MiscellaneousFee $fee): bool
    {
        DB::beginTransaction();

        try {
            $fee->delete();

            Log::info('Miscellaneous fee deleted', [
                'fee_id' => $fee->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
