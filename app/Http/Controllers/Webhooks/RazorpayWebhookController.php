<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\OnlineTransaction;
use App\Models\PaymentMethod;
use App\Services\PaymentGateways\RazorpayGateway;
use App\Services\School\FeePaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay sends server-to-server webhooks for payment events. We treat the
 * webhook as the source of truth for reconciliation: even if the parent's
 * browser callback never fires, the webhook will eventually settle the
 * payment. Idempotency is provided by FeePaymentService via the
 * razorpay_payment_id used as the idempotency key.
 */
class RazorpayWebhookController extends Controller
{
    public function __construct(
        protected RazorpayGateway $gateway,
        protected FeePaymentService $paymentService,
    ) {}

    public function handle(Request $request): Response
    {
        $rawBody   = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature', '');

        try {
            if (!$this->gateway->verifyWebhookSignature($rawBody, $signature)) {
                Log::warning('Razorpay webhook signature mismatch', [
                    'event' => $request->input('event'),
                ]);
                return response('invalid signature', 400);
            }
        } catch (Exception $e) {
            Log::error('Razorpay webhook verification error: ' . $e->getMessage());
            return response('verification error', 500);
        }

        $event = $request->input('event');

        return match ($event) {
            'payment.captured', 'payment.authorized' => $this->handlePaymentSuccess($request),
            'payment.failed'                          => $this->handlePaymentFailure($request),
            default                                   => response('ignored', 200),
        };
    }

    protected function handlePaymentSuccess(Request $request): Response
    {
        $payment = data_get($request->input('payload'), 'payment.entity');

        if (!is_array($payment) || empty($payment['order_id']) || empty($payment['id'])) {
            return response('malformed payload', 400);
        }

        $orderId   = $payment['order_id'];
        $paymentId = $payment['id'];

        try {
            DB::transaction(function () use ($orderId, $paymentId, $payment) {
                $transaction = OnlineTransaction::where('gateway_order_id', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (!$transaction) {
                    Log::warning('Razorpay webhook for unknown order', ['order_id' => $orderId]);
                    return;
                }

                if ($transaction->status === OnlineTransaction::STATUS_SUCCESS) {
                    return; // already reconciled
                }

                $fee = Fee::where('school_id', $transaction->school_id)
                    ->find($transaction->fee_id);

                if (!$fee) {
                    Log::error('Razorpay webhook: fee no longer exists', [
                        'order_id' => $orderId,
                        'fee_id'   => $transaction->fee_id,
                    ]);
                    return;
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
                        'transaction_id'    => $paymentId,
                        'idempotency_key'   => $paymentId,
                        'remarks'           => 'Online payment via Razorpay (webhook)',
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
                    'gateway_transaction_id' => $paymentId,
                    'payload'                => $payment,
                ]);
            });

            return response('ok', 200);
        } catch (Exception $e) {
            Log::error('Razorpay webhook processing failed', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
            // Returning 500 prompts Razorpay to retry the webhook.
            return response('processing failed', 500);
        }
    }

    protected function handlePaymentFailure(Request $request): Response
    {
        $payment = data_get($request->input('payload'), 'payment.entity');
        $orderId = $payment['order_id'] ?? null;

        if (!$orderId) {
            return response('malformed payload', 400);
        }

        OnlineTransaction::where('gateway_order_id', $orderId)
            ->where('status', OnlineTransaction::STATUS_PENDING)
            ->update([
                'status'        => OnlineTransaction::STATUS_FAILED,
                'failed_at'     => now(),
                'error_message' => $payment['error_description'] ?? ($payment['error_code'] ?? null),
                'payload'       => $payment,
            ]);

        return response('ok', 200);
    }
}
