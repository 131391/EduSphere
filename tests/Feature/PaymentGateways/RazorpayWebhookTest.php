<?php

namespace Tests\Feature\PaymentGateways;

use App\Enums\FeeStatus;
use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\OnlineTransaction;
use App\Models\PaymentMethod;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\School\FeePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for RazorpayWebhookController — idempotent reconciliation.
 *
 * Covers:
 *  - Happy path: webhook with valid signature → payment recorded, transaction settled
 *  - Idempotent replay: re-delivering the same webhook does NOT double-charge
 *  - Signature rejection: invalid HMAC → 400
 *  - Unknown order: valid signature but no matching transaction → 200 (logged, no crash)
 *  - Already reconciled: transaction already STATUS_SUCCESS → short-circuits
 */
class RazorpayWebhookTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private AcademicYear $year;
    private Student $student;
    private Fee $fee;
    private OnlineTransaction $transaction;
    private string $webhookSecret = 'whsec_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.razorpay.key'            => 'rzp_test_key',
            'services.razorpay.secret'         => 'rzp_test_secret',
            'services.razorpay.webhook_secret' => $this->webhookSecret,
            'services.razorpay.currency'       => 'INR',
        ]);

        $this->school = School::factory()->create();
        $this->year   = AcademicYear::factory()->create(['school_id' => $this->school->id]);

        $this->student = Student::factory()->create([
            'school_id'        => $this->school->id,
            'academic_year_id' => $this->year->id,
            'status'           => StudentStatus::Active,
        ]);

        $this->fee = Fee::factory()->create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->year->id,
            'payable_amount'   => 8000,
            'paid_amount'      => 0,
            'due_amount'       => 8000,
            'payment_status'   => FeeStatus::Pending,
        ]);

        $this->transaction = OnlineTransaction::create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'fee_id'           => $this->fee->id,
            'amount'           => 8000,
            'gateway_name'     => 'razorpay',
            'gateway_order_id' => 'order_webhook_test',
            'status'           => OnlineTransaction::STATUS_PENDING,
        ]);

        app()->instance('currentSchool', $this->school);

        // Authenticate a user for FeePaymentService activity logging
        $this->actingAs(User::factory()->create(['school_id' => $this->school->id]));
    }

    /**
     * Build a webhook payload matching Razorpay's payment.captured structure.
     */
    private function buildWebhookPayload(string $orderId, string $paymentId, string $event = 'payment.captured'): array
    {
        return [
            'event'   => $event,
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'       => $paymentId,
                        'order_id' => $orderId,
                        'amount'   => 800000, // paise
                        'currency' => 'INR',
                        'method'   => 'upi',
                        'status'   => 'captured',
                    ],
                ],
            ],
        ];
    }

    /**
     * Sign the raw JSON body with the webhook secret.
     */
    private function signWebhook(string $body): string
    {
        return hash_hmac('sha256', $body, $this->webhookSecret);
    }

    // ─── Happy Path ────────────────────────────────────────────────

    public function test_webhook_captures_payment_and_settles_transaction(): void
    {
        $payload = $this->buildWebhookPayload(
            $this->transaction->gateway_order_id,
            'pay_webhook_001',
        );

        $body = json_encode($payload);
        $sig  = $this->signWebhook($body);

        $response = $this->call(
            'POST',
            route('webhooks.razorpay'),
            [],    // parameters
            [],    // cookies
            [],    // files
            [
                'HTTP_X_RAZORPAY_SIGNATURE' => $sig,
                'CONTENT_TYPE'              => 'application/json',
            ],
            $body,
        );

        $response->assertOk();

        // Transaction was marked success
        $this->transaction->refresh();
        $this->assertEquals(OnlineTransaction::STATUS_SUCCESS, $this->transaction->status);
        $this->assertEquals('pay_webhook_001', $this->transaction->gateway_transaction_id);

        // FeePayment was created
        $this->assertDatabaseHas('fee_payments', [
            'fee_id'         => $this->fee->id,
            'transaction_id' => 'pay_webhook_001',
            'school_id'      => $this->school->id,
        ]);

        // Fee balance updated
        $this->fee->refresh();
        $this->assertEquals('8000.00', (string) $this->fee->paid_amount);
        $this->assertEquals('0.00', (string) $this->fee->due_amount);
        $this->assertEquals(FeeStatus::Paid, $this->fee->payment_status);
    }

    // ─── Idempotent Replay ─────────────────────────────────────────

    public function test_webhook_replay_does_not_double_charge(): void
    {
        $payload = $this->buildWebhookPayload(
            $this->transaction->gateway_order_id,
            'pay_webhook_idem',
        );

        $body = json_encode($payload);
        $sig  = $this->signWebhook($body);

        $headers = [
            'HTTP_X_RAZORPAY_SIGNATURE' => $sig,
            'CONTENT_TYPE'              => 'application/json',
        ];

        // First delivery
        $first = $this->call('POST', route('webhooks.razorpay'), [], [], [], $headers, $body);
        $first->assertOk();

        // Second delivery (Razorpay retry)
        $second = $this->call('POST', route('webhooks.razorpay'), [], [], [], $headers, $body);
        $second->assertOk();

        // Only ONE FeePayment row exists
        $paymentCount = FeePayment::where('fee_id', $this->fee->id)->count();
        $this->assertEquals(1, $paymentCount, 'Webhook replay must not create duplicate payments.');

        // Fee paid_amount did not double
        $this->fee->refresh();
        $this->assertEquals('8000.00', (string) $this->fee->paid_amount);
    }

    // ─── Signature Rejection ──────────────────────────────────────

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = $this->buildWebhookPayload(
            $this->transaction->gateway_order_id,
            'pay_webhook_bad_sig',
        );

        $body   = json_encode($payload);
        $badSig = hash_hmac('sha256', 'tampered_body', $this->webhookSecret);

        $response = $this->call(
            'POST',
            route('webhooks.razorpay'),
            [], [], [],
            [
                'HTTP_X_RAZORPAY_SIGNATURE' => $badSig,
                'CONTENT_TYPE'              => 'application/json',
            ],
            $body,
        );

        $response->assertStatus(400);

        // Transaction stays pending
        $this->transaction->refresh();
        $this->assertEquals(OnlineTransaction::STATUS_PENDING, $this->transaction->status);

        // No payment created
        $this->assertDatabaseMissing('fee_payments', [
            'fee_id' => $this->fee->id,
        ]);
    }

    // ─── Unknown Order ────────────────────────────────────────────

    public function test_webhook_unknown_order_returns_ok_without_crash(): void
    {
        $payload = $this->buildWebhookPayload(
            'order_does_not_exist',
            'pay_webhook_ghost',
        );

        $body = json_encode($payload);
        $sig  = $this->signWebhook($body);

        $response = $this->call(
            'POST',
            route('webhooks.razorpay'),
            [], [], [],
            [
                'HTTP_X_RAZORPAY_SIGNATURE' => $sig,
                'CONTENT_TYPE'              => 'application/json',
            ],
            $body,
        );

        // Controller logs a warning but returns 200 (not 500).
        $response->assertOk();
    }

    // ─── Already Reconciled ──────────────────────────────────────

    public function test_webhook_skips_already_reconciled_transaction(): void
    {
        // First: settle the transaction via webhook
        $payload = $this->buildWebhookPayload(
            $this->transaction->gateway_order_id,
            'pay_webhook_already',
        );

        $body = json_encode($payload);
        $sig  = $this->signWebhook($body);

        $headers = [
            'HTTP_X_RAZORPAY_SIGNATURE' => $sig,
            'CONTENT_TYPE'              => 'application/json',
        ];

        $this->call('POST', route('webhooks.razorpay'), [], [], [], $headers, $body)->assertOk();

        // Confirm it was settled
        $this->transaction->refresh();
        $this->assertEquals(OnlineTransaction::STATUS_SUCCESS, $this->transaction->status);

        $paymentsBefore = FeePayment::where('fee_id', $this->fee->id)->count();

        // Second: re-deliver the same event
        $this->call('POST', route('webhooks.razorpay'), [], [], [], $headers, $body)->assertOk();

        // No new FeePayment
        $paymentsAfter = FeePayment::where('fee_id', $this->fee->id)->count();
        $this->assertEquals($paymentsBefore, $paymentsAfter);

        // Fee unchanged
        $this->fee->refresh();
        $this->assertEquals('8000.00', (string) $this->fee->paid_amount);
    }

    // ─── Payment Failure Webhook ─────────────────────────────────

    public function test_webhook_payment_failed_marks_transaction_failed(): void
    {
        $payload = $this->buildWebhookPayload(
            $this->transaction->gateway_order_id,
            'pay_webhook_fail',
            'payment.failed',
        );

        // Add error fields that Razorpay includes on failure
        $payload['payload']['payment']['entity']['error_code']        = 'BAD_REQUEST_ERROR';
        $payload['payload']['payment']['entity']['error_description'] = 'Card declined';

        $body = json_encode($payload);
        $sig  = $this->signWebhook($body);

        $response = $this->call(
            'POST',
            route('webhooks.razorpay'),
            [], [], [],
            [
                'HTTP_X_RAZORPAY_SIGNATURE' => $sig,
                'CONTENT_TYPE'              => 'application/json',
            ],
            $body,
        );

        $response->assertOk();

        $this->transaction->refresh();
        $this->assertEquals(OnlineTransaction::STATUS_FAILED, $this->transaction->status);
        $this->assertEquals('Card declined', $this->transaction->error_message);

        // No payment was created
        $this->assertDatabaseMissing('fee_payments', [
            'fee_id' => $this->fee->id,
        ]);
    }
}
