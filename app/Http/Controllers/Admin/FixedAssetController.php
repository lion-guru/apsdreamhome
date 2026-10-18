<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\FixedAssetService;
use Exception;

class FixedAssetController extends AdminController
{
    protected $assetService;

    public function __construct()
    {
        parent::__construct();
        $this->assetService = new FixedAssetService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $category = $_GET['category'] ?? '';
            $assets = $this->assetService->getAll($status ?: null, $category ?: null);
            $stats = $this->assetService->getStats();

            return $this->render('admin/fixed_assets/index', [
                'page_title' => 'Fixed Assets - APS Dream Home',
                'active_page' => 'fixed_assets',
                'assets' => $assets,
                'stats' => $stats,
                'filters' => ['status' => $status, 'category' => $category],
                'categories' => ['building','vehicle','furniture','equipment','land','computer','other'],
                'statuses' => ['active','disposed','sold','stolen','damaged','retired'],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load assets: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/fixed_assets/create', [
            'page_title' => 'Add Fixed Asset',
            'active_page' => 'fixed_assets',
            'departments' => $this->assetService->getDepartments(),
            'employees' => $this->assetService->getEmployees(),
            'categories' => ['building','vehicle','furniture','equipment','land','computer','other'],
            'today' => date('Y-m-d'),
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $required = ['asset_code','asset_name','category','purchase_date','purchase_cost','useful_life_years'];
            foreach ($required as $f) {
                if (empty($_POST[$f])) return $this->jsonError(ucfirst(str_replace('_',' ',$f)).' is required', 400);
            }
            $existing = $this->db->fetchOne("SELECT id FROM fixed_assets WHERE asset_code = ?", [$_POST['asset_code']]);
            if ($existing) return $this->jsonError('Asset code already exists', 400);

            $id = $this->assetService->create($_POST);
            $this->logActivity('asset_created', ['asset_id' => $id, 'code' => $_POST['asset_code']]);
            return $this->jsonResponse(['success'=>true,'message'=>'Asset created','asset_id'=>$id,'redirect'=>BASE_URL.'/admin/fixed-assets']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: '.$e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $asset = $this->assetService->getById((int)$id);
            if (!$asset) { $this->setFlash('error','Asset not found'); return $this->redirect('/admin/fixed-assets'); }
            $schedule = $this->assetService->getDepreciationSchedule((int)$id);
            return $this->render('admin/fixed_assets/show', [
                'page_title' => 'Asset: '.$asset['asset_name'],
                'active_page' => 'fixed_assets',
                'asset' => $asset,
                'schedule' => $schedule,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error','Failed: '.$e->getMessage());
            return $this->redirect('/admin/fixed-assets');
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $asset = $this->assetService->getById((int)$id);
        if (!$asset) { $this->setFlash('error','Asset not found'); return $this->redirect('/admin/fixed-assets'); }
        return $this->render('admin/fixed_assets/edit', [
            'page_title' => 'Edit Asset: '.$asset['asset_name'],
            'active_page' => 'fixed_assets',
            'asset' => $asset,
            'departments' => $this->assetService->getDepartments(),
            'employees' => $this->assetService->getEmployees(),
            'categories' => ['building','vehicle','furniture','equipment','land','computer','other'],
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->assetService->update((int)$id, $_POST);
            return $this->jsonResponse(['success'=>true,'message'=>'Updated','redirect'=>BASE_URL.'/admin/fixed-assets']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: '.$e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->assetService->delete((int)$id);
            return $this->jsonResponse(['success'=>true,'message'=>'Deleted']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function generateSchedule($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $count = $this->assetService->generateDepreciationSchedule((int)$id, $_SESSION['user_id'] ?? 1);
            return $this->jsonResponse(['success'=>true,'message'=>"Generated $count depreciation entries"]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function postDepreciation($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $year = (int)$_POST['period_year'];
            $month = (int)$_POST['period_month'];
            $this->assetService->postDepreciation((int)$id, $year, $month, $_SESSION['user_id'] ?? 1);
            return $this->jsonResponse(['success'=>true,'message'=>'Depreciation posted']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function depreciationReport()
    {
        $this->requireAdmin();
        $year = !empty($_GET['year']) ? (int)$_GET['year'] : null;
        $report = $this->assetService->getDepreciationReport($year);
        return $this->render('admin/fixed_assets/depreciation', [
            'page_title' => 'Depreciation Report',
            'active_page' => 'fixed_assets',
            'report' => $report,
            'year' => $year,
        ]);
    }
}