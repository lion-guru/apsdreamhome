<?php

namespace App\Http\Controllers;

use App\Core\Database\Database;
use App\Services\Payment\RazorpayService;

class PaymentController extends BaseController
{
    private $razorpayService;

    public function __construct()
    {
        parent::__construct();
        $this->razorpayService = new RazorpayService();
    }

    public function initPayment()
    {
        @session_start();

        $bookingId = $_POST['booking_id'] ?? null;
        $amount = $_POST['amount'] ?? 0;
        $userId = $_SESSION['user_id'] ?? null;

        if (!$bookingId || !$amount || !$userId) {
            $_SESSION['error'] = "Invalid payment request";
            header('Location: ' . BASE_URL . '/bookings');
            exit;
        }

        $result = $this->razorpayService->processBookingPayment($bookingId, $userId, $amount);

        if (!$result['success']) {
            $_SESSION['error'] = $result['error'];
            header('Location: ' . BASE_URL . '/bookings');
            exit;
        }

        $this->renderCheckout($result);
    }

    private function renderCheckout($paymentData)
    {
        $base = BASE_URL;
        $orderId = $paymentData['order_id'];
        $amount = $paymentData['amount'];
        $keyId = $paymentData['key_id'];
        $userName = $_SESSION['user_name'] ?? 'Customer';
        $userEmail = $_SESSION['user_email'] ?? '';
        $userPhone = $_SESSION['user_phone'] ?? '';

        echo '<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | APS Dream Home</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body text-center p-5">
                        <h3 class="mb-4">Complete Your Payment</h3>
                        <h2 class="text-primary mb-4">₹' . $amount . '</h2>
                        <p class="text-muted mb-4">Click below to pay securely via Razorpay</p>
                        <button id="payBtn" class="btn btn-primary btn-lg">
                            <i class="fas fa-lock me-2"></i>Pay Now
                        </button>
                        <a href="' . $base . '/bookings" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById(\'payBtn\').onclick = function(e) {
            var options = {
                "key": "' . $keyId . '",
                "amount": ' . $amount . '00,
                "currency": "INR",
                "name": "APS Dream Home",
                "description": "Property Booking Payment",
                "order_id": "' . $orderId . '",
                "handler": function(response) {
                    window.location.href = "' . $base . '/payment/success?" + 
                        "payment_id=" + response.razorpay_payment_id +
                        "&order_id=" + response.razorpay_order_id +
                        "&signature=" + response.razorpay_signature;
                },
                "prefill": {
                    "name": "' . htmlspecialchars($userName) . '",
                    "email": "' . htmlspecialchars($userEmail) . '",
                    "contact": "' . htmlspecialchars($userPhone) . '"
                },
                "theme": {
                    "color": "#0d9488"
                }
            };
            var rzp = new Razorpay(options);
            rzp.open();
        };
    </script>
</body>
</html>';
        exit;
    }

    public function success()
    {
        @session_start();

        $paymentId = $_GET['payment_id'] ?? null;
        $orderId = $_GET['order_id'] ?? null;
        $signature = $_GET['signature'] ?? null;

        if (!$paymentId || !$orderId || !$signature) {
            $_SESSION['error'] = "Invalid payment response";
            header('Location: ' . BASE_URL . '/bookings');
            exit;
        }

        $result = $this->razorpayService->handlePaymentSuccess($paymentId, $orderId, $signature);

        if ($result['success']) {
            $_SESSION['success'] = "Payment successful! Your booking is confirmed.";
            header('Location: ' . BASE_URL . '/bookings/' . $result['booking_id']);
        } else {
            $_SESSION['error'] = "Payment verification failed: " . $result['error'];
            header('Location: ' . BASE_URL . '/bookings');
        }
        exit;
    }

    public function emiCalculator()
    {
        $base = BASE_URL;
        $propertyPrice = $_GET['amount'] ?? 5000000;
        $interestRate = 8.5;
        $tenureYears = $_GET['years'] ?? 20;

        $monthlyRate = $interestRate / (12 * 100);
        $numPayments = $tenureYears * 12;

        $emi = ($propertyPrice * $monthlyRate * pow(1 + $monthlyRate, $numPayments)) /
            (pow(1 + $monthlyRate, $numPayments) - 1);

        $totalPayment = $emi * $numPayments;
        $totalInterest = $totalPayment - $propertyPrice;

        $this->render('payments/emi_calculator', [
            'result' => [
                'emi' => round($emi, 2),
                'total_interest' => round($totalInterest, 2),
                'total_payment' => round($totalPayment, 2),
            ],
            'base' => $base
        ]);
    }

    public function history()
    {
        @session_start();

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $db = Database::getInstance();
        $payments = $db->fetchAll(
            "SELECT p.*, pr.title as property_title 
             FROM payments p 
             LEFT JOIN properties pr ON p.property_id = pr.id 
             WHERE p.customer_id = ? 
             ORDER BY p.created_at DESC",
            [$userId]
        ) ?: [];

        $this->render('payments/history', [
            'transactions' => $payments,
            'filters' => ['status' => $_GET['status'] ?? '', 'gateway' => $_GET['gateway'] ?? '', 'from' => $_GET['from'] ?? '', 'to' => $_GET['to'] ?? ''],
            'base' => BASE_URL
        ]);
    }

    // ========== Additional routes ==========

    public function index()
    {
        @session_start();

        try {
            $db = Database::getInstance();
            $payments = $db->fetchAll(
                "SELECT p.*, pr.title as property_title 
                 FROM payments p 
                 LEFT JOIN properties pr ON p.property_id = pr.id 
                 ORDER BY p.created_at DESC LIMIT 20"
            ) ?: [];

            $stats = $db->fetch(
                "SELECT 
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as total_received,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as total_pending,
                    COALESCE(SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END), 0) as total_refunded
                 FROM payments"
            ) ?: [];
        } catch (\Exception $e) {
            $payments = [];
            $stats = [];
        }

        $this->render('payments/index', [
            'pageTitle' => 'Payments - APS Dream Home',
            'payments' => $payments,
            'totalReceived' => $stats['total_received'] ?? 0,
            'totalPending' => $stats['total_pending'] ?? 0,
            'totalRefunded' => $stats['total_refunded'] ?? 0,
            'base' => BASE_URL
        ]);
    }

    public function initiate()
    {
        @session_start();

        $amount = $_POST['amount'] ?? ($_GET['amount'] ?? 0);
        $purpose = $_POST['purpose'] ?? ($_GET['purpose'] ?? 'booking');
        $userId = $_SESSION['user_id'] ?? null;

        if (!$amount || !$userId) {
            $_SESSION['error'] = "Invalid payment request";
            header('Location: ' . BASE_URL . '/payment');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
            $this->initPayment();
            return;
        }

        $this->render('payments/initiate', [
            'pageTitle' => 'Initiate Payment',
            'amount' => $amount,
            'purpose' => $purpose,
            'base' => BASE_URL
        ]);
    }

    public function process()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Direct processing not supported']);
    }

    public function failure()
    {
        @session_start();
        $error = $_SESSION['error'] ?? 'Payment was cancelled or failed';
        unset($_SESSION['error']);

        $this->render('payments/failure', [
            'pageTitle' => 'Payment Failed',
            'errorMessage' => $error,
            'base' => BASE_URL
        ]);
    }

    public function webhook()
    {
        header('Content-Type: application/json');
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);

        $logFile = __DIR__ . '/../../storage/logs/payments.log';
        file_put_contents($logFile, date('Y-m-d H:i:s') . " WEBHOOK: " . $payload . PHP_EOL, FILE_APPEND);

        echo json_encode(['success' => true, 'message' => 'Webhook received']);
    }

    public function plans()
    {
        $this->render('payments/plans', [
            'pageTitle' => 'Payment Plans - APS Dream Home',
            'base' => BASE_URL
        ]);
    }

    public function refund()
    {
        @session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentId = $_POST['payment_id'] ?? '';
            $_SESSION['success'] = "Refund initiated for payment #$paymentId";
            header('Location: ' . BASE_URL . '/payment/history');
            exit;
        }

        $this->render('payments/refund', [
            'pageTitle' => 'Request Refund',
            'base' => BASE_URL
        ]);
    }

    public function settings()
    {
        @session_start();
        $userId = $_SESSION['user_id'] ?? null;

        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['success'] = "Payment settings updated";
            header('Location: ' . BASE_URL . '/payment/settings');
            exit;
        }

        $this->render('payments/settings', [
            'pageTitle' => 'Payment Settings',
            'base' => BASE_URL
        ]);
    }
}
