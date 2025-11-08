<?php
/**
 * Advanced Payment Controller
 * Handles multiple payment gateways and advanced payment features
 */

namespace App\Controllers;

class AdvancedPaymentController extends BaseController {

    private $payment_gateways = [
        'razorpay' => [
            'name' => 'Razorpay',
            'enabled' => true,
            'supports' => ['credit_card', 'debit_card', 'upi', 'net_banking', 'wallet']
        ],
        'paypal' => [
            'name' => 'PayPal',
            'enabled' => false, // Enable when configured
            'supports' => ['paypal', 'credit_card']
        ],
        'stripe' => [
            'name' => 'Stripe',
            'enabled' => false, // Enable when configured
            'supports' => ['credit_card', 'debit_card', 'apple_pay', 'google_pay']
        ],
        'payu' => [
            'name' => 'PayU',
            'enabled' => false, // Enable when configured
            'supports' => ['credit_card', 'debit_card', 'upi', 'net_banking', 'wallet']
        ]
    ];

    /**
     * Payment gateway selection page
     */
    public function gatewaySelection() {
        $this->data['page_title'] = 'Choose Payment Method - ' . APP_NAME;
        $this->data['gateways'] = $this->getEnabledGateways();
        $this->data['order_details'] = $_SESSION['payment_order'] ?? [];

        if (empty($this->data['order_details'])) {
            $this->redirect(BASE_URL);
            return;
        }

        $this->render('payment/gateway_selection');
    }

    /**
     * Process payment with selected gateway
     */
    public function processPayment() {
        try {
            $gateway = $_POST['gateway'] ?? '';
            $payment_method = $_POST['payment_method'] ?? '';

            if (!$this->isValidGateway($gateway)) {
                $this->setFlashMessage('error', 'Invalid payment gateway selected');
                $this->redirect(BASE_URL . 'payment/gateway-selection');
                return;
            }

            if (!$this->isValidPaymentMethod($gateway, $payment_method)) {
                $this->setFlashMessage('error', 'Invalid payment method for selected gateway');
                $this->redirect(BASE_URL . 'payment/gateway-selection');
                return;
            }

            // Process payment based on gateway
            switch ($gateway) {
                case 'razorpay':
                    return $this->processRazorpayPayment($payment_method);
                case 'paypal':
                    return $this->processPayPalPayment($payment_method);
                case 'stripe':
                    return $this->processStripePayment($payment_method);
                case 'payu':
                    return $this->processPayUPayment($payment_method);
                default:
                    throw new \Exception('Unsupported payment gateway');
            }

        } catch (\Exception $e) {
            error_log('Payment processing error: ' . $e->getMessage());
            $this->setFlashMessage('error', 'Payment processing failed: ' . $e->getMessage());
            $this->redirect(BASE_URL . 'payment/gateway-selection');
        }
    }

    /**
     * Process Razorpay payment
     */
    private function processRazorpayPayment($payment_method) {
        try {
            // Initialize Razorpay
            $razorpay = new \App\Core\RazorpayGateway();

            $order_data = $_SESSION['payment_order'];
            $payment_data = [
                'amount' => $order_data['amount'] * 100, // Convert to paise
                'currency' => 'INR',
                'receipt' => 'RC_' . $order_data['order_id'],
                'payment_method' => $payment_method
            ];

            $razorpay_order = $razorpay->createOrder($payment_data);

            // Redirect to Razorpay checkout
            $this->data['razorpay_order'] = $razorpay_order;
            $this->data['gateway'] = 'razorpay';
            $this->render('payment/razorpay_checkout');

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Razorpay payment initialization failed');
            $this->redirect(BASE_URL . 'payment/gateway-selection');
        }
    }

    /**
     * Process PayPal payment
     */
    private function processPayPalPayment($payment_method) {
        try {
            $paypal = new \App\Core\PayPalGateway();

            $order_data = $_SESSION['payment_order'];
            $payment_data = [
                'amount' => $order_data['amount'],
                'currency' => 'USD',
                'description' => $order_data['description'],
                'payment_method' => $payment_method
            ];

            $paypal_response = $paypal->createPayment($payment_data);

            // Redirect to PayPal
            if (isset($paypal_response['approval_url'])) {
                header('Location: ' . $paypal_response['approval_url']);
                exit;
            }

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'PayPal payment initialization failed');
            $this->redirect(BASE_URL . 'payment/gateway-selection');
        }
    }

    /**
     * Process Stripe payment
     */
    private function processStripePayment($payment_method) {
        try {
            $stripe = new \App\Core\StripeGateway();

            $order_data = $_SESSION['payment_order'];
            $payment_data = [
                'amount' => $order_data['amount'] * 100, // Convert to cents
                'currency' => 'inr',
                'payment_method' => $payment_method,
                'description' => $order_data['description']
            ];

            $payment_intent = $stripe->createPaymentIntent($payment_data);

            // Return client secret for Stripe Elements
            $this->data['client_secret'] = $payment_intent['client_secret'];
            $this->data['gateway'] = 'stripe';
            $this->render('payment/stripe_checkout');

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Stripe payment initialization failed');
            $this->redirect(BASE_URL . 'payment/gateway-selection');
        }
    }

    /**
     * Process PayU payment
     */
    private function processPayUPayment($payment_method) {
        try {
            $payu = new \App\Core\PayUGateway();

            $order_data = $_SESSION['payment_order'];
            $payment_data = [
                'amount' => $order_data['amount'],
                'productinfo' => $order_data['description'],
                'firstname' => $_SESSION['user_name'] ?? 'Customer',
                'email' => $_SESSION['user_email'] ?? '',
                'phone' => $_SESSION['user_phone'] ?? '',
                'payment_method' => $payment_method
            ];

            $payu_response = $payu->initiatePayment($payment_data);

            // Redirect to PayU
            if (isset($payu_response['payment_url'])) {
                header('Location: ' . $payu_response['payment_url']);
                exit;
            }

        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'PayU payment initialization failed');
            $this->redirect(BASE_URL . 'payment/gateway-selection');
        }
    }

    /**
     * Handle payment success callback
     */
    public function paymentSuccess() {
        try {
            $gateway = $_GET['gateway'] ?? '';
            $payment_id = $_GET['payment_id'] ?? '';
            $order_id = $_GET['order_id'] ?? '';

            if (empty($gateway) || empty($payment_id) || empty($order_id)) {
                throw new \Exception('Invalid payment callback parameters');
            }

            // Verify payment with gateway
            $payment_verified = $this->verifyPayment($gateway, $payment_id, $order_id);

            if (!$payment_verified['success']) {
                throw new \Exception('Payment verification failed');
            }

            // Update order status
            $this->updateOrderStatus($order_id, 'completed', $payment_verified['transaction_id']);

            // Send confirmation email
            $this->sendPaymentConfirmation($order_id);

            // Clear payment session
            unset($_SESSION['payment_order']);

            $this->data['page_title'] = 'Payment Successful - ' . APP_NAME;
            $this->data['payment_details'] = $payment_verified;
            $this->data['order_id'] = $order_id;

            $this->render('payment/success');

        } catch (\Exception $e) {
            error_log('Payment success handling error: ' . $e->getMessage());
            $this->data['error'] = $e->getMessage();
            $this->render('payment/error');
        }
    }

    /**
     * Handle payment failure callback
     */
    public function paymentFailed() {
        try {
            $gateway = $_GET['gateway'] ?? '';
            $order_id = $_GET['order_id'] ?? '';

            // Update order status to failed
            if (!empty($order_id)) {
                $this->updateOrderStatus($order_id, 'failed');
            }

            // Clear payment session
            unset($_SESSION['payment_order']);

            $this->data['page_title'] = 'Payment Failed - ' . APP_NAME;
            $this->data['error_message'] = 'Payment was cancelled or failed';

            $this->render('payment/failed');

        } catch (\Exception $e) {
            error_log('Payment failure handling error: ' . $e->getMessage());
            $this->render('payment/error');
        }
    }

    /**
     * EMI Calculator
     */
    public function emiCalculator() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $principal = (float)($_POST['principal'] ?? 0);
            $interest_rate = (float)($_POST['interest_rate'] ?? 0);
            $tenure_years = (int)($_POST['tenure_years'] ?? 0);

            if ($principal > 0 && $interest_rate > 0 && $tenure_years > 0) {
                $emi_data = $this->calculateEMI($principal, $interest_rate, $tenure_years);

                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'data' => $emi_data
                ]);
                exit;
            }
        }

        $this->data['page_title'] = 'EMI Calculator - ' . APP_NAME;
        $this->render('payment/emi_calculator');
    }

    /**
     * Get enabled payment gateways
     */
    private function getEnabledGateways() {
        return array_filter($this->payment_gateways, function($gateway) {
            return $gateway['enabled'];
        });
    }

    /**
     * Validate payment gateway
     */
    private function isValidGateway($gateway) {
        return isset($this->payment_gateways[$gateway]) && $this->payment_gateways[$gateway]['enabled'];
    }

    /**
     * Validate payment method for gateway
     */
    private function isValidPaymentMethod($gateway, $payment_method) {
        return in_array($payment_method, $this->payment_gateways[$gateway]['supports']);
    }

    /**
     * Verify payment with gateway
     */
    private function verifyPayment($gateway, $payment_id, $order_id) {
        try {
            switch ($gateway) {
                case 'razorpay':
                    $razorpay = new \App\Core\RazorpayGateway();
                    return $razorpay->verifyPayment($payment_id, $order_id);
                case 'paypal':
                    $paypal = new \App\Core\PayPalGateway();
                    return $paypal->verifyPayment($payment_id, $order_id);
                case 'stripe':
                    $stripe = new \App\Core\StripeGateway();
                    return $stripe->verifyPayment($payment_id, $order_id);
                case 'payu':
                    $payu = new \App\Core\PayUGateway();
                    return $payu->verifyPayment($payment_id, $order_id);
                default:
                    throw new \Exception('Unsupported gateway for verification');
            }
        } catch (\Exception $e) {
            error_log('Payment verification error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update order status
     */
    private function updateOrderStatus($order_id, $status, $transaction_id = null) {
        try {
            global $pdo;

            $sql = "UPDATE orders SET status = ?, transaction_id = ?, updated_at = NOW() WHERE order_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$status, $transaction_id, $order_id]);

            return true;

        } catch (\Exception $e) {
            error_log('Order status update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment confirmation email
     */
    private function sendPaymentConfirmation($order_id) {
        try {
            $emailNotification = new \App\Core\EmailNotification();
            $emailNotification->sendPaymentConfirmation($order_id);

            return true;

        } catch (\Exception $e) {
            error_log('Payment confirmation email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Calculate EMI
     */
    private function calculateEMI($principal, $annual_interest_rate, $tenure_years) {
        $monthly_rate = $annual_interest_rate / (12 * 100);
        $tenure_months = $tenure_years * 12;

        if ($monthly_rate == 0) {
            $monthly_emi = $principal / $tenure_months;
        } else {
            $monthly_emi = $principal * $monthly_rate * pow(1 + $monthly_rate, $tenure_months) /
                          (pow(1 + $monthly_rate, $tenure_months) - 1);
        }

        $total_amount = $monthly_emi * $tenure_months;
        $total_interest = $total_amount - $principal;

        return [
            'principal' => $principal,
            'monthly_emi' => round($monthly_emi, 2),
            'total_interest' => round($total_interest, 2),
            'total_amount' => round($total_amount, 2),
            'tenure_months' => $tenure_months,
            'interest_rate' => $annual_interest_rate
        ];
    }

    /**
     * Get payment history
     */
    public function paymentHistory() {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        try {
            global $pdo;

            $sql = "SELECT o.*, p.title as property_title, p.city, p.state
                    FROM orders o
                    LEFT JOIN properties p ON o.property_id = p.id
                    WHERE o.user_id = ?
                    ORDER BY o.created_at DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);

            $payments = $stmt->fetchAll();

            $this->data['page_title'] = 'Payment History - ' . APP_NAME;
            $this->data['payments'] = $payments;

            $this->render('payment/history');

        } catch (\Exception $e) {
            error_log('Payment history error: ' . $e->getMessage());
            $this->setFlashMessage('error', 'Failed to load payment history');
            $this->redirect(BASE_URL . 'dashboard');
        }
    }

    /**
     * Download payment receipt
     */
    public function downloadReceipt($order_id) {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        try {
            global $pdo;

            $sql = "SELECT o.*, p.title as property_title, p.city, p.state,
                           u.name as customer_name, u.email as customer_email
                    FROM orders o
                    LEFT JOIN properties p ON o.property_id = p.id
                    LEFT JOIN users u ON o.user_id = u.id
                    WHERE o.order_id = ? AND o.user_id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$order_id, $_SESSION['user_id']]);

            $payment = $stmt->fetch();

            if (!$payment) {
                $this->setFlashMessage('error', 'Payment receipt not found');
                $this->redirect(BASE_URL . 'payment/history');
                return;
            }

            // Generate PDF receipt
            $receipt_data = [
                'order_id' => $payment['order_id'],
                'customer_name' => $payment['customer_name'],
                'property_title' => $payment['property_title'],
                'amount' => $payment['amount'],
                'payment_date' => $payment['created_at'],
                'transaction_id' => $payment['transaction_id'],
                'payment_method' => $payment['payment_method']
            ];

            // For now, redirect to a receipt view
            // In production, generate actual PDF
            $this->data['receipt_data'] = $receipt_data;
            $this->render('payment/receipt');

        } catch (\Exception $e) {
            error_log('Receipt download error: ' . $e->getMessage());
            $this->setFlashMessage('error', 'Failed to generate receipt');
            $this->redirect(BASE_URL . 'payment/history');
        }
    }

    /**
     * Admin - Payment analytics
     */
    public function adminPaymentAnalytics() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        try {
            global $pdo;

            // Payment trends
            $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                           COUNT(*) as total_payments,
                           SUM(amount) as total_amount,
                           AVG(amount) as avg_amount
                    FROM orders
                    WHERE status = 'completed'
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month DESC
                    LIMIT 12";

            $stmt = $pdo->query($sql);
            $payment_trends = $stmt->fetchAll();

            // Payment methods distribution
            $sql = "SELECT payment_method, COUNT(*) as count, SUM(amount) as total
                    FROM orders
                    WHERE status = 'completed'
                    GROUP BY payment_method";

            $stmt = $pdo->query($sql);
            $payment_methods = $stmt->fetchAll();

            // Gateway performance
            $sql = "SELECT gateway, COUNT(*) as transactions, SUM(amount) as volume,
                           AVG(amount) as avg_transaction
                    FROM orders
                    WHERE status = 'completed'
                    GROUP BY gateway";

            $stmt = $pdo->query($sql);
            $gateway_performance = $stmt->fetchAll();

            $this->data['page_title'] = 'Payment Analytics - ' . APP_NAME;
            $this->data['payment_trends'] = $payment_trends;
            $this->data['payment_methods'] = $payment_methods;
            $this->data['gateway_performance'] = $gateway_performance;

            $this->render('admin/payment_analytics');

        } catch (\Exception $e) {
            error_log('Payment analytics error: ' . $e->getMessage());
            $this->setFlashMessage('error', 'Failed to load payment analytics');
            $this->redirect(BASE_URL . 'admin');
        }
    }
}
