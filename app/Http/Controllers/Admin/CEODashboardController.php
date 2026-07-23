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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show CEO dashboard
     */
    public function index()
    {
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
            } catch (\Exception $e) {
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
                FROM commissions
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
                'top_performers' => $top_performers
            ];

            return $this->render('admin/dashboards/ceo');
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            $analytics = [];
        }
        echo json_encode(['success' => true, 'data' => $analytics ?? []]);
        exit;
    }

    /**
     * Get team performance (AJAX)
     */
    public function getTeamPerformance()
    {
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
        } catch (\Exception $e) {
            $performance = [];
        }
        echo json_encode(['success' => true, 'data' => $performance ?? []]);
        exit;
    }
}
