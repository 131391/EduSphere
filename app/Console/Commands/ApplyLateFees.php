<?php

namespace App\Console\Commands;

use App\Enums\FeeStatus;
use App\Models\Fee;
use App\Models\LateFee;
use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApplyLateFees extends Command
{
    protected $signature   = 'fees:apply-late';
    protected $description = 'Apply configured late fees to overdue pending/partial fees.';

    public function handle(): int
    {
        $schoolsProcessed = 0;
        $feesUpdated      = 0;

        School::query()->active()->chunk(50, function ($schools) use (&$schoolsProcessed, &$feesUpdated) {
            foreach ($schools as $school) {
                $config = LateFee::where('school_id', $school->id)
                    ->orderBy('fine_date')    // tiered: 5 days → ₹50, 15 days → ₹100, etc.
                    ->get();

                if ($config->isEmpty()) {
                    continue;
                }

                Fee::where('school_id', $school->id)
                    ->whereIn('payment_status', [FeeStatus::Pending, FeeStatus::Partial])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now()->startOfDay())
                    ->chunkById(500, function ($fees) use ($config, &$feesUpdated) {
                        foreach ($fees as $fee) {
                            $updated = $this->applyLateFee($fee, $config);
                            if ($updated) {
                                $feesUpdated++;
                            }
                        }
                    });

                $schoolsProcessed++;
            }
        });

        $this->info("Done. Schools processed: {$schoolsProcessed}, Fees updated: {$feesUpdated}.");

        return self::SUCCESS;
    }

    /**
     * Apply the highest applicable late-fee tier to a single fee record.
     *
     * Uses `fine_date` as "days after due date" (the migration-comment
     * interpretation confirmed during planning).
     */
    private function applyLateFee(Fee $fee, $config): bool
    {
        return DB::transaction(function () use ($fee, $config) {
            $fee = Fee::whereKey($fee->id)->lockForUpdate()->first();

            if (!$fee || !$fee->due_date) {
                return false;
            }

            // diffInDays sign convention varies by Carbon version. Compute
            // signed days from due_date → today; positive means overdue.
            $daysLate = (int) $fee->due_date->copy()->startOfDay()
                ->diffInDays(now()->startOfDay(), false);

            if ($daysLate <= 0) {
                return false;
            }

            // Find the highest tier where fine_date <= daysLate
            $applicable = $config->last(fn ($c) => $c->fine_date <= $daysLate);

            if (!$applicable) {
                return false;
            }

            // Already at or above this tier? Skip (idempotent).
            if (bccomp($fee->late_fee ?? '0', $applicable->late_fee_amount, 2) >= 0) {
                return false;
            }

            $delta = bcsub($applicable->late_fee_amount, $fee->late_fee ?? '0', 2);

            $fee->late_fee       = $applicable->late_fee_amount;
            $fee->payable_amount = bcadd($fee->payable_amount, $delta, 2);
            $fee->due_amount     = bcadd($fee->due_amount, $delta, 2);
            $fee->payment_status = FeeStatus::Overdue;
            $fee->save();

            Log::info('Late fee applied', [
                'fee_id'     => $fee->id,
                'school_id'  => $fee->school_id,
                'days_late'  => $daysLate,
                'late_fee'   => $applicable->late_fee_amount,
                'delta'      => $delta,
            ]);

            // Notify parents
            $student = $fee->student;
            if ($student) {
                $notifiables = collect();
                if ($student->father_email) {
                    $notifiables->push(\Illuminate\Support\Facades\Notification::route('mail', $student->father_email));
                }
                if ($student->mother_email) {
                    $notifiables->push(\Illuminate\Support\Facades\Notification::route('mail', $student->mother_email));
                }
                if ($notifiables->isEmpty() && $student->user && $student->user->email) {
                    $notifiables->push($student->user);
                }

                if ($notifiables->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send(
                        $notifiables, 
                        new \App\Notifications\LateFeeAppliedNotification($fee, $delta)
                    );
                }
            }

            return true;
        });
    }
}
