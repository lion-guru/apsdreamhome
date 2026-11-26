<?php
/**
 * Admin Offboarding / Exit Process Controller
 */

namespace App\Http\Controllers\Admin;

use App\Services\OffboardingService;
use App\Http\Controllers\BaseController;

class OffboardingController extends BaseController
{
    private $offboardingService;

    public function __construct()
    {
        parent::__construct();
        // Admin views need the admin chrome; BaseController defaults to the public layout.
        $this->layout = 'layouts/admin';
        $this->requireAdmin();
        $this->offboardingService = new OffboardingService();
    }

    /** S790: the detail view's checklist toggles post here; no route or
     *  handler existed. Delegate to the service's existing status updater. */
    public function updateItem()
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        $itemId = (int)($_POST['item_id'] ?? 0);
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        if ($itemId <= 0 || $employeeId <= 0 || !in_array($status, ['pending','in_progress','completed','skipped','waived'], true)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid checklist update'], 400);
        }
        $result = $this->offboardingService->updateItemStatus($itemId, $employeeId, $status, [
            'notes' => $_POST['notes'] ?? null,
        ]);
        return $this->jsonResponse($result, !empty($result['success']) ? 200 : 404);
    }

    /** S798: the detail view's Assign Template modal posts here; no route or
     *  handler existed. Delegate to the service's existing assignTemplate(). */
    public function assignTemplate()
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $templateId = (int)($_POST['template_id'] ?? 0);
        $assignedBy = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        if ($employeeId <= 0 || $templateId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid template assignment'], 400);
        }
        $result = $this->offboardingService->assignTemplate($employeeId, $templateId, $assignedBy);
        return $this->jsonResponse($result, !empty($result['success']) ? 200 : 404);
    }

    /**
     * Offboarding dashboard
     */
    public function index()
    {
        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'search' => $_GET['search'] ?? '',
            'department' => $_GET['department'] ?? '',
        ];

        $offboardings = $this->offboardingService->getAllOffboardingStatus($filters);
        $stats = $this->offboardingService->getOffboardingStats();

        return $this->render('admin/offboarding/index', [
            'page_title' => 'Offboarding / Exit Process',
            'offboardings' => $offboardings,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    /**
     * Show offboarding detail
     */
    public function show($id)
    {
        $offboarding = $this->offboardingService->getOffboardingProgress($id);
        if (!$offboarding) {
            $_SESSION['error'] = 'Offboarding record not found';
            $this->redirect(BASE_URL . '/admin/offboarding');
            return;
        }

        $checklist = $this->offboardingService->getOffboardingChecklist($id);

        return $this->render('admin/offboarding/detail', [
            'page_title' => 'Offboarding Details',
            'offboarding' => $offboarding,
            'checklist' => $checklist,
        ]);
    }

    /**
     * Initiate offboarding
     */
    public function initiate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/offboarding');
            return;
        }

        $data = [
            'employee_id' => (int)($_POST['employee_id'] ?? 0),
            'initiated_by' => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
            'last_working_day' => $_POST['last_working_day'] ?? '',
            'reason' => $_POST['reason'] ?? '',
            'template_id' => (int)($_POST['template_id'] ?? 0),
            'notes' => $_POST['notes'] ?? '',
        ];

        $result = $this->offboardingService->initiateOffboarding($data['employee_id'], $data['initiated_by'], $data);

        // S765: the offboarding index also calls this endpoint with fetch().json();
        // answer JSON to that caller while leaving the plain form POST unchanged.
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        if ($result['success']) {
            $this->redirect(BASE_URL . '/admin/offboarding/' . $result['offboarding_id']);
        } else {
            $this->redirect(BASE_URL . '/admin/offboarding');
        }
    }

    /**
     * Update offboarding status
     */
    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/offboarding/' . $id);
            return;
        }

        $result = $this->offboardingService->updateItemStatus(
            (int)($_POST['item_id'] ?? 0),
            (int)($_POST['employee_id'] ?? 0),
            $_POST['status'] ?? 'pending',
            ['notes' => $_POST['notes'] ?? '']
        );

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/admin/offboarding/' . $id);
    }

    /**
     * Complete offboarding
     */
    public function complete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/offboarding/' . $id);
            return;
        }

        $result = $this->offboardingService->completeOffboarding($id, [
            'completed_by' => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
            'exit_interview_notes' => $_POST['exit_interview_notes'] ?? '',
            'final_settlement_amount' => (float)($_POST['final_settlement_amount'] ?? 0),
            'asset_return_status' => $_POST['asset_return_status'] ?? 'completed',
            'clearance_status' => $_POST['clearance_status'] ?? 'completed',
        ]);

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/admin/offboarding');
    }
}