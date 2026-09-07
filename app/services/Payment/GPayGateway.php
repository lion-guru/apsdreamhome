<?php

namespace App\Services\Payment;

/**
 * Google Pay / UPI Payment Gateway Integration
 * Supports UPI QR code generation, UPI Intent, and Google Pay direct integration
 * Implements PaymentGatewayInterface
 */
class GPayGateway implements PaymentGatewayInterface
{
    private $merchantVpa;
    private $merchantName;
    private $merchantCode;
    private $isTestMode;
    private $phonepeGateway; // For UPI Intent via PhonePe

    public function __construct(array $config = [])
    {
        $this->merchantVpa = $config['merchant_vpa'] ?? $_ENV['GP_MERCHANT_VPA'] ?? 'apsdreamhome@upi';
        $this->merchantName = $config['merchant_name'] ?? $_ENV['GP_MERCHANT_NAME'] ?? 'APS Dream Home';
        $this->merchantCode = $config['merchant_code'] ?? $_ENV['GP_MERCHANT_CODE'] ?? 'APSDH';
        $this->isTestMode = $config['test_mode'] ?? ($_ENV['GP_TEST_MODE'] ?? true);
        
        // PhonePe can handle UPI Intent for Google Pay
        if (!empty($config['phonepe_merchant_id']) || !empty($_ENV['PHONEPE_MERCHANT_ID'])) {
            $this->phonepeGateway = new PhonePeGateway($config);
        }
    }

    public function getGatewayName(): string
    {
        return 'gpay';
    }

    public function isConfigured(): bool
    {
        return !empty($this->merchantVpa);
    }

    /**
     * Generate UPI Deep Link / Intent URL
     * Works with Google Pay, PhonePe, Paytm, BHIM, and other UPI apps
     */
    public function generateUpiLink(array $paymentData): string
    {
        $params = [
            'pa' => $this->merchantVpa,           // Payee VPA
            'pn' => $this->merchantName,          // Payee Name
            'am' => number_format($paymentData['amount'], 2, '.', ''), // Amount
            'cu' => $paymentData['currency'] ?? 'INR', // Currency
            'tr' => $paymentData['receipt'] ?? uniqid('txn_'), // Transaction Ref
            'tn' => $paymentData['description'] ?? 'APS Dream Home Payment', // Transaction Note
            'mc' => $this->merchantCode,          // Merchant Category Code
        ];

        // Add customer info if available
        if (!empty($paymentData['customer_name'])) {
            $params['pn'] = $paymentData['customer_name'];
        }

        $queryString = http_build_query($params);
        return 'upi://pay?' . $queryString;
    }

    /**
     * Generate UPI QR Code (returns base64 encoded PNG)
     */
    public function generateUpiQrCode(array $paymentData): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'GPay/UPI not configured'];
        }

        $upiLink = $this->generateUpiLink($paymentData);
        
        // Use a QR code library or Google Charts API
        $qrSize = $paymentData['qr_size'] ?? 300;
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$qrSize}x{$qrSize}&data=" . urlencode($upiLink);
        
        // Also provide base64 for inline display
        $ch = curl_init($qrUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10
        ]);
        $qrImage = curl_exec($ch);
        curl_close($ch);

        $base64 = base64_encode($qrImage);

        return [
            'success' => true,
            'upi_link' => $upiLink,
            'qr_code_url' => $qrUrl,
            'qr_code_base64' => 'data:image/png;base64,' . $base64,
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'INR',
            'transaction_ref' => $paymentData['receipt'] ?? uniqid('txn_')
        ];
    }

    /**
     * Initiate Payment - creates UPI QR/Intent
     */
    public function initiatePayment(array $paymentData): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'GPay/UPI not configured'];
        }

        $qrResult = $this->generateUpiQrCode($paymentData);
        
        if (!$qrResult['success']) {
            return $qrResult;
        }

        // If PhonePe gateway available, also create UPI Intent for Google Pay
        $upiIntent = null;
        if ($this->phonepeGateway && $this->phonepeGateway->isConfigured()) {
            $upiIntent = $this->phonepeGateway->initiateUpiIntent($paymentData);
        }

        return [
            'success' => true,
            'order_id' => $paymentData['receipt'] ?? uniqid('txn_'),
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'] ?? 'INR',
            'upi_qr' => [
                'url' => $qrResult['qr_code_url'],
                'base64' => $qrResult['qr_code_base64']
            ],
            'upi_link' => $qrResult['upi_link'],
            'upi_intent' => $upiIntent['upi_intent'] ?? null,
            'merchant_vpa' => $this->merchantVpa,
            'merchant_name' => $this->merchantName
        ];
    }

    /**
     * Initiate Google Pay specific payment (requires Google Pay API integration)
     * This is a placeholder - full Google Pay API requires OAuth2 and is complex
     */
    public function initiateGooglePayPayment(array $paymentData): array
    {
        // For full Google Pay API integration, you need:
        // 1. Google Pay Merchant Center setup
        // 2. OAuth2 credentials
        // 3. Payment token handling
        
        // For now, fall back to UPI Intent via PhonePe
        if ($this->phonepeGateway && $this->phonepeGateway->isConfigured()) {
            return $this->phonepeGateway->initiateUpiIntent($paymentData);
        }

        return [
            'success' => false,
            'error' => 'Full Google Pay API not configured. Using UPI fallback.',
            'fallback' => $this->initiatePayment($paymentData)
        ];
    }

    /**
     * Verify Payment (check status via UPI callback or PhonePe)
     */
    public function verifyPayment(string $paymentId, array $data): array
    {
        // For UPI payments, verification typically happens via:
        // 1. UPI callback/webhook from PSP (PhonePe, Razorpay, etc.)
        // 2. Manual bank statement reconciliation
        // 3. Checking with PhonePe if UPI Intent was used
        
        if ($this->phonepeGateway && $this->phonepeGateway->isConfigured()) {
            return $this->phonepeGateway->verifyPayment($paymentId, $data);
        }

        return [
            'success' => false,
            'error' => 'UPI payment verification requires PSP webhook integration',
            'payment_id' => $paymentId
        ];
    }

    /**
     * Get Payment Status
     */
    public function getPaymentStatus(string $paymentId): array
    {
        if ($this->phonepeGateway && $this->phonepeGateway->isConfigured()) {
            return $this->phonepeGateway->getPaymentStatus($paymentId);
        }

        return [
            'success' => false,
            'error' => 'Payment status check requires PSP integration',
            'payment_id' => $paymentId
        ];
    }

    /**
     * Process Refund
     */
    public function refund(string $paymentId, float $amount, string $reason = null): array
    {
        if ($this->phonepeGateway && $this->phonepeGateway->isConfigured()) {
            return $this->phonepeGateway->refund($paymentId, $amount, $reason);
        }

        return [
            'success' => false,
            'error' => 'Refund requires PSP integration (PhonePe/Razorpay)',
            'payment_id' => $paymentId
        ];
    }
}

/**
 * UPI QR Code Generator (standalone utility)
 * Can be used independently for generating UPI QR codes
 */
class UpiQrGenerator
{
    public static function generate(string $vpa, float $amount, string $merchantName, string $transactionRef, string $note = ''): array
    {
        $params = [
            'pa' => $vpa,
            'pn' => $merchantName,
            'am' => number_format($amount, 2, '.', ''),
            'cu' => 'INR',
            'tr' => $transactionRef,
            'tn' => $note ?: "Payment to {$merchantName}",
        ];

        $upiLink = 'upi://pay?' . http_build_query($params);
        
        // Generate QR code using Google Charts API (free)
        $qrSize = 300;
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$qrSize}x{$qrSize}&data=" . urlencode($upiLink);
        
        $ch = curl_init($qrUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 10
        ]);
        $qrImage = curl_exec($ch);
        curl_close($ch);

        return [
            'success' => true,
            'upi_link' => $upiLink,
            'qr_code_url' => $qrUrl,
            'qr_code_base64' => 'data:image/png;base64,' . base64_encode($qrImage),
            'amount' => $amount,
            'vpa' => $vpa
        ];
    }
}