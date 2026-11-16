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
            $db = \App\Core\Database\Database::getInstance();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$userId];
            if (TenantContext::getId() > 1) {
                $params[] = TenantContext::getId();
            }

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
            if (!empty($allDownline)) {
                $placeholders = implode(',', array_fill(0, count($allDownline), '?'));
                $members = $db->fetchAll("SELECT user_id, current_level FROM mlm_profiles WHERE user_id IN ($placeholders)", $allDownline) ?: [];
                foreach ($members as $member) {
                    $rank = $member['current_level'] ?? 'associate';
                    $byRank[$rank] = ($byRank[$rank] ?? 0) + 1;
                }
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
        $db = \App\Core\Database\Database::getInstance();
        $params = [$userId];
        if (TenantContext::getId() > 1) {
            $params[] = TenantContext::getId();
        }

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
        $db = \App\Core\Database\Database::getInstance()->getConnection();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $tid = TenantContext::getId();

        // Active plan
        $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];
        $stmt = $db->prepare("SELECT * FROM mlm_commission_plans WHERE status = 'active'{$tidSql} ORDER BY version DESC LIMIT 1");
        $stmt->execute($params);
        $plan = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;

        // Plan levels
        $levels = [];
        if ($plan) {
            $lstmt = $db->prepare("SELECT * FROM mlm_plan_levels WHERE plan_id = ? ORDER BY level_order");
            $lstmt->execute([(int)$plan['id']]);
            $levels = $lstmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        // Rank benefits (active, ordered)
        $rbSql = $tid > 1 ? " WHERE is_active = 1 AND tenant_id = ?" : " WHERE is_active = 1";
        $rbParams = $tid > 1 ? [$tid] : [];
        $rstmt = $db->prepare("SELECT * FROM mlm_rank_benefits{$rbSql} ORDER BY rank_order");
        $rstmt->execute($rbParams);
        $rankBenefits = $rstmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        // Associate profile
        $pstmt = $db->prepare("SELECT * FROM associate_profiles WHERE user_id = ? LIMIT 1");
        $pstmt->execute([$userId]);
        $profile = $pstmt->fetch(\PDO::FETCH_ASSOC) ?: [];

        // Referral code stick in session for the view's link block
        if (!empty($profile['referral_code'])) {
            $_SESSION['referral_code'] = $profile['referral_code'];
        }

        // Current rank (profile slug) + display name
        $ladder = ['associate', 'senior_associate', 'bdm', 'sr_bdm', 'vice_president', 'president', 'site_manager'];
        $displayNames = [
            'associate' => 'Associate',
            'senior_associate' => 'Sr. Associate',
            'bdm' => 'BDM',
            'sr_bdm' => 'Sr. BDM',
            'vice_president' => 'Vice President',
            'president' => 'President',
            'site_manager' => 'Site Manager',
        ];
        $currentLevel = strtolower((string)($profile['level'] ?? 'associate'));
        $curIdx = array_search($currentLevel, $ladder, true);
        $curIdx = $curIdx === false ? 0 : $curIdx;
        $currentSlug = $ladder[$curIdx];
        $currentRank = $displayNames[$currentSlug];

        // Next rank from rank_benefits
        $nextRank = null;
        foreach ($rankBenefits as $rb) {
            if ((int)$rb['rank_order'] === $curIdx + 2) {
                $nextRank = $rb;
                $nextRank['display_name'] = $displayNames[strtolower(str_replace([' ', '.'], ['_', ''], (string)$rb['rank_name']))] ?? (string)$rb['rank_name'];
                $nextRank['level_name'] = $nextRank['display_name'];
                break;
            }
        }

        // Team size = full unilevel tree under this associate
        $teamSize = 0;
        $visited = 0;
        $frontier = [$userId];
        while (!empty($frontier) && $visited < 10000) {
            $in = implode(',', array_fill(0, count($frontier), '?'));
            $stmt = $db->prepare("SELECT associate_id FROM mlm_network_tree WHERE parent_id IN ($in)");
            $stmt->execute($frontier);
            $kids = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
            $teamSize += count($kids);
            $visited += count($kids);
            $frontier = $kids;
        }

        // Direct referrals = root-level children
        $dstmt = $db->prepare("SELECT COUNT(*) AS c FROM mlm_network_tree WHERE parent_id = ?");
        $dstmt->execute([$userId]);
        $directReferrals = (int)($dstmt->fetchColumn() ?: 0);

        // Total commission earned (canonical ledger path)
        $cstmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger WHERE beneficiary_user_id = ?");
        $cstmt->execute([$userId]);
        $commissionTotal = (float)$cstmt->fetchColumn();

        $userProfile = [
            'name'             => '',
            'email'            => '',
            'phone'            => '',
            'referral_code'    => $profile['referral_code'] ?? '',
            'level'            => $currentLevel,
            'direct_referrals' => $directReferrals,
            'lifetime_sales'   => (float)($profile['total_sales'] ?? 0),
            'commission_earned'=> (float)($profile['commission_earned'] ?? $commissionTotal),
        ];

        $this->render('associate/mlm_plan', [
            'page_title'            => 'MLM Plan - Associate Portal',
            'page_description'      => 'View the commission plan and rank structure',
            'plan'                  => $plan,
            'current_plan'          => $plan ? ['plan_name' => $plan['plan_name'] ?? 'MLM Plan'] : null,
            'levels'                => $levels,
            'rank_benefits'         => $rankBenefits,
            'current_rank'          => $currentRank,
            'user_profile'          => $userProfile,
            'user_team_size'        => $teamSize,
            'user_commission_total' => $commissionTotal,
            'next_rank'             => $nextRank,
            'mlm_settings'          => [],
        ], 'layouts/associate');
    }
}

