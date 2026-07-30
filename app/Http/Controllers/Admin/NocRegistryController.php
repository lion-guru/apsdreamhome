<?php
namespace App\Http\Controllers\Admin;

use App\Services\NocRegistryService;

/**
 * NocRegistryController — NOC & Registry admin management
 * Routes: /admin/noc-registry/*
 */
class NocRegistryController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new NocRegistryService(
            $this->db instanceof \PDO ? $this->db : null
        );
    }

    // ========== Dashboard ==========

    public function dashboard()
    {
        $this->requireAdmin();
        $stats = $this->service->getDashboardStats();
        $recentNocs = $this->service->listNocs();
        $recentRegistries = $this->service->listRegistries();

        $this->render('admin/noc-registry/dashboard', [
            'page_title' => 'NOC & Registry Pipeline',
            'stats' => $stats,
            'recent_nocs' => array_slice($recentNocs, 0, 10),
            'recent_registries' => array_slice($recentRegistries, 0, 10),
        ]);
    }

    // ========== Eligibility ==========

    public function eligibilityCheck()
    {
        $this->requireAdmin();
        $bookingId = (int)($_GET['booking_id'] ?? 0);
        $result = null;
        $eligibleBookings = $this->service->listEligibleBookings();

        if ($bookingId > 0) {
            $nocResult = $this->service->checkNocEligibility($bookingId);
            $regResult = $this->service->checkRegistryEligibility($bookingId);
            $result = [
                'booking' => $nocResult['booking'],
                'noc' => $nocResult,
                'registry' => $regResult,
            ];
        }

        $this->render('admin/noc-registry/eligibility', [
            'page_title' => 'Eligibility Check',
            'result' => $result,
            'eligible_bookings' => $eligibleBookings,
            'booking_id' => $bookingId,
        ]);
    }

    // ========== NOC CRUD ==========

    public function nocList()
    {
        $this->requireAdmin();
        $status = $_GET['status'] ?? null;
        $nocs = $this->service->listNocs(['status' => $status]);

        $this->render('admin/noc-registry/noc-list', [
            'page_title' => 'NOC Requests',
            'nocs' => $nocs,
            'status_filter' => $status,
        ]);
    }

    public function nocCreate()
    {
        $this->requireAdmin();
        $eligibleBookings = $this->service->listEligibleBookings();

        $this->render('admin/noc-registry/noc-create', [
            'page_title' => 'Request NOC',
            'eligible_bookings' => $eligibleBookings,
        ]);
    }

    public function nocStore()
    {
        $this->requireAdmin();
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            redirect('/admin/noc-registry/nocs/create');
            return;
        }

        $result = $this->service->createNocRequest([
            'booking_id' => (int)($_POST['booking_id'] ?? 0),
            'requested_by' => (int)($_SESSION['admin_id'] ?? 0),
            'purpose' => $_POST['purpose'] ?? 'Property transfer / Registry',
            'notes' => $_POST['notes'] ?? null,
        ]);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
            redirect('/admin/noc-registry/nocs/' . $result['noc_id']);
        } else {
            $_SESSION['flash_error'] = $result['error'];
            redirect('/admin/noc-registry/nocs/create');
        }
    }

    public function nocDetail($id = null)
    {
        $this->requireAdmin();
        $id = (int)($id ?? $_GET['id'] ?? 0);
        if ($id <= 0) { redirect('/admin/noc-registry/nocs'); return; }

        $noc = $this->service->getNoc($id);
        if (!$noc) {
            $_SESSION['flash_error'] = 'NOC not found';
            redirect('/admin/noc-registry/nocs');
            return;
        }

        $eligibility = $this->service->checkNocEligibility($noc['booking_id']);

        $this->render('admin/noc-registry/noc-detail', [
            'page_title' => 'NOC #' . $id,
            'noc' => $noc,
            'eligibility' => $eligibility,
        ]);
    }

    public function nocApprove()
    {
        $this->requireAdmin();
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            return;
        }

        $nocId = (int)($_POST['noc_id'] ?? 0);
        $result = $this->service->approveNoc($nocId, (int)($_SESSION['admin_id'] ?? 0));

        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['success'] ? $result['message'] : $result['error'];
        redirect('/admin/noc-registry/nocs/' . $nocId);
    }

    public function nocReject()
    {
        $this->requireAdmin();
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            return;
        }

        $nocId = (int)($_POST['noc_id'] ?? 0);
        $reason = $_POST['reason'] ?? 'Rejected by admin';
        $result = $this->service->rejectNoc($nocId, (int)($_SESSION['admin_id'] ?? 0), $reason);

        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['success'] ? $result['message'] : $result['error'];
        redirect('/admin/noc-registry/nocs/' . $nocId);
    }

    public function nocReprocess()
    {
        $this->requireAdmin();
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            return;
        }

        $nocId = (int)($_POST['noc_id'] ?? 0);
        $pdo = $this->getPdoLocal();
        list($tSql, $tParams) = $this->tenantWhere();
        $stmt = $pdo->prepare("UPDATE noc_requests SET status = 'processing', rejection_reason = NULL WHERE id = ? $tSql");
        $stmt->execute(array_merge([$nocId], $tParams));

        $_SESSION['flash_success'] = "NOC #{$nocId} set to processing";
        redirect('/admin/noc-registry/nocs/' . $nocId);
    }

    // ========== Registry CRUD ==========

    public function registryList()
    {
        $this->requireAdmin();
        $status = $_GET['status'] ?? null;
        $registries = $this->service->listRegistries(['status' => $status]);

        $this->render('admin/noc-registry/registry-list', [
            'page_title' => 'Registries',
            'registries' => $registries,
            'status_filter' => $status,
        ]);
    }

    public function registryCreate()
    {
        $this->requireAdmin();
        $eligibleBookings = $this->service->listEligibleBookings();
        $stampDuty = $this->service->calculateStampDuty(0);

        $this->render('admin/noc-registry/registry-create', [
            'page_title' => 'New Registry',
            'eligible_bookings' => $eligibleBookings,
            'stamp_duty' => $stampDuty,
        ]);
    }

    public function registryStore()
    {
        $this->requireAdmin();
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            redirect('/admin/noc-registry/registries/create');
            return;
        }

        $result = $this->service->createRegistry([
            'booking_id' => (int)($_POST['booking_id'] ?? 0),
            'sub_registrar_office' => $_POST['sub_registrar_office'] ?? 'SRO Gorakhpur',
            'notes' => $_POST['notes'] ?? null,
        ]);

        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
            redirect('/admin/noc-registry/registries/' . $result['registry_id']);
        } else {
            $_SESSION['flash_error'] = $result['error'];
            redirect('/admin/noc-registry/registries/create');
        }
    }

    public function registryDetail($id = null)
    {
        $this->requireAdmin();
        $id = (int)($id ?? $_GET['id'] ?? 0);
        if ($id <= 0) { redirect('/admin/noc-registry/registries'); return; }

        $registry = $this->service->getRegistry($id);
        if (!$registry) {
            $_SESSION['flash_error'] = 'Registry not found';
            redirect('/admin/noc-registry/registries');
            return;
        }

        $stampDutyCalc = $this->service->calculateStampDuty((float)$registry['total_plot_value'] ?? 0);

        $this->render('admin/noc-registry/registry-detail', [
            'page_title' => 'Registry #' . $id,
            'registry' => $registry,
            'stamp_duty_calc' => $stampDutyCalc,
        ]);
    }

    public function registryUpdateStatus()
    {
        $this->requireAdmin();
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['flash_error'] = 'Invalid CSRF token';
            return;
        }

        $registryId = (int)($_POST['registry_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        $notes = $_POST['notes'] ?? null;
        $regNo = $_POST['registration_no'] ?? null;

        $result = $this->service->updateRegistryStatus($registryId, $newStatus, $notes, $regNo);

        $_SESSION[$result['success'] ? 'flash_success' : 'flash_error'] = $result['success'] ? $result['message'] : $result['error'];
        redirect('/admin/noc-registry/registries/' . $registryId);
    }

    // ========== Helpers ==========

    private function getPdoLocal(): \PDO
    {
        return \App\Core\Database\Database::getInstance()->getConnection();
    }
}
