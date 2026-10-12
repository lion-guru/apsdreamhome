<?php

namespace App\Http\Controllers\Front;

use App\Core\Database\Database;
use App\Services\PlotTransferService;

/**
 * Customer Resale & Plot Transfer Desk
 * Allows buyers to request ownership transfers (resale / family gift)
 * and view / print issued Transfer NOCs.
 */
class CustomerTransferController extends PageController
{
    private PlotTransferService $transferService;

    public function __construct()
    {
        parent::__construct();
        $this->transferService = new PlotTransferService();
    }

    private function requireCustomer(): int
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

        return (int)$_SESSION['user_id'];
    }

    public function index()
    {
        $cid = $this->requireCustomer();
        $db = Database::getInstance();

        // 1. Existing transfer requests by this customer
        $transfers = $db->fetchAll(
            "SELECT t.*, p.plot_number, p.area_sqft, c.name AS colony_name, b.booking_number
             FROM plot_transfers t
             JOIN plots p ON p.id = t.plot_id
             JOIN colonies c ON c.id = p.colony_id
             LEFT JOIN plot_bookings b ON b.id = t.booking_id
             WHERE t.from_customer_id = ?
             ORDER BY t.created_at DESC",
            [$cid]
        ) ?: [];

        // 2. Active bookings belonging to this customer eligible to request transfer
        $bookings = $db->fetchAll(
            "SELECT b.*, p.plot_number, p.area_sqft, c.name AS colony_name
             FROM plot_bookings b
             JOIN plots p ON p.id = b.plot_id
             JOIN colonies c ON c.id = p.colony_id
             WHERE b.customer_id = ? AND b.status IN ('token_paid', 'agreement_signed', 'emi_active', 'partially_paid', 'fully_paid', 'registration_done', 'active', 'booked')
             ORDER BY b.id DESC",
            [$cid]
        ) ?: [];

        // Evaluate eligibility for each booking
        $eligibleList = [];
        foreach ($bookings as $b) {
            $el = $this->transferService->checkTransferEligibility((int)$b['id']);
            $b['transfer_eligible'] = $el['eligible'];
            $b['transfer_reasons'] = $el['reasons'];
            $eligibleList[] = $b;
        }

        $this->render('customer/transfers/index', [
            'page_title' => 'Plot Resale & Transfer Desk',
            'transfers' => $transfers,
            'bookings' => $eligibleList,
        ]);
    }

    public function applyForm($bookingId)
    {
        $cid = $this->requireCustomer();
        $db = Database::getInstance();

        $booking = $db->fetchRow(
            "SELECT b.*, p.plot_number, p.area_sqft, p.dimensions, c.name AS colony_name
             FROM plot_bookings b
             JOIN plots p ON p.id = b.plot_id
             JOIN colonies c ON c.id = p.colony_id
             WHERE b.id = ? AND b.customer_id = ?",
            [(int)$bookingId, $cid]
        );

        if (!$booking) {
            $this->setFlash('error', 'Booking not found or not owned by your account.');
            return $this->redirect('/customer/plot-transfers');
        }

        $eligibility = $this->transferService->checkTransferEligibility((int)$bookingId);

        $this->render('customer/transfers/apply', [
            'page_title' => 'Apply for Plot Ownership Transfer',
            'booking' => $booking,
            'eligibility' => $eligibility,
        ]);
    }

    public function applyStore()
    {
        $cid = $this->requireCustomer();
        $this->validateCsrfOrFail();

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $db = Database::getInstance();

        // Security ownership check
        $ownerCheck = $db->fetchRow(
            "SELECT id FROM plot_bookings WHERE id = ? AND customer_id = ?",
            [$bookingId, $cid]
        );

        if (!$ownerCheck) {
            $this->setFlash('error', 'Unauthorized: You can only apply for plots you own.');
            return $this->redirect('/customer/plot-transfers');
        }

        $postData = [
            'booking_id' => $bookingId,
            'new_buyer_name' => trim((string)($_POST['new_buyer_name'] ?? '')),
            'new_buyer_phone' => trim((string)($_POST['new_buyer_phone'] ?? '')),
            'new_buyer_aadhaar' => trim((string)($_POST['new_buyer_aadhaar'] ?? '')),
            'new_buyer_pan' => trim((string)($_POST['new_buyer_pan'] ?? '')),
            'new_buyer_address' => trim((string)($_POST['new_buyer_address'] ?? '')),
            'transfer_type' => $_POST['transfer_type'] ?? 'resale',
            'transfer_amount' => (float)($_POST['transfer_amount'] ?? 0),
            'transfer_reason' => trim((string)($_POST['transfer_reason'] ?? '')),
        ];

        $res = $this->transferService->applyTransfer($postData);

        if ($res['success']) {
            $this->setFlash('success', 'Transfer application submitted successfully. Admin review in progress.');
        } else {
            $this->setFlash('error', $res['message']);
        }

        return $this->redirect('/customer/plot-transfers');
    }

    public function nocView($id)
    {
        $cid = $this->requireCustomer();
        $db = Database::getInstance();

        $transfer = $db->fetchRow(
            "SELECT t.*, p.plot_number, p.area_sqft, p.dimensions,
                    c.name AS colony_name, c.location AS colony_location,
                    u.name AS current_customer_name, u.phone AS current_customer_phone,
                    b.booking_number
             FROM plot_transfers t
             JOIN plots p ON p.id = t.plot_id
             JOIN colonies c ON c.id = p.colony_id
             LEFT JOIN users u ON u.id = t.from_customer_id
             LEFT JOIN plot_bookings b ON b.id = t.booking_id
             WHERE t.id = ? AND (t.from_customer_id = ? OR t.to_customer_id = ?)",
            [(int)$id, $cid, $cid]
        );

        if (!$transfer || empty($transfer['noc_number'])) {
            $this->setFlash('error', 'Approved NOC not found.');
            return $this->redirect('/customer/plot-transfers');
        }

        $base = defined('BASE_URL') ? BASE_URL : '';
        $verifyUrl = "{$base}/verify/transfer/" . urlencode($transfer['noc_number']);

        $this->render('admin/transfers/noc_print', [
            'page_title' => 'Transfer NOC — ' . $transfer['noc_number'],
            'transfer' => $transfer,
            'verify_url' => $verifyUrl,
        ]);
    }
}
