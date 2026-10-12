<?php

namespace App\Http\Controllers\Admin;

use App\Services\WorkOrderService;
use App\Core\Database\Database;

/**
 * Admin Civil Infrastructure Work Orders & RA Milestone Billing Desk
 */
class WorkOrderController extends AdminController
{
    private WorkOrderService $workOrderService;

    public function __construct()
    {
        parent::__construct();
        $this->workOrderService = new WorkOrderService();
    }

    public function index()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;
        $status = trim((string)($_GET['status'] ?? 'all'));

        $orders = $this->workOrderService->getWorkOrders($colonyId, $status);
        $stats = $this->workOrderService->getSummaryStats();

        $colonies = Database::getInstance()->getConnection()->query(
            "SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->render('admin/construction/work_orders', [
            'page_title' => 'Civil Infrastructure Work Orders',
            'orders' => $orders,
            'stats' => $stats,
            'colonies' => $colonies,
            'current_colony' => $colonyId,
            'current_status' => $status,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'colony_id' => (int)($_POST['colony_id'] ?? 0),
            'work_title' => trim((string)($_POST['work_title'] ?? '')),
            'work_category' => $_POST['work_category'] ?? 'general',
            'contractor_name' => trim((string)($_POST['contractor_name'] ?? '')),
            'contractor_phone' => trim((string)($_POST['contractor_phone'] ?? '')),
            'contractor_pan' => trim((string)($_POST['contractor_pan'] ?? '')),
            'contractor_gstin' => trim((string)($_POST['contractor_gstin'] ?? '')),
            'contract_amount' => (float)($_POST['contract_amount'] ?? 0),
            'retention_pct' => (float)($_POST['retention_pct'] ?? 5.0),
            'tds_pct' => (float)($_POST['tds_pct'] ?? 1.0),
            'start_date' => $_POST['start_date'] ?? null,
            'completion_deadline' => $_POST['completion_deadline'] ?? null,
            'scope_description' => trim((string)($_POST['scope_description'] ?? '')),
            'created_by' => (int)($_SESSION['user_id'] ?? 1),
        ];

        $res = $this->workOrderService->createWorkOrder($data);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect('/admin/work-orders');
    }

    public function show($id)
    {
        $this->requireAdmin();

        $order = $this->workOrderService->getWorkOrderDetail((int)$id);
        if (!$order) {
            $this->setFlash('error', 'Work order not found.');
            return $this->redirect('/admin/work-orders');
        }

        $this->render('admin/construction/work_order_detail', [
            'page_title' => "Work Order #{$order['order_number']}",
            'order' => $order,
        ]);
    }

    public function billStore($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'bill_number' => trim((string)($_POST['bill_number'] ?? '')),
            'measurement_ref' => trim((string)($_POST['measurement_ref'] ?? '')),
            'measurement_notes' => trim((string)($_POST['measurement_notes'] ?? '')),
            'gross_amount' => (float)($_POST['gross_amount'] ?? 0),
            'bill_date' => $_POST['bill_date'] ?? date('Y-m-d'),
        ];

        $res = $this->workOrderService->submitBill((int)$id, $data);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect("/admin/work-orders/{$id}");
    }

    public function billApprove($id, $billId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $res = $this->workOrderService->approveBill((int)$billId);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect("/admin/work-orders/{$id}");
    }

    public function billDisburse($id, $billId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $amount = (float)($_POST['paid_amount'] ?? 0);
        $ref = trim((string)($_POST['payment_ref'] ?? ''));

        $res = $this->workOrderService->disburseBillPayment((int)$billId, $amount, $ref);
        $this->setFlash($res['success'] ? 'success' : 'error', $res['message']);

        return $this->redirect("/admin/work-orders/{$id}");
    }

    public function printOrder($id)
    {
        $this->requireAdmin();

        $order = $this->workOrderService->getWorkOrderDetail((int)$id);
        if (!$order) {
            echo "Work order not found.";
            exit;
        }

        require dirname(__DIR__, 2) . '/views/admin/construction/work_order_print.php';
        exit;
    }
}
