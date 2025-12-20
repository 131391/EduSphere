<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\AdmissionCode;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdmissionCodeService
{
    public function getPaginatedCodes(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = AdmissionCode::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('code', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createCode(School $school, array $data): AdmissionCode
    {
        DB::beginTransaction();

        try {
            $code = AdmissionCode::create([
                'school_id' => $school->id,
                'code' => $data['code'],
                'is_active' => true,
            ]);

            Log::info('Admission code created', [
                'code_id' => $code->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $code->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create admission code', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateCode(AdmissionCode $code, array $data): AdmissionCode
    {
        DB::beginTransaction();

        try {
            $code->update($data);

            Log::info('Admission code updated', [
                'code_id' => $code->id,
            ]);

            DB::commit();

            return $code->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteCode(AdmissionCode $code): bool
    {
        DB::beginTransaction();

        try {
            $code->delete();

            Log::info('Admission code deleted', [
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
