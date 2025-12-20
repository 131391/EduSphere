<?php

namespace App\Services\School;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcademicYearService
{
    public function getPaginatedAcademicYears(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = AcademicYear::where('school_id', $school->id)
            ->withCount(['students', 'fees']);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'start_date';
        $sortDirection = $filters['direction'] ?? 'desc';

        $allowedSortColumns = ['id', 'name', 'start_date', 'end_date', 'created_at'];
        if (in_array($sortColumn, $allowedSortColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        return $query->paginate($perPage);
    }

    public function createAcademicYear(School $school, array $data): AcademicYear
    {
        DB::beginTransaction();

        try {
            // If setting as current, unset other current years
            if (isset($data['is_current']) && $data['is_current']) {
                AcademicYear::where('school_id', $school->id)
                    ->update(['is_current' => false]);
            }

            $academicYear = AcademicYear::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_current' => $data['is_current'] ?? false,
            ]);

            Log::info('Academic year created', [
                'academic_year_id' => $academicYear->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $academicYear->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create academic year', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function updateAcademicYear(AcademicYear $academicYear, array $data): AcademicYear
    {
        DB::beginTransaction();

        try {
            // If setting as current, unset other current years
            if (isset($data['is_current']) && $data['is_current']) {
                AcademicYear::where('school_id', $academicYear->school_id)
                    ->where('id', '!=', $academicYear->id)
                    ->update(['is_current' => false]);
            }

            $academicYear->update($data);

            Log::info('Academic year updated', [
                'academic_year_id' => $academicYear->id,
            ]);

            DB::commit();

            return $academicYear->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteAcademicYear(AcademicYear $academicYear): bool
    {
        DB::beginTransaction();

        try {
            if ($academicYear->students()->count() > 0) {
                throw new \Exception('Cannot delete academic year with enrolled students');
            }

            $academicYear->delete();

            Log::info('Academic year deleted', [
                'academic_year_id' => $academicYear->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getStatistics(School $school): array
    {
        return [
            'total_years' => AcademicYear::where('school_id', $school->id)->count(),
            'current_year' => AcademicYear::where('school_id', $school->id)
                ->where('is_current', true)
                ->first(),
        ];
    }
}
