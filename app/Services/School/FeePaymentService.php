<?php

namespace App\Services\School;

use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\School;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Enums\FeeStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FeePaymentService
{
    /**
     * Process a fee payment for one or multiple fee heads
     */
    public function collectPayment(School $school, array $data): array
    {
        $studentId = $data['student_id'];
        $academicYearId = $data['academic_year_id'];
        $paymentDate = $data['payment_date'];
        $paymentMethodId = $data['payment_method_id'];
        $transactionId = $data['transaction_id'] ?? null;
        $remarks = $data['remarks'] ?? null;
        $payments = $data['payments']; // Array of ['fee_id' => X, 'amount' => Y]

        $receiptNo = $this->generateReceiptNumber($school);

        DB::beginTransaction();
        try {
            $totalCollected = 0;

            foreach ($payments as $payment) {
                $fee = Fee::where('id', $payment['fee_id'])
                    ->where('school_id', $school->id)
                    ->lockForUpdate()
                    ->first();

                if (!$fee) {
                    throw new \Exception("Fee record not found: " . $payment['fee_id']);
                }

                $amountToPay = floatval($payment['amount']);
                
                if ($amountToPay <= 0) continue;

                // Guard against overpayment
                $currentDue = (float)($fee->payable_amount ?? 0)
                    - (float)($fee->paid_amount ?? 0)
                    - (float)($fee->waiver_amount ?? 0)
                    - (float)($fee->discount_amount ?? 0);
                if ($amountToPay > $currentDue && $currentDue > 0) {
                    $amountToPay = $currentDue;
                }

                // Create Payment record
                FeePayment::create([
                    'school_id' => $school->id,
                    'student_id' => $studentId,
                    'fee_id' => $fee->id,
                    'academic_year_id' => $academicYearId,
                    'amount' => $amountToPay,
                    'payment_date' => $paymentDate,
                    'payment_method_id' => $paymentMethodId,
                    'receipt_no' => $receiptNo,
                    'transaction_id' => $transactionId,
                    'remarks' => $remarks,
                    'created_by' => auth()->id(),
                ]);

                // Update Fee record — coerce nullable columns to float to avoid arithmetic errors
                $fee->paid_amount = (float)($fee->paid_amount ?? 0) + $amountToPay;
                $fee->due_amount = (float)($fee->payable_amount ?? 0)
                    - $fee->paid_amount
                    - (float)($fee->waiver_amount ?? 0)
                    - (float)($fee->discount_amount ?? 0);
                
                if ($fee->due_amount <= 0) {
                    $fee->payment_status = FeeStatus::Paid;
                    $fee->due_amount = 0;
                } else {
                    $fee->payment_status = FeeStatus::Partial;
                }

                $fee->payment_date = $paymentDate;
                $fee->save();

                $totalCollected += $amountToPay;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Payment collected successfully. Receipt No: {$receiptNo}",
                'data' => [
                    'receipt_no' => $receiptNo,
                    'total_amount' => $totalCollected
                ]
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
     * Generate a unique receipt number
     */
    protected function generateReceiptNumber(School $school): string
    {
        // Use a DB-level advisory lock per school to prevent race conditions
        $key = 'receipt_seq_' . $school->id;
        return \Illuminate\Support\Facades\Cache::lock($key, 5)->block(3, function () use ($school) {
            $prefix = 'RCPT';
            $year   = date('Y');
            $last   = FeePayment::withTrashed()
                ->where('school_id', $school->id)
                ->where('receipt_no', 'like', "{$prefix}-{$school->id}-{$year}-%")
                ->max('receipt_no');
            $count = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;
            return "{$prefix}-{$school->id}-{$year}-" . str_pad($count, 6, '0', STR_PAD_LEFT);
        });
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
