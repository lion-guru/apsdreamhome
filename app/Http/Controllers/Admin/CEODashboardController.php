<?php

/**
 * CEO Dashboard Controller
 * MVC Pattern - Proper Role-based Dashboard Management
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Exception;

class CEODashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
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
            $revenue_stats = $this->db->fetchOne(
                "SELECT 
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN amount END), 0) as total_revenue,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as total_transactions,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN amount END), 0) as pending_revenue,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_transactions
                FROM booking_payments
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );

            // Get team statistics
            $team_stats = $this->db->fetchOne(
                "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN role = 'admin' THEN 1 END) as admin_users,
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

            // Get recent activities
            $activities = $this->db->fetchAll(
                "SELECT * FROM admin_activities 
                ORDER BY created_at DESC 
                LIMIT 10"
            );

            $this->data = [
                'page_title' => 'CEO Dashboard',
                'business_stats' => $business_stats,
                'revenue_stats' => $revenue_stats,
                'team_stats' => $team_stats,
                'commission_stats' => $commission_stats,
                'activities' => $activities
            ];

            return $this->render('admin/dashboards/ceo');
        } catch (Exception $e) {
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
        try {
            $analytics = $this->db->fetchAll(
                "SELECT 
                    DATE(created_at) as date,
                    SUM(CASE WHEN status = 'completed' THEN amount END) as daily_revenue,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as daily_transactions
                FROM booking_payments
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC"
            );

            return $this->jsonResponse(['success' => true, 'data' => $analytics]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get team performance (AJAX)
     */
    public function getTeamPerformance()
    {
        try {
            $performance = $this->db->fetchAll(
                "SELECT 
                    u.role,
                    COUNT(*) as user_count,
                    COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_count,
                    COALESCE(AVG(CASE WHEN c.status = 'completed' THEN c.amount END), 0) as avg_performance
                FROM users u
                LEFT JOIN commissions c ON u.id = c.agent_id 
                    AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY u.role
                ORDER BY user_count DESC"
            );

            return $this->jsonResponse(['success' => true, 'data' => $performance]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
