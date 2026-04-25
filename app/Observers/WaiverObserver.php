<?php

namespace App\Observers;

use App\Models\Waiver;
use App\Models\Fee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WaiverObserver
{
    /**
     * Handle the Waiver "created" event.
     */
    public function created(Waiver $waiver): void
    {
        $this->applyWaiver($waiver);
    }

    /**
     * Handle the Waiver "updated" event.
     */
    public function updated(Waiver $waiver): void
    {
        // For simplicity, we remove the old waiver distribution and re-apply the new one
        $this->removeWaiver($waiver);
        $this->applyWaiver($waiver);
    }

    /**
     * Handle the Waiver "deleted" event.
     */
    public function deleted(Waiver $waiver): void
    {
        $this->removeWaiver($waiver);
    }

    /**
     * Handle the Waiver "restored" event.
     */
    public function restored(Waiver $waiver): void
    {
        $this->applyWaiver($waiver);
    }

    /**
     * Handle the Waiver "force deleted" event.
     */
    public function forceDeleted(Waiver $waiver): void
    {
        // No action needed if it was already soft deleted
    }

    /**
     * Distribute the waiver amount across the student's fees for the same
     * period. Runs in a DB transaction with row-level locks so concurrent
     * payment collection cannot interleave and corrupt due_amount.
     */
    protected function applyWaiver(Waiver $waiver): void
    {
        DB::transaction(function () use ($waiver) {
            $fees = Fee::where('school_id', $waiver->school_id)
                ->where('student_id', $waiver->student_id)
                ->where('academic_year_id', $waiver->academic_year_id)
                ->where('fee_period', $waiver->fee_period)
                ->where('payment_status', '!=', \App\Enums\FeeStatus::Paid)
                ->orderBy('payable_amount', 'desc')
                ->lockForUpdate()
                ->get();

            if ($fees->isEmpty()) {
                return;
            }

            $remainingWaiver = (string) $waiver->waiver_amount;

            foreach ($fees as $fee) {
                if (bccomp($remainingWaiver, '0', 2) <= 0) {
                    break;
                }

                // Capacity = payable - (discount + paid + existing waiver).
                // payable_amount already includes late_fee (added by ApplyLateFees).
                $availableToAbsorb = bcsub(
                    $fee->payable_amount,
                    bcadd(
                        bcadd($fee->discount_amount ?? '0', $fee->paid_amount ?? '0', 2),
                        $fee->waiver_amount ?? '0',
                        2
                    ),
                    2
                );

                if (bccomp($availableToAbsorb, '0', 2) <= 0) {
                    continue;
                }

                $applyAmount = bccomp($remainingWaiver, $availableToAbsorb, 2) === 1
                    ? $availableToAbsorb
                    : $remainingWaiver;

                $fee->waiver_amount = bcadd($fee->waiver_amount ?? '0', $applyAmount, 2);

                $deductions = bcadd(
                    bcadd($fee->paid_amount ?? '0', $fee->waiver_amount, 2),
                    $fee->discount_amount ?? '0',
                    2
                );
                $fee->due_amount = bcsub($fee->payable_amount, $deductions, 2);

                $fee->save();

                $remainingWaiver = bcsub($remainingWaiver, $applyAmount, 2);
            }
        });
    }

    /**
     * Remove the waiver amount from the student's fees for the same period.
     */
    protected function removeWaiver(Waiver $waiver): void
    {
        DB::transaction(function () use ($waiver) {
            $fees = Fee::where('school_id', $waiver->school_id)
                ->where('student_id', $waiver->student_id)
                ->where('academic_year_id', $waiver->academic_year_id)
                ->where('fee_period', $waiver->fee_period)
                ->whereNotNull('waiver_amount')
                ->where('waiver_amount', '>', 0)
                ->lockForUpdate()
                ->get();

            foreach ($fees as $fee) {
                $fee->waiver_amount = '0.00';

                $deductions = bcadd($fee->paid_amount ?? '0', $fee->discount_amount ?? '0', 2);
                $fee->due_amount = bcsub($fee->payable_amount, $deductions, 2);

                $fee->save();
            }
        });
    }
}
