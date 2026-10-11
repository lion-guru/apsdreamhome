<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\ForeclosureService;

class ForeclosureController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    protected $foreclosureService;

    public function __construct()
    {
        parent::__construct();
        $this->foreclosureService = new \App\Services\ForeclosureService();
    }

    /**
     * Ensure customer is logged in. Redirect to login if not.
     */
    private function requireCustomer(): void
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }

        $allowedRoles = ['', 'customer', 'user'];
        $role = $_SESSION['role'] ?? '';

        if (!in_array($role, $allowedRoles, true)) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }

    /**
     * GET /customer/foreclosure?booking_id=X
     * Shows foreclosure preview with rebate and UPI QR
     */
    public function preview(): void
    {
        $this->requireCustomer();
        $bookingId = (int)($_GET['booking_id'] ?? 0);

        if ($bookingId <= 0) {
            $_SESSION['error'] = 'Invalid booking';
            $this->redirect('/customer/passbook');
            return;
        }

        // Verify booking belongs to this customer
        $booking = $this->db->fetchOne(
            "SELECT id, booking_number, total_amount, customer_id FROM plot_bookings 
             WHERE id = ? AND customer_id = ?",
            [$bookingId, $_SESSION['user_id']]
        );

        if (!$booking) {
            $_SESSION['error'] = 'Booking not found';
            $this->redirect('/customer/passbook');
            return;
        }

        $foreclosureService = new \App\Services\ForeclosureService();
        $foreclosure = $foreclosureService->calculateForeclosure($bookingId);
        $foreclosure['booking_id'] = $bookingId;
        $foreclosure['booking_number'] = $booking['booking_number'];

        if ($foreclosure['final_amount'] <= 0) {
            $_SESSION['error'] = 'No pending installments to foreclose';
            $this->redirect('/customer/passbook?booking_id=' . $bookingId);
            return;
        }

        // Generate UPI QR
        $upi = $foreclosureService->generateForeclosureUpiQr($foreclosure);

        $this->layout = 'layouts/customer';
        $this->render('customer/foreclosure_preview', [
            'page_title' => 'Foreclosure Preview',
            'booking' => $booking,
            'foreclosure' => $foreclosure,
            'upi' => $upi,
        ]);
    }

    /**
     * POST /customer/foreclosure/process
     * Processes the foreclosure payment
     */
    public function process(): void
    {
        $this->requireCustomer();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            exit;
        }

        // CSRF check
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $_SESSION['error'] = 'Invalid or expired security token. Please try again.';
            $this->redirect('/customer/foreclosure?booking_id=' . (int)($_POST['booking_id'] ?? 0));
            return;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $paymentMethod = $_POST['payment_method'] ?? 'upi';
        $transactionId = $_POST['transaction_id'] ?? 'FC' . time() . rand(1000, 9999);

        $foreclosureService = new \App\Services\ForeclosureService();
        $result = $foreclosureService->processForeclosure([
            'booking_id' => $bookingId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'customer_id' => $_SESSION['user_id'],
        ]);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }

        $this->redirect('/customer/passbook?booking_id=' . $bookingId);
    }

    /**
     * AJAX endpoint to generate UPI QR for foreclosure
     * POST /customer/foreclosure/upi-qr
     */
    public function upiQr(): void
    {
        header('Content-Type: application/json');
        
        $bookingId = (int)($_POST['booking_id'] ?? 0);
        if ($bookingId <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid booking'], 400);
            return;
        }

        $foreclosureService = new \App\Services\ForeclosureService();
        $foreclosure = $foreclosureService->calculateForeclosure($bookingId);
        $foreclosure['booking_id'] = $bookingId;

        if ($foreclosure['final_amount'] <= 0) {
            $this->jsonResponse(['success' => false, 'error' => 'No pending installments'], 400);
            return;
        }

        $upi = $foreclosureService->generateForeclosureUpiQr($foreclosure);
        $this->jsonResponse(['success' => true, 'data' => $upi]);
    }
}