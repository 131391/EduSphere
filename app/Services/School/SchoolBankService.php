<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\SchoolBank;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SchoolBankService
{
    public function getPaginatedBanks(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = SchoolBank::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('bank_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('account_number', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('branch_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createBank(School $school, array $data): SchoolBank
    {
        DB::beginTransaction();

        try {
            $bank = SchoolBank::create([
                'school_id' => $school->id,
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'],
                'branch_name' => $data['branch_name'] ?? null,
                'ifsc_code' => $data['ifsc_code'] ?? null,
                'is_active' => true,
            ]);

            Log::info('School bank created', [
                'bank_id' => $bank->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $bank->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create school bank', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateBank(SchoolBank $bank, array $data): SchoolBank
    {
        DB::beginTransaction();

        try {
            $bank->update($data);

            Log::info('School bank updated', [
                'bank_id' => $bank->id,
            ]);

            DB::commit();

            return $bank->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteBank(SchoolBank $bank): bool
    {
        DB::beginTransaction();

        try {
            $bank->delete();

            Log::info('School bank deleted', [
                'bank_id' => $bank->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
