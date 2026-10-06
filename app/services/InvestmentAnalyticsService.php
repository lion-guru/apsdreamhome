<?php
namespace App\Services;

use PDO;
use App\Traits\ServiceTenantTrait;

class InvestmentAnalyticsService
{
    use ServiceTenantTrait;
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo) {
            $this->pdo = $pdo;
            return;
        }
        $this->pdo = \App\Core\Database\Database::getInstance()->getConnection();
    }

    /**
     * Get overall investment statistics
     */
    public function getOverviewStats(): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $stats = [];
        
        // Total investments
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_investments,
                COALESCE(SUM(principal_amount), 0) as total_principal,
                COALESCE(SUM(current_value), 0) as total_current_value,
                COALESCE(SUM(current_value - principal_amount), 0) as total_returns,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_investments,
                COUNT(CASE WHEN status = 'matured' THEN 1 END) as matured_investments,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_investments
            FROM investments WHERE 1=1 {$tenantWhere}
        ");
        $stmt->execute($params);
        $stats['overview'] = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stats['overview']) {
            $stats['overview']['avg_return_pct'] = (float)$stats['overview']['total_principal'] > 0 
                ? round((((float)$stats['overview']['total_current_value'] - (float)$stats['overview']['total_principal']) / (float)$stats['overview']['total_principal']) * 100, 2)
                : 0;
        }

        // By category
        $stmt = $this->pdo->prepare("
            SELECT 
                ip.plan_category,
                COUNT(i.id) as count,
                COALESCE(SUM(i.principal_amount), 0) as total_principal,
                COALESCE(SUM(i.current_value), 0) as total_current_value
            FROM investments i
            JOIN investment_plans ip ON i.plan_id = ip.id
            WHERE i.status = 'active' {$tenantWhere}
            GROUP BY ip.plan_category
        ");
        $stmt->execute($params);
        $stats['by_category'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // By risk level
        $stmt = $this->pdo->prepare("
            SELECT 
                ip.risk_level,
                COUNT(i.id) as count,
                COALESCE(SUM(i.principal_amount), 0) as total_principal
            FROM investments i
            JOIN investment_plans ip ON i.plan_id = ip.id
            WHERE i.status = 'active' {$tenantWhere}
            GROUP BY ip.risk_level
        ");
        $stmt->execute($params);
        $stats['by_risk'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Monthly trends (last 12 months)
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count,
                COALESCE(SUM(principal_amount), 0) as total_principal
            FROM investments
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) {$tenantWhere}
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ");
        $stmt->execute($params);
        $stats['monthly_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Top investors
        $stmt = $this->pdo->prepare("
            SELECT 
                u.id, u.name, u.email,
                COALESCE(SUM(i.principal_amount), 0) as total_invested,
                COALESCE(SUM(i.current_value - i.principal_amount), 0) as total_returns
            FROM investments i
            JOIN users u ON i.user_id = u.id
            WHERE i.status = 'active' {$tenantWhere}
            GROUP BY u.id
            ORDER BY total_invested DESC
            LIMIT 10
        ");
        $stmt->execute($params);
        $stats['top_investors'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // SIP statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_sips,
                COALESCE(SUM(i.monthly_amount), 0) as total_monthly_commitment,
                COALESCE(SUM(i.principal_amount), 0) as total_sip_principal
            FROM investments i
            WHERE i.auto_invest = 1 AND i.status = 'active' {$tenantWhere}
        ");
        $stmt->execute($params);
        $stats['sip_stats'] = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        // Installment stats
        $stmt = $this->pdo->prepare("
            SELECT 
                status,
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total_amount
            FROM investment_installments
            WHERE 1=1 {$tenantWhere}
            GROUP BY status
        ");
        $stmt->execute($params);
        $stats['installment_stats'] = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return $stats;
    }

    /**
     * Get plan-wise performance
     */
    public function getPlanPerformance(): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $stmt = $this->pdo->prepare("
            SELECT 
                ip.id,
                ip.plan_name,
                ip.plan_code,
                ip.plan_category,
                ip.expected_return_pct,
                ip.tenure_months,
                COUNT(i.id) as total_investments,
                COALESCE(SUM(i.principal_amount), 0) as total_principal,
                COALESCE(SUM(i.current_value), 0) as total_current_value,
                COALESCE(SUM(i.current_value - i.principal_amount), 0) as total_returns,
                COALESCE(AVG(i.current_value - i.principal_amount) / NULLIF(i.principal_amount, 0) * 100, 0) as avg_return_pct
            FROM investment_plans ip
            LEFT JOIN investments i ON ip.id = i.plan_id AND i.status = 'active'
            WHERE ip.is_active = 1 {$tenantWhere}
            GROUP BY ip.id
            ORDER BY total_principal DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get recent investment activity
     */
    public function getRecentActivity(int $limit = 20): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND i.tenant_id = ?" : "";
        $params = array_merge([$limit], $tid > 1 ? [$tid] : []);

        $stmt = $this->pdo->prepare("
            SELECT 
                i.id, i.investment_ref, i.principal_amount, i.current_value,
                i.status, i.created_at,
                u.name as user_name, u.email,
                ip.plan_name, ip.plan_category
            FROM investments i
            JOIN users u ON i.user_id = u.id
            JOIN investment_plans ip ON i.plan_id = ip.id
            WHERE 1=1 {$tenantWhere}
            ORDER BY i.created_at DESC
            LIMIT ?
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get installment collection report
     */
    public function getInstallmentCollectionReport(string $startDate, string $endDate): array
    {
        $tid = $this->tenantId();
        $tenantWhere = $tid > 1 ? " AND ii.tenant_id = ?" : "";
        $params = array_merge([$startDate, $endDate], $tid > 1 ? [$tid] : []);

        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(ii.paid_at) as paid_date,
                COUNT(*) as count,
                COALESCE(SUM(ii.amount), 0) as total_collected,
                COALESCE(SUM(CASE WHEN ii.status = 'paid' THEN ii.amount ELSE 0 END), 0) as paid_amount,
                COALESCE(SUM(CASE WHEN ii.status = 'failed' THEN ii.amount ELSE 0 END), 0) as failed_amount,
                COUNT(CASE WHEN ii.status = 'paid' THEN 1 END) as paid_count,
                COUNT(CASE WHEN ii.status = 'failed' THEN 1 END) as failed_count
            FROM investment_installments ii
            WHERE ii.paid_at >= ? AND ii.paid_at <= ? {$tenantWhere}
            GROUP BY DATE(ii.paid_at)
            ORDER BY paid_date ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}