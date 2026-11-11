<?php

/**
 * CEO Dashboard Controller
 * MVC Pattern - Proper Role-based Dashboard Management
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Exception;

class CEODashboardController extends AdminController
{
    private $cacheService;

    public function __construct()
    {
        parent::__construct();
        $this->cacheService = new \App\Services\Cache\CacheService();
    }

    /**
     * Show CEO dashboard
     */
    public function index()
    {
        $this->requireAdmin();

        // ── Dashboard cache (5 min TTL) ──
        $cacheKey = 'ceo_dashboard';
        $cached = $this->cacheService->get($cacheKey);
        if ($cached !== false && $cached !== null) {
            $this->data = $cached;
            return $this->render('admin/dashboards/ceo');
        }

        try {
            // Get overall business statistics
            $business_stats = $this->db->fetchOne(
                "SELECT 
                    COUNT(*) as total_properties,
                    COALESCE(SUM(price), 0) as total_property_value,
                    COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_properties,
                    COUNT(CASE WHEN status = 'available' THEN 1 END) as available_properties
                FROM properties"
            );

            // Get revenue statistics
            try {
                $revenue_stats = $this->db->fetchOne(
                    "SELECT 
                        COALESCE(SUM(payment_amount), 0) as total_revenue,
                        COUNT(*) as total_transactions,
                        0 as pending_revenue,
                        0 as pending_transactions
                    FROM booking_payments
                    WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                );
            } catch (\Throwable $e) {
                $revenue_stats = ['total_revenue' => 0, 'total_transactions' => 0, 'pending_revenue' => 0, 'pending_transactions' => 0];
            }

            // Get team statistics
            $team_stats = $this->db->fetchOne(
                "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN role = 'admin' THEN 1 END) as users,
                    COUNT(CASE WHEN role = 'associate' THEN 1 END) as associate_users,
                    COUNT(CASE WHEN role = 'customer' THEN 1 END) as customer_users,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users
                FROM users"
            );

            // Get commission statistics
            $commission_stats = $this->db->fetchOne(
                "SELECT 
                    COALESCE(SUM(amount), 0) as total_commissions,
                    COUNT(*) as total_commission_transactions,
                    COALESCE(AVG(amount), 0) as avg_commission
                FROM mlm_commission_ledger
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );

            // Get top performers (gamification)
            $top_performers = [];
            try {
                $svc = new \App\Services\GamificationService();
                $top_associate = $svc->getTopAssociate();
                $top_agent = $svc->getTopAgent();
                $top_employee = $svc->getTopEmployee();
                $top_performers = [
                    'associate' => $top_associate,
                    'agent' => $top_agent,
                    'employee' => $top_employee
                ];
            } catch (\Throwable $e) {
                error_log('Top performers error: ' . $e->getMessage());
                $top_performers = [
                    'associate' => ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'],
                    'agent' => ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'],
                    'employee' => ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A']
                ];
            }

            // Get recent activities
            $activities = $this->db->fetchAll(
                "SELECT * FROM activity_logs_unified 
                ORDER BY created_at DESC 
                LIMIT 10"
            );

            $this->data = [
                'page_title' => 'CEO Dashboard',
                'business_stats' => $business_stats,
                'revenue_stats' => $revenue_stats,
                'team_stats' => $team_stats,
                'commission_stats' => $commission_stats,
                'activities' => $activities,
                'top_performers' => $top_performers,
            'ai_insights' => $this->getAIInsights($business_stats, $revenue_stats, $team_stats, $commission_stats)
            ];

            // Cache dashboard data for 5 minutes (300 seconds)
            $this->cacheService->set($cacheKey, $this->data, 300);

            return $this->render('admin/dashboards/ceo');
        } catch (\Throwable $e) {
            error_log("CEO Dashboard Error: " . $e->getMessage());
            $this->setFlash('error', 'Dashboard loading failed');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Get revenue analytics (AJAX)
     */
    public function getRevenueAnalytics()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        try {
            $analytics = $this->db->fetchAll(
                "SELECT 
                    DATE(payment_date) as date,
                    SUM(payment_amount) as daily_revenue,
                    COUNT(*) as daily_transactions
                FROM booking_payments
                WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(payment_date)
                ORDER BY date DESC"
            );
        } catch (\Throwable $e) {
            $analytics = [];
        }
        echo json_encode(['success' => true, 'data' => $analytics ?? []]);
        exit;
    }

    /**
     * Get AI-powered business insights for CEO dashboard
     */
    private function getAIInsights($business_stats, $revenue_stats, $team_stats, $commission_stats): array
    {
        $insights = [];
        try {
            $engines = \App\Services\AI\FreeAIEngines::getInstance();
            if ($engines) {
                $prompt = "As a CEO of a real estate company, analyze these metrics and provide 3-5 strategic insights:\n"
                    . "Total Properties: " . ($business_stats['total_properties'] ?? 0) . "\n"
                    . "Sold: " . ($business_stats['sold_properties'] ?? 0) . "\n"
                    . "Available: " . ($business_stats['available_properties'] ?? 0) . "\n"
                    . "Total Revenue (30d): " . ($revenue_stats['total_revenue'] ?? 0) . "\n"
                    . "Transactions: " . ($revenue_stats['total_transactions'] ?? 0) . "\n"
                    . "Total Users: " . ($team_stats['total_users'] ?? 0) . "\n"
                    . "Active Users: " . ($team_stats['active_users'] ?? 0) . "\n"
                    . "Total Commissions (30d): " . ($commission_stats['total_commissions'] ?? 0) . "\n"
                    . "Provide actionable insights in JSON: {\"insights\":[{\"title\":\"...\",\"description\":\"...\",\"priority\":\"high|medium|low\",\"action\":\"...\"}]}";

                $result = $engines->generate($prompt, ['temperature' => 0.5, 'max_tokens' => 500], 'executive');
                $text = $result['text'] ?? '';
                $start = strpos($text, '{');
                $end = strrpos($text, '}');
                if ($start !== false && $end !== false && $end > $start) {
                    $jsonText = substr($text, $start, $end - $start + 1);
                    $json = json_decode($jsonText, true);
                    if (is_array($json) && isset($json['insights'])) {
                        $insights = $json['insights'];
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log('CEO AI Insights error: ' . $e->getMessage());
        }

        // Fallback insights if AI unavailable
        if (empty($insights)) {
            $insights = [
                ['title' => 'Revenue Tracking', 'description' => 'Monitor daily revenue trends for growth opportunities.', 'priority' => 'medium', 'action' => 'Review revenue dashboard'],
                ['title' => 'Team Performance', 'description' => 'Track associate and agent performance metrics.', 'priority' => 'medium', 'action' => 'Check team performance tab'],
                ['title' => 'Inventory Management', 'description' => 'Optimize available property inventory.', 'priority' => 'low', 'action' => 'Review available properties'],
            ];
        }

        return $insights;
    }

    /**
     * Get team performance (AJAX)
     */
    public function getTeamPerformance()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        try {
            $performance = $this->db->fetchAll(
                "SELECT 
                    u.role,
                    COUNT(*) as user_count,
                    COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_count,
                    COALESCE(AVG(CASE WHEN c.status = 'paid' THEN c.amount END), 0) as avg_performance
                FROM users u
                LEFT JOIN commissions c ON u.id = c.user_id 
                    AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY u.role
                ORDER BY user_count DESC"
            );
        } catch (\Throwable $e) {
            $performance = [];
        }
        echo json_encode(['success' => true, 'data' => $performance ?? []]);
        exit;
    }
}
