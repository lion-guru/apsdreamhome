<?php
namespace App\Http\Controllers\Admin;

use App\Services\RankEvaluationService;

class MLMSettingsController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    private $rankService;

    public function __construct()
    {
        parent::__construct();
        $this->rankService = new RankEvaluationService();
    }

    public function levels()
    {
        $this->requireAdmin();
        $levels = $this->db->fetchAll("SELECT * FROM mlm_levels ORDER BY level_number ASC");
        $this->render('admin/mlm-settings/levels', [
            'page_title' => 'MLM Levels - Admin',
            'levels' => $levels,
        ]);
    }

    public function editLevel($id)
    {
        $this->requireAdmin();
        $level = $this->db->fetch("SELECT * FROM mlm_levels WHERE id = ?", [$id]);
        if (!$level) {
            $this->setFlash('error', 'Level not found');
            $this->redirect('/admin/mlm-settings/levels');
            return;
        }
        $this->render('admin/mlm-settings/edit_level', [
            'page_title' => 'Edit Level - Admin',
            'level' => $level,
        ]);
    }

    public function updateLevel($id)
    {
        $this->requireAdmin();
        $fields = ['level_name', 'level_number', 'direct_commission_percentage', 'team_commission_percentage',
            'level_difference_commission_percentage', 'matching_bonus_percentage', 'leadership_bonus_percentage',
            'performance_bonus_percentage', 'joining_fee', 'monthly_maintenance',
            'team_size_required', 'direct_referrals_required', 'monthly_target'];

        $set = [];
        $params = [];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) {
                $set[] = "$f = ?";
                $params[] = $_POST[$f];
            }
        }
        if (empty($set)) {
            $this->setFlash('error', 'No fields to update');
            $this->redirect('/admin/mlm-settings/levels');
            return;
        }
        $params[] = $id;
        $this->db->query("UPDATE mlm_levels SET " . implode(', ', $set) . " WHERE id = ?", $params);
        $this->setFlash('success', 'Level updated successfully');
        $this->redirect('/admin/mlm-settings/levels');
    }

    public function rules()
    {
        $this->requireAdmin();
        try {
            $rules = $this->db->fetchAll("SELECT * FROM commission_calculation_rules ORDER BY priority ASC");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $this->render('admin/mlm-settings/rules', [
            'page_title' => 'Commission Rules - Admin',
            'rules' => $rules,
        ]);
    }

    public function updateRule($id)
    {
        $this->requireAdmin();
        $rule = $this->db->fetch("SELECT * FROM commission_calculation_rules WHERE id = ?", [$id]);
        if (!$rule) {
            $this->setFlash('error', 'Rule not found');
            $this->redirect('/admin/mlm-settings/rules');
            return;
        }
        $rate = $_POST['rate_percentage'] ?? $rule['rate_percentage'];
        $active = isset($_POST['is_active']) ? 1 : 0;
        try {
            [$tw, $tp] = $this->tenantWhere();
            $this->db->query("UPDATE commission_calculation_rules SET rate_percentage = ?, is_active = ? WHERE id = ?" . $tw,
                array_merge([$rate, $active, $id], $tp));
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $this->setFlash('success', 'Rule updated');
        $this->redirect('/admin/mlm-settings/rules');
    }

    public function evaluateRanks()
    {
        $this->requireAdmin();
        $results = $this->rankService->evaluateAll();
        $promoted = array_filter($results, fn($r) => $r['promoted']);
        $this->render('admin/mlm-settings/evaluate', [
            'page_title' => 'Rank Evaluation - Admin',
            'results' => $results,
            'promoted_count' => count($promoted),
            'total_count' => count($results),
        ]);
    }

    public function associateProgress()
    {
        $this->requireAdmin();
        $users = $this->db->fetchAll("
            SELECT u.id, u.name, u.email, u.phone,
                   mp.current_level, mp.direct_referrals, mp.lifetime_sales,
                   mp.total_team_size
            FROM mlm_profiles mp
            JOIN users u ON mp.user_id = u.id
            WHERE mp.status = 'active'
            ORDER BY mp.current_level DESC, mp.lifetime_sales DESC
        ");
        $progressData = [];
        foreach ($users as $a) {
            $progress = $this->rankService->getProgress($a['id']);
            $progressData[] = $progress ?? [];
        }
        $this->render('admin/mlm-settings/associate_progress', [
            'page_title' => 'Associate Rank Progress - Admin',
            'users' => $users,
            'progress_data' => $progressData,
        ]);
    }
}
