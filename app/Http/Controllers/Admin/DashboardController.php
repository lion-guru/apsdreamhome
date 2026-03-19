<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Dashboard Controller - Custom MVC Implementation
 * Handles admin dashboard operations with dependency injection
 */
class DashboardController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
    }

    /**
     * Display admin dashboard
     */
    public function index()
    {
        try {
            $data = [
                'page_title' => 'Admin Dashboard - APS Dream Home',
                'active_page' => 'dashboard',
                'dashboard_stats' => $this->getDashboardStats(),
                'recent_activities' => $this->getRecentActivities(),
                'quick_actions' => $this->getQuickActions()
            ];

            return $this->render('admin/dashboard/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Dashboard Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load dashboard');
            return $this->redirect('admin/login');
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        try {
            $stats = [];

            // Total customers
            $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
            $result = $this->db->fetchOne($sql);
            $stats['total_customers'] = (int)($result['total'] ?? 0);

            // Total associates
            $sql = "SELECT COUNT(*) as total FROM users WHERE role = 'associate'";
            $result = $this->db->fetchOne($sql);
            $stats['total_associates'] = (int)($result['total'] ?? 0);

            // Total properties
            $sql = "SELECT COUNT(*) as total FROM properties";
            $result = $this->db->fetchOne($sql);
            $stats['total_properties'] = (int)($result['total'] ?? 0);

            // Total bookings
            $sql = "SELECT COUNT(*) as total FROM bookings";
            $result = $this->db->fetchOne($sql);
            $stats['total_bookings'] = (int)($result['total'] ?? 0);

            // Today's bookings
            $sql = "SELECT COUNT(*) as total FROM bookings WHERE DATE(created_at) = CURDATE()";
            $result = $this->db->fetchOne($sql);
            $stats['today_bookings'] = (int)($result['total'] ?? 0);

            // Total revenue
            $sql = "SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings";
            $result = $this->db->fetchOne($sql);
            $stats['total_revenue'] = (float)($result['total'] ?? 0);

            // This month's revenue
            $sql = "SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings 
                    WHERE MONTH(created_at) = MONTH(CURRENT_DATE) 
                    AND YEAR(created_at) = YEAR(CURRENT_DATE)";
            $result = $this->db->fetchOne($sql);
            $stats['monthly_revenue'] = (float)($result['total'] ?? 0);

            // Pending commissions
            $sql = "SELECT COUNT(*) as total, COALESCE(SUM(amount), 0) as amount 
                    FROM mlm_commission_ledger WHERE status = 'pending'";
            $result = $this->db->fetchOne($sql);
            $stats['pending_commissions'] = [
                'count' => (int)($result['total'] ?? 0),
                'amount' => (float)($result['amount'] ?? 0)
            ];

            return $stats;
        } catch (Exception $e) {
            $this->loggingService->error("Get Dashboard Stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities(): array
    {
        try {
            $sql = "SELECT al.*, u.name as user_name
                    FROM admin_activity_log al
                    LEFT JOIN users u ON al.admin_id = u.id
                    ORDER BY al.created_at DESC
                    LIMIT 10";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Recent Activities error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get quick actions
     */
    private function getQuickActions(): array
    {
        return [
            [
                'title' => 'Add New Customer',
                'url' => 'admin/customers/create',
                'icon' => 'user-plus',
                'color' => 'primary'
            ],
            [
                'title' => 'Add New Property',
                'url' => 'admin/properties/create',
                'icon' => 'home',
                'color' => 'success'
            ],
            [
                'title' => 'Create Booking',
                'url' => 'admin/bookings/create',
                'icon' => 'calendar-plus',
                'color' => 'info'
            ],
            [
                'title' => 'View Reports',
                'url' => 'admin/reports',
                'icon' => 'chart-bar',
                'color' => 'warning'
            ]
        ];
    }

    /**
     * API endpoint for dashboard stats (AJAX)
     */
    public function getStats()
    {
        try {
            $stats = $this->getDashboardStats();
            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Stats API error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * API endpoint for recent activities (AJAX)
     */
    public function getActivities()
    {
        try {
            $limit = (int)($_GET['limit'] ?? 10);
            $activities = $this->getRecentActivities();

            // Apply limit if specified
            if ($limit > 0 && count($activities) > $limit) {
                $activities = array_slice($activities, 0, $limit);
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $activities
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Activities API error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch activities'
            ], 500);
        }
    }

    /**
     * System health check
     */
    public function healthCheck()
    {
        try {
            $health = [
                'database' => $this->checkDatabaseHealth(),
                'disk_space' => $this->checkDiskSpace(),
                'memory_usage' => $this->checkMemoryUsage(),
                'active_sessions' => $this->checkActiveSessions(),
                'system_uptime' => $this->getSystemUptime()
            ];

            return $this->jsonResponse([
                'success' => true,
                'data' => $health
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Health Check error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Health check failed'
            ], 500);
        }
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth(): array
    {
        try {
            // Test database connection
            $sql = "SELECT 1 as test";
            $result = $this->db->fetchOne($sql);

            // Get table counts
            $sql = "SELECT COUNT(*) as table_count FROM information_schema.tables 
                    WHERE table_schema = DATABASE()";
            $result = $this->db->fetchOne($sql);

            return [
                'status' => 'healthy',
                'connection' => 'connected',
                'table_count' => (int)($result['table_count'] ?? 0),
                'last_check' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'connection' => 'disconnected',
                'error' => $e->getMessage(),
                'last_check' => date('Y-m-d H:i:s')
            ];
        }
    }

    /**
     * Check disk space
     */
    private function checkDiskSpace(): array
    {
        try {
            $totalSpace = disk_total_space('.');
            $freeSpace = disk_free_space('.');
            $usedSpace = $totalSpace - $freeSpace;

            return [
                'total' => $this->formatBytes($totalSpace),
                'used' => $this->formatBytes($usedSpace),
                'free' => $this->formatBytes($freeSpace),
                'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2)
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check memory usage
     */
    private function checkMemoryUsage(): array
    {
        try {
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));

            return [
                'current' => $this->formatBytes($memoryUsage),
                'limit' => $this->formatBytes($memoryLimit),
                'usage_percentage' => round(($memoryUsage / $memoryLimit) * 100, 2),
                'peak' => $this->formatBytes(memory_get_peak_usage(true))
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check active sessions
     */
    private function checkActiveSessions(): array
    {
        try {
            // This is a simplified check - in production you'd check session storage
            $sessionPath = session_save_path();
            $sessionFiles = glob($sessionPath . '/sess_*');

            return [
                'active_sessions' => count($sessionFiles),
                'session_path' => $sessionPath,
                'last_check' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): string
    {
        try {
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                return 'Load: ' . implode(', ', $load);
            }

            // Fallback for Windows
            return 'System running';
        } catch (Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Parse memory limit string
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = strtolower($limit);
        $multiplier = 1;

        if (strpos($limit, 'g') !== false) {
            $multiplier = 1024 * 1024 * 1024;
        } elseif (strpos($limit, 'm') !== false) {
            $multiplier = 1024 * 1024;
        } elseif (strpos($limit, 'k') !== false) {
            $multiplier = 1024;
        }

        return (int)preg_replace('/[^0-9]/', '', $limit) * $multiplier;
    }
}
