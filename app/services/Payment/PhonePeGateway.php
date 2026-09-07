<?php

namespace App\Services\Payment;

/**
 * PhonePe Payment Gateway Integration
 * Implements PaymentGatewayInterface for PhonePe payment gateway
 */
class PhonePeGateway implements PaymentGatewayInterface
{
    private $merchantId;
    private $saltKey;
    private $saltIndex;
    private $baseUrl;
    private $isTestMode;

    public function __construct(array $config = [])
    {
        $this->merchantId = $config['merchant_id'] ?? $_ENV['PHONEPE_MERCHANT_ID'] ?? '';
        $this->saltKey = $config['salt_key'] ?? $_ENV['PHONEPE_SALT_KEY'] ?? '';
        $this->saltIndex = $config['salt_index'] ?? $_ENV['PHONEPE_SALT_INDEX'] ?? '1';
        $this->isTestMode = $config['test_mode'] ?? ($_ENV['PHONEPE_TEST_MODE'] ?? true);
        
        $this->baseUrl = $this->isTestMode
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox'
            : 'https://api.phonepe.com/apis/pg';
    }

    public function getGatewayName(): string
    {
        return 'phonepe';
    }

    public function isConfigured(): bool
    {
        return !empty($this->merchantId) && !empty($this->saltKey);
    }

    /**
     * Generate PhonePe checksum (SHA256)
     */
    private function generateChecksum(string $payload, string $endpoint): string
    {
        $string = $payload . $endpoint . $this->saltKey;
        return hash('sha256', $string) . '###' . $this->saltIndex;
    }

    /**
     * Create PhonePe Payment Order (Standard Checkout)
     */
    public function initiatePayment(array $paymentData): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'PhonePe not configured'];
        }

        $merchantTransactionId = $paymentData['receipt'] ?? uniqid('txn_');
        $amountInPaise = (int)($paymentData['amount'] * 100);

        $payload = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $merchantTransactionId,
            'merchantUserId' => $paymentData['customer_phone'] ?? 'user_' . time(),
            'amount' => $amountInPaise,
            'redirectUrl' => $paymentData['redirect_url'] ?? ($_ENV['APP_URL'] ?? 'http://localhost') . '/payment/phonepe/callback',
            'redirectMode' => 'POST',
            'callbackUrl' => $paymentData['callback_url'] ?? ($_ENV['APP_URL'] ?? 'http://localhost') . '/api/v2/mobile/payment/phonepe/webhook',
            'paymentInstrument' => [
                'type' => 'PAY_PAGE'
            ]
        ];

        $jsonPayload = json_encode($payload);
        $base64Payload = base64_encode($jsonPayload);
        $checksum = $this->generateChecksum($base64Payload, '/pg/v1/pay');

        $ch = curl_init($this->baseUrl . '/pg/v1/pay');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-VERIFY: ' . $checksum,
                'X-MERCHANT-ID: ' . $this->merchantId
            ],
            CURLOPT_POSTFIELDS => json_encode(['request' => $base64Payload])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Failed to create PhonePe order', 'details' => json_decode($response, true)];
        }

        $result = json_decode($response, true);

        if (!isset($result['success']) || !$result['success']) {
            return ['success' => false, 'error' => $result['message'] ?? 'PhonePe order creation failed'];
        }

        return [
            'success' => true,
            'order_id' => $merchantTransactionId,
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'INR',
            'instrument_response' => [
                'type' => 'PAY_PAGE',
                'redirectInfo' => [
                    'url' => $result['data']['instrumentResponse']['redirectInfo']['url'] ?? '',
                    'method' => 'POST'
                ]
            ],
            'redirect_url' => $result['data']['instrumentResponse']['redirectInfo']['url'] ?? '',
            'merchant_transaction_id' => $merchantTransactionId
        ];
    }

    /**
     * Create PhonePe UPI Intent Payment (for direct UPI app launch)
     */
    public function initiateUpiIntent(array $paymentData): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'PhonePe not configured'];
        }

        $merchantTransactionId = $paymentData['receipt'] ?? uniqid('upi_');
        $amountInPaise = (int)($paymentData['amount'] * 100);

        $payload = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $merchantTransactionId,
            'merchantUserId' => $paymentData['customer_phone'] ?? 'user_' . time(),
            'amount' => $amountInPaise,
            'redirectUrl' => $paymentData['redirect_url'] ?? ($_ENV['APP_URL'] ?? 'http://localhost') . '/payment/phonepe/callback',
            'redirectMode' => 'POST',
            'callbackUrl' => $paymentData['callback_url'] ?? ($_ENV['APP_URL'] ?? 'http://localhost') . '/api/v2/mobile/payment/phonepe/webhook',
            'paymentInstrument' => [
                'type' => 'UPI_INTENT',
                'targetApp' => $paymentData['upi_app'] ?? ''
            ]
        ];

        $jsonPayload = json_encode($payload);
        $base64Payload = base64_encode($jsonPayload);
        $checksum = $this->generateChecksum($base64Payload, '/pg/v1/pay');

        $ch = curl_init($this->baseUrl . '/pg/v1/pay');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-VERIFY: ' . $checksum,
                'X-MERCHANT-ID: ' . $this->merchantId
            ],
            CURLOPT_POSTFIELDS => json_encode(['request' => $base64Payload])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Failed to create PhonePe UPI intent', 'details' => json_decode($response, true)];
        }

        $result = json_decode($response, true);

        if (!isset($result['success']) || !$result['success']) {
            return ['success' => false, 'error' => $result['message'] ?? 'PhonePe UPI intent creation failed'];
        }

        return [
            'success' => true,
            'order_id' => $merchantTransactionId,
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'INR',
            'upi_intent' => $result['data']['instrumentResponse']['intent'] ?? '',
            'merchant_transaction_id' => $merchantTransactionId
        ];
    }

    /**
     * Verify PhonePe Payment
     */
    public function verifyPayment(string $paymentId, array $data): array
    {
        // For webhook/callback verification
        $merchantTransactionId = $data['merchantTransactionId'] ?? $paymentId;
        $checksum = $data['checksum'] ?? '';
        
        if (empty($merchantTransactionId)) {
            return ['success' => false, 'error' => 'Missing transaction ID'];
        }

        // Verify by checking transaction status with PhonePe
        return $this->getPaymentStatus($merchantTransactionId);
    }

    /**
     * Check Payment Status with PhonePe
     */
    public function getPaymentStatus(string $paymentId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'PhonePe not configured'];
        }

        $endpoint = "/pg/v1/status/{$this->merchantId}/{$paymentId}";
        $checksum = $this->generateChecksum('', $endpoint);

        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-VERIFY: ' . $checksum,
                'X-MERCHANT-ID: ' . $this->merchantId
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Failed to fetch payment status'];
        }

        $result = json_decode($response, true);

        if (!isset($result['success']) || !$result['success']) {
            return ['success' => false, 'error' => $result['message'] ?? 'Failed to get status'];
        }

        $state = $result['data']['state'] ?? 'UNKNOWN';
        $isSuccess = $state === 'COMPLETED';

        return [
            'success' => $isSuccess,
            'status' => $state,
            'amount' => ($result['data']['amount'] ?? 0) / 100,
            'transaction_id' => $result['data']['transactionId'] ?? $paymentId,
            'gateway_response' => $result
        ];
    }

    /**
     * Process Refund via PhonePe
     */
    public function refund(string $paymentId, float $amount, string $reason = null): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'PhonePe not configured'];
        }

        $merchantRefundId = 'REF_' . $paymentId . '_' . time();
        $amountInPaise = (int)($amount * 100);

        $payload = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $paymentId,
            'merchantRefundId' => $merchantRefundId,
            'amount' => $amountInPaise,
            'reason' => $reason ?? 'Customer requested refund'
        ];

        $jsonPayload = json_encode($payload);
        $base64Payload = base64_encode($jsonPayload);
        $checksum = $this->generateChecksum($base64Payload, '/pg/v1/refund');

        $ch = curl_init($this->baseUrl . '/pg/v1/refund');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-VERIFY: ' . $checksum,
                'X-MERCHANT-ID: ' . $this->merchantId
            ],
            CURLOPT_POSTFIELDS => json_encode(['request' => $base64Payload])
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Refund request failed', 'details' => json_decode($response, true)];
        }

        $result = json_decode($response, true);

        return [
            'success' => $result['success'] ?? false,
            'refund_id' => $result['data']['merchantRefundId'] ?? $merchantRefundId,
            'gateway_response' => $result
        ];
    }
}