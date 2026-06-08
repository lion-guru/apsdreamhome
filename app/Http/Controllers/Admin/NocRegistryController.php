<?php

namespace App\Http\Controllers\Admin;

use App\Services\NocRegistryService;
use App\Services\AuditService;

class NocRegistryController extends AdminController
{
    protected $service;
    protected $audit;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new NocRegistryService($this->db); } catch (\Throwable $e) { $this->service = null; }
        try { $this->audit = new AuditService($this->db); } catch (\Throwable $e) { $this->audit = null; }
    }

    // ==================== NOC ====================

    public function dashboard()
    {
        $stats = $this->service ? $this->service->getDashboardSummary() : [];
        $pendingNocs = $this->service ? $this->service->listNocs('pending', 10) : [];
        $pendingRegistries = $this->service ? $this->service->listRegistries('pending', 10) : [];
        return $this->render('admin/noc-registry/dashboard', [
            'page_title' => 'NOC & Registry Dashboard',
            'stats' => $stats,
            'pendingNocs' => $pendingNocs,
            'pendingRegistries' => $pendingRegistries
        ]);
    }

    public function nocList()
    {
        $status = $_GET['status'] ?? '';
        $nocs = $this->service ? $this->service->listNocs($status, 100) : [];
        return $this->render('admin/noc-registry/noc-list', [
            'page_title' => 'NOC Requests',
            'nocs' => $nocs,
            'filters' => ['status' => $status]
        ]);
    }

    public function nocDetail()
    {
        $id = (int)($_GET['id'] ?? 0);
        $noc = $this->service ? $this->service->getNocById($id) : null;
        if (!$noc) {
            $this->setFlash('error', 'NOC request not found');
            return $this->redirect(BASE_URL . '/admin/noc-registry/nocs');
        }
        $eligibility = $this->service ? $this->service->checkEligibility((int)$noc['booking_id']) : null;
        return $this->render('admin/noc-registry/noc-detail', [
            'page_title' => 'NOC Details',
            'noc' => $noc,
            'eligibility' => $eligibility
        ]);
    }

    public function nocCreate()
    {
        $bookings = $this->getBookings();
        $users = $this->getUsers();
        return $this->render('admin/noc-registry/noc-create', [
            'page_title' => 'Request NOC',
            'bookings' => $bookings,
            'users' => $users
        ]);
    }

    public function nocStore()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $data = [
            'booking_id' => (int)($_POST['booking_id'] ?? 0),
            'plot_id' => (int)($_POST['plot_id'] ?? 0),
            'user_id' => (int)($_POST['user_id'] ?? 0),
            'requested_by' => $this->getUserId(),
            'purpose' => trim($_POST['purpose'] ?? ''),
            'notes' => trim($_POST['notes'] ?? '')
        ];
        $result = $this->service ? $this->service->requestNoc($data) : ['success' => false, 'error' => 'Service unavailable'];
        if ($result['success']) {
            // Auto-process eligibility
            $this->service->processNoc($result['noc_id']);
            if ($this->audit) $this->audit->log('noc.request', $this->getUserId(), $this->getUserRole(), 'noc_requests', $result['noc_id'], "NOC requested for booking #{$data['booking_id']}");
            $this->setFlash('success', 'NOC request created and eligibility checked');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed');
        }
        return $this->redirect(BASE_URL . '/admin/noc-registry/nocs');
    }

    public function nocApprove()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'CSRF'], 403); return; }
        $id = (int)($_POST['id'] ?? 0);
        if ($this->service && $this->service->approveNoc($id, $this->getUserId())) {
            if ($this->audit) $this->audit->log('noc.approve', $this->getUserId(), $this->getUserRole(), 'noc_requests', $id, "NOC #$id approved");
            $this->setFlash('success', 'NOC approved');
        } else {
            $this->setFlash('error', 'Failed to approve');
        }
        return $this->redirect(BASE_URL . '/admin/noc-registry/nocs/' . $id);
    }

    public function nocReject()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'CSRF'], 403); return; }
        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if (!$reason) { $this->setFlash('error', 'Reason required'); return $this->redirect(BASE_URL . '/admin/noc-registry/nocs/' . $id); }
        if ($this->service && $this->service->rejectNoc($id, $this->getUserId(), $reason)) {
            if ($this->audit) $this->audit->log('noc.reject', $this->getUserId(), $this->getUserRole(), 'noc_requests', $id, "NOC #$id rejected: $reason");
            $this->setFlash('warning', 'NOC rejected');
        } else {
            $this->setFlash('error', 'Failed');
        }
        return $this->redirect(BASE_URL . '/admin/noc-registry/nocs/' . $id);
    }

    public function nocReprocess()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'CSRF'], 403); return; }
        $id = (int)($_POST['id'] ?? 0);
        $result = $this->service ? $this->service->processNoc($id) : ['success' => false];
        if ($result['success']) {
            $status = $result['status'] ?? 'unknown';
            $this->setFlash($status === 'approved' ? 'success' : 'warning', "NOC re-processed: $status");
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed');
        }
        return $this->redirect(BASE_URL . '/admin/noc-registry/nocs/' . $id);
    }

    public function eligibilityCheck()
    {
        $bookingId = (int)($_GET['booking_id'] ?? 0);
        if (!$bookingId) { $this->json(['error' => 'booking_id required'], 400); return; }
        $result = $this->service ? $this->service->checkEligibility($bookingId) : ['eligible' => false, 'checks' => [], 'blockers' => ['Service unavailable']];
        $this->json($result);
    }

    // ==================== REGISTRY ====================

    public function registryList()
    {
        $status = $_GET['status'] ?? '';
        $registries = $this->service ? $this->service->listRegistries($status, 100) : [];
        return $this->render('admin/noc-registry/registry-list', [
            'page_title' => 'Registry Requests',
            'registries' => $registries,
            'filters' => ['status' => $status]
        ]);
    }

    public function registryDetail()
    {
        $id = (int)($_GET['id'] ?? 0);
        try {
            $stmt = $this->db->prepare("SELECT r.*, pb.booking_number, pb.total_plot_value, p.plot_number, p.block, u.name as customer_name FROM registries r LEFT JOIN plot_bookings pb ON r.booking_id = pb.id LEFT JOIN plots p ON r.plot_id = p.id LEFT JOIN users u ON r.user_id = u.id WHERE r.id = ?");
            $stmt->execute([$id]);
            $registry = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { $registry = null; }
        if (!$registry) { $this->setFlash('error', 'Registry not found'); return $this->redirect(BASE_URL . '/admin/noc-registry/registries'); }
        return $this->render('admin/noc-registry/registry-detail', [
            'page_title' => 'Registry Details',
            'registry' => $registry
        ]);
    }

    public function registryCreate()
    {
        $bookings = $this->getApprovedNocBookings();
        $users = $this->getUsers();
        return $this->render('admin/noc-registry/registry-create', [
            'page_title' => 'Request Registry',
            'bookings' => $bookings,
            'users' => $users
        ]);
    }

    public function registryStore()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'CSRF'], 403); return; }
        $data = [
            'booking_id' => (int)($_POST['booking_id'] ?? 0),
            'plot_id' => (int)($_POST['plot_id'] ?? 0),
            'user_id' => (int)($_POST['user_id'] ?? 0),
            'associate_id' => !empty($_POST['associate_id']) ? (int)$_POST['associate_id'] : null,
            'sub_registrar_office' => trim($_POST['sub_registrar_office'] ?? ''),
            'stamp_duty_amount' => (float)($_POST['stamp_duty_amount'] ?? 0),
            'registration_fee' => (float)($_POST['registration_fee'] ?? 0),
            'other_charges' => (float)($_POST['other_charges'] ?? 0),
            'total_registry_cost' => (float)($_POST['total_registry_cost'] ?? 0),
            'notes' => trim($_POST['notes'] ?? '')
        ];
        $result = $this->service ? $this->service->requestRegistry($data) : ['success' => false, 'error' => 'Service unavailable'];
        if ($result['success']) {
            if ($this->audit) $this->audit->log('registry.request', $this->getUserId(), $this->getUserRole(), 'registries', $result['registry_id'], "Registry requested for booking #{$data['booking_id']}");
            $this->setFlash('success', 'Registry request created');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed');
        }
        return $this->redirect(BASE_URL . '/admin/noc-registry/registries');
    }

    public function registryUpdateStatus()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) { $this->json(['success' => false, 'error' => 'CSRF'], 403); return; }
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $reason = trim($_POST['reason'] ?? '');
        $valid = ['pending','appointment_scheduled','documents_submitted','in_progress','completed','rejected','cancelled'];
        if (!in_array($status, $valid)) { $this->setFlash('error', 'Invalid status'); return $this->redirect(BASE_URL . '/admin/noc-registry/registries/' . $id); }
        if ($this->service && $this->service->updateRegistryStatus($id, $status, $reason)) {
            if ($this->audit) $this->audit->log('registry.update_status', $this->getUserId(), $this->getUserRole(), 'registries', $id, "Registry #$id → $status");
            $this->setFlash('success', "Registry status updated to $status");
        } else {
            $this->setFlash('error', 'Failed');
        }
        return $this->redirect(BASE_URL . '/admin/noc-registry/registries/' . $id);
    }

    // ---- Helpers ----

    private function getBookings(): array
    {
        try {
            $stmt = $this->db->query("SELECT pb.id, pb.booking_number, pb.customer_id, pb.total_plot_value, pb.status, u.name as customer_name, p.plot_number, p.block FROM plot_bookings pb LEFT JOIN users u ON pb.customer_id = u.id LEFT JOIN plots p ON pb.plot_id = p.id WHERE pb.status NOT IN ('cancelled','transferred') ORDER BY pb.created_at DESC LIMIT 100");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    private function getApprovedNocBookings(): array
    {
        try {
            $stmt = $this->db->query("SELECT pb.id, pb.booking_number, pb.customer_id, pb.total_plot_value, pb.status, u.name as customer_name, p.plot_number, p.block FROM plot_bookings pb INNER JOIN noc_requests nr ON nr.booking_id = pb.id AND nr.status = 'approved' LEFT JOIN users u ON pb.customer_id = u.id LEFT JOIN plots p ON pb.plot_id = p.id ORDER BY pb.created_at DESC LIMIT 100");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }

    private function getUsers(): array
    {
        try {
            $stmt = $this->db->query("SELECT id, name FROM users WHERE role IN ('admin','agent','associate','employee') ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) { return []; }
    }
}
