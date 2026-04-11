<?php
/**
 * Payment Controller
 * Handles Razorpay integration and callbacks
 */

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
    
    /**
     * Initialize payment for booking
     */
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
        
        // Initialize payment
        $result = $this->razorpayService->processBookingPayment($bookingId, $userId, $amount);
        
        if (!$result['success']) {
            $_SESSION['error'] = $result['error'];
            header('Location: ' . BASE_URL . '/bookings');
            exit;
        }
        
        // Render checkout page
        $this->renderCheckout($result);
    }
    
    /**
     * Razorpay checkout page
     */
    private function renderCheckout($paymentData)
    {
        $base = BASE_URL;
        $orderId = $paymentData['order_id'];
        $amount = $paymentData['amount'];
        $keyId = $paymentData['key_id'];
        
        echo <<<HTML
<!DOCTYPE html>
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
                        <h2 class="text-primary mb-4">₹{$amount}</h2>
                        <p class="text-muted mb-4">Click below to pay securely via Razorpay</p>
                        <button id="payBtn" class="btn btn-primary btn-lg">
                            <i class="fas fa-lock me-2"></i>Pay Now
                        </button>
                        <a href="{$base}/bookings" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('payBtn').onclick = function(e) {
            var options = {
                "key": "{$keyId}",
                "amount": {$amount}00,
                "currency": "INR",
                "name": "APS Dream Home",
                "description": "Property Booking Payment",
                "order_id": "{$orderId}",
                "handler": function(response) {
                    // Redirect to success handler
                    window.location.href = "{$base}/payment/success?" + 
                        "payment_id=" + response.razorpay_payment_id +
                        "&order_id=" + response.razorpay_order_id +
                        "&signature=" + response.razorpay_signature;
                },
                "prefill": {
                    "name": "{$_SESSION['user_name'] ?? 'Customer'}",
                    "email": "{$_SESSION['user_email'] ?? ''}",
                    "contact": "{$_SESSION['user_phone'] ?? ''}"
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
</html>
HTML;
        exit;
    }
    
    /**
     * Handle payment success callback
     */
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
        
        // Process payment success
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
    
    /**
     * EMI Calculator page
     */
    public function emiCalculator()
    {
        $base = BASE_URL;
        
        // Calculate EMI for sample amounts
        $propertyPrice = $_GET['amount'] ?? 5000000;
        $interestRate = 8.5; // 8.5% per annum
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
    
    /**
     * Payment history
     */
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
}
