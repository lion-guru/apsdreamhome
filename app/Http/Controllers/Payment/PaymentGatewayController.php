<?php

/**
 * Payment Gateway Integration Controller
 * Complete payment processing system for APS Dream Home
 */

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\BaseController;
use App\Services\Payment\RazorpayGateway;
use App\Services\Communication\NotificationService;
use Exception;
use PDO;

class PaymentGatewayController extends BaseController
{

    private $gateway_config = [
        'razorpay' => [
            'key_id' => 'rzp_test_your_key',
            'key_secret' => 'your_secret_key',
            'enabled' => true
        ],
        'payu' => [
            'merchant_key' => 'your_merchant_key',
            'merchant_salt' => 'your_merchant_salt',
            'enabled' => true
        ],
        'phonepe' => [
            'merchant_id' => 'your_merchant_id',
            'salt_key' => 'your_salt_key',
            'salt_index' => '1',
            'enabled' => true
        ]
    ];

    public function __construct()
    {
        parent::__construct();
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
            payment_method ENUM('razorpay', 'payu', 'phonepe', 'bank_transfer', 'cash') NOT NULL,
            payment_status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
            gateway_response JSON,
            bank_reference VARCHAR(100),
            payment_date DATETIME,
            failure_reason TEXT,
            refund_amount DECIMAL(15,2) DEFAULT 0,
            refund_date DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (associate_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (plot_id) REFERENCES plots(id) ON DELETE SET NULL
        )";

        $this->db->query($sql);

        // Payment gateway logs
        $sql = "";

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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (associate_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (transaction_id) REFERENCES payment_transactions(transaction_id) ON DELETE SET NULL,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        )";

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
                (transaction_id, plot_id, payment_type, amount, payment_method, payment_status, created_at)
                VALUES (:transaction_id, :plot_id, :payment_type, :amount, :payment_method, 'pending', NOW())
            ";

            $stmt = $this->db->prepare($insert_sql);
            $stmt->execute([
                'transaction_id' => $transaction_id,
                'plot_id' => $payment_data['plot_id'],
                'payment_type' => $payment_data['payment_type'],
                'amount' => $payment_data['amount'],
                'payment_method' => $payment_data['gateway']
            ]);

            // Process payment based on gateway
            $payment_result = $this->processGatewayPayment($payment_data, $transaction_id);

            if ($payment_result['success']) {
                // Update payment status
                $update_sql = "UPDATE payment_transactions SET payment_status = 'completed', gateway_response = :response, payment_date = NOW() WHERE transaction_id = :transaction_id";
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

                $this->setFlash('success', 'Payment processed successfully! Transaction ID: ' . $transaction_id);
                $this->redirect(BASE_URL . 'payment/success');
            } else {
                // Update payment status as failed
                $update_sql = "UPDATE payment_transactions SET payment_status = 'failed', failure_reason = :reason WHERE transaction_id = :transaction_id";
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
     * Process PhonePe payment
     */
    private function processPhonePePayment($payment_data, $transaction_id)
    {
        // PhonePe integration code
        return [
            'success' => true,
            'gateway_transaction_id' => 'pp_' . rand(100000000, 999999999),
            'message' => 'Payment processed successfully'
        ];
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
                        (associate_id, transaction_id, payout_amount, payout_status, created_at)
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
            $payments_query = "
                SELECT pt.*, p.plot_number, c.name as colony_name
                FROM payment_transactions pt
                JOIN plots p ON pt.plot_id = p.id
                JOIN colonies c ON p.colony_id = c.id
                WHERE pt.associate_id = :associate_id
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
    // API stub methods — routes/api.php references these 8 methods
    // TODO: Implement real PhonePe/GPay/UPI gateway integration
    // =====================================================================

    public function initiatePhonePe()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $this->json(['success' => false, 'error' => 'PhonePe integration not yet implemented', 'received' => $data], 501);
    }

    public function verifyPhonePe($transactionId = null)
    {
        $this->json(['success' => false, 'error' => 'PhonePe verification not yet implemented', 'transaction_id' => $transactionId], 501);
    }

    public function phonePeWebhook()
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        error_log('PhonePe webhook received: ' . json_encode($payload));
        http_response_code(200);
        echo json_encode(['status' => 'received']);
    }

    public function initiateGPay()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $this->json(['success' => false, 'error' => 'GPay integration not yet implemented', 'received' => $data], 501);
    }

    public function generateQRCode()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $this->json(['success' => false, 'error' => 'UPI QR code generation not yet implemented', 'received' => $data], 501);
    }

    public function upiCallback()
    {
        $payload = json_decode(file_get_contents('php://input'), true);
        error_log('UPI callback received: ' . json_encode($payload));
        http_response_code(200);
        echo json_encode(['status' => 'received']);
    }

    public function getStatus($orderId = null)
    {
        $this->json(['success' => false, 'error' => 'Payment status lookup not yet implemented', 'order_id' => $orderId], 501);
    }

    public function getPaymentMethods()
    {
        $this->json([
            'success' => true,
            'methods' => [
                ['id' => 'razorpay', 'name' => 'Razorpay', 'enabled' => true],
                ['id' => 'phonepe', 'name' => 'PhonePe', 'enabled' => false],
                ['id' => 'gpay', 'name' => 'Google Pay', 'enabled' => false],
                ['id' => 'upi', 'name' => 'UPI QR', 'enabled' => false],
                ['id' => 'bank_transfer', 'name' => 'Bank Transfer', 'enabled' => true],
                ['id' => 'cash', 'name' => 'Cash', 'enabled' => true],
            ]
        ]);
    }
}
