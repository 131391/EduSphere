<?php

namespace App\Observers;

use App\Models\Waiver;
use App\Models\Fee;
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
     * Distribute the waiver amount across the student's fees for the same period.
     */
    protected function applyWaiver(Waiver $waiver): void
    {
        $fees = Fee::where('school_id', $waiver->school_id)
            ->where('student_id', $waiver->student_id)
            ->where('academic_year_id', $waiver->academic_year_id)
            ->where('fee_period', $waiver->fee_period)
            ->where('payment_status', '!=', \App\Enums\FeeStatus::Paid)
            ->orderBy('payable_amount', 'desc')
            ->get();

        if ($fees->isEmpty()) {
            return;
        }

        $remainingWaiver = (string) $waiver->waiver_amount;

        foreach ($fees as $fee) {
            if (bccomp($remainingWaiver, '0', 2) <= 0) {
                break;
            }

            // How much can this fee absorb?
            // A fee can absorb up to its (payable_amount - discount_amount - paid_amount)
            $availableToAbsorb = bcsub($fee->payable_amount, bcadd($fee->discount_amount ?? '0', $fee->paid_amount ?? '0', 2), 2);
            
            // Subtract any existing waiver on this fee to find actual capacity
            $availableToAbsorb = bcsub($availableToAbsorb, $fee->waiver_amount ?? '0', 2);

            if (bccomp($availableToAbsorb, '0', 2) <= 0) {
                continue;
            }

            // The amount to apply to this fee
            $applyAmount = bccomp($remainingWaiver, $availableToAbsorb, 2) === 1 ? $availableToAbsorb : $remainingWaiver;

            // Update the fee
            $newWaiverAmount = bcadd($fee->waiver_amount ?? '0', $applyAmount, 2);
            $fee->waiver_amount = $newWaiverAmount;
            
            // Recalculate due_amount. Note: payable_amount already includes late_fee (added by ApplyLateFees)
            $discounts = bcadd($fee->discount_amount ?? '0', $fee->waiver_amount ?? '0', 2);
            $totalDeductions = bcadd($discounts, $fee->paid_amount ?? '0', 2);
            $newDueAmount = bcsub($fee->payable_amount, $totalDeductions, 2);

            $fee->due_amount = $newDueAmount;
            $fee->save();

            $remainingWaiver = bcsub($remainingWaiver, $applyAmount, 2);
        }
    }

    /**
     * Remove the waiver amount from the student's fees for the same period.
     */
    protected function removeWaiver(Waiver $waiver): void
    {
        $fees = Fee::where('school_id', $waiver->school_id)
            ->where('student_id', $waiver->student_id)
            ->where('academic_year_id', $waiver->academic_year_id)
            ->where('fee_period', $waiver->fee_period)
            ->whereNotNull('waiver_amount')
            ->where('waiver_amount', '>', 0)
            ->get();

        foreach ($fees as $fee) {
            $fee->waiver_amount = '0.00';
            
            // Recalculate due_amount. Note: payable_amount already includes late_fee
            $discounts = $fee->discount_amount ?? '0'; // waiver is now 0
            $totalDeductions = bcadd($discounts, $fee->paid_amount ?? '0', 2);
            $newDueAmount = bcsub($fee->payable_amount, $totalDeductions, 2);

            $fee->due_amount = $newDueAmount;
            $fee->save();
        }
    }
}
