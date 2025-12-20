<?php

namespace App\Services\School;

use App\Models\PaymentMethod;
use App\Models\School;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentMethodService
{
    public function getPaginatedMethods(
        School $school,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = PaymentMethod::where('school_id', $school->id);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        $sortColumn = $filters['sort'] ?? 'id';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage);
    }

    public function createMethod(School $school, array $data): PaymentMethod
    {
        DB::beginTransaction();

        try {
            $method = PaymentMethod::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'is_active' => true,
            ]);

            Log::info('Payment method created', [
                'method_id' => $method->id,
                'school_id' => $school->id,
            ]);

            DB::commit();

            return $method->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create payment method', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateMethod(PaymentMethod $method, array $data): PaymentMethod
    {
        DB::beginTransaction();

        try {
            $method->update($data);

            Log::info('Payment method updated', [
                'method_id' => $method->id,
            ]);

            DB::commit();

            return $method->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteMethod(PaymentMethod $method): bool
    {
        DB::beginTransaction();

        try {
            $method->delete();

            Log::info('Payment method deleted', [
                'method_id' => $method->id,
            ]);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
