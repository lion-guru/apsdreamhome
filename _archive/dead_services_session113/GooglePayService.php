<?php

namespace App\Services\Payment;

/**
 * Google Pay (GPay) UPI Service
 * UPI Intent integration for Android apps
 */
class GooglePayService
{
    private $merchantName;
    private $merchantId;
    private $vpa;
    
    public function __construct()
    {
        $this->merchantName = $_ENV['GPAY_MERCHANT_NAME'] ?? 'APS Dream Home';
        $this->merchantId = $_ENV['GPAY_MERCHANT_ID'] ?? 'BCR2DN4T77AN1X7D';
        $this->vpa = $_ENV['GPAY_VPA'] ?? 'apsdreamhome@upi';
    }
    
    /**
     * Generate UPI Intent for Google Pay
     */
    public function generateUpiIntent(array $params): array
    {
        $transactionRefId = $params['order_id'] ?? 'TXN' . time();
        $amount = number_format($params['amount'], 2, '.', '');
        $note = $params['note'] ?? 'Payment to APS Dream Home';
        
        // Build UPI URI
        $upiUri = sprintf(
            'upi://pay?pa=%s&pn=%s&mc=%s&tr=%s&tn=%s&am=%s&cu=INR',
            urlencode($this->vpa),
            urlencode($this->merchantName),
            '1520', // Merchant category code for real estate
            urlencode($transactionRefId),
            urlencode($note),
            $amount
        );
        
        return [
            'success' => true,
            'upi_uri' => $upiUri,
            'intent_data' => [
                'action' => 'android.intent.action.VIEW',
                'data' => $upiUri,
                'package' => 'com.google.android.apps.nbu.paisa.user' // GPay package
            ],
            'transaction_id' => $transactionRefId,
            'amount' => $params['amount'],
            'merchant_vpa' => $this->vpa,
            'merchant_name' => $this->merchantName
        ];
    }
    
    /**
     * Generate UPI Intent for all apps
     */
    public function generateUniversalUpiIntent(array $params): array
    {
        $transactionRefId = $params['order_id'] ?? 'TXN' . time();
        $amount = number_format($params['amount'], 2, '.', '');
        $note = $params['note'] ?? 'Payment to APS Dream Home';
        
        // Build universal UPI URI
        $upiUri = sprintf(
            'upi://pay?pa=%s&pn=%s&mc=%s&tr=%s&tn=%s&am=%s&cu=INR',
            urlencode($this->vpa),
            urlencode($this->merchantName),
            '1520',
            urlencode($transactionRefId),
            urlencode($note),
            $amount
        );
        
        // Alternative apps and their packages
        $apps = [
            'google_pay' => [
                'name' => 'Google Pay',
                'package' => 'com.google.android.apps.nbu.paisa.user',
                'icon' => 'gpay',
                'uri' => $upiUri
            ],
            'phonepe' => [
                'name' => 'PhonePe',
                'package' => 'com.phonepe.app',
                'icon' => 'phonepe',
                'uri' => $upiUri
            ],
            'paytm' => [
                'name' => 'Paytm',
                'package' => 'net.one97.paytm',
                'icon' => 'paytm',
                'uri' => $upiUri
            ],
            'amazon_pay' => [
                'name' => 'Amazon Pay',
                'package' => 'in.amazon.mShop.android.shopping',
                'icon' => 'amazon',
                'uri' => $upiUri
            ],
            'bhim' => [
                'name' => 'BHIM',
                'package' => 'in.org.npci.upiapp',
                'icon' => 'bhim',
                'uri' => $upiUri
            ]
        ];
        
        return [
            'success' => true,
            'upi_uri' => $upiUri,
            'transaction_id' => $transactionRefId,
            'amount' => $params['amount'],
            'merchant_vpa' => $this->vpa,
            'merchant_name' => $this->merchantName,
            'supported_apps' => $apps,
            'note' => 'User can choose any UPI app'
        ];
    }
    
    /**
     * Verify UPI payment status
     * Note: UPI Intent doesn't provide direct callback, use webhook/polling
     */
    public function verifyPayment(string $transactionId, string $status = null): array
    {
        // In real implementation, check from database
        // UPI Intent relies on webhook callback or manual verification
        
        if ($status) {
            return [
                'success' => strtoupper($status) === 'SUCCESS',
                'status' => strtoupper($status),
                'transaction_id' => $transactionId,
                'verified' => true,
                'method' => 'callback'
            ];
        }
        
        // Default: pending verification
        return [
            'success' => false,
            'status' => 'PENDING',
            'transaction_id' => $transactionId,
            'verified' => false,
            'note' => 'Payment status pending. Verify through callback or manual check.'
        ];
    }
    
    /**
     * Generate QR code data for UPI
     */
    public function generateQRCodeData(array $params): string
    {
        $transactionRefId = $params['order_id'] ?? 'TXN' . time();
        $amount = number_format($params['amount'], 2, '.', '');
        $note = $params['note'] ?? 'Payment to APS Dream Home';
        
        // QR code uses same UPI URI format
        return sprintf(
            'upi://pay?pa=%s&pn=%s&mc=%s&tr=%s&tn=%s&am=%s&cu=INR',
            urlencode($this->vpa),
            urlencode($this->merchantName),
            '1520',
            urlencode($transactionRefId),
            urlencode($note),
            $amount
        );
    }
    
    /**
     * Handle UPI callback/webhook
     */
    public function handleCallback(array $data): array
    {
        $requiredFields = ['txnId', 'responseCode', 'ApprovalRefNo'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'error' => "Missing required field: {$field}"
                ];
            }
        }
        
        $isSuccess = in_array($data['responseCode'], ['00', '0', 'SUCCESS']);
        
        return [
            'success' => $isSuccess,
            'transaction_id' => $data['txnId'],
            'approval_ref' => $data['ApprovalRefNo'],
            'status' => $isSuccess ? 'SUCCESS' : 'FAILED',
            'response_code' => $data['responseCode'],
            'amount' => $data['txnRef'] ?? null,
            'timestamp' => $data['txnTimestamp'] ?? date('Y-m-d H:i:s'),
            'raw_data' => $data
        ];
    }
    
    /**
     * Get merchant details
     */
    public function getMerchantDetails(): array
    {
        return [
            'name' => $this->merchantName,
            'vpa' => $this->vpa,
            'id' => $this->merchantId,
            'category_code' => '1520',
            'category' => 'Real Estate Services'
        ];
    }
    
    /**
     * Validate UPI VPA format
     */
    public function validateVPA(string $vpa): bool
    {
        // UPI VPA format: name@handle
        return preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z]+$/', $vpa) === 1;
    }
    
    /**
     * Format amount for UPI
     */
    public function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
    
    /**
     * Get payment status text
     */
    public function getStatusText(string $code): string
    {
        $statuses = [
            '00' => 'Transaction Successful',
            '01' => 'Transaction Failed',
            '02' => 'Pending/Processing',
            '03' => 'Timeout',
            '04' => 'Cancelled by User',
            '05' => 'Declined by Bank',
            '06' => 'Insufficient Balance',
            '07' => 'Invalid PIN',
            '08' => 'Invalid VPA',
            '09' => 'Transaction Limit Exceeded'
        ];
        
        return $statuses[$code] ?? 'Unknown Status';
    }
    
    /**
     * Generate deep link for iOS
     */
    public function generateiOSDeepLink(array $params): array
    {
        $upiUri = $this->generateUniversalUpiIntent($params)['upi_uri'];
        
        return [
            'success' => true,
            'universal_link' => $upiUri,
            'ios_apps' => [
                'google_pay' => 'https://apps.apple.com/in/app/google-pay/id1193357041',
                'phonepe' => 'https://apps.apple.com/in/app/phonepe/id1170055821',
                'paytm' => 'https://apps.apple.com/in/app/paytm/id473941634'
            ],
            'note' => 'iOS will show app selection dialog'
        ];
    }
}
