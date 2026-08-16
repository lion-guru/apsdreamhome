<?php

namespace App\Http\Controllers\Admin;

use App\Services\VisitService;
use App\Services\AuditService;

class VisitController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private $service;
    private $audit;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new VisitService($this->db); } catch (\Throwable $e) { $this->service = null; }
        try { $this->audit = new AuditService($this->db); } catch (\Throwable $e) { $this->audit = null; }
    }

    public function index()
    {
        $stats = $this->service ? $this->service->getStats() : [];
        $visits = $this->service ? $this->service->getAll('', '', 50) : [];
        $slots = $this->service ? $this->service->getAvailableSlots(date('Y-m-d'), date('Y-m-d', strtotime('+14 days'))) : [];
        return $this->render('admin/visits/index', [
            'page_title' => 'Property Visits',
            'page_heading' => 'Property Visit Schedule',
            'stats' => $stats,
            'visits' => $visits,
            'slots' => $slots,
            'BASE_URL' => defined('BASE_URL') ? BASE_URL : 'http://localhost/apsdreamhome'
        ]);
    }

    public function calendar()
    {
        $month = (int)($_GET['month'] ?? date('m'));
        $year = (int)($_GET['year'] ?? date('Y'));
        $visits = $this->service ? $this->service->getAll('', '', 500) : [];
        return $this->render('admin/visits/calendar', [
            'page_title' => 'Visit Calendar',
            'month' => $month,
            'year' => $year,
            'visits' => $visits
        ]);
    }

    public function create()
    {
        $leads = [];
        $properties = [];
        $users = [];
        try {
            $stmt = $this->db->prepare("SELECT id, name, phone, status FROM leads ORDER BY created_at DESC LIMIT 100");
            $stmt->execute();
            $leads = $stmt->fetchAll();
        } catch (\Throwable $e) { error_log('VisitController::create error: ' . $e->getMessage()); }
        try {
            $stmt = $this->db->prepare("SELECT id, name, location FROM user_properties WHERE status = 'approved' ORDER BY created_at DESC LIMIT 100");
            $stmt->execute();
            $properties = $stmt->fetchAll();
        } catch (\Throwable $e) { error_log('VisitController::create error: ' . $e->getMessage()); }
        try {
            list($tSql, $tParams) = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT id, name FROM users WHERE role IN ('admin','agent','employee'){$tSql} ORDER BY name ASC");
            $stmt->execute($tParams);
            $users = $stmt->fetchAll();
        } catch (\Throwable $e) { error_log('VisitController::create error: ' . $e->getMessage()); }
        return $this->render('admin/visits/create', [
            'page_title' => 'Schedule Site Visit',
            'leads' => $leads,
            'properties' => $properties,
            'users' => $users
        ]);
    }

    public function store()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $data = [
            'customer_name' => $_POST['customer_name'] ?? trim($_POST['lead_id'] ?? ''),
            'customer_email' => $_POST['customer_email'] ?? '',
            'customer_phone' => $_POST['customer_phone'] ?? '',
            'property_id' => (int)($_POST['property_id'] ?? 0),
            'visit_date' => $_POST['visit_date'] ?? '',
            'visit_time' => $_POST['visit_time'] ?? '',
            'visit_type' => $_POST['visit_type'] ?? 'site_visit',
            'notes' => $_POST['notes'] ?? '',
        ];
        $result = $this->service ? $this->service->bookVisit($data) : ['success' => false, 'error' => 'Service unavailable'];
        if ($result['success']) {
            try {
                $leadId = (int)($_POST['lead_id'] ?? 0);
                if ($leadId > 0) {
                    list($tSql, $tParams) = $this->tenantWhere();
                    $this->db->prepare("UPDATE leads SET status = 'visit_scheduled' WHERE id = ? $tSql")->execute(array_merge([$leadId], $tParams));
                }
            } catch (\Throwable $e) { error_log('VisitController::store error: ' . $e->getMessage()); }
            if ($this->audit) $this->audit->log('visit.create', (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0), $_SESSION['role'] ?? 'admin', 'visit', $result['visit_id'] ?? 0, "Scheduled visit for " . $data['customer_name']);
            $this->setFlash('success', 'Site visit scheduled successfully');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to schedule visit');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }

    public function show()
    {
        $id = (int)($_GET['id'] ?? $_GET['visit_id'] ?? 0);
        $visit = $this->service ? $this->service->getById($id) : null;
        if (!$visit) {
            $this->setFlash('error', 'Visit not found');
            return $this->redirect(BASE_URL . '/admin/visits');
        }
        return $this->render('admin/visits/show', [
            'page_title' => 'Visit Details',
            'visit' => $visit
        ]);
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        $visit = $this->service ? $this->service->getById($id) : null;
        if (!$visit) {
            $this->setFlash('error', 'Visit not found');
            return $this->redirect(BASE_URL . '/admin/visits');
        }
        $users = [];
        try {
            list($tSql, $tParams) = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT id, name FROM users WHERE role IN ('admin','agent','employee'){$tSql} ORDER BY name ASC");
            $stmt->execute($tParams);
            $users = $stmt->fetchAll();
        } catch (\Throwable $e) { error_log('VisitController::edit error: ' . $e->getMessage()); }
        return $this->render('admin/visits/edit', [
            'page_title' => 'Edit Visit',
            'visit' => $visit,
            'users' => $users
        ]);
    }

    public function update()
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $id = (int)($_POST['visit_id'] ?? $_GET['id'] ?? 0);
        if (!$id) {
            $this->setFlash('error', 'Invalid visit ID');
            return $this->redirect(BASE_URL . '/admin/visits');
        }
        try {
            $status = $_POST['status'] ?? 'scheduled';
            $notes = $_POST['notes'] ?? null;
            $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
            $visitDate = $_POST['visit_date'] ?? null;
            $visitTime = $_POST['visit_time'] ?? null;

            list($tSql, $tParams) = $this->tenantWhere();
            $sql = "UPDATE property_visits SET status = ?, notes = ?";
            $params = [$status, $notes];
            if ($assignedTo !== null) { $sql .= ", assigned_to = ?"; $params[] = $assignedTo; }
            if ($visitDate) { $sql .= ", visit_date = ?"; $params[] = $visitDate . ' ' . ($visitTime ?? '00:00:00'); }
            if ($visitTime) { $sql .= ", visit_time = ?"; $params[] = $visitTime; }
            $sql .= " WHERE id = ? $tSql";
            $params[] = $id;
            $params = array_merge($params, $tParams);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            if ($this->audit) $this->audit->log('visit.update', (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0), $_SESSION['role'] ?? 'admin', 'visit', $id, "Updated visit #$id to status: $status");
            $this->setFlash('success', 'Visit updated successfully');
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Update failed: ' . $e->getMessage());
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }

    public function destroy()
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        if ($id) {
            $this->service->cancel($id, 'Deleted by admin');
            if ($this->audit) $this->audit->log('visit.delete', (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0), $_SESSION['role'] ?? 'admin', 'visit', $id, "Deleted visit #$id");
            $this->setFlash('success', 'Visit deleted');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }

    public function confirm()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            $this->service->updateStatus($id, 'confirmed');
            if ($this->audit) $this->audit->log('visit.confirm', (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0), $_SESSION['role'] ?? 'admin', 'visit', $id, "Confirmed visit #$id");
            $this->setFlash('success', 'Visit confirmed');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }

    public function complete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            $this->service->updateStatus($id, 'completed');
            $this->setFlash('success', 'Visit marked as completed');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }

    public function cancel()
    {
        $id = (int)($_GET['id'] ?? 0);
        $reason = $_POST['reason'] ?? $_GET['reason'] ?? 'Cancelled by admin';
        if ($this->service && $id) {
            $this->service->cancel($id, $reason);
            if ($this->audit) $this->audit->log('visit.cancel', (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0), $_SESSION['role'] ?? 'admin', 'visit', $id, "Cancelled visit #$id: $reason");
            $this->setFlash('success', 'Visit cancelled');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }

    public function noshow()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            $this->service->updateStatus($id, 'no_show');
            $this->setFlash('warning', 'Marked as no-show');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }

    public function updateStatus()
    {
        $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $status = $_POST['status'] ?? $_GET['status'] ?? '';
        $notes = $_POST['notes'] ?? null;
        if ($id && $status) {
            $this->service->updateStatus($id, $status, $notes);
            if ($this->audit) $this->audit->log('visit.status', (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0), $_SESSION['role'] ?? 'admin', 'visit', $id, "Status changed to $status for visit #$id");
            $this->setFlash('success', 'Status updated');
        }
        return $this->redirect(BASE_URL . '/admin/visits');
    }
}
