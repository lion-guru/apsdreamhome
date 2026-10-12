<?php

namespace App\Http\Controllers\Admin;

use App\Services\ConstructionApprovalService;
use App\Core\Database\Database;
use PDO;

/**
 * Township Architectural Map Approval & Civil Construction Excavation Permit NOC Desk
 */
class ConstructionApprovalController extends AdminController
{
    private ConstructionApprovalService $approvalService;

    public function __construct()
    {
        parent::__construct();
        $this->approvalService = new ConstructionApprovalService();
    }

    public function index()
    {
        $this->requireAdmin();

        $status = trim((string)($_GET['status'] ?? 'all'));
        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $sql = "SELECT ca.*, p.plot_number, p.area_sqft, c.name AS colony_name, u.name AS customer_name, u.phone AS customer_phone
                FROM construction_approvals ca
                JOIN plots p ON ca.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                JOIN users u ON ca.customer_id = u.id
                WHERE 1=1";
        $params = [];

        if ($status !== 'all' && in_array($status, ['submitted', 'site_inspected', 'approved', 'rejected'], true)) {
            $sql .= " AND ca.status = ?";
            $params[] = $status;
        }
        if ($colonyId) {
            $sql .= " AND p.colony_id = ?";
            $params[] = $colonyId;
        }
        $sql .= " ORDER BY ca.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $approvals = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stats = $this->approvalService->getSummaryStats();
        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->render('admin/construction/approvals_index', [
            'page_title' => 'Township Construction Permits & Architectural Map NOC Desk',
            'approvals' => $approvals,
            'stats' => $stats,
            'colonies' => $colonies,
            'current_status' => $status,
            'current_colony' => $colonyId,
        ]);
    }

    public function inspect($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $id = (int)$id;
        $data = [
            'site_engineer_name' => trim((string)($_POST['site_engineer_name'] ?? '')),
            'inspection_date' => !empty($_POST['inspection_date']) ? $_POST['inspection_date'] : date('Y-m-d'),
            'inspection_notes' => trim((string)($_POST['inspection_notes'] ?? ''))
        ];

        $this->approvalService->recordSiteInspection($id, $data);
        $_SESSION['flash_success'] = 'Site setback inspection logged successfully.';
        $this->redirect('/admin/construction-approvals');
    }

    public function approve($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $id = (int)$id;
        $engineer = trim((string)($_POST['site_engineer_name'] ?? 'APS Chief Town Planner'));
        $notes = trim((string)($_POST['notes'] ?? ''));

        $res = $this->approvalService->approvePermit($id, $engineer, $notes);
        if ($res['success']) {
            $_SESSION['flash_success'] = "Construction permit NOC approved: {$res['permit_token']}";
        } else {
            $_SESSION['flash_error'] = $res['message'];
        }

        $this->redirect('/admin/construction-approvals');
    }

    public function reject($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $id = (int)$id;
        $reason = trim((string)($_POST['rejection_reason'] ?? 'Violation of mandatory township setback guidelines.'));

        $this->approvalService->rejectApplication($id, $reason);
        $_SESSION['flash_error'] = 'Application marked as rejected.';
        $this->redirect('/admin/construction-approvals');
    }

    public function permit($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        $approval = $this->approvalService->getApprovalDetails($id);

        if (!$approval || $approval['status'] !== 'approved') {
            http_response_code(404);
            echo "Construction permit not approved or not found.";
            exit;
        }

        $this->view('customer/construction/permit_print', [
            'approval' => $approval,
            'page_title' => 'Excavation & Construction Permit NOC #' . $approval['permit_token']
        ]);
    }
}
