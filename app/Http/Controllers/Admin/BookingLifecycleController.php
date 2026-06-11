<?php
/**
 * Module 2: Customer Sales + Allotment + Registry
 *
 * BookingLifecycleController
 *
 * Thin admin controller over BookingLifecycleService. 20 actions covering:
 *  - dashboard, bookings list, detail
 *  - create / edit booking
 *  - payment schedule view + regenerate
 *  - record payment
 *  - demand letter view
 *  - cancel + refund
 *  - transfer
 *  - commissions list
 *  - refunds list
 *  - RERA compliance log
 *
 * URL prefix: /admin/sales/*
 * All actions require admin auth and use aps-cp-* layout via $this->render().
 */

namespace App\Http\Controllers\Admin;

use App\Services\Sales\BookingLifecycleService;
use App\Services\Sales\BookingLifecycleService as Service; // explicit alias
use App\Services\Sales\BookingLifecycleService as Svc;     // short alias
use Exception;

class BookingLifecycleController extends AdminController
{
    /** @var \PDO */
    protected $db;

    /** @var BookingLifecycleService */
    protected $service;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->db = \App\Core\Database\Database::getInstance();
            if (method_exists($this->db, 'getPdo')) {
                $this->db = $this->db->getPdo();
            }
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());

            $this->db = null;
        }
        try {
            $this->service = new BookingLifecycleService(
                $this->db instanceof \PDO ? $this->db : null
            );
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());

            $this->service = new BookingLifecycleService();
        }
    }

    /* =========================================================
     *  Dashboard
     * ========================================================= */

    public function index()
    {
        $this->requireAdmin();
        $stats = $this->service->getDashboardStats();
        $recent = $this->service->listBookings(['per_page' => 10]);
        $overdue = $this->service->getOverdueInstallments();

        $this->render('admin/sales/dashboard', [
            'page_title'   => 'Sales Dashboard',
            'page_heading' => 'Customer Sales + Allotment — Dashboard',
            'stats'        => $stats,
            'recent'       => $recent['data'] ?? [],
            'overdue'      => array_slice($overdue, 0, 10),
        ]);
    }

    /* =========================================================
     *  Bookings list
     * ========================================================= */

    public function bookings()
    {
        $this->requireAdmin();
        $filters = [
            'status'    => $_GET['status']   ?? '',
            'search'    => $_GET['search']   ?? '',
            'date_from' => $_GET['date_from']?? '',
            'date_to'   => $_GET['date_to']  ?? '',
            'page'      => (int)($_GET['page'] ?? 1),
            'per_page'  => 20,
        ];
        $result = $this->service->listBookings($filters);

        $this->render('admin/sales/bookings', [
            'page_title'   => 'Bookings',
            'page_heading' => 'Plot Bookings',
            'bookings'     => $result['data'] ?? [],
            'pagination'   => $result,
            'filters'      => $filters,
            'statuses'     => BookingLifecycleService::STATUSES,
        ]);
    }

    /* =========================================================
     *  Booking detail
     * ========================================================= */

    public function bookingDetail($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $booking = $this->service->getBookingById($id);
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/admin/sales/bookings');
        }
        $schedule = $this->service->getPaymentSchedule($id);
        $receipts = $this->fetchReceipts($id);
        $demandLetters = $this->fetchDemandLetters($id);
        $docs = $this->fetchDocuments($id);
        $history = $this->fetchStatusHistory($id);
        $commissions = $this->fetchCommissions($id);

        $this->render('admin/sales/booking-detail', [
            'page_title'     => 'Booking #' . $id,
            'page_heading'   => 'Booking — ' . htmlspecialchars((string)($booking['booking_number'] ?? '')),
            'booking'        => $booking,
            'schedule'       => $schedule,
            'receipts'       => $receipts,
            'demand_letters' => $demandLetters,
            'documents'      => $docs,
            'history'        => $history,
            'commissions'    => $commissions,
        ]);
    }

    /* =========================================================
     *  Create booking
     * ========================================================= */

    public function createBookingForm($plotId = null)
    {
        $this->requireAdmin();
        $plots      = $this->fetchAvailablePlots();
        $customers  = $this->fetchCustomers();
        $associates = $this->fetchAssociates();
        $salesManagers = $this->fetchSalesManagers();

        $this->render('admin/sales/booking-form', [
            'page_title'     => 'New Booking',
            'page_heading'   => 'Create New Plot Booking',
            'mode'           => 'create',
            'booking'        => ['plot_id' => (int)$plotId],
            'plots'          => $plots,
            'customers'      => $customers,
            'associates'     => $associates,
            'sales_managers' => $salesManagers,
        ]);
    }

    public function createBookingStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = [
            'plot_id'          => $_POST['plot_id']          ?? 0,
            'customer_id'      => $_POST['customer_id']      ?? 0,
            'total_plot_value' => $_POST['total_plot_value'] ?? 0,
            'booking_amount'   => $_POST['booking_amount']   ?? 0,
            'agreement_value'  => $_POST['agreement_value']  ?? 0,
            'channel'          => $_POST['channel']          ?? 'direct',
            'associate_id'     => $_POST['associate_id']     ?? 0,
            'sales_manager_id' => $_POST['sales_manager_id'] ?? 0,
            'commission_pct'   => $_POST['commission_pct']   ?? 0,
            'notes'            => $_POST['notes']            ?? '',
        ];
        $result = $this->service->createBooking($data);
        if (!empty($result['success'])) {
            $this->setFlash('success', 'Booking ' . ($result['booking_number'] ?? '') . ' created');
            return $this->redirect('/admin/sales/bookings/' . $result['id']);
        }
        $this->setFlash('error', $result['error'] ?? 'Failed to create booking');
        return $this->redirect('/admin/sales/bookings/new');
    }

    /* =========================================================
     *  Edit booking
     * ========================================================= */

    public function editBooking($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $booking = $this->service->getBookingById($id);
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/admin/sales/bookings');
        }
        $this->render('admin/sales/booking-form', [
            'page_title'     => 'Edit Booking',
            'page_heading'   => 'Edit Booking — ' . htmlspecialchars((string)($booking['booking_number'] ?? '')),
            'mode'           => 'edit',
            'booking'        => $booking,
            'plots'          => $this->fetchAvailablePlots(true),
            'customers'      => $this->fetchCustomers(),
            'associates'     => $this->fetchAssociates(),
            'sales_managers' => $this->fetchSalesManagers(),
        ]);
    }

    public function updateBooking($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;
        $booking = $this->service->getBookingById($id);
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/admin/sales/bookings');
        }
        try {
            $sql = "UPDATE plot_bookings SET
                        booking_amount   = ?,
                        agreement_value  = ?,
                        channel          = ?,
                        associate_id     = ?,
                        sales_manager_id = ?,
                        commission_pct   = ?,
                        notes            = ?
                    WHERE id = ?";
            $this->db->prepare($sql)->execute([
                (float)($_POST['booking_amount']   ?? $booking['booking_amount']),
                (float)($_POST['agreement_value']  ?? $booking['agreement_value']),
                (string)($_POST['channel']         ?? $booking['channel']),
                !empty($_POST['associate_id'])     ? (int)$_POST['associate_id']     : null,
                !empty($_POST['sales_manager_id']) ? (int)$_POST['sales_manager_id'] : null,
                (float)($_POST['commission_pct']   ?? $booking['commission_pct']),
                (string)($_POST['notes']           ?? $booking['notes']),
                $id,
            ]);
            $this->setFlash('success', 'Booking updated');
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());

            $this->setFlash('error', 'Update failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/sales/bookings/' . $id);
    }

    /* =========================================================
     *  Payment schedule
     * ========================================================= */

    public function paymentSchedule($bookingId)
    {
        $this->requireAdmin();
        $bookingId = (int)$bookingId;
        $booking = $this->service->getBookingById($bookingId);
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/admin/sales/bookings');
        }
        $this->render('admin/sales/payment-schedule', [
            'page_title'   => 'Payment Schedule',
            'page_heading' => 'EMI Schedule — ' . htmlspecialchars((string)($booking['booking_number'] ?? '')),
            'booking'      => $booking,
            'schedule'     => $this->service->getPaymentSchedule($bookingId),
        ]);
    }

    public function regenerateSchedule($bookingId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $bookingId = (int)$bookingId;
        $months = (int)($_POST['tenure_months']  ?? 0);
        $rate   = (float)($_POST['rate_per_annum'] ?? 0);
        $result = $this->service->generatePaymentSchedule($bookingId, $months, $rate);
        if (!empty($result['success'])) {
            $this->setFlash('success', 'Schedule regenerated: ' . count($result['installments']) . ' installments');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to regenerate schedule');
        }
        return $this->redirect('/admin/sales/bookings/' . $bookingId . '/schedule');
    }

    /* =========================================================
     *  Record payment
     * ========================================================= */

    public function recordPaymentForm($installmentId)
    {
        $this->requireAdmin();
        $installmentId = (int)$installmentId;
        $inst = $this->fetchInstallment($installmentId);
        if (!$inst) {
            $this->setFlash('error', 'Installment not found');
            return $this->redirect('/admin/sales/bookings');
        }
        $booking = $this->service->getBookingById((int)$inst['booking_id']);
        $this->render('admin/sales/payment-form', [
            'page_title'   => 'Record Payment',
            'page_heading' => 'Collect Installment — #' . $installmentId,
            'installment'  => $inst,
            'booking'      => $booking,
        ]);
    }

    public function recordPaymentStore($installmentId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $installmentId = (int)$installmentId;
        $data = [
            'amount'          => $_POST['amount']         ?? 0,
            'payment_mode'    => $_POST['payment_mode']   ?? 'cash',
            'cheque_number'   => $_POST['cheque_number']  ?? '',
            'cheque_date'     => $_POST['cheque_date']    ?? '',
            'bank_name'       => $_POST['bank_name']      ?? '',
            'transaction_ref' => $_POST['transaction_ref']?? '',
            'notes'           => $_POST['notes']          ?? '',
            'status'          => $_POST['status']         ?? 'cleared',
            'paid_date'       => $_POST['paid_date']      ?? date('Y-m-d'),
            'collected_by'    => $_SESSION['admin_id']    ?? null,
        ];
        $result = $this->service->recordPayment($installmentId, $data);
        if (!empty($result['success'])) {
            $this->setFlash('success', 'Receipt ' . ($result['receipt_number'] ?? '') . ' created');
            $inst = $this->fetchInstallment($installmentId);
            return $this->redirect('/admin/sales/bookings/' . ($inst['booking_id'] ?? 0));
        }
        $this->setFlash('error', $result['error'] ?? 'Failed to record payment');
        return $this->redirect('/admin/sales/installments/' . $installmentId . '/pay');
    }

    /* =========================================================
     *  Demand letter
     * ========================================================= */

    public function demandLetter($installmentId)
    {
        $this->requireAdmin();
        $installmentId = (int)$installmentId;
        $inst = $this->fetchInstallment($installmentId);
        if (!$inst) {
            $this->setFlash('error', 'Installment not found');
            return $this->redirect('/admin/sales/bookings');
        }
        $booking = $this->service->getBookingById((int)$inst['booking_id']);
        $letter = null;
        try {
            $row = $this->db->prepare(
                "SELECT * FROM booking_demand_letters WHERE installment_id = ? ORDER BY id DESC LIMIT 1"
            );
            $row->execute([$installmentId]);
            $letter = $row->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
}
        $this->render('admin/sales/demand-letter', [
            'page_title'   => 'Demand Letter',
            'page_heading' => 'Demand Letter — Installment #' . $installmentId,
            'installment'  => $inst,
            'booking'      => $booking,
            'letter'       => $letter,
        ]);
    }

    /* =========================================================
     *  Cancel booking
     * ========================================================= */

    public function cancelBookingForm($bookingId)
    {
        $this->requireAdmin();
        $bookingId = (int)$bookingId;
        $booking = $this->service->getBookingById($bookingId);
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/admin/sales/bookings');
        }
        $this->render('admin/sales/cancel-form', [
            'page_title'   => 'Cancel Booking',
            'page_heading' => 'Cancel Booking — ' . htmlspecialchars((string)($booking['booking_number'] ?? '')),
            'booking'      => $booking,
        ]);
    }

    public function cancelBookingStore($bookingId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $bookingId = (int)$bookingId;
        $reason = (string)($_POST['reason'] ?? 'Customer request');
        $charge = (float)($_POST['cancellation_charge'] ?? 0);
        $result = $this->service->cancelBooking($bookingId, $reason, $charge);
        if (!empty($result['success'])) {
            $this->setFlash('success', 'Booking cancelled. Refund #' . ($result['refund_id'] ?? '') . ' queued.');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Cancellation failed');
        }
        return $this->redirect('/admin/sales/bookings/' . $bookingId);
    }

    /* =========================================================
     *  Transfer booking
     * ========================================================= */

    public function transferBookingForm($bookingId)
    {
        $this->requireAdmin();
        $bookingId = (int)$bookingId;
        $booking = $this->service->getBookingById($bookingId);
        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/admin/sales/bookings');
        }
        $this->render('admin/sales/transfer-form', [
            'page_title'   => 'Transfer Booking',
            'page_heading' => 'Transfer Booking — ' . htmlspecialchars((string)($booking['booking_number'] ?? '')),
            'booking'      => $booking,
            'customers'    => $this->fetchCustomers(),
        ]);
    }

    public function transferBookingStore($bookingId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $bookingId = (int)$bookingId;
        $newCustomerId = (int)($_POST['new_customer_id'] ?? 0);
        $reason   = (string)($_POST['reason']   ?? 'Ownership transfer');
        $charge   = (float)($_POST['transfer_charge'] ?? 0);
        $result = $this->service->transferBooking($bookingId, $newCustomerId, $reason, $charge);
        if (!empty($result['success'])) {
            $this->setFlash('success', 'Transfer initiated (ID: ' . ($result['transfer_id'] ?? '') . ')');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Transfer failed');
        }
        return $this->redirect('/admin/sales/bookings/' . $bookingId);
    }

    /* =========================================================
     *  Commissions
     * ========================================================= */

    public function commissions()
    {
        $this->requireAdmin();
        $rows = [];
        $summary = ['total' => 0.0, 'pending' => 0.0, 'paid' => 0.0, 'count' => 0];
        try {
            $stmt = $this->db->query(
                "SELECT bc.*, pb.booking_number, u.name AS beneficiary_name
                 FROM booking_commissions bc
                 JOIN plot_bookings pb ON pb.id = bc.booking_id
                 LEFT JOIN users u ON u.id = bc.beneficiary_user_id
                 ORDER BY bc.id DESC LIMIT 200"
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $summary['count']++;
                $summary['total'] += (float)$r['amount'];
                if ($r['status'] === 'paid' || $r['status'] === 'approved') {
                    $summary['paid'] += (float)$r['amount'];
                } else {
                    $summary['pending'] += (float)$r['amount'];
                }
            }
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
}
        $this->render('admin/sales/commissions', [
            'page_title'   => 'Commissions',
            'page_heading' => 'Sales Commissions',
            'commissions'  => $rows,
            'summary'      => $summary,
        ]);
    }

    /* =========================================================
     *  Refunds
     * ========================================================= */

    public function refunds()
    {
        $this->requireAdmin();
        $rows = [];
        $summary = ['total' => 0.0, 'pending' => 0, 'processed' => 0];
        try {
            $stmt = $this->db->query(
                "SELECT r.*, pb.booking_number, u.name AS customer_name
                 FROM booking_refunds r
                 JOIN plot_bookings pb ON pb.id = r.booking_id
                 LEFT JOIN users u ON u.id = pb.customer_id
                 ORDER BY r.id DESC LIMIT 200"
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $summary['total'] += (float)$r['refund_amount'];
                if ($r['status'] === 'processed' || $r['status'] === 'approved') {
                    $summary['processed']++;
                } else {
                    $summary['pending']++;
                }
            }
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
}
        $this->render('admin/sales/refunds', [
            'page_title'   => 'Refunds',
            'page_heading' => 'Booking Refunds',
            'refunds'      => $rows,
            'summary'      => $summary,
        ]);
    }

    /* =========================================================
     *  RERA compliance
     * ========================================================= */

    public function reraCompliance()
    {
        $this->requireAdmin();
        $colonies = $this->fetchColonies();
        $selectedColony = (int)($_GET['colony_id'] ?? ($colonies[0]['id'] ?? 0));
        $rows = $selectedColony > 0
            ? $this->service->getReraCompliance($selectedColony)
            : [];
        $this->render('admin/sales/rera-compliance', [
            'page_title'     => 'RERA Compliance',
            'page_heading'   => 'RERA 70% Escrow + Quarterly Progress',
            'colonies'       => $colonies,
            'selected_colony' => $selectedColony,
            'rows'           => $rows,
        ]);
    }

    public function reraComplianceStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $colonyId  = (int)($_POST['colony_id']   ?? 0);
        $year      = (int)($_POST['year']        ?? date('Y'));
        $quarter   = (string)($_POST['quarter']  ?? 'Q1');
        $progress  = (float)($_POST['progress'] ?? 0);
        $withdrawn = (float)($_POST['withdrawn']?? 0);
        $result = $this->service->updateReraCompliance($colonyId, $year, $quarter, $progress, $withdrawn);
        if (!empty($result['success'])) {
            $this->setFlash('success', 'RERA compliance recorded');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed');
        }
        return $this->redirect('/admin/sales/rera?colony_id=' . $colonyId);
    }

    /* =========================================================
     *  Registry / NOC check
     * ========================================================= */

    public function registryCheck($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $mwSvc = new \App\Services\Accounting\MoneyWorkflowService();
        $eligibility = $mwSvc->checkRegistryEligibility($id);
        $booking = $eligibility['booking'] ?? null;

        if (!$booking) {
            $this->setFlash('error', 'Booking not found');
            return $this->redirect('/admin/sales/bookings');
        }

        $this->render('admin/sales/registry_check', [
            'page_title'   => 'Registry / NOC Check',
            'page_heading' => 'Registry / NOC — ' . htmlspecialchars((string)($booking['booking_number'] ?? '')),
            'booking'      => $booking,
            'eligibility'  => $eligibility,
        ]);
    }

    public function generateNoc($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;

        $mwSvc = new \App\Services\Accounting\MoneyWorkflowService();
        $generatedBy = (int)($_SESSION['admin_id'] ?? 1);
        $result = $mwSvc->generateNoc($id, $generatedBy);

        if (!empty($result['success'])) {
            $this->json([
                'success'    => true,
                'noc_id'     => $result['noc_id'],
                'noc_number' => $result['noc_number'],
                'generated_at' => $result['generated_at'],
            ]);
        } else {
            $this->json([
                'success' => false,
                'error'   => $result['error'] ?? 'Failed to generate NOC',
                'reasons' => $result['reasons'] ?? [],
            ], 422);
        }
    }

    /* =========================================================
     *  Helper data fetchers (private)
     * ========================================================= */

    private function fetchReceipts(int $bookingId): array
    {
        try {
            $s = $this->db->prepare(
                "SELECT * FROM booking_payment_receipts
                 WHERE booking_id = ? ORDER BY receipt_date DESC, id DESC"
            );
            $s->execute([$bookingId]);
            return $s->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
 return []; }
    }

    private function fetchDemandLetters(int $bookingId): array
    {
        try {
            $s = $this->db->prepare(
                "SELECT * FROM booking_demand_letters
                 WHERE booking_id = ? ORDER BY generated_date DESC, id DESC"
            );
            $s->execute([$bookingId]);
            return $s->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
 return []; }
    }

    private function fetchDocuments(int $bookingId): array
    {
        try {
            $s = $this->db->prepare(
                "SELECT * FROM booking_documents
                 WHERE booking_id = ? ORDER BY id DESC"
            );
            $s->execute([$bookingId]);
            return $s->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
 return []; }
    }

    private function fetchStatusHistory(int $bookingId): array
    {
        try {
            $s = $this->db->prepare(
                "SELECT bsh.*, u.name AS changed_by_name
                 FROM booking_status_history bsh
                 LEFT JOIN users u ON u.id = bsh.changed_by
                 WHERE bsh.booking_id = ?
                 ORDER BY bsh.id DESC LIMIT 50"
            );
            $s->execute([$bookingId]);
            return $s->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
 return []; }
    }

    private function fetchCommissions(int $bookingId): array
    {
        try {
            $s = $this->db->prepare(
                "SELECT bc.*, u.name AS beneficiary_name
                 FROM booking_commissions bc
                 LEFT JOIN users u ON u.id = bc.beneficiary_user_id
                 WHERE bc.booking_id = ?
                 ORDER BY bc.level ASC, bc.id ASC"
            );
            $s->execute([$bookingId]);
            return $s->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
 return []; }
    }

    private function fetchInstallment(int $id): ?array
    {
        try {
            $s = $this->db->prepare("SELECT * FROM booking_payment_schedules WHERE id = ?");
            $s->execute([$id]);
            $row = $s->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
 return null; }
    }

    private function fetchAvailablePlots(bool $includeBooked = false): array
    {
        try {
            $sql = "SELECT p.id, p.plot_number, p.plot_code, p.area_sqft, p.total_price, c.name AS colony_name
                    FROM plots p
                    LEFT JOIN colonies c ON c.id = p.colony_id
                    " . ($includeBooked ? '' : "WHERE p.status = 'available'") . "
                    ORDER BY c.name, p.plot_number
                    LIMIT 200";
            return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
 return []; }
    }

    private function fetchCustomers(): array
    {
        try {
            $s = $this->db->prepare(
                "SELECT id, name, email, phone FROM users
                 WHERE role = 'customer'
                 ORDER BY name LIMIT 500"
            );
            $s->execute();
            return $s->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
 return []; }
    }

    private function fetchAssociates(): array
    {
        try {
            return $this->db->query(
                "SELECT id, name, email, phone FROM associates
                 WHERE status = 'active' ORDER BY name LIMIT 200"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
 return []; }
    }

    private function fetchSalesManagers(): array
    {
        try {
            $s = $this->db->prepare(
                "SELECT id, name, email FROM users
                 WHERE role IN ('admin','manager','employee')
                 ORDER BY name LIMIT 100"
            );
            $s->execute();
            return $s->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[{$className}] {$methodName}() exception: " . $e->getMessage());
 return []; }
    }

    private function fetchColonies(): array
    {
        try {
            return $this->db->query(
                "SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            try {
                return $this->db->query("SELECT id, name FROM colonies ORDER BY name LIMIT 50")
                                ->fetchAll(\PDO::FETCH_ASSOC);
            } catch (Exception $e2) {
                error_log("[{$className}] {$methodName}() exception: " . $e2->getMessage());
 return []; }
        }
    }
}
