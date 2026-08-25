<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateTeamController
 * Handles associate team/MLM network
 */
class TeamController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * My Team / Downline
     */
    public function team()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            // Direct referrals
            $direct = $db->fetchAll("
                SELECT mnt.*, u.name, u.email, u.phone, u.status, u.created_at,
                       mp.current_level, mp.lifetime_sales
                FROM mlm_network_tree mnt
                JOIN users u ON u.id = mnt.associate_id
                LEFT JOIN mlm_profiles mp ON mp.user_id = mnt.associate_id
                WHERE mnt.parent_id = ?{$tidSql}
                ORDER BY mnt.associate_id
            ", $params) ?: [];

            // Total team stats
            $totalDirect = count($direct);
            $activeDirect = count(array_filter($direct, fn($d) => ($d['status'] ?? '') === 'active'));

            // Recursive function to get all downline
            $allDownline = $this->getAllDownline($userId, $tidSql);
            $totalTeam = count($allDownline);

            // Team by rank
            $byRank = [];
            foreach ($allDownline as $member) {
                $rank = $member['current_level'] ?? 'associate';
                $byRank[$rank] = ($byRank[$rank] ?? 0) + 1;
            }

            // Recent joinings
            $recent = $db->fetchAll("
                SELECT u.name, u.email, u.phone, u.created_at, mp.current_level
                FROM users u
                LEFT JOIN mlm_profiles mp ON mp.user_id = u.id
                WHERE u.id IN (SELECT associate_id FROM mlm_network_tree WHERE parent_id = ?{$tidSql})
                ORDER BY u.created_at DESC LIMIT 10
            ", $params) ?: [];

            $this->render('associate/team', [
                'page_title' => 'My Team - Associate Portal',
                'page_description' => 'View your downline network',
                'direct' => $direct,
                'total_direct' => $totalDirect,
                'active_direct' => $activeDirect,
                'total_team' => $totalTeam,
                'by_rank' => $byRank,
                'recent' => $recent,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateTeamController error: ' . $e->getMessage());
        }
    }

    /**
     * Recursively get all downline members
     */
    private function getAllDownline($userId, $tidSql): array
    {
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $params = [$userId];
        if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

        $direct = $db->fetchAll("SELECT associate_id FROM mlm_network_tree WHERE parent_id = ?{$tidSql}", $params) ?: [];
        $all = [];

        foreach ($direct as $child) {
            $childId = (int)$child['associate_id'];
            $all[] = $childId;
            $all = array_merge($all, $this->getAllDownline($childId, $tidSql));
        }

        return $all;
    }

    /**
     * MLM Plan details
     */
    public function mlmPlan()
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];

        // Get active plan
        $stmt = $db->prepare("SELECT * FROM mlm_commission_plans WHERE status = 'active'{$tidSql} ORDER BY version DESC LIMIT 1");
        $stmt->execute($params);
        $plan = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Plan levels
        $levels = [];
        if ($plan) {
            $stmt = $db->prepare("SELECT * FROM mlm_plan_levels WHERE plan_id = ? ORDER BY level_order");
            $stmt->execute([$plan['id']]);
            $levels = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        // Rank benefits
        $ranks = [];
        $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
        $params = TenantContext::getId() > 1 ? [TenantContext::getId()] : [];
        $stmt = $db->prepare("SELECT * FROM mlm_rank_benefits{$tidSql} ORDER BY rank_order");
        $stmt->execute($params);
        $ranks = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $this->render('associate/mlm_plan', [
            'page_title' => 'MLM Plan - Associate Portal',
            'page_description' => 'View the commission plan and rank structure',
            'plan' => $plan,
            'levels' => $levels,
            'ranks' => $ranks,
        ], 'layouts/associate');
    }
}

