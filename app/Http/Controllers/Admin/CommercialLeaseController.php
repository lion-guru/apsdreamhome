<?php

namespace App\Http\Controllers\Admin;

use App\Services\CommercialLeaseService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Commercial Lease & Retail Shop Rent Roll Desk
 */
class CommercialLeaseController extends AdminController
{
    private CommercialLeaseService $leaseService;

    public function __construct()
    {
        parent::__construct();
        $this->leaseService = new CommercialLeaseService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;
        $status = trim((string)($_GET['status'] ?? 'all'));

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $sql = "SELECT cl.*, c.name AS colony_name
                FROM commercial_leases cl
                LEFT JOIN colonies c ON cl.colony_id = c.id
                WHERE 1=1";
        $params = [];

        if ($colonyId) {
            $sql .= " AND cl.colony_id = ?";
            $params[] = $colonyId;
        }
        if ($status !== 'all' && in_array($status, ['active', 'notice_period', 'terminated', 'expired'], true)) {
            $sql .= " AND cl.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY cl.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $leases = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stats = $this->leaseService->getRentRollStats();
        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->render('admin/commercial_leases/index', [
            'page_title' => 'Commercial Shop Leases & Rent Roll Desk',
            'leases' => $leases,
            'stats' => $stats,
            'colonies' => $colonies,
            'current_colony' => $colonyId,
            'current_status' => $status
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'tenant_id' => 1,
            'colony_id' => (int)($_POST['colony_id'] ?? 0),
            'unit_number' => trim((string)($_POST['unit_number'] ?? '')),
            'unit_type' => trim((string)($_POST['unit_type'] ?? 'retail_shop')),
            'carpet_area_sqft' => (float)($_POST['carpet_area_sqft'] ?? 0.0),
            'tenant_name' => trim((string)($_POST['tenant_name'] ?? '')),
            'tenant_business_name' => trim((string)($_POST['tenant_business_name'] ?? '')),
            'tenant_phone' => trim((string)($_POST['tenant_phone'] ?? '')),
            'tenant_email' => trim((string)($_POST['tenant_email'] ?? '')),
            'tenant_gstin' => trim((string)($_POST['tenant_gstin'] ?? '')),
            'monthly_rent' => (float)($_POST['monthly_rent'] ?? 0.0),
            'security_deposit' => (float)($_POST['security_deposit'] ?? 0.0),
            'annual_escalation_pct' => (float)($_POST['annual_escalation_pct'] ?? 5.0),
            'lock_in_months' => (int)($_POST['lock_in_months'] ?? 12),
            'lease_start_date' => !empty($_POST['lease_start_date']) ? $_POST['lease_start_date'] : date('Y-m-d'),
            'lease_end_date' => !empty($_POST['lease_end_date']) ? $_POST['lease_end_date'] : date('Y-m-d', strtotime('+11 months')),
            'electricity_submeter_reading' => (float)($_POST['electricity_submeter_reading'] ?? 0.0)
        ];

        $res = $this->leaseService->createLease($data);
        if ($res['success']) {
            $_SESSION['flash_success'] = "Commercial lease agreement created: {$res['lease_token']}";
        } else {
            $_SESSION['flash_error'] = $res['message'];
        }

        $this->redirect('/admin/commercial-leases');
    }

    public function show($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $lease = $this->leaseService->getLeaseDetails($id);
        if (!$lease) {
            $_SESSION['flash_error'] = 'Lease not found.';
            $this->redirect('/admin/commercial-leases');
        }

        $invoices = $this->leaseService->getLeaseInvoices($id);

        $this->render('admin/commercial_leases/show', [
            'page_title' => 'Commercial Lease — ' . htmlspecialchars($lease['unit_number']) . ' (' . htmlspecialchars($lease['tenant_name']) . ')',
            'lease' => $lease,
            'invoices' => $invoices
        ]);
    }

    public function generateInvoice($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $id = (int)$id;
        $month = trim((string)($_POST['billing_month'] ?? date('Y-m')));
        $options = [
            'electricity_units' => (float)($_POST['electricity_units'] ?? 0.0),
            'electricity_rate' => (float)($_POST['electricity_rate'] ?? 9.50),
            'maintenance_fee' => (float)($_POST['maintenance_fee'] ?? 0.0)
        ];

        $res = $this->leaseService->generateInvoice($id, $month, $options);
        if ($res['success']) {
            $_SESSION['flash_success'] = $res['message'];
        } else {
            $_SESSION['flash_error'] = $res['message'];
        }

        $this->redirect('/admin/commercial-leases/' . $id);
    }

    public function payInvoice($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $invId = (int)$id;
        $leaseId = (int)($_POST['lease_id'] ?? 0);
        $amount = (float)($_POST['paid_amount'] ?? 0.0);
        $mode = trim((string)($_POST['payment_mode'] ?? 'upi'));
        $ref = trim((string)($_POST['payment_ref'] ?? ''));
        $notes = trim((string)($_POST['notes'] ?? ''));

        $ok = $this->leaseService->recordPayment($invId, $amount, $mode, $ref, $notes);
        if ($ok) {
            $_SESSION['flash_success'] = 'Payment of ₹' . number_format($amount, 2) . ' recorded successfully.';
        } else {
            $_SESSION['flash_error'] = 'Failed to record payment.';
        }

        if ($leaseId > 0) {
            $this->redirect('/admin/commercial-leases/' . $leaseId);
        } else {
            $this->redirect('/admin/commercial-leases');
        }
    }

    public function printInvoice($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $invoice = $this->leaseService->getInvoiceDetails($id);
        if (!$invoice) {
            http_response_code(404);
            echo "Invoice not found.";
            exit;
        }

        $this->view('admin/commercial_leases/invoice_print', [
            'invoice' => $invoice,
            'page_title' => 'Commercial Rent Invoice #' . $invoice['invoice_no']
        ]);
    }
}
