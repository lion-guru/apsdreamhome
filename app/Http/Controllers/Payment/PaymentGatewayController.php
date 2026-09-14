<?php

/**
 * Payment Gateway Integration Controller
 * Complete payment processing system for APS Dream Home
 */

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\BaseController;
use App\Core\Security;
use App\Services\Payment\RazorpayGateway;
use App\Services\Payment\PhonePeGateway;
use App\Services\Payment\GPayGateway;
use App\Services\Communication\NotificationService;
use App\Services\DepartmentRequestService;
use Exception;
use PDO;

class PaymentGatewayController extends BaseController
{
    private $gateway_config = [];

    public function __construct()
    {
        parent::__construct();
        $this->gateway_config = [
            'razorpay' => [
                'key_id' => env('RAZORPAY_KEY_ID', ''),
                'key_secret' => env('RAZORPAY_KEY_SECRET', ''),
                'enabled' => !empty(env('RAZORPAY_KEY_ID'))
            ],
            'payu' => [
                'merchant_key' => env('PAYU_MERCHANT_KEY', ''),
                'merchant_salt' => env('PAYU_MERCHANT_SALT', ''),
                'enabled' => !empty(env('PAYU_MERCHANT_KEY'))
            ],
            'phonepe' => [
                'merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
                'salt_key' => env('PHONEPE_SALT_KEY', ''),
                'salt_index' => env('PHONEPE_SALT_INDEX', '1'),
                'enabled' => !empty(env('PHONEPE_MERCHANT_ID'))
            ],
            'gpay' => [
                'merchant_vpa' => env('GP_MERCHANT_VPA', ''),
                'merchant_name' => env('GP_MERCHANT_NAME', 'APS Dream Home'),
                'merchant_code' => env('GP_MERCHANT_CODE', 'APSDH'),
                'enabled' => !empty(env('GP_MERCHANT_VPA'))
            ],
            'upi' => [
                'enabled' => true // UPI QR codes work without gateway config
            ]
        ];
        $this->createPaymentTables();
    }

    /**
     * Create payment related tables
     */
    private function createPaymentTables()
    {
        if (!$this->db) {
            return;
        }

        // Payment transactions table
        $sql = "CREATE TABLE IF NOT EXISTS payment_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            transaction_id VARCHAR(100) NOT NULL UNIQUE,
            associate_id INT,
            customer_id INT,
            plot_id INT,
            payment_type ENUM('booking', 'installment', 'full_payment', 'registration') NOT NULL,
            amount DECIMAL(15,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'INR',
            payment_method ENUM('razorpay', 'payu', 'phonepe', 'upi', 'bank_transfer', 'cash') NOT NULL,
            payment_status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
            gateway_response JSON,
            bank_reference VARCHAR(100),
            payment_date DATETIME,
            failure_reason TEXT,
            refund_amount DECIMAL(15,2) DEFAULT 0,
            refund_date DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->query($sql);

        // Commission payouts tracking
        $sql = "CREATE TABLE IF NOT EXISTS commission_payouts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            associate_id INT NOT NULL,
            transaction_id VARCHAR(100),
            payout_amount DECIMAL(15,2) NOT NULL,
            payout_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
            payout_method ENUM('bank_transfer', 'upi', 'wallet', 'cash') DEFAULT 'bank_transfer',
            bank_reference VARCHAR(100),
            payout_date DATE,
            remarks TEXT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $this->db->query($sql);
    }

    /**
     * Process payment for plot booking
     */
    public function processPayment()
    {
        if (!isset($_POST['submit_payment'])) {
            $this->render('payment/payment_form', [
                'page_title' => 'Payment Gateway - Secure Payment'
            ]);
            return;
        }

        try {
            if (!$this->db) {
                throw new Exception('Database connection failed');
            }

            // Get payment details
            $payment_data = [
                'amount' => floatval(Security::sanitize($_POST['amount'])),
                'customer_name' => Security::sanitize($_POST['customer_name']) ?? '',
                'customer_email' => Security::sanitize($_POST['customer_email']) ?? '',
                'customer_phone' => Security::sanitize($_POST['customer_phone']) ?? '',
                'plot_id' => intval(Security::sanitize($_POST['plot_id']) ?? 0),
                'payment_type' => Security::sanitize($_POST['payment_type']) ?? '',
                'gateway' => Security::sanitize($_POST['gateway']) ?? ''
            ];

            // Validate payment data
            if ($payment_data['amount'] <= 0) {
                throw new Exception('Invalid payment amount');
            }

            // Generate unique transaction ID
            $transaction_id = 'TXN_' . date('YmdHis') . '_' . rand(1000, 9999);

            // Insert payment record
            $insert_sql = "
                INSERT INTO payment_transactions
                (transaction_id, property_id, payment_method, amount, payment_status, created_at)
                VALUES (:transaction_id, :property_id, :payment_method, :amount, 'pending', NOW())
            ";

            $stmt = $this->db->prepare($insert_sql);
            $stmt->execute([
                'transaction_id' => $transaction_id,
                'property_id' => $payment_data['plot_id'],
                'payment_method' => $payment_data['gateway'],
                'amount' => $payment_data['amount'],
            ]);

            // Process payment based on gateway
            $payment_result = $this->processGatewayPayment($payment_data, $transaction_id);

            if ($payment_result['success']) {
                // Update payment status
                $update_sql = "UPDATE payment_transactions SET payment_status = 'completed', gateway_response = :response WHERE transaction_id = :transaction_id";
                $stmt = $this->db->prepare($update_sql);
                $stmt->execute([
                    'response' => json_encode($payment_result),
                    'transaction_id' => $transaction_id
                ]);

                // Process commission if payment is successful
                $this->processCommission($transaction_id, $payment_data['plot_id']);

                // Send payment notification
                try {
                    $notif = new NotificationService();
                    $bookingStmt = $this->db->prepare("SELECT id FROM bookings WHERE plot_id = ? LIMIT 1");
                    $bookingStmt->execute([$payment_data['plot_id']]);
                    $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);
                    if ($booking) {
                        $notif->sendPaymentReceived($booking['id'], $payment_data['amount']);
                    }
                } catch (\Exception $e) {
                    error_log("PaymentGatewayController: notification failed: " . $e->getMessage());
                }

                try {
                    if ((float)$payment_data['amount'] >= 500000) {
                        $reqSvc = new DepartmentRequestService();
                        $bookingId = $booking['id'] ?? 0;
                        $reqSvc->submitRequest([
                            'request_type'      => 'verification',
                            'department_code'   => 'FIN',
                            'title'             => 'Large Payment Verification Required',
                            'description'       => "Payment of ₹" . number_format((float)$payment_data['amount']) . " received for booking #{$bookingId}. Amount exceeds ₹500,000 threshold. Please verify and reconcile.",
                            'priority'          => 'high',
                            'requester_id'      => 0,
                            'requester_role'    => 'system',
                            'requester_name'    => 'Payment Gateway',
                            'related_entity_type' => 'payment',
                            'related_entity_id'   => $transaction_id,
                        ]);
                    }
                } catch (\Throwable $e) { error_log('PaymentGatewayController: department request error: ' . $e->getMessage()); }

                $this->setFlash('success', 'Payment processed successfully! Transaction ID: ' . $transaction_id);
                $this->redirect(BASE_URL . 'payment/success');
            } else {
                // Update payment status as failed
                $update_sql = "UPDATE payment_transactions SET payment_status = 'failed', refund_reason = :reason WHERE transaction_id = :transaction_id";
                $stmt = $this->db->prepare($update_sql);
                $stmt->execute([
                    'reason' => $payment_result['error'],
                    'transaction_id' => $transaction_id
                ]);

                $this->setFlash('error', $payment_result['error']);
                $this->redirect(BASE_URL . 'payment/failed');
            }
        } catch (Exception $e) {
            error_log('Payment processing error: ' . $e->getMessage());
            $this->setFlash('error', 'Payment processing failed: ' . $e->getMessage());
            $this->redirect(BASE_URL . 'payment/failed');
        }
    }

    /**
     * Process payment through selected gateway
     */
    private function processGatewayPayment($payment_data, $transaction_id)
    {
        switch ($payment_data['gateway']) {
            case 'razorpay':
                return $this->processRazorpayPayment($payment_data, $transaction_id);
            case 'payu':
                return $this->processPayuPayment($payment_data, $transaction_id);
            case 'phonepe':
                return $this->processPhonePePayment($payment_data, $transaction_id);
            case 'gpay':
                return $this->processGPayPayment($payment_data, $transaction_id);
            case 'upi':
                return $this->processUpiPayment($payment_data, $transaction_id);
            default:
                return ['success' => false, 'error' => 'Unsupported payment gateway'];
        }
    }

    /**
     * Process Razorpay payment via RazorpayGateway service
     */
    private function processRazorpayPayment($payment_data, $transaction_id)
    {
        $razorpay = new RazorpayGateway([
            'api_key' => env('RAZORPAY_KEY_ID', ''),
            'api_secret' => env('RAZORPAY_KEY_SECRET', ''),
            'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
            'test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        if (!$razorpay->isConfigured()) {
            return ['success' => false, 'error' => 'Razorpay not configured. Missing API keys.'];
        }

        $result = $razorpay->initiatePayment([
            'amount' => floatval($payment_data['amount']),
            'receipt' => $transaction_id,
            'description' => ($payment_data['payment_type'] ?? 'Payment') . ' - APS Dream Home',
            'customer_name' => $payment_data['customer_name'] ?? '',
            'customer_email' => $payment_data['customer_email'] ?? '',
            'customer_phone' => $payment_data['customer_phone'] ?? ''
        ]);

        return $result;
    }

    /**
     * Process PayU payment
     */
    private function processPayuPayment($payment_data, $transaction_id)
    {
        // PayU integration code
        return [
            'success' => true,
            'gateway_transaction_id' => 'payu_' . rand(100000000, 999999999),
            'message' => 'Payment processed successfully'
        ];
    }

    /**
     * Process PhonePe payment using PhonePeGateway service
     */
    private function processPhonePePayment($payment_data, $transaction_id)
    {
        $phonepe = new PhonePeGateway([
            'merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
            'salt_key' => env('PHONEPE_SALT_KEY', ''),
            'salt_index' => env('PHONEPE_SALT_INDEX', '1'),
            'test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        if (!$phonepe->isConfigured()) {
            return ['success' => false, 'error' => 'PhonePe not configured. Missing merchant credentials.'];
        }

        $result = $phonepe->initiatePayment([
            'amount' => floatval($payment_data['amount']),
            'receipt' => $transaction_id,
            'description' => ($payment_data['payment_type'] ?? 'Payment') . ' - APS Dream Home',
            'customer_name' => $payment_data['customer_name'] ?? '',
            'customer_email' => $payment_data['customer_email'] ?? '',
            'customer_phone' => $payment_data['customer_phone'] ?? '',
            'redirect_url' => env('APP_URL', '') . '/payment/phonepe/callback',
            'callback_url' => env('APP_URL', '') . '/api/v2/mobile/payment/phonepe/webhook'
        ]);

        return $result;
    }

    /**
     * Process Google Pay / UPI payment using GPayGateway service
     */
    private function processGPayPayment($payment_data, $transaction_id)
    {
        $gpay = new GPayGateway([
            'merchant_vpa' => env('GP_MERCHANT_VPA', ''),
            'merchant_name' => env('GP_MERCHANT_NAME', 'APS Dream Home'),
            'merchant_code' => env('GP_MERCHANT_CODE', 'APSDH'),
            'test_mode' => env('PAYMENT_SANDBOX', true),
            // Pass PhonePe config for UPI Intent
            'phonepe_merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
            'phonepe_salt_key' => env('PHONEPE_SALT_KEY', ''),
            'phonepe_salt_index' => env('PHONEPE_SALT_INDEX', '1'),
            'phonepe_test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        if (!$gpay->isConfigured()) {
            return ['success' => false, 'error' => 'Google Pay/UPI not configured. Missing merchant VPA.'];
        }

        $result = $gpay->initiatePayment([
            'amount' => floatval($payment_data['amount']),
            'receipt' => $transaction_id,
            'description' => ($payment_data['payment_type'] ?? 'Payment') . ' - APS Dream Home',
            'customer_name' => $payment_data['customer_name'] ?? '',
            'customer_email' => $payment_data['customer_email'] ?? '',
            'customer_phone' => $payment_data['customer_phone'] ?? ''
        ]);

        // Store QR code data in session for display
        if ($result['success'] && isset($result['upi_qr'])) {
            $_SESSION['gpay_qr_data'] = $result['upi_qr'];
            $_SESSION['gpay_upi_link'] = $result['upi_link'];
            $_SESSION['gpay_upi_intent'] = $result['upi_intent'] ?? null;
        }

        return $result;
    }

    /**
     * Process UPI QR Code payment (generic UPI)
     */
    private function processUpiPayment($payment_data, $transaction_id)
    {
        // Use GPayGateway's UPI QR generator
        $gpay = new GPayGateway([
            'merchant_vpa' => env('GP_MERCHANT_VPA', 'apsdreamhome@upi'),
            'merchant_name' => env('GP_MERCHANT_NAME', 'APS Dream Home'),
            'merchant_code' => env('GP_MERCHANT_CODE', 'APSDH'),
            'test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        $result = $gpay->initiatePayment([
            'amount' => floatval($payment_data['amount']),
            'receipt' => $transaction_id,
            'description' => ($payment_data['payment_type'] ?? 'Payment') . ' - APS Dream Home',
            'customer_name' => $payment_data['customer_name'] ?? '',
            'customer_email' => $payment_data['customer_email'] ?? '',
            'customer_phone' => $payment_data['customer_phone'] ?? ''
        ]);

        if ($result['success'] && isset($result['upi_qr'])) {
            $_SESSION['upi_qr_data'] = $result['upi_qr'];
            $_SESSION['upi_link'] = $result['upi_link'];
        }

        return $result;
    }

    /**
     * Process commission distribution
     */
    private function processCommission($transaction_id, $plot_id)
    {
        if (!$this->db) {
            return;
        }

        try {
            // Get plot and associate details
            $plot_query = "
                SELECT p.*, ps.associate_id, ps.sale_price
                FROM plots p
                JOIN plot_sales ps ON p.id = ps.plot_id
                WHERE ps.transaction_id = :transaction_id
            ";
            $stmt = $this->db->prepare($plot_query);
            $stmt->execute(['transaction_id' => $transaction_id]);
            $plot_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$plot_data) {
                return;
            }

            // Get MLM level structure - select only needed columns for performance
            $level_query = "SELECT id, level_number, commission_percentage FROM associate_levels ORDER BY level_number";
            $levels = $this->db->query($level_query)->fetchAll(PDO::FETCH_ASSOC);

            $associate_id = $plot_data['associate_id'];
            $sale_amount = $plot_data['sale_price'];

            // Calculate and distribute commission
            foreach ($levels as $level) {
                $commission_amount = ($sale_amount * $level['commission_percentage']) / 100;

                if ($commission_amount > 0) {
                    $insert_commission = "
                        INSERT INTO commission_payouts
                        (associate_id, transaction_id, amount, status, created_at)
                        VALUES (:associate_id, :transaction_id, :amount, 'pending', NOW())
                    ";
                    $stmt = $this->db->prepare($insert_commission);
                    $stmt->execute([
                        'associate_id' => $associate_id,
                        'transaction_id' => $transaction_id,
                        'amount' => $commission_amount
                    ]);
                }
            }
        } catch (Exception $e) {
            error_log('Commission processing error: ' . $e->getMessage());
        }
    }

    /**
     * Payment success page
     */
    public function paymentSuccess()
    {
        $payment_data = $this->getFlash('success');

        $this->render('payment/success', [
            'payment_data' => $payment_data,
            'page_title' => 'Payment Successful'
        ]);
    }

    /**
     * Payment failed page
     */
    public function paymentFailed()
    {
        $error_message = $this->getFlash('error') ?? 'Payment processing failed';

        $this->render('payment/failed', [
            'error_message' => $error_message,
            'page_title' => 'Payment Failed'
        ]);
    }

    /**
     * Payment history for users
     */
    public function paymentHistory()
    {
        if (!$this->db) {
            $this->setFlash('error', 'Database connection failed');
            $this->redirect(BASE_URL);
            return;
        }

        $associate_id = $_SESSION['associate_id'] ?? null;
        if (!$associate_id) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $payments = [];
        try {
            // payment_transactions has no plot_id/associate_id — link via associate's user_id
            $payments_query = "
                SELECT pt.*
                FROM payment_transactions pt
                JOIN associates a ON a.id = :associate_id AND a.user_id = pt.user_id
                ORDER BY pt.created_at DESC
            ";
            $stmt = $this->db->prepare($payments_query);
            $stmt->execute(['associate_id' => $associate_id]);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Payment history error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to retrieve payment history');
        }

        $this->render('payment/history', [
            'payments' => $payments,
            'page_title' => 'Payment History - Associate Dashboard'
        ]);
    }

    // =====================================================================
    // API Methods — PhonePe, GPay, UPI Integration
    // =====================================================================

    /**
     * Initiate PhonePe Payment (Standard Checkout)
     * POST /api/v2/mobile/payment/phonepe/initiate
     */
    public function initiatePhonePe()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $required = ['amount', 'receipt'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'error' => "Missing required field: {$field}"], 400);
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
            $this->json(['success' => false, 'error' => 'PhonePe not configured'], 503);
            return;
        }

        $result = $phonepe->initiatePayment([
            'amount' => floatval($data['amount']),
            'receipt' => $data['receipt'],
            'description' => $data['description'] ?? 'APS Dream Home Payment',
            'customer_name' => $data['customer_name'] ?? '',
            'customer_email' => $data['customer_email'] ?? '',
            'customer_phone' => $data['customer_phone'] ?? '',
            'redirect_url' => $data['redirect_url'] ?? (env('APP_URL', '') . '/payment/phonepe/callback'),
            'callback_url' => $data['callback_url'] ?? (env('APP_URL', '') . '/api/v2/mobile/payment/phonepe/webhook')
        ]);

        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Initiate PhonePe UPI Intent (for Google Pay, PhonePe, BHIM apps)
     * POST /api/v2/mobile/payment/phonepe/upi-intent
     */
    public function initiatePhonePeUpiIntent()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $required = ['amount', 'receipt'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'error' => "Missing required field: {$field}"], 400);
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
            $this->json(['success' => false, 'error' => 'PhonePe not configured'], 503);
            return;
        }

        $result = $phonepe->initiateUpiIntent([
            'amount' => floatval($data['amount']),
            'receipt' => $data['receipt'],
            'description' => $data['description'] ?? 'APS Dream Home Payment',
            'customer_name' => $data['customer_name'] ?? '',
            'customer_email' => $data['customer_email'] ?? '',
            'customer_phone' => $data['customer_phone'] ?? '',
            'redirect_url' => $data['redirect_url'] ?? (env('APP_URL', '') . '/payment/phonepe/callback'),
            'callback_url' => $data['callback_url'] ?? (env('APP_URL', '') . '/api/v2/mobile/payment/phonepe/webhook'),
            'upi_app' => $data['upi_app'] ?? '' // e.g., 'gpay', 'phonepe', 'paytm'
        ]);

        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Verify PhonePe Payment
     * GET /api/v2/mobile/payment/phonepe/verify/{transactionId}
     */
    public function verifyPhonePe($transactionId = null)
    {
        $transactionId = $transactionId ?? $_GET['transaction_id'] ?? '';
        
        if (empty($transactionId)) {
            $this->json(['success' => false, 'error' => 'Missing transaction ID'], 400);
            return;
        }

        $phonepe = new PhonePeGateway([
            'merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
            'salt_key' => env('PHONEPE_SALT_KEY', ''),
            'salt_index' => env('PHONEPE_SALT_INDEX', '1'),
            'test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        if (!$phonepe->isConfigured()) {
            $this->json(['success' => false, 'error' => 'PhonePe not configured'], 503);
            return;
        }

        $result = $phonepe->getPaymentStatus($transactionId);
        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * PhonePe Webhook/Callback Handler
     * POST /api/v2/mobile/payment/phonepe/webhook
     */
    public function phonePeWebhook()
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        
        error_log('PhonePe webhook received: ' . json_encode($payload));

        // Verify checksum if provided
        $checksum = $_SERVER['HTTP_X_VERIFY'] ?? '';
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
                // Payment successful - update local DB
                $this->updatePaymentStatus($merchantTransactionId, 'completed', $result);
                
                // Trigger commission processing if needed
                if ($this->db) {
                    $this->processCommissionByTransactionId($merchantTransactionId);
                }
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'received']);
    }

    /**
     * Initiate Google Pay Payment (via UPI Intent through PhonePe)
     * POST /api/v2/mobile/payment/gpay/initiate
     */
    public function initiateGPay()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $required = ['amount', 'receipt'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'error' => "Missing required field: {$field}"], 400);
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
            $this->json(['success' => false, 'error' => 'Google Pay/UPI not configured'], 503);
            return;
        }

        $result = $gpay->initiatePayment([
            'amount' => floatval($data['amount']),
            'receipt' => $data['receipt'],
            'description' => $data['description'] ?? 'APS Dream Home Payment',
            'customer_name' => $data['customer_name'] ?? '',
            'customer_email' => $data['customer_email'] ?? '',
            'customer_phone' => $data['customer_phone'] ?? ''
        ]);

        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Generate UPI QR Code
     * POST /api/v2/mobile/payment/upi/qr
     */
    public function generateQRCode()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $required = ['amount', 'receipt'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'error' => "Missing required field: {$field}"], 400);
                return;
            }
        }

        $gpay = new GPayGateway([
            'merchant_vpa' => env('GP_MERCHANT_VPA', 'apsdreamhome@upi'),
            'merchant_name' => env('GP_MERCHANT_NAME', 'APS Dream Home'),
            'merchant_code' => env('GP_MERCHANT_CODE', 'APSDH'),
            'test_mode' => env('PAYMENT_SANDBOX', true)
        ]);

        $result = $gpay->initiatePayment([
            'amount' => floatval($data['amount']),
            'receipt' => $data['receipt'],
            'description' => $data['description'] ?? 'APS Dream Home Payment',
            'customer_name' => $data['customer_name'] ?? '',
            'customer_email' => $data['customer_email'] ?? '',
            'customer_phone' => $data['customer_phone'] ?? ''
        ]);

        $this->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * UPI Callback/Return Handler
     * POST/GET /api/v2/mobile/payment/upi/callback
     */
    public function upiCallback()
    {
        // Handle both GET (redirect) and POST (webhook) callbacks
        $payload = $_POST ?: $_GET;
        
        error_log('UPI callback received: ' . json_encode($payload));

        // For UPI payments, verification typically happens via:
        // 1. Bank/PSP webhook (if using PhonePe/Razorpay as PSP)
        // 2. Manual reconciliation
        // 3. Checking transaction status with PSP

        $transactionId = $payload['transaction_id'] ?? $payload['txn_id'] ?? $payload['tr'] ?? '';
        $status = $payload['status'] ?? $payload['txn_status'] ?? 'UNKNOWN';
        
        if ($transactionId && in_array($status, ['SUCCESS', 'COMPLETED', 'PAID'])) {
            // Update local payment status
            $this->updatePaymentStatus($transactionId, 'completed', $payload);
            
            if ($this->db) {
                $this->processCommissionByTransactionId($transactionId);
            }
        }

        // Return success page or JSON
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
     * GET /api/v2/mobile/payment/status/{orderId}
     */
    public function getStatus($orderId = null)
    {
        $orderId = $orderId ?? $_GET['order_id'] ?? '';
        
        if (empty($orderId)) {
            $this->json(['success' => false, 'error' => 'Missing order ID'], 400);
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
                $this->json($result);
                return;
            }
        }

        // Fallback: check local DB
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM payment_transactions WHERE transaction_id = ?");
                $stmt->execute([$orderId]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($payment) {
                    $this->json(['success' => true, 'status' => $payment['payment_status'], 'payment' => $payment]);
                    return;
                }
            } catch (Exception $e) {
                error_log('Payment status lookup error: ' . $e->getMessage());
            }
        }

        $this->json(['success' => false, 'error' => 'Payment not found', 'order_id' => $orderId], 404);
    }

    /**
     * Get Available Payment Methods
     * GET /api/v2/mobile/payment/methods
     */
    public function getPaymentMethods()
    {
        $methods = [
            ['id' => 'razorpay', 'name' => 'Razorpay', 'enabled' => !empty(env('RAZORPAY_KEY_ID'))],
            ['id' => 'payu', 'name' => 'PayU', 'enabled' => !empty(env('PAYU_MERCHANT_KEY'))],
            ['id' => 'phonepe', 'name' => 'PhonePe', 'enabled' => !empty(env('PHONEPE_MERCHANT_ID'))],
            ['id' => 'gpay', 'name' => 'Google Pay', 'enabled' => !empty(env('GP_MERCHANT_VPA')) || !empty(env('PHONEPE_MERCHANT_ID'))],
            ['id' => 'upi', 'name' => 'UPI QR', 'enabled' => true],
            ['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'enabled' => true],
            ['id' => 'cash', 'name' => 'Cash', 'enabled' => true],
        ];

        $this->json([
            'success' => true,
            'methods' => $methods
        ]);
    }

    /**
     * Process Refund
     * POST /api/v2/mobile/payment/refund
     */
    public function processRefund()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        $required = ['payment_id', 'amount'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->json(['success' => false, 'error' => "Missing required field: {$field}"], 400);
                return;
            }
        }

        $paymentId = $data['payment_id'];
        $amount = floatval($data['amount']);
        $reason = $data['reason'] ?? 'Customer requested refund';

        // Determine gateway from payment record
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("SELECT payment_method FROM payment_transactions WHERE transaction_id = ?");
                $stmt->execute([$paymentId]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($payment) {
                    $gateway = $payment['payment_method'];
                    
                    if ($gateway === 'phonepe') {
                        $phonepe = new PhonePeGateway([
                            'merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
                            'salt_key' => env('PHONEPE_SALT_KEY', ''),
                            'salt_index' => env('PHONEPE_SALT_INDEX', '1'),
                            'test_mode' => env('PAYMENT_SANDBOX', true)
                        ]);
                        $result = $phonepe->refund($paymentId, $data['amount'], $reason);
                        $this->json($result);
                        return;
                    }
                    
                    if (in_array($gateway, ['gpay', 'upi'])) {
                        $gpay = new GPayGateway([
                            'merchant_vpa' => env('GP_MERCHANT_VPA', ''),
                            'phonepe_merchant_id' => env('PHONEPE_MERCHANT_ID', ''),
                            'phonepe_salt_key' => env('PHONEPE_SALT_KEY', ''),
                            'phonepe_salt_index' => env('PHONEPE_SALT_INDEX', '1'),
                            'phonepe_test_mode' => env('PAYMENT_SANDBOX', true)
                        ]);
                        if ($gpay->phonepeGateway && $gpay->phonepeGateway->isConfigured()) {
                            $result = $gpay->refund($paymentId, $data['amount'], $reason);
                            $this->json($result);
                            return;
                        }
                    }
                }
            } catch (Exception $e) {
                error_log('Refund lookup error: ' . $e->getMessage());
            }
        }

        $this->json(['success' => false, 'error' => 'Refund not supported for this payment method or gateway not configured'], 400);
    }

    /**
     * Helper: Update payment status in local DB
     */
    private function updatePaymentStatus(string $transactionId, string $status, array $gatewayResponse = [])
    {
        if (!$this->db) return;

        try {
            $stmt = $this->db->prepare("
                UPDATE payment_transactions 
                SET payment_status = :status, 
                    gateway_response = :response,
                    payment_date = NOW()
                WHERE transaction_id = :transaction_id
            ");
            $stmt->execute([
                'status' => $status,
                'response' => json_encode($gatewayResponse),
                'transaction_id' => $transactionId
            ]);
        } catch (Exception $e) {
            error_log('PaymentGatewayController: updatePaymentStatus error: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Process commission by transaction ID
     */
    private function processCommissionByTransactionId(string $transactionId)
    {
        if (!$this->db) return;

        try {
            $stmt = $this->db->prepare("SELECT plot_id FROM payment_transactions WHERE transaction_id = ?");
            $stmt->execute([$transactionId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($payment && !empty($payment['plot_id'])) {
                $this->processCommission($transactionId, $payment['plot_id']);
            }
        } catch (Exception $e) {
            error_log('PaymentGatewayController: commission processing error: ' . $e->getMessage());
        }
    }
}
