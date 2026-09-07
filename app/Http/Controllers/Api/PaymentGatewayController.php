<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\Payment\PhonePeGateway;
use App\Services\Payment\GPayGateway;

/**
 * Payment Gateway API Controller
 * Handles PhonePe, Google Pay, UPI QR code generation, and payment verification
 */
class PaymentGatewayController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Initiate PhonePe Payment (Standard Checkout)
     * POST /api/payment/phonepe/initiate
     * Body: { amount, order_id, description, customer_name, customer_email, customer_phone, redirect_url, callback_url }
     */
    public function initiatePhonePe()
    {
        header('Content-Type: application/json');
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['amount'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
                return;
            }
        }

        $phonepe = new PhonePeGateway([
            'merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
            'salt_key' => env('PHONEPE_SALT_KEY', ''),
            'salt_index' => env('PHONEPE_SALT_INDEX', '1'),
            'test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        if (!$phonepe->isConfigured()) {
            http_response_code(503);
            echo json_encode(['success' => false, 'error' => 'PhonePe not configured']);
            return;
        }

        $orderId = $data['order_id'] ?? uniqid('PH_');
        $result = $phonepe->initiatePayment([
            'amount' => floatval($data['amount']),
            'receipt' => $orderId,
            'description' => $data['description'] ?? 'APS Dream Home Payment',
            'customer_name' => $data['customer_name'] ?? '',
            'customer_email' => $data['customer_email'] ?? '',
            'customer_phone' => $data['customer_phone'] ?? '',
            'redirect_url' => $data['redirect_url'] ?? (env('APP_URL', '') . '/payment/phonepe/callback'),
            'callback_url' => $data['callback_url'] ?? (env('APP_URL', '') . '/api/payment/phonepe/webhook')
        ]);

        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
    }

    /**
     * Initiate PhonePe UPI Intent (for Google Pay, PhonePe, BHIM apps)
     * POST /api/payment/phonepe/upi-intent
     */
    public function initiatePhonePeUpiIntent()
    {
        header('Content-Type: application/json');
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['amount'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
                return;
            }
        }

        $phonepe = new PhonePeGateway([
            'merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
            'salt_key' => env('PHONEPE_SALT_KEY', ''),
            'salt_index' => env('PHONEPE_SALT_INDEX', '1'),
            'test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        if (!$phonepe->isConfigured()) {
            http_response_code(503);
            echo json_encode(['success' => false, 'error' => 'PhonePe not configured']);
            return;
        }

        $orderId = $data['order_id'] ?? uniqid('PH_');
        $result = $phonepe->initiateUpiIntent([
            'amount' => floatval($data['amount']),
            'receipt' => $orderId,
            'description' => $data['description'] ?? 'APS Dream Home Payment',
            'customer_name' => $data['customer_name'] ?? '',
            'customer_email' => $data['customer_email'] ?? '',
            'customer_phone' => $data['customer_phone'] ?? '',
            'redirect_url' => $data['redirect_url'] ?? (env('APP_URL', '') . '/payment/phonepe/callback'),
            'callback_url' => $data['callback_url'] ?? (env('APP_URL', '') . '/api/payment/phonepe/webhook'),
            'upi_app' => $data['upi_app'] ?? '' // e.g., 'gpay', 'phonepe', 'paytm'
        ]);

        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
    }

    /**
     * Verify PhonePe Payment
     * GET /api/payment/phonepe/verify/{transactionId}
     */
    public function verifyPhonePe($transactionId = null)
    {
        header('Content-Type: application/json');
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }

        $transactionId = $transactionId ?? $_GET['transactionId'] ?? '';
        if (empty($transactionId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing transaction ID']);
            return;
        }

        $phonepe = new PhonePeGateway([
            'merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
            'salt_key' => env('PHONEPE_SALT_KEY', ''),
            'salt_index' => env('PHONEPE_SALT_INDEX', '1'),
            'test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        if (!$phonepe->isConfigured()) {
            http_response_code(503);
            echo json_encode(['success' => false, 'error' => 'PhonePe not configured']);
            return;
        }

        $result = $phonepe->getPaymentStatus($transactionId);
        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
    }

    /**
     * PhonePe Webhook/Callback Handler
     * POST /api/payment/phonepe/webhook
     */
    public function phonePeWebhook()
    {
        header('Content-Type: application/json');
        $payload = json_decode(file_get_contents('php://input'), true);
        
        error_log('PhonePe webhook received: ' . json_encode($payload));

        $merchantTransactionId = $payload['merchantTransactionId'] ?? '';
        
        if ($merchantTransactionId) {
            $phonepe = new PhonePeGateway([
                'merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
                'salt_key' => env('PHONEPE_SALT_KEY', ''),
                'salt_index' => env('PHONEPE_SALT_INDEX', '1'),
                'test_mode' => env('PAYMENT_SANDBOX', true)
            ]);

            $result = $phonepe->getPaymentStatus($merchantTransactionId);
            
            if ($result['success']) {
                // Update local DB if needed (would need DB connection here)
                // This would typically be handled by a queued job
                error_log("PhonePe payment {$merchantTransactionId} completed: " . json_encode($result));
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'received']);
    }

    /**
     * Initiate Google Pay Payment (via UPI Intent through PhonePe)
     * POST /api/payment/gpay/initiate
     * Body: { amount, order_id, description, customer_name, customer_email, customer_phone }
     */
    public function initiateGPay()
    {
        header('Content-Type: application/json');
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['amount'];
        foreach (['amount'] as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
                return;
            }
        }

        $gpay = new GPayGateway([
            'merchant_vpa' => env('GP_MERCHANT_VPA', ''),
            'merchant_name' => env('GP_MERCHANT_NAME', 'APS Dream Home'),
            'merchant_code' => env('GP_MERCHANT_CODE', 'APSDH'),
            'test_mode' => env('PAYMENT_SANDBOX', true),
            'phonepe_merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
            'phonepe_salt_key' => env('PHONEPE_SALT_KEY', ''),
            'phonepe_salt_index' => env('PHONEPE_SALT_INDEX', '1'),
            'phonepe_test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        if (!$gpay->isConfigured()) {
            http_response_code(503);
            echo json_encode(['success' => false, 'error' => 'Google Pay/UPI not configured']);
            return;
        }

        $orderId = $data['order_id'] ?? uniqid('GP_');
        $result = $gpay->initiatePayment([
            'amount' => floatval($data['amount']),
            'receipt' => $data['order_id'] ?? uniqid('GP_'),
            'description' => $data['description'] ?? 'APS Dream Home Payment',
            'customer_name' => $data['customer_name'] ?? '',
            'customer_email' => $data['customer_email'] ?? '',
            'customer_phone' => $data['customer_phone'] ?? ''
        ]);

        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
    }

    /**
     * Generate UPI QR Code
     * POST /api/payment/upi/qrcode
     * Body: { amount, order_id, description, customer_name }
     */
    public function generateQRCode()
    {
        header('Content-Type: application/json');
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['amount'];
        foreach (['amount'] as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "Missing required field: {$field}"]);
                return;
            }
        }

        $gpay = new GPayGateway([
            'merchant_vpa' => env('GP_MERCHANT_VPA', 'apsdreamhome@upi'),
            'merchant_name' => env('GP_MERCHANT_NAME', 'APS Dream Home'),
            'merchant_code' => env('GP_MERCHANT_CODE', 'APSDH'),
            'test_mode' => env('PAYMENT_SANDBOX', true),
            'phonepe_merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
            'phonepe_salt_key' => env('PHONEPE_SALT_KEY', ''),
            'phonepe_salt_index' => env('PHONEPE_SALT_INDEX', '1'),
            'phonepe_test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        if (!$gpay->isConfigured()) {
            // Fallback: generate basic UPI QR
            $amount = floatval($data['amount']);
            $orderId = $data['order_id'] ?? uniqid('UPI_');
            $upiString = "upi://pay?pa=apsdreamhome@upi&pn=APS%20Dream%20Home&am=$amount&tr=" . uniqid('UPI_') . "&cu=INR";
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => [
                    'order_id' => $data['order_id'] ?? uniqid('UPI_'),
                    'amount' => $amount,
                    'upi_string' => $upiString,
                    'qr_data' => base64_encode($upiString)
                ]
            ]);
            return;
        }

        $result = $gpay->initiatePayment([
            'amount' => floatval($data['amount']),
            'receipt' => $data['order_id'] ?? uniqid('UPI_'),
            'description' => $data['description'] ?? 'APS Dream Home Payment',
            'customer_name' => $data['customer_name'] ?? '',
            'customer_email' => $data['customer_email'] ?? '',
            'customer_phone' => $data['customer_phone'] ?? ''
        ]);

        http_response_code($result['success'] ? 200 : 400);
        echo json_encode($result);
    }

    /**
     * UPI Callback/Return Handler
     * POST/GET /api/payment/upi/callback
     */
    public function upiCallback()
    {
        header('Content-Type: application/json');
        
        // Handle both GET (redirect) and POST (webhook)
        $payload = $_POST ?: $_GET;
        
        error_log('UPI callback received: ' . json_encode($payload));

        $transactionId = $payload['transaction_id'] ?? $payload['txn_id'] ?? $payload['tr'] ?? '';
        $status = $payload['status'] ?? $payload['txn_status'] ?? 'UNKNOWN';
        
        if ($transactionId && in_array($status, ['SUCCESS', 'COMPLETED', 'PAID'])) {
            // Could update local DB here if needed
            error_log("UPI payment {$transactionId} completed");
        }

        // Return success for API calls
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            http_response_code(200);
            echo json_encode(['status' => 'received', 'transaction_id' => $payload['tr'] ?? $payload['transaction_id'] ?? '']);
        } else {
            // Redirect to success/failed page
            $redirectUrl = in_array($status, ['SUCCESS', 'COMPLETED', 'PAID']) 
                ? (env('APP_URL', '') . '/payment/success?txn=' . $transactionId)
                : (env('APP_URL', '') . '/payment/failed?txn=' . $transactionId);
            header('Location: ' . $redirectUrl);
        }
        exit;
    }

    /**
     * Get Payment Status
     * GET /api/payment/status/{orderId}
     */
    public function getStatus($orderId = null)
    {
        header('Content-Type: application/json');
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }

        $orderId = $orderId ?? $_GET['orderId'] ?? '';
        if (empty($orderId)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing order ID']);
            return;
        }

        // Try PhonePe first (most common for UPI)
        $phonepe = new PhonePeGateway([
            'merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
            'salt_key' => env('PHONEPE_SALT_KEY', ''),
            'salt_index' => env('PHONEPE_SALT_INDEX', '1'),
            'test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        if ($phonepe->isConfigured()) {
            $result = $phonepe->getPaymentStatus($orderId);
            if ($result['success']) {
                http_response_code(200);
                echo json_encode($result);
                return;
            }
        }

        // Fallback: check local DB if needed
        // For now, return not found
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Payment not found', 'order_id' => $orderId]);
    }

    /**
     * Get Available Payment Methods
     * GET /api/payment/methods
     */
    public function getPaymentMethods()
    {
        header('Content-Type: application/json');
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }

        $methods = [
            ['id' => 'razorpay', 'name' => 'Razorpay', 'enabled' => !empty(env('RAZORPAY_KEY_ID'))],
            ['id' => 'payu', 'name' => 'PayU', 'enabled' => !empty(env('PAYU_MERCHANT_KEY'))],
            ['id' => 'phonepe', 'name' => 'PhonePe', 'enabled' => !empty(env('PHONEPE_MERCHANT_ID'))],
            ['id' => 'gpay', 'name' => 'Google Pay', 'enabled' => !empty(env('GP_MERCHANT_VPA')) || !empty(env('PHONEPE_MERCHANT_ID'))],
            ['id' => 'upi', 'name' => 'UPI QR', 'enabled' => true],
            ['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'enabled' => true],
            ['id' => 'cash', 'name' => 'Cash', 'enabled' => true],
        ];

        echo json_encode([
            'success' => true,
            'data' => $methods
        ]);
    }
}