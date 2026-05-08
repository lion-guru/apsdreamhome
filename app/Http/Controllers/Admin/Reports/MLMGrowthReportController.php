<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Core\Controller;
use App\Core\Database\Database;

/**
 * MLM Growth Report Controller
 * Network growth analytics and visualization
 */
class MLMGrowthReportController extends Controller
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
        $this->requireAuth();
        
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
            FROM associates
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get top performing associates
     */
    private function getTopPerformers($db): array
    {
        $sql = "
            SELECT 
                a.id,
                a.name,
                a.email,
                a.referral_code,
                COUNT(DISTINCT r.id) as direct_referrals,
                SUM(c.amount) as total_commissions,
                a.created_at
            FROM associates a
            LEFT JOIN mlm_referrals r ON a.id = r.sponsor_id
            LEFT JOIN mlm_commissions c ON a.id = c.associate_id AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY a.id
            ORDER BY total_commissions DESC
            LIMIT 20
        ";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get level distribution
     */
    private function getLevelDistribution($db): array
    {
        $sql = "
            SELECT 
                mlm_level as level,
                COUNT(*) as associate_count,
                ROUND(AVG(total_earnings), 2) as avg_earnings
            FROM associates
            GROUP BY mlm_level
            ORDER BY mlm_level ASC
        ";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get commission trends
     */
    private function getCommissionTrends($db): array
    {
        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(amount) as total_commissions,
                COUNT(DISTINCT associate_id) as active_earners,
                AVG(amount) as avg_commission
            FROM mlm_commissions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get monthly comparison data
     */
    private function getMonthlyComparison($db): array
    {
        // Current month vs previous month
        $sql = "
            SELECT 
                'Current Month' as period,
                COUNT(DISTINCT a.id) as new_associates,
                COUNT(DISTINCT r.id) as new_referrals,
                COALESCE(SUM(c.amount), 0) as total_commissions
            FROM associates a
            LEFT JOIN mlm_referrals r ON a.id = r.sponsor_id 
                AND r.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
            LEFT JOIN mlm_commissions c ON c.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
            WHERE a.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')
            
            UNION ALL
            
            SELECT 
                'Previous Month' as period,
                COUNT(DISTINCT a.id) as new_associates,
                COUNT(DISTINCT r.id) as new_referrals,
                COALESCE(SUM(c.amount), 0) as total_commissions
            FROM associates a
            LEFT JOIN mlm_referrals r ON a.id = r.sponsor_id 
                AND r.created_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')
                AND r.created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            LEFT JOIN mlm_commissions c ON c.created_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')
                AND c.created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
            WHERE a.created_at >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m-01')
                AND a.created_at < DATE_FORMAT(NOW(), '%Y-%m-01')
        ";
        
        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Export report as PDF
     */
    public function exportPdf(): void
    {
        $this->requireAuth();
        
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
        $this->requireAuth();
        
        $reportData = $this->generateGrowthReport();
        
        header('Content-Type: application/json');
        echo json_encode([
            'network_growth' => $reportData['network_growth'],
            'level_distribution' => $reportData['level_distribution'],
            'commission_trends' => $reportData['commission_trends']
        ]);
    }
}
