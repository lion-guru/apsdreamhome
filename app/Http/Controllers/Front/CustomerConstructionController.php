<?php

namespace App\Http\Controllers\Front;

use App\Services\ConstructionApprovalService;
use App\Core\Database\Database;
use PDO;

/**
 * Customer Township Construction Permit & Architectural Map NOC Desk
 */
class CustomerConstructionController extends PageController
{
    private ConstructionApprovalService $approvalService;

    public function __construct()
    {
        parent::__construct();
        $this->approvalService = new ConstructionApprovalService();
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
        $applications = $this->approvalService->getCustomerApplications($cid);

        $this->view('customer/construction/index', [
            'page_title' => 'My Township Construction Permits & Map Clearances',
            'applications' => $applications
        ]);
    }

    public function apply()
    {
        $cid = $this->requireCustomer();
        $pdo = Database::getInstance()->getConnection();

        $stmt = $pdo->prepare(
            "SELECT pb.id AS booking_id, pb.plot_id, pb.booking_number,
                    p.plot_number, p.area_sqft, p.facing, c.name AS colony_name
             FROM plot_bookings pb
             JOIN plots p ON pb.plot_id = p.id
             JOIN colonies c ON p.colony_id = c.id
             WHERE pb.customer_id = ?
             ORDER BY pb.id DESC"
        );
        $stmt->execute([$cid]);
        $plots = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->view('customer/construction/apply', [
            'page_title' => 'Apply for Township Construction Excavation Permit',
            'plots' => $plots
        ]);
    }

    public function store()
    {
        $cid = $this->requireCustomer();
        $this->validateCsrfOrFail();

        $plotId = (int)($_POST['plot_id'] ?? 0);
        $bookingId = !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;

        $data = [
            'tenant_id' => 1,
            'customer_id' => $cid,
            'booking_id' => $bookingId,
            'plot_id' => $plotId,
            'building_type' => trim((string)($_POST['building_type'] ?? 'residential_g1')),
            'ground_coverage_sqft' => (float)($_POST['ground_coverage_sqft'] ?? 0.0),
            'total_builtup_sqft' => (float)($_POST['total_builtup_sqft'] ?? 0.0),
            'front_setback_ft' => (float)($_POST['front_setback_ft'] ?? 0.0),
            'rear_setback_ft' => (float)($_POST['rear_setback_ft'] ?? 0.0),
            'side_setback_left_ft' => (float)($_POST['side_setback_left_ft'] ?? 0.0),
            'side_setback_right_ft' => (float)($_POST['side_setback_right_ft'] ?? 0.0),
            'rainwater_harvesting_pit' => !empty($_POST['rainwater_harvesting_pit']) ? 1 : 0,
            'architect_name' => trim((string)($_POST['architect_name'] ?? '')),
            'architect_council_reg' => trim((string)($_POST['architect_council_reg'] ?? ''))
        ];

        $res = $this->approvalService->submitApplication($data);

        if ($res['success']) {
            $_SESSION['flash_success'] = "Construction permit application submitted. Reference Token: {$res['permit_token']}";
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/customer/construction-approvals');
            exit;
        }

        $_SESSION['flash_error'] = $res['message'];
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/customer/construction/apply');
        exit;
    }

    public function permit($id)
    {
        $cid = $this->requireCustomer();
        $id = (int)$id;

        $approval = $this->approvalService->getApprovalDetails($id);
        if (!$approval || (int)$approval['customer_id'] !== $cid || $approval['status'] !== 'approved') {
            http_response_code(403);
            echo "Access Denied: You cannot view this permit.";
            exit;
        }

        $this->view('customer/construction/permit_print', [
            'approval' => $approval,
            'page_title' => 'Official Construction Permit NOC #' . $approval['permit_token']
        ]);
    }
}
