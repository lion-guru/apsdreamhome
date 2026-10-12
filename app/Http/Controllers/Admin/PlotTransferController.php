<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use App\Services\PlotTransferService;
use App\Services\MaintenanceBillingService;

/**
 * Admin Plot Transfer & Resale NOC Desk
 * Oversees resale, family gifts, and ownership transfer requests with
 * audit-ready NOC certification and verification QR.
 */
class PlotTransferController extends AdminController
{
    private PlotTransferService $transferService;

    public function __construct()
    {
        parent::__construct();
        $this->transferService = new PlotTransferService();
    }

    public function index()
    {
        $this->requireAdmin();
        $db = Database::getInstance();

        $status = trim((string)($_GET['status'] ?? 'all'));
        $search = trim((string)($_GET['q'] ?? ''));

        $where = ["1=1"];
        $params = [];

        if ($status !== 'all' && in_array($status, ['requested', 'under_review', 'approved', 'completed', 'rejected'], true)) {
            $where[] = "t.status = ?";
            $params[] = $status;
        }

        if ($search !== '') {
            $where[] = "(t.noc_number LIKE ? OR t.new_buyer_name LIKE ? OR t.new_buyer_phone LIKE ? OR p.plot_number LIKE ? OR u.name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }

        $whereSql = implode(' AND ', $where);

        $transfers = $db->fetchAll(
            "SELECT t.*, p.plot_number, p.area_sqft, c.name AS colony_name,
                    u.name AS current_customer_name, u.phone AS current_customer_phone,
                    b.booking_number
             FROM plot_transfers t
             JOIN plots p ON p.id = t.plot_id
             JOIN colonies c ON c.id = p.colony_id
             LEFT JOIN users u ON u.id = t.from_customer_id
             LEFT JOIN plot_bookings b ON b.id = t.booking_id
             WHERE {$whereSql}
             ORDER BY t.created_at DESC",
            $params
        ) ?: [];

        $counts = [
            'all' => (int)$db->fetchColumn("SELECT COUNT(*) FROM plot_transfers"),
            'requested' => (int)$db->fetchColumn("SELECT COUNT(*) FROM plot_transfers WHERE status = 'requested'"),
            'approved' => (int)$db->fetchColumn("SELECT COUNT(*) FROM plot_transfers WHERE status = 'approved'"),
            'completed' => (int)$db->fetchColumn("SELECT COUNT(*) FROM plot_transfers WHERE status = 'completed'"),
            'rejected' => (int)$db->fetchColumn("SELECT COUNT(*) FROM plot_transfers WHERE status = 'rejected'"),
        ];

        $this->render('admin/transfers/index', [
            'page_title' => 'Plot Transfer & Resale NOC Desk',
            'transfers' => $transfers,
            'counts' => $counts,
            'current_status' => $status,
            'search' => $search,
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        $db = Database::getInstance();

        $transfer = $db->fetchRow(
            "SELECT t.*, p.plot_number, p.area_sqft, p.rate_per_sqft, p.total_price,
                    c.name AS colony_name, c.id AS colony_id,
                    u.name AS current_customer_name, u.phone AS current_customer_phone, u.email AS current_customer_email,
                    u2.name AS new_customer_user_name,
                    b.booking_number, b.total_amount, b.id AS booking_id
             FROM plot_transfers t
             JOIN plots p ON p.id = t.plot_id
             JOIN colonies c ON c.id = p.colony_id
             LEFT JOIN users u ON u.id = t.from_customer_id
             LEFT JOIN users u2 ON u2.id = t.to_customer_id
             LEFT JOIN plot_bookings b ON b.id = t.booking_id
             WHERE t.id = ?",
            [(int)$id]
        );

        if (!$transfer) {
            $this->setFlash('error', 'Transfer application not found.');
            return $this->redirect('/admin/plot-transfers');
        }

        // Live eligibility re-check
        $eligibility = $transfer['booking_id']
            ? $this->transferService->checkTransferEligibility((int)$transfer['booking_id'])
            : ['eligible' => true, 'reasons' => []];

        $this->render('admin/transfers/show', [
            'page_title' => 'Review Transfer #' . $transfer['id'],
            'transfer' => $transfer,
            'eligibility' => $eligibility,
        ]);
    }

    public function approve($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $transferFee = max(0, (float)($_POST['transfer_fee'] ?? 0));
        $remarks = trim((string)($_POST['remarks'] ?? ''));
        $adminId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 1);

        $res = $this->transferService->approveTransfer((int)$id, $adminId, $transferFee, $remarks);

        if ($res['success']) {
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('error', $res['message']);
        }

        return $this->redirect('/admin/plot-transfers/' . $id);
    }

    public function execute($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $adminId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 1);
        $res = $this->transferService->executeTransfer((int)$id, $adminId);

        if ($res['success']) {
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('error', $res['message']);
        }

        return $this->redirect('/admin/plot-transfers/' . $id);
    }

    public function reject($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $reason = trim((string)($_POST['rejection_reason'] ?? 'Requirements or documents incomplete'));
        $adminId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 1);

        $res = $this->transferService->rejectTransfer((int)$id, $adminId, $reason);
        $this->setFlash('success', $res['message']);

        return $this->redirect('/admin/plot-transfers/' . $id);
    }

    /**
     * Printable official Transfer NOC Certificate & Re-allotment Document
     */
    public function nocCertificate($id)
    {
        $this->requireAdmin();
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
             WHERE t.id = ?",
            [(int)$id]
        );

        if (!$transfer || empty($transfer['noc_number'])) {
            $this->setFlash('error', 'Valid approved NOC record not found.');
            return $this->redirect('/admin/plot-transfers');
        }

        $base = defined('BASE_URL') ? BASE_URL : '';
        $qrVerifyUrl = "{$base}/verify/transfer/" . urlencode($transfer['noc_number']);

        $this->render('admin/transfers/noc_print', [
            'page_title' => 'Transfer NOC — ' . $transfer['noc_number'],
            'transfer' => $transfer,
            'verify_url' => $qrVerifyUrl,
        ]);
    }
}
