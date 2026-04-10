<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database;
use App\Core\App;
use Exception;

/**
 * Admin Dashboard Controller
 * Handles admin dashboard operations and analytics
 */
class AdminDashboard
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = App::getInstance();
    }

    /**
     * Get dashboard overview
     * @return array Dashboard data
     */
    public function getOverview()
    {
        try {
            // Get key metrics
            $stats = $this->db->fetch("
                SELECT 
                    COUNT(DISTINCT u.id) as total_users,
                    COUNT(DISTINCT p.id) as total_properties,
                    COUNT(DISTINCT a.id) as total_associates,
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(CASE WHEN o.status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN o.amount > 0 THEN o.amount ELSE 0 END) as total_revenue
                FROM users u
                LEFT JOIN properties p ON u.id = p.user_id
                LEFT JOIN associates a ON u.id = a.user_id
                LEFT JOIN orders o ON u.id = o.user_id
                WHERE u.role IN ('admin', 'manager')
            ");

            return [
                'success' => true,
                'overview' => [
                    'users' => $stats['total_users'] ?? 0,
                    'properties' => $stats['total_properties'] ?? 0,
                    'associates' => $stats['total_associates'] ?? 0,
                    'orders' => $stats['total_orders'] ?? 0,
                    'revenue' => $stats['total_revenue'] ?? 0,
                    'order_stats' => [
                        'completed' => $stats['completed_orders'] ?? 0,
                        'pending' => $stats['pending_orders'] ?? 0
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch dashboard overview: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get recent activities
     * @param int $limit Number of activities to return
     * @return array Recent activities
     */
    public function getRecentActivities($limit = 10)
    {
        try {
            $sql = "SELECT 
                    al.action,
                    al.details,
                    al.ip_address,
                    al.created_at,
                    u.name as user_name
                FROM activity_log al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC 
                LIMIT ?";

            $activities = $this->db->fetchAll($sql, [$limit]);

            return [
                'success' => true,
                'activities' => $activities
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch recent activities: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get sales analytics
     * @param string $period Period (day, week, month, year)
     * @return array Sales data
     */
    public function getSalesAnalytics($period = 'month')
    {
        try {
            $sql = "SELECT 
                    DATE(o.created_at) as order_date,
                    SUM(CASE WHEN o.status = 'completed' THEN o.amount ELSE 0 END) as daily_sales,
                    COUNT(o.id) as daily_orders
                FROM orders o 
                WHERE o.status = 'completed'";

            // Adjust query based on period
            switch ($period) {
                case 'day':
                    $sql .= " AND DATE(o.created_at) = CURDATE()";
                    break;
                case 'week':
                    $sql .= " AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $sql .= " AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                    break;
                case 'year':
                    $sql .= " AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
                    break;
            }

            $sql .= " GROUP BY DATE(o.created_at) ORDER BY order_date DESC LIMIT 30";

            $sales = $this->db->fetchAll($sql);

            return [
                'success' => true,
                'sales_analytics' => [
                    'period' => $period,
                    'data' => $sales
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch sales analytics: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get property performance metrics
     * @return array Property metrics
     */
    public function getPropertyMetrics()
    {
        try {
            $sql = "SELECT 
                    COUNT(p.id) as total_properties,
                    COUNT(CASE WHEN p.status = 'active' THEN 1 END) as active_properties,
                    COUNT(CASE WHEN p.featured = 1 THEN 1 END) as featured_properties,
                    AVG(p.price) as avg_price,
                    MIN(p.price) as min_price,
                    MAX(p.price) as max_price
                FROM properties p";

            $metrics = $this->db->fetch($sql);

            return [
                'success' => true,
                'property_metrics' => [
                    'total_properties' => $metrics['total_properties'] ?? 0,
                    'active_properties' => $metrics['active_properties'] ?? 0,
                    'featured_properties' => $metrics['featured_properties'] ?? 0,
                    'price_analysis' => [
                        'average' => $metrics['avg_price'] ?? 0,
                        'minimum' => $metrics['min_price'] ?? 0,
                        'maximum' => $metrics['max_price'] ?? 0
                    ]
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch property metrics: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get system health status
     * @return array System health data
     */
    public function getSystemHealth()
    {
        try {
            // Database connection check
            $dbStatus = $this->db->fetch("SELECT 1 as status") ? 'healthy' : 'error';

            // Recent error count
            $errorCount = $this->db->fetch("SELECT COUNT(*) as count FROM error_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");

            // Active user count
            $activeUsers = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)");

            return [
                'success' => true,
                'system_health' => [
                    'database' => $dbStatus,
                    'recent_errors' => $errorCount['count'] ?? 0,
                    'active_users' => $activeUsers['count'] ?? 0,
                    'status' => $dbStatus === 'healthy' && $errorCount['count'] < 10 ? 'healthy' : 'warning'
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to fetch system health: ' . $e->getMessage()
            ];
        }
    }
}
