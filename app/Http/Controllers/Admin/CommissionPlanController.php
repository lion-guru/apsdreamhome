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

        // Fetch saved presets
        $presets = [];
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = \App\Core\Middleware\TenantContext::getId();
            $stmt = $pdo->prepare("SELECT * FROM commission_simulator_presets WHERE tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$tid]);
            $presets = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::simulator presets error: ' . $e->getMessage());
        }

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
            } elseif ($mode === 'referral_sweep') {
                $result = $this->simulator->referralSweep(
                    (float)($_POST['candidate_pct'] ?? 2.0),
                    max(1, (int)($_POST['window_days'] ?? 90))
                );
                $result['sim_kind'] = 'referral_sweep';
            } elseif ($mode === 'wallet_sweep') {
                $result = $this->simulator->walletSweep(
                    (float)($_POST['candidate_l1'] ?? 20.0),
                    (float)($_POST['candidate_l2'] ?? 5.0),
                    max(1, (int)($_POST['window_days'] ?? 90))
                );
                $result['sim_kind'] = 'wallet_sweep';
            } else {
                $result = $this->simulator->simulateSale($saleAmount, $planId, $rankIdx);
            }
        }

        $this->render('admin/commission/plans/simulator', compact('plans', 'activePlan', 'result', 'presets'));
    }

    /* ── APPLY WALLET SWEEP (bulk package pct update, audited) ── */
    public function applyWalletPct()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect('/admin/commission-plans/simulator');
            return;
        }

        try {
            $l1 = min(max((float)($_POST['candidate_l1'] ?? 20.0), 0.0), 30.0);
            $l2 = min(max((float)($_POST['candidate_l2'] ?? 5.0), 0.0), max(0.0, 30.0 - $l1));
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $before = $pdo->query(
                "SELECT COUNT(*) AS n, GROUP_CONCAT(DISTINCT referral_pct_l1) AS l1s, GROUP_CONCAT(DISTINCT referral_pct_l2) AS l2s
                 FROM wallet_activation_packages WHERE is_active = 1"
            )->fetch(\PDO::FETCH_ASSOC) ?: [];
            $count = (int)($before['n'] ?? 0);
            if ($count <= 0) {
                $_SESSION['error'] = 'No active wallet packages to update.';
                $this->redirect('/admin/commission-plans/simulator');
                return;
            }
            $stmt = $pdo->prepare(
                "UPDATE wallet_activation_packages SET referral_pct_l1 = ?, referral_pct_l2 = ?, updated_at = NOW() WHERE is_active = 1"
            );
            $stmt->execute([$l1, $l2]);
            $cfg = \App\Services\ServiceConfigService::getInstance();
            $cfg->auditManual(
                'wallet_packages',
                'referral_pct_l1/l2 (bulk)',
                'L1 in (' . ($before['l1s'] ?? '?') . ') L2 in (' . ($before['l2s'] ?? '?') . ") on {$count} package(s)",
                "L1={$l1}% L2={$l2}% on {$count} package(s)",
                'Applied from commission simulator wallet sweep'
            );
            $_SESSION['success'] = "Applied L1={$l1}% L2={$l2}% to {$count} package(s). Past activations unchanged.";
            $this->redirect('/admin/commission-plans/simulator');
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::applyWalletPct error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to apply: ' . $e->getMessage();
            $this->redirect('/admin/commission-plans/simulator');
        }
    }

    /* ── PRESETS: Save ── */
    public function savePreset()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $simMode = $_POST['sim_mode'] ?? 'single';
        $paramsJson = $_POST['params_json'] ?? '{}';

        if (!$name) {
            $_SESSION['error'] = 'Preset name is required';
            $this->redirect('/admin/commission-plans/simulator');
            return;
        }

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = \App\Core\Middleware\TenantContext::getId();
            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;

            $stmt = $pdo->prepare(
                "INSERT INTO commission_simulator_presets (name, description, sim_mode, params_json, tenant_id, created_by) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$name, $description, $simMode, $paramsJson, $tid, $userId]);

            $_SESSION['success'] = "Preset '{$name}' saved";
        } catch (\Throwable $e) {
            if ($e->getCode() === 23000) {
                $_SESSION['error'] = 'A preset with this name already exists';
            } else {
                error_log('CommissionPlanController::savePreset error: ' . $e->getMessage());
                $_SESSION['error'] = 'Failed to save preset: ' . $e->getMessage();
            }
        }
        $this->redirect('/admin/commission-plans/simulator');
    }

    /* ── PRESETS: Load (AJAX) ── */
    public function getPreset($id)
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = \App\Core\Middleware\TenantContext::getId();
            $stmt = $pdo->prepare("SELECT * FROM commission_simulator_presets WHERE id = ? AND tenant_id = ? LIMIT 1");
            $stmt->execute([(int)$id, $tid]);
            $preset = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$preset) {
                echo json_encode(['success' => false, 'message' => 'Preset not found']);
                return;
            }

            echo json_encode(['success' => true, 'preset' => $preset]);
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::getPreset error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to load preset']);
        }
    }

    /* ── PRESETS: Delete ── */
    public function deletePreset($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        header('Content-Type: application/json');

        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = \App\Core\Middleware\TenantContext::getId();
            $stmt = $pdo->prepare("DELETE FROM commission_simulator_presets WHERE id = ? AND tenant_id = ?");
            $stmt->execute([(int)$id, $tid]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Preset deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Preset not found']);
            }
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::deletePreset error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Failed to delete preset']);
        }
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