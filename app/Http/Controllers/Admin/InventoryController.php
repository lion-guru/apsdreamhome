<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\InventoryService;
use Exception;

class InventoryController extends AdminController
{
    protected $invService;

    public function __construct()
    {
        parent::__construct();
        $this->invService = new InventoryService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $category = $_GET['category'] ?? '';
            $materials = $this->invService->getAll($status ?: null, $category ?: null);
            $stats = $this->invService->getStats();
            return $this->render('admin/inventory_mgmt/index', [
                'page_title' => 'Inventory Management - APS Dream Home',
                'active_page' => 'inventory_mgmt',
                'materials' => $materials,
                'stats' => $stats,
                'filters' => ['status' => $status, 'category' => $category],
                'categories' => $this->invService->getCategories(),
                'statuses' => ['in_stock','low_stock','out_of_stock','discontinued'],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load inventory: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/inventory_mgmt/create', [
            'page_title' => 'Add Material',
            'active_page' => 'inventory_mgmt',
            'categories' => $this->invService->getCategories(),
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $required = ['material_name','material_category','unit','current_stock','unit_cost'];
            foreach ($required as $f) {
                if (empty($_POST[$f])) return $this->jsonError(ucfirst(str_replace('_',' ',$f)).' is required', 400);
            }
            $id = $this->invService->create($_POST);
            return $this->jsonResponse(['success'=>true,'message'=>'Material added','material_id'=>$id,'redirect'=>BASE_URL.'/admin/inventory']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: '.$e->getMessage(), 500);
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $material = $this->invService->getById((int)$id);
        if (!$material) { $this->setFlash('error','Material not found'); return $this->redirect('/admin/inventory'); }
        return $this->render('admin/inventory_mgmt/edit', [
            'page_title' => 'Edit: '.$material['material_name'],
            'active_page' => 'inventory_mgmt',
            'material' => $material,
            'categories' => $this->invService->getCategories(),
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->invService->update((int)$id, $_POST);
            return $this->jsonResponse(['success'=>true,'message'=>'Updated','redirect'=>BASE_URL.'/admin/inventory']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: '.$e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->invService->delete((int)$id);
            return $this->jsonResponse(['success'=>true,'message'=>'Deleted']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function stockIn()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->invService->stockIn((int)$_POST['material_id'], $_POST);
            return $this->jsonResponse(['success'=>true,'message'=>'Stock added']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function stockOut()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->invService->stockOut((int)$_POST['material_id'], $_POST);
            return $this->jsonResponse(['success'=>true,'message'=>'Stock issued']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function valuation()
    {
        $this->requireAdmin();
        try {
            $method = $_GET['method'] ?? 'weighted_average';
            if (!in_array($method, ['weighted_average', 'fifo', 'lifo'])) {
                $method = 'weighted_average';
            }
            $data = $this->invService->getValuationReport($method);
            return $this->render('admin/inventory_mgmt/valuation', [
                'page_title' => 'Inventory Valuation - APS Dream Home',
                'active_page' => 'inventory_mgmt',
                'data' => $data,
                'method' => $method,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load valuation: ' . $e->getMessage());
            return $this->redirect('/admin/inventory');
        }
    }

    public function usageLog()
    {
        $this->requireAdmin();
        $materialId = !empty($_GET['material_id']) ? (int)$_GET['material_id'] : null;
        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;
        $logs = $this->invService->getUsageLog($materialId, $colonyId);
        return $this->render('admin/inventory_mgmt/usage_log', [
            'page_title' => 'Material Usage Log',
            'active_page' => 'inventory_mgmt',
            'logs' => $logs,
            'material_id' => $materialId,
            'colony_id' => $colonyId,
        ]);
    }
}