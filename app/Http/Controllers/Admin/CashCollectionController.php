<?php

namespace App\Http\Controllers\Admin;

use App\Services\CashCollectionService;
use App\Services\AuditService;

class CashCollectionController extends AdminController
{
    protected $service;
    protected $audit;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new CashCollectionService($this->db); } catch (\Throwable $e) { $this->service = null; }
        try { $this->audit = new AuditService($this->db); } catch (\Throwable $e) { $this->audit = null; }
    }

    public function index()
    {
        $status = $_GET['status'] ?? '';
        $collectorId = !empty($_GET['collector_id']) ? (int)$_GET['collector_id'] : null;
        $fromDate = $_GET['from'] ?? '';
        $toDate = $_GET['to'] ?? '';
        $stats = $this->service ? $this->service->getStats() : [];
        $collections = $this->service ? $this->service->getCollections($status, $collectorId, $fromDate, $toDate, 100) : [];
        $collectors = $this->service ? $this->service->getCollectors() : [];
        return $this->render('admin/cash-collections/index', [
            'page_title' => 'Cash Collections',
            'page_heading' => 'On-Field Cash Collection & Reconciliation',
            'stats' => $stats,
            'collections' => $collections,
            'collectors' => $collectors,
            'filters' => ['status' => $status, 'collector_id' => $collectorId, 'from' => $fromDate, 'to' => $toDate]
        ]);
    }

    public function create()
    {
        $collectors = $this->getUsers();
        $bookings = $this->getBookings();
        return $this->render('admin/cash-collections/create', [
            'page_title' => 'Submit Collection Receipt',
            'collectors' => $collectors,
            'bookings' => $bookings
        ]);
    }

    public function store()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        // Handle receipt photo upload
        $photoPath = null;
        if (!empty($_FILES['receipt_photo']['tmp_name'])) {
            $upload = $this->service ? $this->service->uploadReceipt($_FILES['receipt_photo']) : ['success' => false];
            if ($upload['success']) {
                $photoPath = $upload['path'];
            } else {
                $this->setFlash('error', 'Photo upload failed: ' . ($upload['error'] ?? 'Unknown'));
                return $this->redirect(BASE_URL . '/admin/cash-collections/create');
            }
        }

        $data = [
            'collector_id' => (int)($_POST['collector_id'] ?? 0),
            'customer_name' => trim($_POST['customer_name'] ?? ''),
            'amount' => (float)($_POST['amount'] ?? 0),
            'collection_date' => $_POST['collection_date'] ?? date('Y-m-d'),
            'booking_id' => !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null,
            'installment_id' => !empty($_POST['installment_id']) ? (int)$_POST['installment_id'] : null,
            'payment_method' => $_POST['payment_method'] ?? 'cash',
            'reference_number' => trim($_POST['reference_number'] ?? ''),
            'receipt_photo' => $photoPath,
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        $result = $this->service ? $this->service->submitCollection($data) : ['success' => false, 'error' => 'Service unavailable'];
        if ($result['success']) {
            if ($this->audit) $this->audit->log('cash_collection.submit', $this->getUserId(), $this->getUserRole(), 'cash_collection', $result['collection_id'], "Submitted ₹" . number_format($data['amount']) . " from " . $data['customer_name']);
            $this->setFlash('success', 'Collection receipt submitted successfully');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to submit');
        }
        return $this->redirect(BASE_URL . '/admin/cash-collections');
    }

    public function show()
    {
        $id = (int)($_GET['id'] ?? 0);
        $collection = $this->service ? $this->service->getById($id) : null;
        if (!$collection) {
            $this->setFlash('error', 'Collection not found');
            return $this->redirect(BASE_URL . '/admin/cash-collections');
        }
        return $this->render('admin/cash-collections/show', [
            'page_title' => 'Collection Details',
            'collection' => $collection
        ]);
    }

    public function verify()
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $adminId = $this->getUserId();
        if ($id && $this->service && $this->service->verifyCollection($id, $adminId)) {
            if ($this->audit) $this->audit->log('cash_collection.verify', $adminId, $this->getUserRole(), 'cash_collection', $id, "Verified collection #$id");
            $this->setFlash('success', 'Collection verified');
        } else {
            $this->setFlash('error', 'Failed to verify');
        }
        return $this->redirect(BASE_URL . '/admin/cash-collections');
    }

    public function reject()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $adminId = $this->getUserId();
        if (!$reason) {
            $this->setFlash('error', 'Rejection reason is required');
            return $this->redirect(BASE_URL . '/admin/cash-collections');
        }
        if ($id && $this->service && $this->service->rejectCollection($id, $adminId, $reason)) {
            if ($this->audit) $this->audit->log('cash_collection.reject', $adminId, $this->getUserRole(), 'cash_collection', $id, "Rejected collection #$id: $reason");
            $this->setFlash('warning', 'Collection rejected');
        } else {
            $this->setFlash('error', 'Failed to reject');
        }
        return $this->redirect(BASE_URL . '/admin/cash-collections');
    }

    public function bulkVerify()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $ids = $_POST['ids'] ?? [];
        $adminId = $this->getUserId();
        if (!empty($ids) && $this->service) {
            $count = $this->service->bulkVerify($ids, $adminId);
            $this->setFlash('success', "$count collection(s) verified");
        }
        return $this->redirect(BASE_URL . '/admin/cash-collections');
    }

    public function reconciliations()
    {
        $status = $_GET['status'] ?? '';
        $reconciliations = $this->service ? $this->service->getReconciliations($status) : [];
        $collectors = $this->service ? $this->service->getCollectors() : [];
        return $this->render('admin/cash-collections/reconciliations', [
            'page_title' => 'Reconciliation',
            'page_heading' => 'Cash Reconciliation Sessions',
            'reconciliations' => $reconciliations,
            'collectors' => $collectors
        ]);
    }

    public function reconciliationForm()
    {
        $collectors = $this->service ? $this->service->getCollectors() : [];
        return $this->render('admin/cash-collections/reconciliation-create', [
            'page_title' => 'Start Reconciliation',
            'page_heading' => 'Start Reconciliation Session',
            'collectors' => $collectors
        ]);
    }

    public function reconciliationCreate()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $collectorId = (int)($_POST['collector_id'] ?? 0);
        $sessionDate = $_POST['session_date'] ?? date('Y-m-d');
        $notes = trim($_POST['notes'] ?? '');
        if (!$collectorId) {
            $this->setFlash('error', 'Select a collector');
            return $this->redirect(BASE_URL . '/admin/cash-collections/reconciliations');
        }
        $result = $this->service ? $this->service->createReconciliation($collectorId, $sessionDate, $notes) : ['success' => false];
        if ($result['success']) {
            $disc = $result['discrepancy'] ?? 0;
            $msg = abs($disc) > 0.01
                ? "Reconciliation created with ₹" . number_format($disc, 2) . " discrepancy"
                : 'Reconciliation created — all balances match';
            $this->setFlash(abs($disc) > 0.01 ? 'warning' : 'success', $msg);
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed');
        }
        return $this->redirect(BASE_URL . '/admin/cash-collections/reconciliations');
    }

    public function reconciliationClose()
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $adminId = $this->getUserId();
        if ($id && $this->service && $this->service->closeReconciliation($id, $adminId)) {
            $this->setFlash('success', 'Reconciliation closed');
        } else {
            $this->setFlash('error', 'Failed to close');
        }
        return $this->redirect(BASE_URL . '/admin/cash-collections/reconciliations');
    }

    private function getUsers(): array
    {
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE role IN ('admin','agent','associate','employee'){$tidSql} ORDER BY name ASC");
            $stmt->execute($tidParams);
            return $stmt->fetchAll();
        } catch (\Throwable $e) { error_log("CashCollectionController::getUsers error: " . $e->getMessage()); return []; }
    }

    public function reconcile()
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $adminId = $this->getUserId();
        if ($id && $this->service && $this->service->closeReconciliation($id, $adminId)) {
            $this->setFlash('success', 'Reconciliation reconciled');
        } else {
            $this->setFlash('error', 'Failed to reconcile');
        }
        return $this->redirect(BASE_URL . '/admin/cash-collections/reconciliations');
    }

    private function getBookings(): array
    {
        try {
            $stmt = $this->db->query("SELECT pb.id, pb.booking_number, u.name as customer_name
                FROM plot_bookings pb
                LEFT JOIN users u ON u.id = pb.customer_id
                WHERE pb.status NOT IN ('cancelled','transferred')
                ORDER BY pb.created_at DESC LIMIT 100");
            return $stmt->fetchAll();
        } catch (\Throwable $e) { return []; }
    }
}
