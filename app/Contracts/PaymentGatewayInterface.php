<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Create an order/session in the payment gateway.
     *
     * @param float $amount
     * @param string $currency
     * @param string $receiptId
     * @param array $metadata
     * @return array Returns an array containing at least the 'order_id'
     */
    public function createOrder(float $amount, string $currency, string $receiptId, array $metadata = []): array;

    /**
     * Verify the payment signature to ensure it's authentic.
     *
     * @param array $payload The webhook/callback payload
     * @return bool
     */
    public function verifySignature(array $payload): bool;
}
