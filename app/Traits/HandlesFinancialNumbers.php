<?php

namespace App\Traits;

use App\Services\School\NumberingService;

trait HandlesFinancialNumbers
{
    /**
     * Generate a sequential bill number via the atomic NumberingService.
     */
    protected function generateBillNumber(int $schoolId): string
    {
        return app(NumberingService::class)->nextBillNo($schoolId);
    }

    /**
     * Generate a sequential receipt number via the atomic NumberingService.
     */
    protected function generateReceiptNumber(int $schoolId): string
    {
        return app(NumberingService::class)->nextReceiptNo($schoolId);
    }
}
