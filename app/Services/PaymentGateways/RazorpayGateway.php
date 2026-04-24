<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Exception;

class RazorpayGateway implements PaymentGatewayInterface
{
    protected string $keyId;
    protected string $keySecret;

    public function __construct()
    {
        $this->keyId = config('services.razorpay.key');
        $this->keySecret = config('services.razorpay.secret');

        if (empty($this->keyId) || empty($this->keySecret)) {
            throw new Exception("Razorpay keys are not configured.");
        }
    }

    /**
     * Create an order in Razorpay.
     * Note: Amount is in rupees, Razorpay expects paise (amount * 100).
     */
    public function createOrder(float $amount, string $currency, string $receiptId, array $metadata = []): array
    {
        $amountInPaise = intval($amount * 100);

        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amountInPaise,
                'currency' => $currency,
                'receipt' => (string) $receiptId,
                'notes' => $metadata
            ]);

        if ($response->failed()) {
            throw new Exception("Razorpay Order Creation Failed: " . $response->body());
        }

        $data = $response->json();

        return [
            'order_id' => $data['id'],
            'amount' => $amountInPaise,
            'currency' => $data['currency'],
            'status' => $data['status']
        ];
    }

    /**
     * Verify the payment signature.
     */
    public function verifySignature(array $payload): bool
    {
        if (!isset($payload['razorpay_order_id'], $payload['razorpay_payment_id'], $payload['razorpay_signature'])) {
            return false;
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $payload['razorpay_order_id'] . '|' . $payload['razorpay_payment_id'],
            $this->keySecret
        );

        return hash_equals($expectedSignature, $payload['razorpay_signature']);
    }
}
