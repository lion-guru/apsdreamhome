<?php

namespace App\Services\Payment;

/**
 * PhonePe Payment Gateway Service
 * UPI-based payment integration for India
 */
class PhonePeGatewayService
{
    private $merchantId;
    private $saltKey;
    private $saltIndex;
    private $apiUrl;
    private $isProduction;
    
    public function __construct()
    {
        $this->merchantId = $_ENV['PHONEPE_MERCHANT_ID'] ?? 'PGTESTPAYUAT';
        $this->saltKey = $_ENV['PHONEPE_SALT_KEY'] ?? '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399';
        $this->saltIndex = $_ENV['PHONEPE_SALT_INDEX'] ?? '1';
        $this->isProduction = ($_ENV['PHONEPE_ENV'] ?? 'sandbox') === 'production';
        
        $this->apiUrl = $this->isProduction 
            ? 'https://api.phonepe.com/apis/hermes'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox';
    }
    
    /**
     * Initiate UPI payment
     */
    public function initiatePayment(array $params): array
    {
        $payload = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $params['order_id'],
            'merchantUserId' => $params['customer_id'] ?? 'MUID' . time(),
            'amount' => $params['amount'] * 100, // Convert to paise
            'redirectUrl' => $params['redirect_url'] ?? '/payment/phonepe/callback',
            'redirectMode' => 'POST',
            'callbackUrl' => $params['callback_url'] ?? '/api/payment/phonepe/webhook',
            'paymentInstrument' => [
                'type' => 'PAY_PAGE'
            ]
        ];
        
        $jsonPayload = json_encode($payload);
        $base64Payload = base64_encode($jsonPayload);
        
        // Generate checksum
        $checksum = $this->generateChecksum($base64Payload . '/pg/v1/pay' . $this->saltKey);
        
        $headers = [
            'Content-Type: application/json',
            'X-VERIFY: ' . $checksum . '###' . $this->saltIndex
        ];
        
        $requestData = [
            'request' => $base64Payload
        ];
        
        // Make API call
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/pg/v1/pay');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            
            if (isset($result['data']['instrumentResponse']['redirectInfo']['url'])) {
                return [
                    'success' => true,
                    'redirect_url' => $result['data']['instrumentResponse']['redirectInfo']['url'],
                    'transaction_id' => $result['data']['merchantTransactionId'] ?? $params['order_id'],
                    'response' => $result
                ];
            }
            
            return [
                'success' => false,
                'error' => $result['message'] ?? 'Payment initiation failed',
                'response' => $result
            ];
        }
        
        return [
            'success' => false,
            'error' => 'PhonePe API error',
            'http_code' => $httpCode,
            'raw_response' => $response
        ];
    }
    
    /**
     * Verify payment status
     */
    public function verifyPayment(string $merchantTransactionId): array
    {
        $path = '/pg/v1/status/' . $this->merchantId . '/' . $merchantTransactionId;
        $checksum = $this->generateChecksum($path . $this->saltKey);
        
        $headers = [
            'Content-Type: application/json',
            'X-VERIFY: ' . $checksum . '###' . $this->saltIndex,
            'X-MERCHANT-ID: ' . $this->merchantId
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            
            $isSuccess = isset($result['code']) && $result['code'] === 'PAYMENT_SUCCESS';
            
            return [
                'success' => $isSuccess,
                'status' => $result['data']['state'] ?? 'UNKNOWN',
                'amount' => ($result['data']['amount'] ?? 0) / 100, // Convert from paise
                'transaction_id' => $result['data']['transactionId'] ?? null,
                'payment_type' => $result['data']['paymentInstrument']['type'] ?? 'UNKNOWN',
                'response' => $result
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Verification failed',
            'http_code' => $httpCode
        ];
    }
    
    /**
     * Initiate UPI collect request
     */
    public function initiateUpiCollect(string $vpa, array $params): array
    {
        $payload = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $params['order_id'],
            'merchantUserId' => $params['customer_id'] ?? 'MUID' . time(),
            'amount' => $params['amount'] * 100,
            'callbackUrl' => $params['callback_url'] ?? '/api/payment/phonepe/webhook',
            'paymentInstrument' => [
                'type' => 'UPI_COLLECT',
                'vpa' => $vpa
            ]
        ];
        
        $jsonPayload = json_encode($payload);
        $base64Payload = base64_encode($jsonPayload);
        
        $checksum = $this->generateChecksum($base64Payload . '/pg/v1/pay' . $this->saltKey);
        
        $headers = [
            'Content-Type: application/json',
            'X-VERIFY: ' . $checksum . '###' . $this->saltIndex
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/pg/v1/pay');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request' => $base64Payload]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true) ?? ['success' => false, 'error' => 'Request failed'];
    }
    
    /**
     * Refund payment
     */
    public function refund(string $originalTransactionId, float $amount, string $orderId): array
    {
        $payload = [
            'merchantId' => $this->merchantId,
            'merchantTransactionId' => $orderId,
            'originalTransactionId' => $originalTransactionId,
            'amount' => $amount * 100,
            'callbackUrl' => '/api/payment/phonepe/refund-webhook'
        ];
        
        $jsonPayload = json_encode($payload);
        $base64Payload = base64_encode($jsonPayload);
        
        $checksum = $this->generateChecksum($base64Payload . '/pg/v1/refund' . $this->saltKey);
        
        $headers = [
            'Content-Type: application/json',
            'X-VERIFY: ' . $checksum . '###' . $this->saltIndex
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/pg/v1/refund');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['request' => $base64Payload]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true) ?? ['success' => false, 'error' => 'Refund failed'];
    }
    
    /**
     * Validate webhook callback
     */
    public function validateWebhook(array $headers, string $payload): bool
    {
        $xVerify = $headers['X-VERIFY'] ?? '';
        
        if (empty($xVerify)) {
            return false;
        }
        
        $parts = explode('###', $xVerify);
        $receivedChecksum = $parts[0] ?? '';
        $saltIndex = $parts[1] ?? '';
        
        // Recalculate checksum
        $calculatedChecksum = $this->generateChecksum($payload . $this->saltKey);
        
        return hash_equals($calculatedChecksum, $receivedChecksum);
    }
    
    /**
     * Generate SHA256 checksum
     */
    private function generateChecksum(string $input): string
    {
        return hash('sha256', $input);
    }
    
    /**
     * Get supported payment methods
     */
    public function getSupportedMethods(): array
    {
        return [
            'UPI_COLLECT' => 'UPI Collect (Request money)',
            'UPI_PAY' => 'UPI Pay (Intent)',
            'CARD' => 'Credit/Debit Card',
            'NETBANKING' => 'Net Banking',
            'WALLET' => 'Wallets'
        ];
    }
}
