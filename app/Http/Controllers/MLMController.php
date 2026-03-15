<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Core\Database;
use App\Core\Security;
use Exception;

/**
 * MLM Controller
 * Handles MLM operations and dashboard
 */
class MLMController extends BaseController
{
    private $db;

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
            // Get user's MLM profile
            $profile = $this->db->fetchOne(
                "SELECT level, sponsor_id, left_count, right_count, total_commission FROM mlm_profiles WHERE user_id = ?",
                [$userId]
            );

            if (!$profile) {
                return $this->getMockDashboardData();
            }

            // Get downline members
            $downline = $this->db->fetchAll(
                "SELECT u.name, u.email, m.level, m.position, m.created_at 
                 FROM users u 
                 JOIN mlm_profiles m ON u.id = m.user_id 
                 WHERE m.sponsor_id = ? 
                 ORDER BY m.created_at DESC 
                 LIMIT 10",
                [$profile['sponsor_id']]
            );

            // Get monthly commission
            $monthlyCommission = $this->db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total FROM commissions 
                 WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
                [$userId]
            );

            // Calculate team size and business volume
            $teamSize = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM mlm_profiles WHERE sponsor_id = ? OR user_id = ?",
                [$profile['sponsor_id'], $userId]
            );

            $businessVolume = $this->db->fetchOne(
                "SELECT COALESCE(SUM(amount), 0) as total FROM commissions 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
                [$userId]
            );

            // Get current rank info
            $currentRank = $this->getCurrentRank($userId);

            return [
                'current_level' => $this->getLevelName($profile['level']),
                'plan_name' => $currentRank['plan_name'],
                'total_downline' => $teamSize['count'],
                'monthly_commission' => $monthlyCommission['total'] ?? 0,
                'business_volume' => $businessVolume['total'] ?? 0,
                'active_members' => count($downline),
                'binary_commission' => 0,
                'unilevel_commission' => 0,
                'matrix_commission' => 0,
                'binary_amount' => '0.00',
                'unilevel_amount' => '0.00',
                'matrix_amount' => '0.00',
                'next_rank' => $currentRank['next_rank'],
                'rank_progress' => $currentRank['progress'] ?? 0,
                'required_downline' => $currentRank['required_members'] ?? 0,
                'required_bv' => $currentRank['required_bv'] ?? 0,
                'time_remaining' => $currentRank['time_remaining'] ?? 'N/A',
                'associate_name' => $downline[0]['name'] ?? 'Unknown',
                'left_leg_name' => 'Left Team',
                'left_leg_count' => $this->db->fetchOne(
                    "SELECT COUNT(*) as count FROM mlm_profiles WHERE sponsor_id = ? AND position = 'left'",
                    [$userId]
                )['count'],
                'right_leg_name' => 'Right Team',
                'right_leg_count' => $this->db->fetchOne(
                    "SELECT COUNT(*) as count FROM mlm_profiles WHERE sponsor_id = ? AND position = 'right'",
                    [$userId]
                )['count'],
                'next_payout_date' => date('Y-m-d', strtotime('next month')),
                'last_payout_date' => date('Y-m-d', strtotime('last month')),
                'last_bonus' => 0
            ];

        } catch (Exception $e) {
            error_log("MLM Dashboard Error: " . $e->getMessage());
            return $this->getMockDashboardData();
        }
    }

    /**
     * Get current rank information
     */
    private function getCurrentRank($userId)
    {
        try {
            // Get user's current rank based on downline and performance
            $rankData = $this->db->fetchOne(
                "SELECT 
                    (SELECT COUNT(*) FROM mlm_profiles WHERE sponsor_id = ? OR user_id = ?) as team_size,
                    (SELECT COALESCE(SUM(amount), 0) FROM commissions WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) as monthly_volume
                ) as rank_data",
                [$userId, $userId]
            );

            $teamSize = $rankData['team_size'] ?? 0;
            $monthlyVolume = $rankData['monthly_volume'] ?? 0;

            // Determine rank based on performance
            $rank = 'Associate';
            $nextRank = 'Silver';
            $requiredMembers = 0;
            $requiredBV = 0;
            $progress = 0;
            $timeRemaining = 'N/A';

            if ($teamSize >= 50 && $monthlyVolume >= 10000) {
                $rank = 'Diamond';
                $nextRank = 'Diamond';
                $requiredMembers = 100;
                $requiredBV = 10000;
                $progress = 100;
                $timeRemaining = 'Achieved';
            } elseif ($teamSize >= 25 && $monthlyVolume >= 5000) {
                $rank = 'Platinum';
                $nextRank = 'Diamond';
                $requiredMembers = 50;
                $requiredBV = 5000;
                $progress = 50;
                $timeRemaining = 'Half way';
            } elseif ($teamSize >= 10 && $monthlyVolume >= 1000) {
                $rank = 'Gold';
                $nextRank = 'Platinum';
                $requiredMembers = 25;
                $requiredBV = 1000;
                $progress = 25;
                $timeRemaining = 'Quarter way';
            } elseif ($teamSize >= 5 && $monthlyVolume >= 500) {
                $rank = 'Silver';
                $nextRank = 'Gold';
                $requiredMembers = 10;
                $requiredBV = 500;
                $progress = 10;
                $timeRemaining = '3 months';
            }

            return [
                'plan_name' => $rank,
                'next_rank' => $nextRank,
                'required_members' => $requiredMembers,
                'required_bv' => $requiredBV,
                'progress' => $progress,
                'time_remaining' => $timeRemaining
            ];

        } catch (Exception $e) {
            error_log("Rank Calculation Error: " . $e->getMessage());
            return [
                'plan_name' => 'Associate',
                'next_rank' => 'Silver',
                'required_members' => 0,
                'required_bv' => 0,
                'progress' => 0,
                'time_remaining' => 'N/A'
            ];
        }
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
            'current_level' => 'Gold',
            'plan_name' => 'Gold',
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

        $userId = $_SESSION['user_id'] ?? 0;

        try {
            // Get network tree data
            $network = $this->db->fetchAll(
                "SELECT 
                    u.name,
                    u.email,
                    m.level,
                    m.position,
                    m.sponsor_id,
                    sp.name as sponsor_name
                 FROM users u 
                 JOIN mlm_profiles m ON u.id = m.user_id 
                 LEFT JOIN users sp ON sp.id = m.sponsor_id 
                 WHERE m.sponsor_id = ? OR u.id = ?
                 ORDER BY m.created_at ASC",
                [$userId, $userId]
            );

            return $this->jsonResponse([
                'success' => true,
                'network' => $network
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

        $userId = $_SESSION['user_id'] ?? 0;

        try {
            // Get commission details
            $commissions = $this->db->fetchAll(
                "SELECT 
                    c.amount,
                    c.type,
                    c.description,
                    c.created_at,
                    u.name as user_name
                 FROM commissions c 
                 JOIN users u ON c.user_id = u.id 
                 WHERE c.user_id = ? 
                 ORDER BY c.created_at DESC 
                 LIMIT 50",
                [$userId]
            );

            return $this->jsonResponse([
                'success' => true,
                'commissions' => $commissions
            ]);

        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get commission details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add commission
     */
    public function addCommission()
    {
        $this->requireLogin();

        try {
            $data = $this->getRequestData();

            $commission = [
                'user_id' => $_SESSION['user_id'],
                'amount' => Security::sanitize($data['amount'] ?? 0),
                'type' => Security::sanitize($data['type'] ?? 'binary'),
                'description' => Security::sanitize($data['description'] ?? ''),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->db->execute(
                "INSERT INTO commissions (user_id, amount, type, description, created_at) VALUES (?, ?, ?, ?)",
                [
                    $commission['user_id'],
                    $commission['amount'],
                    $commission['type'],
                    $commission['description'],
                    $commission['created_at']
                ]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Commission added successfully',
                'commission' => $commission
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
}