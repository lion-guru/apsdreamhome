<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\PurchaseOrderService;
use Exception;

class PurchaseOrderController extends AdminController
{
    protected $poService;

    public function __construct()
    {
        parent::__construct();
        $this->poService = new PurchaseOrderService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $orders = $this->poService->getAll($status ?: null);
            $stats = $this->poService->getStats();
            return $this->render('admin/purchase_orders/index', [
                'page_title' => 'Purchase Orders - APS Dream Home',
                'active_page' => 'purchase_orders',
                'orders' => $orders,
                'stats' => $stats,
                'filters' => ['status' => $status],
                'statuses' => ['pending','approved','received','cancelled'],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load POs: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/purchase_orders/create', [
            'page_title' => 'Create Purchase Order',
            'active_page' => 'purchase_orders',
            'vendors' => $this->poService->getVendors(),
            'departments' => $this->poService->getDepartments(),
            'today' => date('Y-m-d'),
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $required = ['vendor_id','order_date'];
            foreach ($required as $f) {
                if (empty($_POST[$f])) return $this->jsonError(ucfirst($f) . ' is required', 400);
            }
            $id = $this->poService->create($_POST);
            $this->logActivity('po_created', ['po_id' => $id, 'number' => $_POST['order_number'] ?? '']);
            return $this->jsonResponse(['success'=>true,'message'=>'PO created','po_id'=>$id,'redirect'=>BASE_URL.'/admin/purchase-orders']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: '.$e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $order = $this->poService->getById((int)$id);
            if (!$order) { $this->setFlash('error','PO not found'); return $this->redirect('/admin/purchase-orders'); }
            $items = $this->poService->getItems((int)$id);
            $grnRecords = $this->poService->getGrnRecords((int)$id);
            return $this->render('admin/purchase_orders/show', [
                'page_title' => 'PO: '.$order['order_number'],
                'active_page' => 'purchase_orders',
                'order' => $order,
                'items' => $items,
                'grnRecords' => $grnRecords,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error','Failed: '.$e->getMessage());
            return $this->redirect('/admin/purchase-orders');
        }
    }

    public function approve($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->poService->approve((int)$id, $_SESSION['user_id'] ?? 1);
            return $this->jsonResponse(['success'=>true,'message'=>'PO approved']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function cancel($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->poService->cancel((int)$id);
            return $this->jsonResponse(['success'=>true,'message'=>'PO cancelled']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function receive($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $grnId = $this->poService->receive((int)$id, $_POST);
            return $this->jsonResponse(['success'=>true,'message'=>'GRN created','grn_id'=>$grnId]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->poService->delete((int)$id);
            return $this->jsonResponse(['success'=>true,'message'=>'PO deleted']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}