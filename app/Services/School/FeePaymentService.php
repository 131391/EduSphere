<?php

namespace App\Services\School;

use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\School;
use App\Models\Student;
use App\Enums\FeeStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeePaymentService
{
    protected NumberingService $numbering;

    public function __construct(NumberingService $numbering)
    {
        $this->numbering = $numbering;
    }

    /**
     * Process a fee payment for one or multiple fee heads.
     *
     * Supports idempotency: if an `idempotency_key` is provided and a
     * matching payment already exists, the previous result is replayed.
     *
     * All monetary arithmetic uses BCMath at 2-decimal precision to
     * avoid IEEE-754 floating-point drift.
     */
    public function collectPayment(School $school, array $data): array
    {
        $studentId       = $data['student_id'];
        $academicYearId  = $data['academic_year_id'];
        $paymentDate     = $data['payment_date'];
        $paymentMethodId = $data['payment_method_id'];
        $transactionId   = $data['transaction_id'] ?? null;
        $remarks         = $data['remarks'] ?? null;
        $payments        = $data['payments']; // Array of ['fee_id' => X, 'amount' => Y]
        $idempotencyKey  = $data['idempotency_key'] ?? null;

        // ── Idempotency check (before starting a transaction) ────────
        if ($idempotencyKey) {
            $existing = FeePayment::where('school_id', $school->id)
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existing) {
                return $this->replay($existing);
            }
        }

        // Reject up-front if every line item is zero. Avoids burning a
        // receipt number and writing a no-op activity log entry.
        $hasPositiveAmount = false;
        foreach ($payments as $payment) {
            if (bccomp(bcadd((string) ($payment['amount'] ?? '0'), '0', 2), '0', 2) > 0) {
                $hasPositiveAmount = true;
                break;
            }
        }
        if (!$hasPositiveAmount) {
            return [
                'success' => false,
                'message' => 'Payment must include at least one positive amount.',
            ];
        }

        DB::beginTransaction();
        try {
            $student = Student::where('school_id', $school->id)
                ->findOrFail($studentId);

            // Generate receipt number INSIDE the transaction (atomic)
            $receiptNo = $this->numbering->nextReceiptNo($school->id);

            $totalCollected = '0.00';

            foreach ($payments as $payment) {
                $fee = Fee::where('id', $payment['fee_id'])
                    ->where('school_id', $school->id)
                    ->lockForUpdate()
                    ->first();

                if (!$fee) {
                    throw new \Exception("Fee record not found: " . $payment['fee_id']);
                }

                if ((int) $fee->student_id !== (int) $student->id) {
                    throw new \Exception("Fee record does not belong to student: " . $payment['fee_id']);
                }

                $amountToPay = $payment['amount'];
                // Normalise to string for BCMath
                $amountToPay = bcadd($amountToPay, '0', 2);

                if (bccomp($amountToPay, '0', 2) <= 0) continue;

                // Guard against overpayment — BCMath precision
                $currentDue = bcsub(
                    bcsub(
                        bcsub($fee->payable_amount ?? '0', $fee->paid_amount ?? '0', 2),
                        $fee->waiver_amount ?? '0',
                        2
                    ),
                    $fee->discount_amount ?? '0',
                    2
                );

                if (bccomp($amountToPay, $currentDue, 2) === 1) {
                    throw new \Exception(
                        "Overpayment rejected for fee #{$fee->id}: attempted {$amountToPay}, outstanding {$currentDue}. "
                        . "Reduce the payment amount to match the balance."
                    );
                }

                // Create Payment record
                FeePayment::create([
                    'school_id'        => $school->id,
                    'student_id'       => $studentId,
                    'fee_id'           => $fee->id,
                    'academic_year_id' => $academicYearId,
                    'amount'           => $amountToPay,
                    'payment_date'     => $paymentDate,
                    'payment_method_id'=> $paymentMethodId,
                    'receipt_no'       => $receiptNo,
                    'transaction_id'   => $transactionId,
                    'idempotency_key'  => $idempotencyKey,
                    'remarks'          => $remarks,
                    'created_by'       => auth()->id(),
                ]);

                // Update Fee record — BCMath precision
                $fee->paid_amount = bcadd($fee->paid_amount ?? '0', $amountToPay, 2);
                $fee->due_amount  = bcsub($currentDue, $amountToPay, 2);

                if (bccomp($fee->due_amount, '0', 2) <= 0) {
                    $fee->payment_status = FeeStatus::Paid;
                    $fee->due_amount = '0.00';
                } else {
                    $fee->payment_status = FeeStatus::Partial;
                }

                $fee->payment_date = $paymentDate;
                $fee->save();

                $totalCollected = bcadd($totalCollected, $amountToPay, 2);
            }

            DB::commit();

            try {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($student)
                    ->withProperties([
                        'receipt_no'        => $receiptNo,
                        'total_amount'      => $totalCollected,
                        'payment_date'      => $paymentDate,
                        'payment_method_id' => $paymentMethodId,
                    ])
                    ->log("Fee payment collected: {$receiptNo}");
            } catch (\Throwable $logException) {
                Log::warning('Payment collected but activity logging failed: ' . $logException->getMessage(), [
                    'school_id'  => $school->id,
                    'student_id' => $student->id,
                    'receipt_no' => $receiptNo,
                ]);
            }

            return [
                'success'      => true,
                'message'      => "Payment collected successfully. Receipt No: {$receiptNo}",
                'receipt_no'   => $receiptNo,
                'total_amount' => $totalCollected,
                'data'         => [
                    'receipt_no'   => $receiptNo,
                    'total_amount' => $totalCollected,
                ],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment Collection Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during payment collection: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Revert a previously recorded payment by receipt number.
     * Uses BCMath to precisely restore the fee balances.
     */
    public function revertPayment(School $school, string $receiptNo): array
    {
        DB::beginTransaction();
        try {
            $payments = FeePayment::where('school_id', $school->id)
                ->where('receipt_no', $receiptNo)
                ->get();

            if ($payments->isEmpty()) {
                throw new \Exception("Receipt number {$receiptNo} not found.");
            }

            $totalReverted = '0.00';
            $student = $payments->first()->student;

            foreach ($payments as $payment) {
                $fee = Fee::where('id', $payment->fee_id)
                    ->where('school_id', $school->id)
                    ->lockForUpdate()
                    ->first();

                if (!$fee) {
                    throw new \Exception("Fee record not found for payment ID: {$payment->id}");
                }

                $amountToRevert = bcadd($payment->amount, '0', 2);

                // Restore paid_amount, then recompute due_amount from base
                // values. Do NOT add the reverted amount to a stale due_amount
                // that may already reflect post-payment waiver/discount/late
                // fee changes.
                $fee->paid_amount = bcsub($fee->paid_amount ?? '0', $amountToRevert, 2);

                if (bccomp($fee->paid_amount, '0', 2) < 0) {
                    $fee->paid_amount = '0.00';
                }

                $deductions = bcadd(
                    bcadd($fee->paid_amount, $fee->waiver_amount ?? '0', 2),
                    $fee->discount_amount ?? '0',
                    2
                );

                $fee->due_amount = bcsub($fee->payable_amount, $deductions, 2);

                if (bccomp($fee->due_amount, '0', 2) < 0) {
                    $fee->due_amount = '0.00';
                }

                if (bccomp($fee->paid_amount, '0', 2) <= 0) {
                    $fee->payment_status = FeeStatus::Pending;
                } elseif (bccomp($fee->due_amount, '0', 2) <= 0) {
                    $fee->payment_status = FeeStatus::Paid;
                } else {
                    $fee->payment_status = FeeStatus::Partial;
                }

                $fee->save();

                $totalReverted = bcadd($totalReverted, $amountToRevert, 2);

                // Soft delete the payment record
                $payment->delete();
            }

            DB::commit();

            try {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($student)
                    ->withProperties([
                        'receipt_no' => $receiptNo,
                        'total_reverted' => $totalReverted,
                    ])
                    ->log("Fee payment reversed: {$receiptNo}");
            } catch (\Throwable $logException) {
                Log::warning('Payment reversed but activity logging failed: ' . $logException->getMessage());
            }

            return [
                'success' => true,
                'message' => "Payment for receipt {$receiptNo} has been successfully reversed.",
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment Reversal Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred during payment reversal: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Replay a previous payment result (idempotency).
     */
    protected function replay(FeePayment $existing): array
    {
        $total = FeePayment::where('school_id', $existing->school_id)
            ->where('receipt_no', $existing->receipt_no)
            ->sum('amount');

        return [
            'success'      => true,
            'message'      => "Payment already processed. Receipt No: {$existing->receipt_no}",
            'receipt_no'   => $existing->receipt_no,
            'total_amount' => bcadd($total, '0', 2),
            'data'         => [
                'receipt_no'   => $existing->receipt_no,
                'total_amount' => bcadd($total, '0', 2),
            ],
        ];
    }

    /**
     * Get pending fees for a student
     */
    public function getStudentPendingFees(Student $student)
    {
        return Fee::where('student_id', $student->id)
            ->where('school_id', $student->school_id)
            ->where('payment_status', '!=', FeeStatus::Paid)
            ->with(['feeName', 'academicYear'])
            ->get();
    }
}
