<?php
namespace App\Http\Controllers\Admin;

use App\Services\CommissionPlanService;
use App\Services\CommissionSimulator;

class CommissionPlanController extends AdminController
{
    /** @var CommissionPlanService */
    private $planService;

    /** @var CommissionSimulator */
    private $simulator;

    public function __construct()
    {
        parent::__construct();
        $pdo = \App\Core\Database\Database::getInstance()->getConnection();
        $this->planService = new CommissionPlanService($pdo);
        $this->simulator = new CommissionSimulator($pdo);
    }

    /* ── LIST ── */
    public function index()
    {
        $this->requireAdmin();
        $plans = $this->planService->getAllPlans();
        $activePlan = $this->planService->getActivePlan();
        $stats = $this->planService->getStats();
        $this->render('admin/commission/plans/index', compact('plans', 'activePlan', 'stats'));
    }

    /* ── CREATE ── */
    public function create()
    {
        $this->requireAdmin();
        $this->render('admin/commission/plans/create', []);
    }

    /* ── STORE ── */
    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        if (empty($_POST['plan_name']) || empty($_POST['plan_code'])) {
            $_SESSION['error'] = 'Plan name and code are required';
            $this->redirect('/admin/commission-plans/create');
            return;
        }

        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $this->planService->createPlan($_POST, $userId);
            $_SESSION['success'] = "Commission plan '{$_POST['plan_name']}' created as v1 with 7 default levels";
            $this->redirect('/admin/commission-plans');
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::store error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to create plan: ' . $e->getMessage();
            $this->redirect('/admin/commission-plans/create');
        }
    }

    /* ── EDIT ── */
    public function edit($id)
    {
        $this->requireAdmin();
        $plan = $this->planService->getPlanById((int)$id);
        if (!$plan) {
            $_SESSION['error'] = 'Plan not found';
            $this->redirect('/admin/commission-plans');
            return;
        }
        $versions = $this->planService->getPlanVersions($plan['plan_code']);
        $this->render('admin/commission/plans/edit', compact('plan', 'versions'));
    }

    /* ── UPDATE ── */
    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $this->planService->updatePlan((int)$id, $_POST, $userId);
            $_SESSION['success'] = 'Plan updated successfully';
            $this->redirect('/admin/commission-plans/edit/' . $id);
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::update error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update: ' . $e->getMessage();
            $this->redirect('/admin/commission-plans/edit/' . $id);
        }
    }

    /* ── CLONE AS NEW VERSION ── */
    public function cloneVersion($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $overrides = [
                'effective_date' => $_POST['effective_date'] ?? date('Y-m-d'),
                'description' => $_POST['description'] ?? null,
            ];
            $newId = $this->planService->clonePlanAsNewVersion((int)$id, $overrides, $userId);
            $_SESSION['success'] = 'New version created from this plan';
            $this->redirect('/admin/commission-plans/edit/' . $newId);
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::cloneVersion error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to clone: ' . $e->getMessage();
            $this->redirect('/admin/commission-plans/edit/' . $id);
        }
    }

    /* ── ACTIVATE ── */
    public function activate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $this->planService->activatePlan((int)$id, $userId);
            $_SESSION['success'] = 'Commission plan activated';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/commission-plans');
    }

    /* ── DEACTIVATE ── */
    public function deactivate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $this->planService->deactivatePlan((int)$id, $userId);
            $_SESSION['success'] = 'Commission plan deactivated';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/commission-plans');
    }

    /* ── DELETE ── */
    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $this->planService->deletePlan((int)$id, $userId);
            $_SESSION['success'] = 'Plan deleted';
        } catch (\Throwable $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect('/admin/commission-plans');
    }

    /* ── HISTORY (Audit Log) ── */
    public function history()
    {
        $this->requireAdmin();
        $planId = (int)($_GET['plan_id'] ?? 0);
        $auditLog = $planId
            ? $this->planService->getAuditLog($planId)
            : $this->planService->getFullAuditLog();
        $plans = $this->planService->getAllPlans();
        $this->render('admin/commission/plans/history', compact('auditLog', 'plans', 'planId'));
    }

    /* ── COMPARE ── */
    public function compare()
    {
        $this->requireAdmin();
        $planIdA = (int)($_GET['plan_a'] ?? 0);
        $planIdB = (int)($_GET['plan_b'] ?? 0);
        $comparison = null;
        if ($planIdA && $planIdB) {
            $comparison = $this->planService->comparePlans($planIdA, $planIdB);
        }
        $plans = $this->planService->getAllPlans();
        $this->render('admin/commission/plans/compare', compact('comparison', 'plans', 'planIdA', 'planIdB'));
    }

    /* ── SIMULATOR (Advanced What-If) ── */
    public function simulator()
    {
        $this->requireAdmin();
        $plans = $this->planService->getAllPlans();
        $activePlan = $this->planService->getActivePlan();
        $result = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $saleAmount = (float)($_POST['sale_amount'] ?? 1500000);
            $planId = (int)($_POST['plan_id'] ?? ($activePlan['id'] ?? 0));
            $rankIdx = (int)($_POST['rank_index'] ?? 0);

            $mode = $_POST['sim_mode'] ?? 'single';

            if ($mode === 'compare') {
                $planIdB = (int)($_POST['plan_id_b'] ?? 0);
                $result = $this->simulator->comparePlans($saleAmount, $planId, $planIdB, $rankIdx);
            } elseif ($mode === 'bulk') {
                $result = $this->simulator->bulkSimulate($saleAmount, $planId);
            } elseif ($mode === 'sensitivity') {
                $result = $this->simulator->sensitivityAnalysis($planId, $rankIdx);
            } else {
                $result = $this->simulator->simulateSale($saleAmount, $planId, $rankIdx);
            }
        }

        $this->render('admin/commission/plans/simulator', compact('plans', 'activePlan', 'result'));
    }

    /* ── CALCULATOR ── */
    public function calculator()
    {
        $this->requireAdmin();
        $plans = $this->planService->getAllPlans();
        $activePlan = $this->planService->getActivePlan();
        $levels = $activePlan ? $this->planService->getLevelsForPlan($activePlan['id']) : [];
        $csrf_token = $_SESSION['csrf_token'] ?? '';
        $this->render('admin/commission/plans/calculator', compact('plans', 'activePlan', 'levels', 'csrf_token'));
    }

    /* ── AJAX: Load Levels ── */
    public function getLevels()
    {
        $this->requireAdmin();
        $planId = (int)($_GET['plan_id'] ?? 0);
        try {
            $levels = $this->planService->getLevelsForPlan($planId);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'levels' => $levels]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /* ── AJAX: Simulate ── */
    public function ajaxSimulate()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $saleAmount = (float)($_GET['sale_amount'] ?? 1500000);
        $planId = (int)($_GET['plan_id'] ?? 0);
        $rankIdx = (int)($_GET['rank_index'] ?? 0);

        if (!$planId) {
            $activePlan = $this->planService->getActivePlan();
            $planId = $activePlan['id'] ?? 0;
        }

        $result = $this->simulator->simulateSale($saleAmount, $planId, $rankIdx);
        echo json_encode($result);
    }
}
