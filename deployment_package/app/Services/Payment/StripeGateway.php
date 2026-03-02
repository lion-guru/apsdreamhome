<?php

namespace App\Services\Payment;

/**
 * Stripe Payment Gateway Integration
 */
class StripeGateway implements PaymentGatewayInterface
{
    private $apiKey;
    private $webhookSecret;
    private $baseUrl = 'https://api.stripe.com/v1';
    private $isTestMode;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? $_ENV['STRIPE_API_KEY'] ?? '';
        $this->webhookSecret = $config['webhook_secret'] ?? $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
        $this->isTestMode = $config['test_mode'] ?? ($_ENV['STRIPE_TEST_MODE'] ?? true);
    }

    public function getGatewayName(): string
    {
        return 'stripe';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Create Stripe Payment Intent
     */
    public function initiatePayment(array $paymentData): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Stripe not configured'];
        }

        $intentData = [
            'amount' => (int)($paymentData['amount'] * 100), // Amount in cents
            'currency' => strtolower($paymentData['currency'] ?? 'inr'),
            'metadata' => $paymentData['metadata'] ?? [],
            'description' => $paymentData['description'] ?? 'APS Dream Homes Payment'
        ];

        $ch = curl_init($this->baseUrl . '/payment_intents');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $this->apiKey . ':',
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($intentData)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Failed to create payment intent', 'details' => json_decode($response, true)];
        }

        $intent = json_decode($response, true);

        return [
            'success' => true,
            'payment_intent_id' => $intent['id'],
            'client_secret' => $intent['client_secret'],
            'amount' => $intent['amount'] / 100,
            'currency' => $intent['currency'],
            'status' => $intent['status']
        ];
    }

    /**
     * Verify Stripe Payment
     */
    public function verifyPayment(string $paymentId, array $data = []): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Stripe not configured'];
        }

        $ch = curl_init($this->baseUrl . '/payment_intents/' . $paymentId);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->apiKey . ':',
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Payment not found'];
        }

        $intent = json_decode($response, true);

        return [
            'success' => $intent['status'] === 'succeeded',
            'payment_id' => $intent['id'],
            'status' => $intent['status'],
            'amount' => $intent['amount'] / 100,
            'currency' => $intent['currency'],
            'metadata' => $intent['metadata'] ?? [],
            'created_at' => date('Y-m-d H:i:s', $intent['created'])
        ];
    }

    /**
     * Get Payment Status
     */
    public function getPaymentStatus(string $paymentId): array
    {
        return $this->verifyPayment($paymentId);
    }

    /**
     * Process Refund
     */
    public function refund(string $paymentId, float $amount, string $reason = null): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Stripe not configured'];
        }

        $refundData = [
            'payment_intent' => $paymentId,
            'amount' => (int)($amount * 100),
            'reason' => 'requested_by_customer'
        ];

        if ($reason) {
            $refundData['metadata'] = ['reason' => $reason];
        }

        $ch = curl_init($this->baseUrl . '/refunds');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $this->apiKey . ':',
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($refundData)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Refund failed', 'details' => json_decode($response, true)];
        }

        $refund = json_decode($response, true);

        return [
            'success' => true,
            'refund_id' => $refund['id'],
            'payment_id' => $paymentId,
            'amount' => $refund['amount'] / 100,
            'status' => $refund['status'],
            'created_at' => date('Y-m-d H:i:s', $refund['created'])
        ];
    }

    /**
     * Verify Webhook Signature
     */
    public function verifyWebhook(string $payload, string $signature): bool
    {
        $timestamp = null;
        $expectedSignature = null;

        $parts = explode(',', $signature);
        foreach ($parts as $part) {
            [$key, $value] = explode('=', $part, 2);
            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $expectedSignature = $value;
            }
        }

        if (!$timestamp || !$expectedSignature) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $payload;
        $computedSignature = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        return hash_equals($expectedSignature, $computedSignature);
    }
}
