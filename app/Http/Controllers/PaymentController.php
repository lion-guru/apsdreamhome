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
        if (session_status() === PHP_SESSION_NONE) session_start();

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
                    "color": "#4f46e5"
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
        if (session_status() === PHP_SESSION_NONE) session_start();

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
            'propertyPrice' => $propertyPrice,
            'interestRate' => $interestRate,
            'tenureYears' => $tenureYears,
            'emi' => round($emi, 2),
            'totalPayment' => round($totalPayment, 2),
            'totalInterest' => round($totalInterest, 2),
            'base' => $base
        ]);
    }

    public function history()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $db = Database::getInstance();
        $payments = $db->fetchAll(
            "SELECT p.*, b.property_id, pr.title as property_title 
             FROM payments p 
             LEFT JOIN bookings b ON p.booking_id = b.id 
             LEFT JOIN properties pr ON b.property_id = pr.id 
             WHERE p.user_id = ? 
             ORDER BY p.created_at DESC",
            [$userId]
        );

        $this->render('payments/history', [
            'payments' => $payments,
            'base' => BASE_URL
        ]);
    }

    // ========== Additional routes ==========

    public function index()
    {
        $this->render('payments/index', [
            'page_title' => 'Payments - APS Dream Home',
            'base' => BASE_URL
        ]);
    }

    public function initiate()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

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
            'page_title' => 'Initiate Payment',
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
        if (session_status() === PHP_SESSION_NONE) session_start();
        $error = $_SESSION['error'] ?? 'Payment was cancelled or failed';
        unset($_SESSION['error']);

        $this->render('payments/failure', [
            'page_title' => 'Payment Failed',
            'error' => $error,
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
            'page_title' => 'Payment Plans - APS Dream Home',
            'base' => BASE_URL
        ]);
    }

    public function refund()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $paymentId = $_POST['payment_id'] ?? '';
            $_SESSION['success'] = "Refund initiated for payment #$paymentId";
            header('Location: ' . BASE_URL . '/payment/history');
            exit;
        }

        $this->render('payments/refund', [
            'page_title' => 'Request Refund',
            'base' => BASE_URL
        ]);
    }

    public function settings()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
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
            'page_title' => 'Payment Settings',
            'base' => BASE_URL
        ]);
    }
}
