<?php
namespace App\Http\Controllers\Admin;

class CommissionPlanController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $plans = $this->db->fetchAll("
                SELECT p.*, 
                    (SELECT COUNT(*) FROM mlm_plan_levels WHERE plan_id = p.id) as level_count,
                    (SELECT COALESCE(SUM(direct_commission + team_commission + level_bonus + matching_bonus + leadership_bonus + performance_bonus), 0) FROM mlm_plan_levels WHERE plan_id = p.id) as total_commission_pct
                FROM mlm_commission_plans p 
                ORDER BY p.status = 'active' DESC, p.created_at DESC
            ");
            $activePlan = $this->db->fetchOne("SELECT * FROM mlm_commission_plans WHERE status = 'active'");
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::index error: ' . $e->getMessage());
            $plans = [];
            $activePlan = null;
        }
        $this->render('admin/commission/plans/index', ['plans' => $plans, 'activePlan' => $activePlan]);
    }

    public function create()
    {
        $this->requireAdmin();
        $this->render('admin/commission/plans/create', []);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $planName = trim($_POST['plan_name'] ?? '');
        $planCode = strtoupper(trim($_POST['plan_code'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $planType = $_POST['plan_type'] ?? 'hybrid';

        if (empty($planName) || empty($planCode)) {
            $_SESSION['error'] = 'Plan name and code are required';
            $this->redirect('/admin/commission-plans/create');
            return;
        }

        try {
            $existing = $this->db->fetchOne("SELECT id FROM mlm_commission_plans WHERE plan_code = ?", [$planCode]);
            if ($existing) {
                $_SESSION['error'] = 'Plan code already exists';
                $this->redirect('/admin/commission-plans/create');
                return;
            }

            $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1;
            $planId = $this->db->insert('mlm_commission_plans', [
                'plan_name' => $planName,
                'plan_code' => $planCode,
                'description' => $description,
                'plan_type' => $planType,
                'status' => 'draft',
                'created_by' => $userId,
            ]);

            $this->createDefaultLevels($planId);

            $_SESSION['success'] = "Commission plan '$planName' created successfully with 7 default levels";
            $this->redirect('/admin/commission-plans');
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::store error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to create plan: ' . $e->getMessage();
            $this->redirect('/admin/commission-plans/create');
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        try {
            $plan = $this->db->fetchOne("SELECT * FROM mlm_commission_plans WHERE id = ?", [$id]);
            if (!$plan) {
                $_SESSION['error'] = 'Plan not found';
                $this->redirect('/admin/commission-plans');
                return;
            }
            $levels = $this->db->fetchAll("SELECT * FROM mlm_plan_levels WHERE plan_id = ? ORDER BY level_order", [$id]);
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::edit error: ' . $e->getMessage());
            $plan = null;
            $levels = [];
        }
        $this->render('admin/commission/plans/edit', ['plan' => $plan, 'levels' => $levels]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $plan = $this->db->fetchOne("SELECT * FROM mlm_commission_plans WHERE id = ?", [$id]);
            if (!$plan) {
                $_SESSION['error'] = 'Plan not found';
                $this->redirect('/admin/commission-plans');
                return;
            }

            $this->db->update('mlm_commission_plans', [
                'plan_name' => trim($_POST['plan_name'] ?? $plan['plan_name']),
                'description' => trim($_POST['description'] ?? $plan['description']),
                'plan_type' => $_POST['plan_type'] ?? $plan['plan_type'],
            ], 'id = ?', [$id]);

            // Update level percentages if provided
            if (isset($_POST['levels']) && is_array($_POST['levels'])) {
                foreach ($_POST['levels'] as $levelId => $data) {
                    $this->db->update('mlm_plan_levels', [
                        'direct_commission' => (float)($data['direct_commission'] ?? 0),
                        'team_commission' => (float)($data['team_commission'] ?? 0),
                        'level_bonus' => (float)($data['level_bonus'] ?? 0),
                        'matching_bonus' => (float)($data['matching_bonus'] ?? 0),
                        'leadership_bonus' => (float)($data['leadership_bonus'] ?? 0),
                        'performance_bonus' => (float)($data['performance_bonus'] ?? 0),
                        'monthly_target' => (float)($data['monthly_target'] ?? 0),
                    ], 'id = ? AND plan_id = ?', [$levelId, $id]);
                }
            }

            $_SESSION['success'] = 'Plan updated successfully';
            $this->redirect('/admin/commission-plans/edit/' . $id);
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::update error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update plan: ' . $e->getMessage();
            $this->redirect('/admin/commission-plans/edit/' . $id);
        }
    }

    public function activate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            // Deactivate all
            $this->db->execute("UPDATE mlm_commission_plans SET status = 'inactive', updated_at = NOW() WHERE status = 'active'");
            // Activate selected
            $this->db->execute("UPDATE mlm_commission_plans SET status = 'active', updated_at = NOW() WHERE id = ?", [$id]);

            $_SESSION['success'] = 'Commission plan activated';
            $this->redirect('/admin/commission-plans');
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::activate error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to activate plan';
            $this->redirect('/admin/commission-plans');
        }
    }

    public function deactivate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $this->db->execute("UPDATE mlm_commission_plans SET status = 'inactive', updated_at = NOW() WHERE id = ?", [$id]);
            $_SESSION['success'] = 'Commission plan deactivated';
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::deactivate error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to deactivate plan';
        }
        $this->redirect('/admin/commission-plans');
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $plan = $this->db->fetchOne("SELECT * FROM mlm_commission_plans WHERE id = ?", [$id]);
            if (!$plan) {
                $_SESSION['error'] = 'Plan not found';
                $this->redirect('/admin/commission-plans');
                return;
            }

            if ($plan['status'] === 'active') {
                $_SESSION['error'] = 'Cannot delete an active plan. Deactivate it first.';
                $this->redirect('/admin/commission-plans');
                return;
            }

            $this->db->execute("DELETE FROM mlm_plan_levels WHERE plan_id = ?", [$id]);
            $this->db->execute("DELETE FROM mlm_commission_plans WHERE id = ?", [$id]);

            $_SESSION['success'] = "Plan '{$plan['plan_name']}' deleted";
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::delete error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to delete plan';
        }
        $this->redirect('/admin/commission-plans');
    }

    public function calculator()
    {
        $this->requireAdmin();
        try {
            $plans = $this->db->fetchAll("SELECT * FROM mlm_commission_plans ORDER BY plan_name");
            $activePlan = $this->db->fetchOne("SELECT * FROM mlm_commission_plans WHERE status = 'active'");
            $levels = [];
            if ($activePlan) {
                $levels = $this->db->fetchAll("SELECT * FROM mlm_plan_levels WHERE plan_id = ? ORDER BY level_order", [$activePlan['id']]);
            }
        } catch (\Throwable $e) {
            error_log('CommissionPlanController::calculator error: ' . $e->getMessage());
            $plans = [];
            $activePlan = null;
            $levels = [];
        }
        $this->render('admin/commission/plans/calculator', ['plans' => $plans, 'activePlan' => $activePlan, 'levels' => $levels]);
    }

    public function getLevels()
    {
        $this->requireAdmin();
        $planId = (int)($_GET['plan_id'] ?? 0);
        try {
            $levels = $this->db->fetchAll("SELECT * FROM mlm_plan_levels WHERE plan_id = ? ORDER BY level_order", [$planId]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'levels' => $levels]);
        } catch (\Throwable $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function createDefaultLevels($planId)
    {
        $defaults = [
            ['Associate', 1, 5.00, 2.00, 0.00, 0.00, 0.00, 0.00, 1000000],
            ['Sr. Associate', 2, 7.00, 3.00, 2.00, 5.00, 0.00, 0.00, 3500000],
            ['BDM', 3, 10.00, 4.00, 3.00, 8.00, 1.00, 0.00, 7000000],
            ['Sr. BDM', 4, 12.00, 5.00, 4.00, 10.00, 2.00, 1.00, 15000000],
            ['Vice President', 5, 15.00, 6.00, 5.00, 12.00, 3.00, 2.00, 30000000],
            ['President', 6, 18.00, 7.00, 6.00, 15.00, 4.00, 3.00, 50000000],
            ['Site Manager', 7, 20.00, 8.00, 7.00, 18.00, 5.00, 5.00, 999999999],
        ];

        foreach ($defaults as $d) {
            $this->db->insert('mlm_plan_levels', [
                'plan_id' => $planId,
                'level_name' => $d[0],
                'level_order' => $d[1],
                'direct_commission' => $d[2],
                'team_commission' => $d[3],
                'level_bonus' => $d[4],
                'matching_bonus' => $d[5],
                'leadership_bonus' => $d[6],
                'performance_bonus' => $d[7],
                'monthly_target' => $d[8],
            ]);
        }
    }
}
