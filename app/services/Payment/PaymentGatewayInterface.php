<?php

namespace App\Services\Payment;

use App\Core\Http\Response;

/**
 * Payment Gateway Interface
 * All payment gateways must implement this interface
 */
interface PaymentGatewayInterface
{
    /**
     * Initialize payment and get checkout URL/parameters
     */
    public function initiatePayment(array $paymentData): array;

    /**
     * Verify payment callback/webhook
     */
    public function verifyPayment(string $paymentId, array $data): array;

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId): array;

    /**
     * Process refund
     */
    public function refund(string $paymentId, float $amount, string $reason = null): array;

    /**
     * Get gateway name
     */
    public function getGatewayName(): string;

    /**
     * Check if gateway is configured
     */
    public function isConfigured(): bool;
}
