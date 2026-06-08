<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Core\Database\Database;

/**
 * MLM Growth Report Controller
 * Network growth analytics and visualization
 */
class MLMGrowthReportController extends \App\Http\Controllers\Admin\AdminController
{
    private $database;
    
    public function __construct()
    {
        parent::__construct();
        $this->database = Database::getInstance();
    }
    
    /**
     * Show MLM Growth Dashboard
     */
    public function index(): void
    {
        $this->requireLogin();
        
        $reportData = $this->generateGrowthReport();
        
        $this->render('admin/reports/mlm_growth', [
            'report' => $reportData,
            'title' => 'MLM Network Growth Report'
        ]);
    }
    
    /**
     * Generate MLM Network Growth Data
     */
    private function generateGrowthReport(): array
    {
        $db = $this->database->getConnection();
        
        // Network size over time
        $networkGrowth = $this->getNetworkGrowthData($db);
        
        // Top performers
        $topPerformers = $this->getTopPerformers($db);
        
        // Level distribution
        $levelDistribution = $this->getLevelDistribution($db);
        
        // Commission trends
        $commissionTrends = $this->getCommissionTrends($db);
        
        // Monthly comparison
        $monthlyComparison = $this->getMonthlyComparison($db);
        
        return [
            'network_growth' => $networkGrowth,
            'top_performers' => $topPerformers,
            'level_distribution' => $levelDistribution,
            'commission_trends' => $commissionTrends,
            'monthly_comparison' => $monthlyComparison,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Get network growth data (last 12 months)
     */
    private function getNetworkGrowthData($db): array
    {
        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as new_associates,
                SUM(COUNT(*)) OVER (ORDER BY DATE_FORMAT(created_at, '%Y-%m')) as total_associates
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get top performing users
     */
    private function getTopPerformers($db): array
    {
        try {
            $sql = "
                SELECT 
                    a.id,
                    a.name,
                    a.email,
                    ml.sponsor_user_id as referral_code,
                    COUNT(DISTINCT cl.id) as direct_referrals,
                    COALESCE(SUM(cl.amount), 0) as total_commissions,
                    a.created_at
                FROM users a
                LEFT JOIN mlm_profiles ml ON ml.user_id = a.id
                LEFT JOIN mlm_commission_ledger cl ON a.id = cl.beneficiary_user_id AND cl.commission_type = 'referral' AND cl.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY a.id, ml.sponsor_user_id
                ORDER BY total_commissions DESC
                LIMIT 20
            ";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[MLMGrowthReportController::getTopPerformers] ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get level distribution
     */
    private function getLevelDistribution($db): array
    {
        try {
            $sql = "
                SELECT 
                    COALESCE(ml.current_level, 'Bronze') as level,
                    COUNT(*) as associate_count,
                    ROUND(AVG(ml.total_commission), 2) as avg_earnings
                FROM users a
                LEFT JOIN mlm_profiles ml ON ml.user_id = a.id
                GROUP BY ml.current_level
                ORDER BY ml.current_level ASC
            ";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[MLMGrowthReportController::getLevelDistribution] ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get commission trends
     */
    private function getCommissionTrends($db): array
    {
        try {
            $sql = "
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(amount) as total_commissions,
                    COUNT(DISTINCT beneficiary_user_id) as active_earners,
                    AVG(amount) as avg_commission
                FROM mlm_commission_ledger
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC
            ";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[MLMGrowthReportController::getCommissionTrends] ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get monthly comparison data
     */
    private function getMonthlyComparison($db): array
    {
        try {
            $sql = "
                SELECT 
                    'Current Month' as period,
                    COUNT(DISTINCT a.id) as new_associates,
                    COUNT(DISTINCT cl.id) as new_referrals,
                    COALESCE(SUM(cl.amount), 0) as total_commissions
                FROM users a
                LEFT JOIN mlm_commission_ledger cl ON cl.beneficiary_user_id = a.id 
                    AND cl.commission_type = 'referral'
                    AND cl.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
                WHERE a.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
                
                UNION ALL
                
                SELECT 
                    'Previous Month' as period,
                    COUNT(DISTINCT a.id) as new_associates,
                    COUNT(DISTINCT cl.id) as new_referrals,
                    COALESCE(SUM(cl.amount), 0) as total_commissions
                FROM users a
                LEFT JOIN mlm_commission_ledger cl ON cl.beneficiary_user_id = a.id 
                    AND cl.commission_type = 'referral'
                    AND cl.created_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')
                    AND cl.created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
                WHERE a.created_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')
                    AND a.created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            ";
            $stmt = $db->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[MLMGrowthReportController::getMonthlyComparison] ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Export report as PDF
     */
    public function exportPdf(): void
    {
        $this->requireLogin();
        
        $reportData = $this->generateGrowthReport();
        
        // Generate PDF using a library like mPDF or DomPDF
        // For now, return JSON
        header('Content-Type: application/json');
        echo json_encode($reportData);
    }
    
    /**
     * API endpoint for chart data
     */
    public function apiChartData(): void
    {
        $this->requireLogin();
        
        $reportData = $this->generateGrowthReport();
        
        header('Content-Type: application/json');
        echo json_encode([
            'network_growth' => $reportData['network_growth'],
            'level_distribution' => $reportData['level_distribution'],
            'commission_trends' => $reportData['commission_trends']
        ]);
    }
}
