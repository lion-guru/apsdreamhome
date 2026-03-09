<?php

namespace App\Services\Admin;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Admin Dashboard Service
 * Complete admin panel with role-based access control and comprehensive analytics
 */
class DashboardService
{
    private Database $db;
    private LoggerInterface $logger;

    // Dashboard cache TTL (5 minutes)
    private const CACHE_TTL = 300;

    // Admin roles
    private const ADMIN_ROLES = ['admin', 'super_admin', 'administrator'];

    public function __construct(Database $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(?int $userId = null): bool
    {
        if (!$userId && isset($_SESSION['user_id'])) {
            $userId = (int)$_SESSION['user_id'];
        }

        if (!$userId) {
            return false;
        }

        try {
            $user = $this->db->fetchOne(
                "SELECT role FROM users WHERE id = ? AND status = 'active'",
                [$userId]
            );

            return $user && in_array($user['role'], self::ADMIN_ROLES, true);
        } catch (\Exception $e) {
            $this->logger->error("Failed to check admin status", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get comprehensive dashboard statistics
     */
    public function getDashboardStats(): array
    {
        try {
            $stats = [
                'users' => $this->getUserStats(),
                'properties' => $this->getPropertyStats(),
                'leads' => $this->getLeadStats(),
                'revenue' => $this->getRevenueStats(),
                'activities' => $this->getActivityStats(),
                'system' => $this->getSystemStats(),
                'performance' => $this->getPerformanceStats()
            ];

            $this->logger->info("Dashboard statistics retrieved successfully");
            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get dashboard stats", ['error' => $e->getMessage()]);
            return $this->getDefaultStats();
        }
    }

    /**
     * Get user statistics
     */
    private function getUserStats(): array
    {
        $stats = [];

        // Total users
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM users");
        $stats['total'] = (int)($result['total'] ?? 0);

        // Active users
        $result = $this->db->fetchOne("SELECT COUNT(*) as active FROM users WHERE status = 'active'");
        $stats['active'] = (int)($result['active'] ?? 0);

        // New users this month
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
        );
        $stats['new_this_month'] = (int)($result['new_users'] ?? 0);

        // Users by role
        $roles = $this->db->fetchAll("SELECT role, COUNT(*) as count FROM users GROUP BY role");
        $stats['by_role'] = [];
        foreach ($roles as $role) {
            $stats['by_role'][$role['role']] = (int)$role['count'];
        }

        return $stats;
    }

    /**
     * Get property statistics
     */
    private function getPropertyStats(): array
    {
        $stats = [];

        // Total properties
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM properties");
        $stats['total'] = (int)($result['total'] ?? 0);

        // Available properties
        $result = $this->db->fetchOne("SELECT COUNT(*) as available FROM properties WHERE status = 'available'");
        $stats['available'] = (int)($result['available'] ?? 0);

        // Sold properties
        $result = $this->db->fetchOne("SELECT COUNT(*) as sold FROM properties WHERE status = 'sold'");
        $stats['sold'] = (int)($result['sold'] ?? 0);

        // Properties by type
        $types = $this->db->fetchAll("SELECT property_type, COUNT(*) as count FROM properties GROUP BY property_type");
        $stats['by_type'] = [];
        foreach ($types as $type) {
            $stats['by_type'][$type['property_type']] = (int)$type['count'];
        }

        // Average price
        $result = $this->db->fetchOne("SELECT AVG(price) as avg_price FROM properties WHERE price > 0");
        $stats['average_price'] = (float)($result['avg_price'] ?? 0);

        return $stats;
    }

    /**
     * Get lead statistics
     */
    private function getLeadStats(): array
    {
        $stats = [];

        // Total leads
        $result = $this->db->fetchOne("SELECT COUNT(*) as total FROM leads");
        $stats['total'] = (int)($result['total'] ?? 0);

        // New leads this month
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as new_leads FROM leads WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
        );
        $stats['new_this_month'] = (int)($result['new_leads'] ?? 0);

        // Leads by status
        $statuses = $this->db->fetchAll("SELECT status, COUNT(*) as count FROM leads GROUP BY status");
        $stats['by_status'] = [];
        foreach ($statuses as $status) {
            $stats['by_status'][$status['status']] = (int)$status['count'];
        }

        // Conversion rate
        $totalLeads = $stats['total'];
        $convertedLeads = $this->db->fetchOne("SELECT COUNT(*) as converted FROM leads WHERE status = 'converted'");
        $stats['conversion_rate'] = $totalLeads > 0 ? round(((int)$convertedLeads['converted'] / $totalLeads) * 100, 2) : 0;

        return $stats;
    }

    /**
     * Get revenue statistics
     */
    private function getRevenueStats(): array
    {
        $stats = [];

        // Total revenue
        $result = $this->db->fetchOne("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
        $stats['total'] = (float)($result['total'] ?? 0);

        // Revenue this month
        $result = $this->db->fetchOne(
            "SELECT SUM(amount) as monthly FROM payments WHERE status = 'completed' AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
        );
        $stats['this_month'] = (float)($result['monthly'] ?? 0);

        // Revenue last month
        $result = $this->db->fetchOne(
            "SELECT SUM(amount) as last_month FROM payments WHERE status = 'completed' AND created_at >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01') AND created_at < DATE_FORMAT(NOW(), '%Y-%m-01')"
        );
        $stats['last_month'] = (float)($result['last_month'] ?? 0);

        // Revenue by source
        $sources = $this->db->fetchAll("SELECT payment_source, SUM(amount) as total FROM payments WHERE status = 'completed' GROUP BY payment_source");
        $stats['by_source'] = [];
        foreach ($sources as $source) {
            $stats['by_source'][$source['payment_source']] = (float)$source['total'];
        }

        return $stats;
    }

    /**
     * Get activity statistics
     */
    private function getActivityStats(): array
    {
        $stats = [];

        // Activities today
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as today FROM activities WHERE DATE(created_at) = CURDATE()"
        );
        $stats['today'] = (int)($result['today'] ?? 0);

        // Activities this week
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as this_week FROM activities WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $stats['this_week'] = (int)($result['this_week'] ?? 0);

        // Activities by type
        $types = $this->db->fetchAll("SELECT activity_type, COUNT(*) as count FROM activities GROUP BY activity_type LIMIT 10");
        $stats['by_type'] = [];
        foreach ($types as $type) {
            $stats['by_type'][$type['activity_type']] = (int)$type['count'];
        }

        return $stats;
    }

    /**
     * Get system statistics
     */
    private function getSystemStats(): array
    {
        $stats = [];

        // Database size
        try {
            $result = $this->db->fetchOne("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = DATABASE()");
            $stats['database_size_mb'] = (float)($result['size_mb'] ?? 0);
        } catch (\Exception $e) {
            $stats['database_size_mb'] = 0;
        }

        // Total tables
        try {
            $result = $this->db->fetchOne("SELECT COUNT(*) as tables FROM information_schema.tables WHERE table_schema = DATABASE()");
            $stats['total_tables'] = (int)($result['tables'] ?? 0);
        } catch (\Exception $e) {
            $stats['total_tables'] = 0;
        }

        // System uptime (simplified)
        $stats['uptime_hours'] = floor((time() - filemtime(__FILE__)) / 3600);

        // Memory usage
        $stats['memory_usage_mb'] = round(memory_get_usage(true) / 1024 / 1024, 2);

        return $stats;
    }

    /**
     * Get performance statistics
     */
    private function getPerformanceStats(): array
    {
        $stats = [];

        // Average response time (simplified)
        $stats['avg_response_time_ms'] = rand(50, 200); // Placeholder

        // Peak concurrent users
        $stats['peak_concurrent_users'] = rand(10, 50); // Placeholder

        // Error rate
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM system_logs WHERE level = 'ERROR' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        $totalErrors = (int)($result['total'] ?? 0);
        $totalRequests = 1000; // Placeholder
        $stats['error_rate_percent'] = $totalRequests > 0 ? round(($totalErrors / $totalRequests) * 100, 2) : 0;

        return $stats;
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities(int $limit = 10): array
    {
        try {
            $activities = $this->db->fetchAll(
                "SELECT * FROM activities ORDER BY created_at DESC LIMIT ?",
                [$limit]
            );

            return array_map(function($activity) {
                return [
                    'id' => $activity['id'],
                    'type' => $activity['activity_type'],
                    'description' => $activity['description'],
                    'user_id' => $activity['user_id'],
                    'created_at' => $activity['created_at']
                ];
            }, $activities);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get recent activities", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get system health status
     */
    public function getSystemHealth(): array
    {
        $health = [
            'overall' => 'healthy',
            'database' => 'healthy',
            'storage' => 'healthy',
            'memory' => 'healthy',
            'services' => []
        ];

        // Check database connection
        try {
            $this->db->fetchOne("SELECT 1");
            $health['database'] = 'healthy';
        } catch (\Exception $e) {
            $health['database'] = 'unhealthy';
            $health['overall'] = 'unhealthy';
        }

        // Check storage (simplified)
        $freeSpace = disk_free_space('.');
        $totalSpace = disk_total_space('.');
        $usagePercent = $totalSpace > 0 ? (($totalSpace - $freeSpace) / $totalSpace) * 100 : 0;
        $health['storage'] = $usagePercent < 80 ? 'healthy' : 'warning';
        $health['storage_usage_percent'] = round($usagePercent, 2);

        // Check memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
        $memoryUsagePercent = $memoryLimitBytes > 0 ? ($memoryUsage / $memoryLimitBytes) * 100 : 0;
        $health['memory'] = $memoryUsagePercent < 80 ? 'healthy' : 'warning';
        $health['memory_usage_percent'] = round($memoryUsagePercent, 2);

        // Check critical services
        $services = ['database', 'cache', 'email', 'storage'];
        foreach ($services as $service) {
            $health['services'][$service] = $this->checkServiceHealth($service);
        }

        return $health;
    }

    /**
     * Check individual service health
     */
    private function checkServiceHealth(string $service): string
    {
        switch ($service) {
            case 'database':
                try {
                    $this->db->fetchOne("SELECT 1");
                    return 'healthy';
                } catch (\Exception $e) {
                    return 'unhealthy';
                }
            
            case 'cache':
                // Simplified cache check
                return 'healthy';
            
            case 'email':
                // Simplified email service check
                return 'healthy';
            
            case 'storage':
                $freeSpace = disk_free_space('.');
                return $freeSpace > 100 * 1024 * 1024 ? 'healthy' : 'warning'; // 100MB minimum
            
            default:
                return 'unknown';
        }
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = strtolower(trim($limit));
        $last = $limit[strlen($limit) - 1];
        $value = (int)$limit;

        switch ($last) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }

    /**
     * Get default stats when database fails
     */
    private function getDefaultStats(): array
    {
        return [
            'users' => ['total' => 0, 'active' => 0, 'new_this_month' => 0, 'by_role' => []],
            'properties' => ['total' => 0, 'available' => 0, 'sold' => 0, 'by_type' => [], 'average_price' => 0],
            'leads' => ['total' => 0, 'new_this_month' => 0, 'by_status' => [], 'conversion_rate' => 0],
            'revenue' => ['total' => 0, 'this_month' => 0, 'last_month' => 0, 'by_source' => []],
            'activities' => ['today' => 0, 'this_week' => 0, 'by_type' => []],
            'system' => ['database_size_mb' => 0, 'total_tables' => 0, 'uptime_hours' => 0, 'memory_usage_mb' => 0],
            'performance' => ['avg_response_time_ms' => 0, 'peak_concurrent_users' => 0, 'error_rate_percent' => 0]
        ];
    }

    /**
     * Get admin menu items based on user permissions
     */
    public function getAdminMenu(?int $userId = null): array
    {
        if (!$this->isAdmin($userId)) {
            return [];
        }

        $menu = [
            'dashboard' => [
                'title' => 'Dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'url' => '/admin/dashboard',
                'active' => false
            ],
            'users' => [
                'title' => 'Users',
                'icon' => 'fas fa-users',
                'url' => '/admin/users',
                'active' => false
            ],
            'properties' => [
                'title' => 'Properties',
                'icon' => 'fas fa-home',
                'url' => '/admin/properties',
                'active' => false
            ],
            'leads' => [
                'title' => 'Leads',
                'icon' => 'fas fa-user-tie',
                'url' => '/admin/leads',
                'active' => false
            ],
            'reports' => [
                'title' => 'Reports',
                'icon' => 'fas fa-chart-bar',
                'url' => '/admin/reports',
                'active' => false
            ],
            'settings' => [
                'title' => 'Settings',
                'icon' => 'fas fa-cog',
                'url' => '/admin/settings',
                'active' => false
            ]
        ];

        return $menu;
    }

    /**
     * Log admin activity
     */
    public function logAdminActivity(int $userId, string $action, array $data = []): bool
    {
        try {
            $this->db->execute(
                "INSERT INTO admin_activities (user_id, action, data, created_at) VALUES (?, ?, ?, NOW())",
                [$userId, $action, json_encode($data)]
            );

            $this->logger->info("Admin activity logged", [
                'user_id' => $userId,
                'action' => $action
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to log admin activity", [
                'user_id' => $userId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get admin activity logs
     */
    public function getAdminActivityLogs(int $limit = 50, array $filters = []): array
    {
        try {
            $sql = "SELECT * FROM admin_activities WHERE 1=1";
            $params = [];

            if (!empty($filters['user_id'])) {
                $sql .= " AND user_id = ?";
                $params[] = $filters['user_id'];
            }

            if (!empty($filters['action'])) {
                $sql .= " AND action = ?";
                $params[] = $filters['action'];
            }

            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(created_at) >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $filters['date_to'];
            }

            $sql .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;

            $logs = $this->db->fetchAll($sql, $params);

            return array_map(function($log) {
                return [
                    'id' => $log['id'],
                    'user_id' => $log['user_id'],
                    'action' => $log['action'],
                    'data' => json_decode($log['data'], true) ?: [],
                    'created_at' => $log['created_at']
                ];
            }, $logs);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get admin activity logs", ['error' => $e->getMessage()]);
            return [];
        }
    }
}
