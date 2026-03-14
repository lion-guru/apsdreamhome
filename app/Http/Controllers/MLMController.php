<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use PDO;
use Exception;
use Security;

/**
 * MLM Controller
 * Handles MLM operations and dashboard
 */
class MLMController extends BaseController
{
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
            'monthly_commission' => $dashboardData['monthly_commission'],
            'business_volume' => $dashboardData['business_volume'],
            'active_members' => $dashboardData['active_members'],
            'binary_commission' => $dashboardData['binary_commission'],
            'unilevel_commission' => $dashboardData['unilevel_commission'],
            'matrix_commission' => $dashboardData['matrix_commission'],
            'binary_amount' => $dashboardData['binary_amount'],
            'unilevel_amount' => $dashboardData['unilevel_amount'],
            'matrix_amount' => $dashboardData['matrix_amount'],
            'next_rank' => $dashboardData['next_rank'],
            'rank_progress' => $dashboardData['rank_progress'],
            'required_downline' => $dashboardData['required_downline'],
            'required_bv' => $dashboardData['required_bv'],
            'time_remaining' => $dashboardData['time_remaining'],
            'associate_name' => $dashboardData['associate_name'],
            'left_leg_name' => $dashboardData['left_leg_name'],
            'left_leg_count' => $dashboardData['left_leg_count'],
            'right_leg_name' => $dashboardData['right_leg_name'],
            'right_leg_count' => $dashboardData['right_leg_count'],
            'next_payout_date' => $dashboardData['next_payout_date'],
            'last_payout_date' => $dashboardData['last_payout_date'],
            'last_bonus' => $dashboardData['last_bonus']
        ]);
    }
    
    /**
     * MLM Genealogy Tree
     */
    public function genealogy()
    {
        $this->requireLogin();
        $this->render('team/genealogy', [
            'page_title' => 'Team Genealogy - APS Dream Home',
            'page_description' => 'Explore your team network visually'
        ]);
    }
    
    /**
     * Get MLM dashboard data
     */
    private function getMLMDashboardData($userId)
    {
        if (!$userId) {
            return $this->getMockDashboardData();
        }

        $perfCalculator = new \App\Services\PerformanceRankCalculator();
        $diffCalculator = new \App\Services\DifferentialCommissionCalculator();

        $perfData = $perfCalculator->calculateRank($userId);
        
        // Get agent name and current rank from profile
        $stmt = $this->db->prepare("SELECT name, current_level FROM mlm_profiles WHERE user_id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get monthly commission
        $stmt = $this->db->prepare("SELECT SUM(amount) FROM commissions WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $stmt->execute([$userId]);
        $monthlyCommission = $stmt->fetchColumn() ?: 0;

        return [
            'current_level' => $profile['current_level'] ?? 'Associate',
            'plan_name' => $perfData['rank'] ?? 'Starter',
            'total_downline' => $perfData['team_size'] ?? 0,
            'monthly_commission' => number_format($monthlyCommission),
            'business_volume' => number_format($perfData['business_volume'] ?? 0),
            'active_members' => $perfData['team_size'] ?? 0,
            'binary_commission' => 0,
            'unilevel_commission' => 0,
            'matrix_commission' => 0,
            'binary_amount' => '0',
            'unilevel_amount' => '0',
            'matrix_amount' => '0',
            'next_rank' => $perfData['next_rank_info']['next_rank'] ?? 'Max',
            'rank_progress' => $perfData['performance'] ?? 0,
            'required_downline' => $perfData['next_rank_info']['required_members'] ?? 0,
            'required_bv' => number_format($perfData['next_rank_info']['required_bv'] ?? 0),
            'time_remaining' => 'N/A',
            'associate_name' => $profile['name'] ?? 'Unknown',
            'left_leg_name' => 'Left Team',
            'left_leg_count' => 0,
            'right_leg_name' => 'Right Team',
            'right_leg_count' => 0,
            'next_payout_date' => date('Y-m-d', strtotime('next month')),
            'last_payout_date' => date('Y-m-d', strtotime('last month')),
            'last_bonus' => '0'
        ];
    }

    private function getMockDashboardData()
    {
        // Fallback or development mock
        return [
            'current_level' => 'Gold',
            'plan_name' => 'Premium Plan',
            'total_downline' => 47,
            'monthly_commission' => '25,000',
            'business_volume' => '2.5L',
            'active_members' => 32,
            'binary_commission' => 12,
            'unilevel_commission' => 10,
            'matrix_commission' => 8,
            'binary_amount' => '15,000',
            'unilevel_amount' => '7,500',
            'matrix_amount' => '2,500',
            'next_rank' => 'Platinum',
            'rank_progress' => 75,
            'required_downline' => 15,
            'required_bv' => '5L',
            'time_remaining' => '15 days',
            'associate_name' => 'John Doe',
            'left_leg_name' => 'Left Team',
            'left_leg_count' => 23,
            'right_leg_name' => 'Right Team',
            'right_leg_count' => 24,
            'next_payout_date' => '2024-03-15',
            'last_payout_date' => '2024-02-15',
            'last_bonus' => '3,000'
        ];
    }
    
    /**
     * Get MLM analytics
     */
    public function getAnalytics()
    {
        header('Content-Type: application/json');
        
        try {
            $analytics = [
                'network_growth' => [
                    'total_downline' => 47,
                    'new_joins' => 5,
                    'active_members' => 32,
                    'growth_rate' => 12.5
                ],
                'commission_analytics' => [
                    'total_commission' => 25000,
                    'binary_commission' => 15000,
                    'unilevel_commission' => 7500,
                    'matrix_commission' => 2500
                ],
                'rank_progression' => [
                    'current_rank' => 'Gold',
                    'next_rank' => 'Platinum',
                    'progress_percentage' => 75
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'analytics' => $analytics
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch analytics'
            ]);
        }
    }
    
    /**
     * Calculate commission
     */
    public function calculateCommission()
    {
        header('Content-Type: application/json');
        
        try {
            $businessVolume = Security::sanitize($_POST['business_volume']) ?? 0;
            $planId = Security::sanitize($_POST['plan_id']) ?? 'starter';
            
            $commission = [
                'binary_commission' => $businessVolume * 0.12,
                'unilevel_commission' => $businessVolume * 0.10,
                'matrix_commission' => $businessVolume * 0.08,
                'total_commission' => $businessVolume * 0.30
            ];
            
            echo json_encode([
                'success' => true,
                'commission' => $commission
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to calculate commission'
            ]);
        }
    }
    
    /**
     * Get network tree
     */
    public function getNetworkTree()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? 0;
            $levels = $_GET['levels'] ?? 3;
            
            $perfCalculator = new \App\Services\PerformanceRankCalculator();
            $networkTree = $perfCalculator->getHierarchyTree($userId, $levels);
            
            echo json_encode([
                'success' => true,
                'network_tree' => $networkTree
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch network tree: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get commission history
     */
    public function getCommissionHistory()
    {
        header('Content-Type: application/json');
        
        try {
            $userId = $_SESSION['user_id'] ?? 0;
            $period = $_GET['period'] ?? 'monthly';
            
            $history = $this->getCommissionHistoryData($userId, $period);
            
            echo json_encode([
                'success' => true,
                'history' => $history
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to fetch commission history'
            ]);
        }
    }
    
    /**
     * Helper methods
     */
    private function getCommissionHistoryData($userId, $period)
    {
        // Sample commission history
        return [
            [
                'date' => '2024-02-15',
                'type' => 'Monthly Commission',
                'amount' => 25000,
                'status' => 'paid',
                'breakdown' => [
                    'binary' => 15000,
                    'unilevel' => 7500,
                    'matrix' => 2500
                ]
            ],
            [
                'date' => '2024-01-15',
                'type' => 'Monthly Commission',
                'amount' => 22000,
                'status' => 'paid',
                'breakdown' => [
                    'binary' => 13200,
                    'unilevel' => 6600,
                    'matrix' => 2200
                ]
            ]
        ];
    }
}
