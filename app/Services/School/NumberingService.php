<?php

namespace App\Services\School;

use Illuminate\Support\Facades\DB;

class NumberingService
{
    /**
     * Get the next receipt number for a school.
     * MUST be called inside an existing DB transaction.
     */
    public function nextReceiptNo(int $schoolId): string
    {
        return $this->nextNumber($schoolId, 'receipt', 'RCPT');
    }

    /**
     * Get the next bill number for a school.
     * MUST be called inside an existing DB transaction.
     */
    public function nextBillNo(int $schoolId): string
    {
        return $this->nextNumber($schoolId, 'bill', 'INV');
    }

    /**
     * Atomically increment and return the next sequence number.
     *
     * Uses INSERT … ON DUPLICATE KEY UPDATE (via upsert) to guarantee
     * atomicity without requiring a separate Cache::lock. The row-level
     * lock held by the surrounding transaction prevents concurrent
     * readers from seeing the same value.
     */
    private function nextNumber(int $schoolId, string $kind, string $prefix): string
    {
        $year = (int) date('Y');

        // Atomically upsert: insert if missing, increment if exists
        DB::table('numbering_sequences')->upsert(
            [
                'school_id'  => $schoolId,
                'kind'       => $kind,
                'year'       => $year,
                'next_value' => 1,
            ],
            ['school_id', 'kind', 'year'],   // conflict columns (PK)
            ['next_value' => DB::raw('next_value + 1')]  // on duplicate
        );

        // Read back the current value under the transaction's lock
        $current = DB::table('numbering_sequences')
            ->where('school_id', $schoolId)
            ->where('kind', $kind)
            ->where('year', $year)
            ->lockForUpdate()
            ->value('next_value');

        return sprintf('%s-%d-%d-%06d', $prefix, $schoolId, $year, $current);
    }
}
