<?php

namespace App\Traits;

use App\Models\Fee;
use App\Models\FeePayment;
use Illuminate\Support\Facades\Cache;

trait HandlesFinancialNumbers
{
    /**
     * Generate a sequential bill number with an atomic lock per school.
     */
    protected function generateBillNumber(int $schoolId): string
    {
        $lockKey = "bill_seq_{$schoolId}";
        return Cache::lock($lockKey, 10)->block(5, function () use ($schoolId) {
            $prefix = 'BILL';
            $year   = date('Y');
            
            $last = Fee::withTrashed()
                ->where('school_id', $schoolId)
                ->where('bill_no', 'like', "{$prefix}-{$schoolId}-{$year}-%")
                ->max('bill_no');

            $count = 1;
            if ($last) {
                $lastCount = (int) substr($last, strrpos($last, '-') + 1);
                $count = $lastCount + 1;
            }

            return "{$prefix}-{$schoolId}-{$year}-" . str_pad($count, 6, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Generate a sequential receipt number with an atomic lock per school.
     */
    protected function generateReceiptNumber(int $schoolId): string
    {
        $lockKey = "receipt_seq_{$schoolId}";
        return Cache::lock($lockKey, 10)->block(5, function () use ($schoolId) {
            $prefix = 'RCPT';
            $year   = date('Y');
            
            $last = FeePayment::withTrashed()
                ->where('school_id', $schoolId)
                ->where('receipt_no', 'like', "{$prefix}-{$schoolId}-{$year}-%")
                ->max('receipt_no');

            $count = 1;
            if ($last) {
                $lastCount = (int) substr($last, strrpos($last, '-') + 1);
                $count = $lastCount + 1;
            }

            return "{$prefix}-{$schoolId}-{$year}-" . str_pad($count, 6, '0', STR_PAD_LEFT);
        });
    }
}
