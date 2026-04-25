<?php

namespace Tests\Unit\PaymentGateways;

use App\Services\PaymentGateways\RazorpayGateway;
use Tests\TestCase;

class RazorpayGatewayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.razorpay.key'            => 'rzp_test_key',
            'services.razorpay.secret'         => 'rzp_test_secret',
            'services.razorpay.webhook_secret' => 'whsec_test_secret',
            'services.razorpay.currency'       => 'INR',
        ]);
    }

    public function test_verify_signature_accepts_valid_hmac(): void
    {
        $gateway = new RazorpayGateway();

        $orderId = 'order_test_123';
        $paymentId = 'pay_test_456';
        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, 'rzp_test_secret');

        $valid = $gateway->verifySignature([
            'razorpay_order_id'   => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature'  => $signature,
        ]);

        $this->assertTrue($valid);
    }

    public function test_verify_signature_rejects_tampered_payload(): void
    {
        $gateway = new RazorpayGateway();

        $signature = hash_hmac('sha256', 'order_test_123|pay_test_456', 'rzp_test_secret');

        $valid = $gateway->verifySignature([
            'razorpay_order_id'   => 'order_TAMPERED',
            'razorpay_payment_id' => 'pay_test_456',
            'razorpay_signature'  => $signature,
        ]);

        $this->assertFalse($valid);
    }

    public function test_verify_signature_rejects_missing_fields(): void
    {
        $gateway = new RazorpayGateway();
        $this->assertFalse($gateway->verifySignature([]));
        $this->assertFalse($gateway->verifySignature(['razorpay_order_id' => 'x']));
    }

    public function test_webhook_signature_verifies_raw_body_with_webhook_secret(): void
    {
        $gateway = new RazorpayGateway();
        $body = json_encode(['event' => 'payment.captured']);
        $sig = hash_hmac('sha256', $body, 'whsec_test_secret');

        $this->assertTrue($gateway->verifyWebhookSignature($body, $sig));
        $this->assertFalse($gateway->verifyWebhookSignature($body . 'tampered', $sig));
    }

    public function test_webhook_signature_uses_webhook_secret_not_api_secret(): void
    {
        $gateway = new RazorpayGateway();
        $body = '{"event":"payment.captured"}';

        // Signed with the API secret should NOT verify
        $sigUsingApiSecret = hash_hmac('sha256', $body, 'rzp_test_secret');
        $this->assertFalse($gateway->verifyWebhookSignature($body, $sigUsingApiSecret));
    }
}
