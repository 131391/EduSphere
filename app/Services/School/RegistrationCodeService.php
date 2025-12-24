<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\RegistrationCode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationCodeService
{
    public function getPaginatedCodes(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = RegistrationCode::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('code', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createCode(School $school, array $data): RegistrationCode
    {
        DB::beginTransaction();

        try {
            $code = RegistrationCode::create([
                'school_id' => $school->id,
                'code' => $data['code'],
                'is_active' => true,
            ]);

            Log::info('Registration code created', [
                'code_id' => $code->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $code->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create registration code', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateCode(RegistrationCode $code, array $data): RegistrationCode
    {
        DB::beginTransaction();

        try {
            $code->update($data);

            Log::info('Registration code updated', [
                'code_id' => $code->id,
            ]);

            DB::commit();

            return $code->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteCode(RegistrationCode $code): bool
    {
        DB::beginTransaction();

        try {
            $code->delete();

            Log::info('Registration code deleted', [
                'code_id' => $code->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
