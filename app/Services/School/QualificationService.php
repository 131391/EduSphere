<?php

namespace App\Services\School;

use App\Models\School;
use App\Models\Qualification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QualificationService
{
    public function getPaginatedQualifications(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = Qualification::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createQualification(School $school, array $data): Qualification
    {
        DB::beginTransaction();

        try {
            $qualification = Qualification::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'is_active' => true,
            ]);

            Log::info('Qualification created', [
                'qualification_id' => $qualification->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $qualification->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create qualification', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateQualification(Qualification $qualification, array $data): Qualification
    {
        DB::beginTransaction();

        try {
            $qualification->update($data);

            Log::info('Qualification updated', [
                'qualification_id' => $qualification->id,
            ]);

            DB::commit();

            return $qualification->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteQualification(Qualification $qualification): bool
    {
        DB::beginTransaction();

        try {
            $qualification->delete();

            Log::info('Qualification deleted', [
                'qualification_id' => $qualification->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
