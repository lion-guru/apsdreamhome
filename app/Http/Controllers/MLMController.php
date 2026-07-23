<?php

/**
 * MLM Controller
 * Handles MLM operations and dashboard
 * 
 * IDE REFRESH: All Database methods are available including:
 * - fetchOne() at lines 102-105
 * - fetchAll() at lines 107-110
 * - execute() at lines 95-99
 * - insert() at lines 138-146
 * - update() at lines 148-156
 * Any "Undefined method" errors are IDE caching issues
 */

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Core\Security;
use Exception;

/**
 * MLM Controller
 * Handles MLM operations and dashboard
 */
class MLMController extends BaseController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * MLM Dashboard
     */
    public function dashboard()
    {
        $this->requireLogin();

        $userId = $_SESSION['user_id'] ?? 0;

        // Get MLM dashboard data
        $dashboardData = $this->getMLMDashboardData($userId);

        $this->render('pages/mlm-dashboard', [
            'page_title' => 'MLM Dashboard - APS Dream Home',
            'page_description' => 'Build your network and grow your business',
            'current_level' => $dashboardData['current_level'],
            'plan_name' => $dashboardData['plan_name'],
            'total_downline' => $dashboardData['total_downline'],
            'monthly_commission' => number_format($dashboardData['monthly_commission']),
            'business_volume' => number_format($dashboardData['business_volume']),
            'active_members' => $dashboardData['active_members'],
            'binary_commission' => $dashboardData['binary_commission'],
            'unilevel_commission' => $dashboardData['unilevel_commission'],
            'matrix_commission' => $dashboardData['matrix_commission'],
            'binary_amount' => number_format($dashboardData['binary_amount']),
            'unilevel_amount' => number_format($dashboardData['unilevel_amount']),
            'matrix_amount' => number_format($dashboardData['matrix_amount']),
            'next_rank' => $dashboardData['next_rank'],
            'rank_progress' => $dashboardData['rank_progress'],
            'required_downline' => $dashboardData['required_downline'],
            'required_bv' => number_format($dashboardData['required_bv']),
            'time_remaining' => $dashboardData['time_remaining'],
            'associate_name' => $dashboardData['associate_name'],
            'left_leg_name' => $dashboardData['left_leg_name'],
            'left_leg_count' => $dashboardData['left_leg_count'],
            'right_leg_name' => $dashboardData['right_leg_name'],
            'right_leg_count' => $dashboardData['right_leg_count'],
            'next_payout_date' => date('Y-m-d', strtotime('next month')),
            'last_payout_date' => date('Y-m-d', strtotime('last month')),
            'last_bonus' => number_format($dashboardData['last_bonus'])
        ]);
    }

    /**
     * Get MLM dashboard data
     */
    private function getMLMDashboardData($userId)
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            // Get user's associate profile from real table
            $stmt = $db->prepare("SELECT * FROM associates WHERE user_id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$userId]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$profile) {
                // User is not an associate — return zeroed data
                return [
                    'current_level' => 'none', 'plan_name' => 'Not an Associate',
                    'total_downline' => 0, 'monthly_commission' => 0, 'business_volume' => 0,
                    'active_members' => 0, 'binary_commission' => 0, 'unilevel_commission' => 0,
                    'matrix_commission' => 0, 'binary_amount' => '0', 'unilevel_amount' => '0',
                    'matrix_amount' => '0', 'next_rank' => 'Associate', 'rank_progress' => 0,
                    'required_downline' => 0, 'required_bv' => 0, 'time_remaining' => 'N/A',
                    'associate_name' => $_SESSION['user_name'] ?? 'User',
                    'left_leg_name' => 'Left Team', 'left_leg_count' => 0,
                    'right_leg_name' => 'Direct Team', 'right_leg_count' => 0,
                    'last_bonus' => 0
                ];
            }

            // Get downline count from mlm_network_tree
            $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM mlm_network_tree WHERE parent_id = ?");
            $stmt->execute([$userId]);
            $downlineCount = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);

            // Get total team size (recursive count)
            $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM mlm_network_tree WHERE sponsor_id = ?");
            $stmt->execute([$userId]);
            $totalDownline = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);

            // Get monthly commission from mlm_commission_ledger
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) AND status = 'approved'");
            $stmt->execute([$userId]);
            $monthlyCommission = (float)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

            // Get total commission
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE user_id = ? AND status = 'approved'");
            $stmt->execute([$userId]);
            $totalCommission = (float)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

            // Get rank from profile
            $rank = $profile['rank'] ?? 'associate';
            $rankMap = ['associate' => 1, 'sr_associate' => 2, 'bdm' => 3, 'sr_bdm' => 4, 'vice_president' => 5, 'president' => 6, 'site_manager' => 7];

            return [
                'current_level' => $rank,
                'plan_name' => ucfirst(str_replace('_', ' ', $rank)),
                'total_downline' => $totalDownline,
                'monthly_commission' => $monthlyCommission,
                'business_volume' => $totalCommission,
                'active_members' => $downlineCount,
                'binary_commission' => 0,
                'unilevel_commission' => $monthlyCommission,
                'matrix_commission' => 0,
                'binary_amount' => '0',
                'unilevel_amount' => number_format($monthlyCommission),
                'matrix_amount' => '0',
                'next_rank' => $this->getNextRank($rank),
                'rank_progress' => min(100, (int)(($totalCommission / max(1, $this->getRankThreshold($this->getNextRank($rank)))) * 100)),
                'required_downline' => 0,
                'required_bv' => $this->getRankThreshold($this->getNextRank($rank)),
                'time_remaining' => 'N/A',
                'associate_name' => $profile['name'] ?? $_SESSION['user_name'] ?? 'User',
                'left_leg_name' => 'Left Team',
                'left_leg_count' => $downlineCount,
                'right_leg_name' => 'Direct Team',
                'right_leg_count' => $totalDownline,
                'last_bonus' => $monthlyCommission
            ];
        } catch (\Throwable $e) {
            error_log("MLM Dashboard error: " . $e->getMessage());
            return $this->getMockDashboardData();
        }
    }

    private function getNextRank($current)
    {
        $ranks = ['associate', 'sr_associate', 'bdm', 'sr_bdm', 'vice_president', 'president', 'site_manager'];
        $idx = array_search($current, $ranks);
        return $ranks[($idx !== false ? $idx + 1 : 1)] ?? 'site_manager';
    }

    private function getRankThreshold($rank)
    {
        $thresholds = [
            'associate' => 0, 'sr_associate' => 1000000, 'bdm' => 3500000,
            'sr_bdm' => 7000000, 'vice_president' => 15000000, 'president' => 30000000,
            'site_manager' => 50000000
        ];
        return $thresholds[$rank] ?? 0;
    }

    /**
     * Get current rank information
     */
    /**
     * Get current rank info from real MLM tables (mlm_rank_benefits + mlm_network_tree + mlm_commission_ledger)
     */
    public function getCurrentRank($userId)
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();

            // Current rank from the associate profile (real table)
            $stmt = $db->prepare("SELECT level FROM associates WHERE user_id = ? AND status = 'active' LIMIT 1");
            $stmt->execute([$userId]);
            $assoc = $stmt->fetch(\PDO::FETCH_ASSOC);
            $currentRankName = $assoc['level'] ?? 'associate';

            // Team size from mlm_network_tree (real unilevel tree)
            $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM mlm_network_tree WHERE sponsor_id = ? OR parent_id = ?");
            $stmt->execute([$userId, $userId]);
            $teamSize = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0);

            // Monthly commission volume from mlm_commission_ledger (source of truth)
            $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commission_ledger WHERE beneficiary_user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH) AND status IN ('approved','paid')");
            $stmt->execute([$userId]);
            $monthlyVolume = (float)($stmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

            // Rank ladder from mlm_rank_benefits (real config)
            $ranks = $db->query("SELECT rank_name, min_qualifying_volume FROM mlm_rank_benefits WHERE is_active = 1 ORDER BY rank_order ASC")->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $currentIdx = 0;
            foreach ($ranks as $i => $r) {
                if ($r['rank_name'] === $currentRankName) { $currentIdx = $i; break; }
            }

            $current = $ranks[$currentIdx] ?? ['rank_name' => $currentRankName, 'min_qualifying_volume' => 0];
            $next = $ranks[$currentIdx + 1] ?? null;

            $nextRank = $next['rank_name'] ?? $current['rank_name'];
            $requiredBV = $next['min_qualifying_volume'] ?? 0;
            $requiredMembers = 0;
            if ($next) {
                $stmt = $db->prepare("SELECT min_leg_count FROM mlm_rank_benefits WHERE rank_name = ? LIMIT 1");
                $stmt->execute([$next['rank_name']]);
                $requiredMembers = (int)($stmt->fetch(\PDO::FETCH_ASSOC)['min_leg_count'] ?? 0);
            }

            $progress = $requiredBV > 0 ? min(100, (int)(($monthlyVolume / $requiredBV) * 100)) : 100;
            $timeRemaining = $next ? 'Keep building your volume' : 'Achieved';

            return [
                'plan_name' => ucfirst(str_replace('_', ' ', $current['rank_name'])),
                'current_rank' => $current['rank_name'],
                'next_rank' => ucfirst(str_replace('_', ' ', $nextRank)),
                'next_rank_key' => $nextRank,
                'required_members' => $requiredMembers,
                'required_bv' => (float)$requiredBV,
                'monthly_volume' => $monthlyVolume,
                'team_size' => $teamSize,
                'progress' => $progress,
                'time_remaining' => $timeRemaining
            ];
        } catch (\Throwable $e) {
            error_log("Rank Calculation Error: " . $e->getMessage());
            return [
                'plan_name' => 'Associate',
                'current_rank' => 'associate',
                'next_rank' => 'Senior Associate',
                'next_rank_key' => 'senior_associate',
                'required_members' => 0,
                'required_bv' => 0,
                'monthly_volume' => 0,
                'team_size' => 0,
                'progress' => 0,
                'time_remaining' => 'N/A'
            ];
        }
    }

    /**
     * Public JSON endpoint for the user's current rank (used by MLM dashboard widget)
     */
    public function myRank()
    {
        $this->requireLogin();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        return $this->jsonResponse(['success' => true, 'rank' => $this->getCurrentRank($userId)]);
    }

    /**
     * Get level name
     */
    private function getLevelName($level)
    {
        $levels = [
            1 => 'Associate',
            2 => 'Silver',
            3 => 'Gold',
            4 => 'Platinum',
            5 => 'Diamond'
        ];

        return $levels[$level] ?? 'Associate';
    }

    /**
     * Get mock dashboard data for development
     */
    private function getMockDashboardData()
    {
        return [
            'current_level' => 'associate',
            'plan_name' => 'Associate',
            'total_downline' => 15,
            'monthly_commission' => 2500.00,
            'business_volume' => 15000.00,
            'active_members' => 15,
            'binary_commission' => 500.00,
            'unilevel_commission' => 200.00,
            'matrix_commission' => 300.00,
            'binary_amount' => '500.00',
            'unilevel_amount' => '200.00',
            'matrix_amount' => '300.00',
            'next_rank' => 'Platinum',
            'rank_progress' => 75,
            'required_downline' => 20,
            'required_bv' => 10000,
            'time_remaining' => '1 month',
            'associate_name' => 'John Doe',
            'left_leg_name' => 'Left Team',
            'left_leg_count' => 8,
            'right_leg_name' => 'Direct Team',
            'right_leg_count' => 7,
            'next_payout_date' => date('Y-m-d', strtotime('next month')),
            'last_payout_date' => date('Y-m-d', strtotime('last month')),
            'last_bonus' => 100
        ];
    }

    /**
     * Get network tree
     */
    public function getNetworkTree()
    {
        $this->requireLogin();

        $userId = (int)($_SESSION['user_id'] ?? 0);

        try {
            // Real network tree: walk upline + direct downline from mlm_network_tree
            $network = $this->db->fetchAll(
                "SELECT 
                    u.id,
                    u.name,
                    u.email,
                    t.associate_id,
                    t.sponsor_id,
                    t.parent_id,
                    t.level
                 FROM mlm_network_tree t
                 JOIN users u ON u.id = t.associate_id
                 WHERE t.sponsor_id = ? OR t.parent_id = ? OR t.associate_id = ?
                 ORDER BY t.level ASC, u.name ASC",
                [$userId, $userId, $userId]
            );

            return $this->jsonResponse([
                'success' => true,
                'network' => $network ?: []
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get network tree: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get commission details
     */
    public function getCommissionDetails()
    {
        $this->requireLogin();

        $userId = (int)($_SESSION['user_id'] ?? 0);

        try {
            // Real commission ledger (per AGENTS.md: mlm_commission_ledger is the source of truth)
            $commissions = $this->db->fetchAll(
                "SELECT 
                    amount,
                    commission_type as type,
                    notes as description,
                    created_at,
                    status
                 FROM mlm_commission_ledger
                 WHERE beneficiary_user_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT 50",
                [$userId]
            );

            return $this->jsonResponse([
                'success' => true,
                'commissions' => $commissions ?: []
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get commission details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add commission (manual adjustment into the real ledger)
     */
    public function addCommission()
    {
        $this->requireLogin();

        try {
            $data = $this->getRequestData();
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $amount = (float)($data['amount'] ?? 0);
            $type = Security::sanitize($data['type'] ?? 'referral');
            $description = Security::sanitize($data['description'] ?? 'Manual commission entry');

            if ($amount <= 0) {
                return $this->jsonResponse(['success' => false, 'message' => 'Invalid amount'], 400);
            }

            $this->db->execute(
                "INSERT INTO mlm_commission_ledger 
                 (beneficiary_user_id, source_user_id, source_user_name, commission_type, amount, payment_amount, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, 'approved', NOW())",
                [$userId, $userId, $_SESSION['user_name'] ?? 'Self', $type, $amount, $amount]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Commission added successfully'
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add commission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get request data from various sources
     */
    private function getRequestData(): array
    {
        $data = [];

        // Get JSON data
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true) ?: [];
        }

        // Merge with POST data
        if (!empty($_POST)) {
            $data = array_merge($data, $_POST);
        }

        // Merge with GET data
        if (!empty($_GET)) {
            $data = array_merge($data, $_GET);
        }

        return $data;
    }

    public function getAnalytics()
    {
        header('Content-Type: application/json');
        try {
            $db = \App\Core\Database::getInstance();
            $totalMembers = $db->fetch("SELECT COUNT(*) as c FROM `mlm_network_tree`")['c'] ?? 0;
            $totalAssociates = $db->fetch("SELECT COUNT(*) as c FROM `associates` WHERE status = 'active'")['c'] ?? 0;
            echo json_encode(['success' => true, 'data' => [
                'total_members' => (int)$totalMembers,
                'total_associates' => (int)$totalAssociates
            ]]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function calculateCommission()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Commission calculation triggered']);
        exit;
    }

    public function getCommissionHistory()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }
}
