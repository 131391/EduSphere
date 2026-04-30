<?php

namespace App\Http\Controllers\Parent;

use App\Enums\FeeStatus;
use App\Http\Controllers\Parent\Concerns\ResolvesParent;
use App\Http\Controllers\TenantController;
use App\Models\Fee;
use App\Models\OnlineTransaction;
use App\Models\PaymentMethod;
use App\Services\PaymentGateways\RazorpayGateway;
use App\Services\School\FeePaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends TenantController
{
    use ResolvesParent;

    public function __construct(protected FeePaymentService $paymentService)
    {
        parent::__construct();
    }

    /**
     * Create a Razorpay order for a fee the authenticated parent owns.
     */
    public function initiate(int $fee_id)
    {
        $fee = $this->resolveOwnedFee($fee_id);

        if (bccomp((string) ($fee->due_amount ?? '0'), '0', 2) <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'This fee is already paid.',
            ], 422);
        }

        try {
            $gateway = $this->gateway();
            $internalReceiptId = 'RC_ONL_' . time() . '_' . $fee->id;

            $order = $gateway->createOrder(
                amount: (float) $fee->due_amount,
                currency: config('services.razorpay.currency', 'INR'),
                receiptId: $internalReceiptId,
                metadata: [
                    'fee_id'     => $fee->id,
                    'student_id' => $fee->student_id,
                    'school_id'  => $fee->school_id,
                ],
            );

            // Unique gateway_order_id is enforced at the DB level, so this also
            // protects against duplicate "Pay" clicks.
            OnlineTransaction::create([
                'school_id'        => $fee->school_id,
                'student_id'       => $fee->student_id,
                'fee_id'           => $fee->id,
                'amount'           => $fee->due_amount,
                'gateway_name'     => 'razorpay',
                'gateway_order_id' => $order['order_id'],
                'status'           => OnlineTransaction::STATUS_PENDING,
            ]);

            return response()->json([
                'success'      => true,
                'order_id'     => $order['order_id'],
                'amount'       => $order['amount'],
                'currency'     => $order['currency'],
                'key'          => config('services.razorpay.key'),
                'name'         => $fee->student->school->name ?? 'EduSphere School',
                'description'  => optional($fee->feeName)->name . ' Payment',
                'student_name' => $fee->student->full_name ?? '',
                'email'        => $fee->student->email ?? '',
                'contact'      => $fee->student->mobile_no ?? '',
            ]);
        } catch (Exception $e) {
            Log::error('Payment initiation failed', [
                'fee_id' => $fee->id,
                'error'  => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Could not initiate payment. Please try again later.',
            ], 500);
        }
    }

    /**
     * Verify a Razorpay checkout callback and record the payment via the
     * canonical FeePaymentService (BCMath, locking, idempotency).
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'razorpay_payment_id' => 'required|string|max:100',
            'razorpay_order_id'   => 'required|string|max:100',
            'razorpay_signature'  => 'required|string|max:255',
            'fee_id'              => 'required|integer|exists:fees,id',
        ]);

        try {
            $gateway = $this->gateway();
        } catch (Exception $e) {
            Log::error('Payment verification unavailable', [
                'fee_id' => $validated['fee_id'],
                'error'  => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Online payment verification is currently unavailable.',
            ], 503);
        }

        if (!$gateway->verifySignature([
            'razorpay_order_id'   => $validated['razorpay_order_id'],
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'razorpay_signature'  => $validated['razorpay_signature'],
        ])) {
            Log::warning('Razorpay signature verification failed', [
                'order_id' => $validated['razorpay_order_id'],
                'user_id'  => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed.',
            ], 422);
        }

        $fee = $this->resolveOwnedFee($validated['fee_id']);

        try {
            return DB::transaction(function () use ($validated, $fee) {
                $transaction = OnlineTransaction::where('school_id', $fee->school_id)
                    ->where('gateway_order_id', $validated['razorpay_order_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$transaction) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unknown transaction.',
                    ], 422);
                }

                if ((int) $transaction->fee_id !== (int) $fee->id) {
                    Log::warning('Razorpay verify fee_id mismatch', [
                        'order_id'           => $validated['razorpay_order_id'],
                        'transaction_fee_id' => $transaction->fee_id,
                        'request_fee_id'     => $fee->id,
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Fee mismatch.',
                    ], 422);
                }

                if ($transaction->status === OnlineTransaction::STATUS_SUCCESS) {
                    return response()->json([
                        'success'    => true,
                        'message'    => 'Payment already processed.',
                        'receipt_no' => $this->lookupReceiptNo($transaction),
                    ]);
                }

                $paymentMethod = PaymentMethod::firstOrCreate(
                    ['school_id' => $fee->school_id, 'code' => 'ONLINE'],
                    ['name' => 'Online Gateway', 'is_active' => true],
                );

                $result = $this->paymentService->collectPayment(
                    $fee->school,
                    [
                        'student_id'        => $fee->student_id,
                        'academic_year_id'  => $fee->academic_year_id,
                        'payment_date'      => now()->toDateString(),
                        'payment_method_id' => $paymentMethod->id,
                        'transaction_id'    => $validated['razorpay_payment_id'],
                        'idempotency_key'   => $validated['razorpay_payment_id'],
                        'remarks'           => 'Online payment via Razorpay',
                        'payments'          => [
                            ['fee_id' => $fee->id, 'amount' => (string) $transaction->amount],
                        ],
                    ],
                );

                if (!$result['success']) {
                    throw new Exception($result['message']);
                }

                $transaction->update([
                    'status'                 => OnlineTransaction::STATUS_SUCCESS,
                    'gateway_transaction_id' => $validated['razorpay_payment_id'],
                    'payload'                => $validated,
                ]);

                return response()->json([
                    'success'    => true,
                    'message'    => 'Payment successful!',
                    'receipt_no' => $result['receipt_no'],
                ]);
            });
        } catch (Exception $e) {
            Log::error('Razorpay verification/processing failed', [
                'order_id' => $validated['razorpay_order_id'],
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the payment.',
            ], 500);
        }
    }

    /**
     * Load a fee record only if (a) it belongs to a student of the
     * authenticated parent, AND (b) both parent and fee live in the
     * tenant bound by the school.access middleware. 404 otherwise so
     * we don't leak existence of unrelated fees.
     */
    protected function resolveOwnedFee(int $feeId): Fee
    {
        $this->ensureSchoolActive();
        $parent = $this->currentParentOrFail();
        $studentIds = $this->ownedStudentIds($parent);

        return Fee::with(['student.school', 'feeName'])
            ->where('school_id', $this->getSchoolId())
            ->whereIn('student_id', $studentIds)
            ->where('payment_status', '!=', FeeStatus::Paid->value)
            ->findOrFail($feeId);
    }

    protected function gateway(): RazorpayGateway
    {
        return app(RazorpayGateway::class);
    }

    /**
     * Look up the receipt number tied to a successful OnlineTransaction.
     */
    protected function lookupReceiptNo(OnlineTransaction $transaction): ?string
    {
        if (!$transaction->gateway_transaction_id) {
            return null;
        }

        return \App\Models\FeePayment::where('school_id', $transaction->school_id)
            ->where('transaction_id', $transaction->gateway_transaction_id)
            ->value('receipt_no');
    }
}
