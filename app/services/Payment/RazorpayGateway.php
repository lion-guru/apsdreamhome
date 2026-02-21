<?php

namespace App\Services\Payment;

/**
 * Razorpay Payment Gateway Integration
 */
class RazorpayGateway implements PaymentGatewayInterface
{
    private $apiKey;
    private $apiSecret;
    private $webhookSecret;
    private $baseUrl = 'https://api.razorpay.com/v1';
    private $isTestMode;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? $_ENV['RAZORPAY_API_KEY'] ?? '';
        $this->apiSecret = $config['api_secret'] ?? $_ENV['RAZORPAY_API_SECRET'] ?? '';
        $this->webhookSecret = $config['webhook_secret'] ?? $_ENV['RAZORPAY_WEBHOOK_SECRET'] ?? '';
        $this->isTestMode = $config['test_mode'] ?? ($_ENV['RAZORPAY_TEST_MODE'] ?? true);
    }

    public function getGatewayName(): string
    {
        return 'razorpay';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiSecret);
    }

    /**
     * Create Razorpay Order
     */
    public function initiatePayment(array $paymentData): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Razorpay not configured'];
        }

        $orderData = [
            'amount' => (int)($paymentData['amount'] * 100), // Amount in paise
            'currency' => $paymentData['currency'] ?? 'INR',
            'receipt' => $paymentData['receipt'] ?? uniqid('rcpt_'),
            'notes' => $paymentData['notes'] ?? []
        ];

        $ch = curl_init($this->baseUrl . '/orders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $this->apiKey . ':' . $this->apiSecret,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($orderData)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Failed to create order', 'details' => json_decode($response, true)];
        }

        $order = json_decode($response, true);

        return [
            'success' => true,
            'order_id' => $order['id'],
            'amount' => $order['amount'] / 100,
            'currency' => $order['currency'],
            'key' => $this->apiKey,
            'name' => $paymentData['merchant_name'] ?? 'APS Dream Homes',
            'description' => $paymentData['description'] ?? 'Property Booking',
            'prefill' => [
                'name' => $paymentData['customer_name'] ?? '',
                'email' => $paymentData['customer_email'] ?? '',
                'contact' => $paymentData['customer_phone'] ?? ''
            ],
            'notes' => $orderData['notes'],
            'theme' => [
                'color' => '#007bff'
            ]
        ];
    }

    /**
     * Verify Razorpay Payment Signature
     */
    public function verifyPayment(string $paymentId, array $data): array
    {
        $orderId = $data['razorpay_order_id'] ?? '';
        $signature = $data['razorpay_signature'] ?? '';

        if (empty($orderId) || empty($signature)) {
            return ['success' => false, 'error' => 'Missing payment parameters'];
        }

        // Verify signature
        $payload = $orderId . '|' . $paymentId;
        $expectedSignature = hash_hmac('sha256', $payload, $this->apiSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            return ['success' => false, 'error' => 'Invalid payment signature'];
        }

        // Fetch payment details from Razorpay
        $ch = curl_init($this->baseUrl . '/payments/' . $paymentId);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->apiKey . ':' . $this->apiSecret,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Failed to fetch payment details'];
        }

        $payment = json_decode($response, true);

        return [
            'success' => true,
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'amount' => $payment['amount'] / 100,
            'currency' => $payment['currency'],
            'status' => $payment['status'],
            'method' => $payment['method'],
            'email' => $payment['email'] ?? null,
            'contact' => $payment['contact'] ?? null,
            'fee' => ($payment['fee'] ?? 0) / 100,
            'tax' => ($payment['tax'] ?? 0) / 100,
            'created_at' => date('Y-m-d H:i:s', $payment['created_at'])
        ];
    }

    /**
     * Get Payment Status
     */
    public function getPaymentStatus(string $paymentId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Razorpay not configured'];
        }

        $ch = curl_init($this->baseUrl . '/payments/' . $paymentId);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->apiKey . ':' . $this->apiSecret,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Payment not found'];
        }

        $payment = json_decode($response, true);

        return [
            'success' => true,
            'payment_id' => $payment['id'],
            'status' => $payment['status'],
            'amount' => $payment['amount'] / 100,
            'currency' => $payment['currency'],
            'method' => $payment['method'],
            'created_at' => date('Y-m-d H:i:s', $payment['created_at'])
        ];
    }

    /**
     * Process Refund
     */
    public function refund(string $paymentId, float $amount, string $reason = null): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Razorpay not configured'];
        }

        $refundData = [
            'amount' => (int)($amount * 100), // Amount in paise
            'notes' => ['reason' => $reason ?? 'Customer requested refund']
        ];

        $ch = curl_init($this->baseUrl . '/payments/' . $paymentId . '/refund');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $this->apiKey . ':' . $this->apiSecret,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($refundData)
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
            'created_at' => date('Y-m-d H:i:s', $refund['created_at'])
        ];
    }

    /**
     * Verify Webhook Signature
     */
    public function verifyWebhook(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        return hash_equals($expectedSignature, $signature);
    }
}
