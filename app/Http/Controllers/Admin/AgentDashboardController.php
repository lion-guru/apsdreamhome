<?php

/**
 * Agent Dashboard Controller
 * MVC Pattern - Proper Role-based Dashboard Management
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\LoggingService;
use Exception;

class AgentDashboardController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
    }

    /**
     * Show agent dashboard
     */
    public function index()
    {
        // For admin users, check admin_id; for regular users, check user_id
        $user_id = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
        
        if (!$user_id) {
            $this->redirect(BASE_URL . '/admin/login');
            return;
        }

        // Get agent statistics
        $stats = $this->db->fetchOne(
            "SELECT 
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as total_sales,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN amount END), 0) as total_commissions,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_sales,
                COALESCE(SUM(CASE WHEN status = 'pending' THEN amount END), 0) as pending_commissions
            FROM mlm_commission_ledger 
            WHERE beneficiary_user_id = ? 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            [$user_id]
        ) ?: ['total_sales' => 0, 'total_commissions' => 0, 'pending_sales' => 0, 'pending_commissions' => 0];

        // Get network statistics
        $network = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as total_associates,
                COUNT(CASE WHEN u.status = 'active' THEN 1 END) as active_associates,
                COUNT(CASE WHEN u.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_associates
            FROM users u 
            WHERE u.sponsor_id = ?",
            [$user_id]
        ) ?: ['total_associates' => 0, 'active_associates' => 0, 'new_associates' => 0];

        // Get recent activities (use correct column name 'action' instead of 'activity_type')
        $activities = $this->db->fetchAll(
            "SELECT id, action as description, created_at 
             FROM activity_logs_unified 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT 10",
            [$user_id]
        ) ?: [];

        // Get performance metrics
        $performance = $this->db->fetchAll(
            "SELECT 
                DATE(created_at) as date,
                COUNT(*) as sales_count,
                COALESCE(SUM(amount), 0) as daily_commission
            FROM mlm_commission_ledger 
            WHERE beneficiary_user_id = ? 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC",
            [$user_id]
        ) ?: [];

        $this->data = [
            'page_title' => 'Agent Dashboard',
            'stats' => $stats,
            'network' => $network,
            'activities' => $activities,
            'performance' => $performance,
            'my_commissions' => [
                'total' => number_format($stats['total_commissions'] ?? 0),
                'pending' => number_format($stats['pending_commissions'] ?? 0)
            ],
            'my_network' => [
                'total_associates' => $network['total_associates'] ?? 0,
                'active_associates' => $network['active_associates'] ?? 0
            ]
        ];

        return $this->render('admin/dashboards/agent');
    }

    /**
     * Get agent performance data (AJAX)
     */
    public function getPerformanceData()
    {
        $user_id = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
        
        if (!$user_id) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $performance = $this->db->query(
                "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as sales_count,
                    COALESCE(SUM(amount), 0) as daily_commission
                FROM mlm_commission_ledger 
                WHERE beneficiary_user_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC",
                [$user_id]
            )->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse(['success' => true, 'data' => $performance]);
        } catch (\Exception $e) {
            $this->loggingService->error("Get Performance Data error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get network tree data (AJAX)
     */
    public function getNetworkTree()
    {
        try {
            $user_id = $_SESSION['user_id'] ?? 1;

            $network = $this->db->prepare(
                "SELECT 
                    u.id,
                    u.name,
                    u.email,
                    u.status,
                    u.created_at,
                    COUNT(CASE WHEN u2.sponsor_id = u.id THEN 1 END) as direct_children
                FROM users u 
                LEFT JOIN users u2 ON u2.sponsor_id = u.id
                WHERE u.sponsor_id = ? OR u.id = ?
                GROUP BY u.id, u.name, u.email, u.status, u.created_at
                ORDER BY u.created_at DESC"
            );
            $network->execute([$user_id, $user_id]);
            $network = $network->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse(['success' => true, 'data' => $network]);
        } catch (\Exception $e) {
            $this->loggingService->error("Get Network Tree error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
