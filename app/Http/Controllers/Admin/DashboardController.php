<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\DashboardService;
use Psr\Log\LoggerInterface;

class DashboardController
{
    private DashboardService $dashboardService;
    private LoggerInterface $logger;

    public function __construct(DashboardService $dashboardService, LoggerInterface $logger)
    {
        $this->dashboardService = $dashboardService;
        $this->logger = $logger;
    }

    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return redirect('/')->with('error', 'Access denied');
            }

            $stats = $this->dashboardService->getDashboardStats();
            $recentActivities = $this->dashboardService->getRecentActivities(10);
            $systemHealth = $this->dashboardService->getSystemHealth();
            $adminMenu = $this->dashboardService->getAdminMenu();
            
            return view('admin.dashboard', [
                'stats' => $stats,
                'recent_activities' => $recentActivities,
                'system_health' => $systemHealth,
                'admin_menu' => $adminMenu,
                'page_title' => 'Admin Dashboard - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load admin dashboard", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }

    /**
     * Get dashboard statistics (AJAX)
     */
    public function getStats()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $stats = $this->dashboardService->getDashboardStats();

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get dashboard stats", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics'
            ], 500);
        }
    }

    /**
     * Get system health status
     */
    public function getSystemHealth()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $health = $this->dashboardService->getSystemHealth();

            return response()->json([
                'success' => true,
                'health' => $health
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get system health", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get system health'
            ], 500);
        }
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $limit = request()->input('limit', 10);
            $activities = $this->dashboardService->getRecentActivities((int)$limit);

            return response()->json([
                'success' => true,
                'activities' => $activities
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get recent activities", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recent activities'
            ], 500);
        }
    }

    /**
     * Get admin menu
     */
    public function getAdminMenu()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $menu = $this->dashboardService->getAdminMenu();

            return response()->json([
                'success' => true,
                'menu' => $menu
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get admin menu", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get admin menu'
            ], 500);
        }
    }

    /**
     * Log admin activity
     */
    public function logActivity()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $userId = $_SESSION['user_id'] ?? null;
            $action = request()->input('action');
            $data = request()->input('data', []);

            if (!$userId || !$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID and action are required'
                ], 400);
            }

            if ($this->dashboardService->logAdminActivity((int)$userId, $action, $data)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Activity logged successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to log activity'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logger->error("Failed to log admin activity", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to log activity'
            ], 500);
        }
    }

    /**
     * Get admin activity logs
     */
    public function getActivityLogs()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $limit = request()->input('limit', 50);
            $filters = request()->only(['user_id', 'action', 'date_from', 'date_to']);

            $logs = $this->dashboardService->getAdminActivityLogs((int)$limit, $filters);

            return response()->json([
                'success' => true,
                'logs' => $logs,
                'total' => count($logs)
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get activity logs", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get activity logs'
            ], 500);
        }
    }

    /**
     * Admin settings page
     */
    public function settings()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return redirect('/')->with('error', 'Access denied');
            }

            $systemHealth = $this->dashboardService->getSystemHealth();
            $adminMenu = $this->dashboardService->getAdminMenu();
            
            return view('admin.settings', [
                'system_health' => $systemHealth,
                'admin_menu' => $adminMenu,
                'page_title' => 'Admin Settings - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load admin settings", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }

    /**
     * Admin reports page
     */
    public function reports()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return redirect('/')->with('error', 'Access denied');
            }

            $stats = $this->dashboardService->getDashboardStats();
            $adminMenu = $this->dashboardService->getAdminMenu();
            
            return view('admin.reports', [
                'stats' => $stats,
                'admin_menu' => $adminMenu,
                'page_title' => 'Admin Reports - APS Dream Home'
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to load admin reports", ['error' => $e->getMessage()]);
            return view('errors.500');
        }
    }

    /**
     * Refresh dashboard data
     */
    public function refresh()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $stats = $this->dashboardService->getDashboardStats();
            $recentActivities = $this->dashboardService->getRecentActivities(5);
            $systemHealth = $this->dashboardService->getSystemHealth();

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'recent_activities' => $recentActivities,
                'system_health' => $systemHealth,
                'refreshed_at' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to refresh dashboard", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh dashboard'
            ], 500);
        }
    }

    /**
     * Get dashboard widgets
     */
    public function getWidgets()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $widgets = request()->input('widgets', []);
            $data = [];

            foreach ($widgets as $widget) {
                switch ($widget) {
                    case 'users':
                        $data['users'] = $this->dashboardService->getDashboardStats()['users'];
                        break;
                    case 'properties':
                        $data['properties'] = $this->dashboardService->getDashboardStats()['properties'];
                        break;
                    case 'leads':
                        $data['leads'] = $this->dashboardService->getDashboardStats()['leads'];
                        break;
                    case 'revenue':
                        $data['revenue'] = $this->dashboardService->getDashboardStats()['revenue'];
                        break;
                    case 'system_health':
                        $data['system_health'] = $this->dashboardService->getSystemHealth();
                        break;
                    case 'activities':
                        $data['activities'] = $this->dashboardService->getRecentActivities(5);
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'widgets' => $data
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Failed to get dashboard widgets", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get widgets'
            ], 500);
        }
    }

    /**
     * Export dashboard data
     */
    public function export()
    {
        try {
            // Check if user is admin
            if (!$this->dashboardService->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $format = request()->input('format', 'json');
            $stats = $this->dashboardService->getDashboardStats();

            switch ($format) {
                case 'json':
                    header('Content-Type: application/json');
                    header('Content-Disposition: attachment; filename="dashboard_stats.json"');
                    echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    break;

                case 'csv':
                    header('Content-Type: text/csv');
                    header('Content-Disposition: attachment; filename="dashboard_stats.csv"');
                    
                    $output = fopen('php://output', 'w');
                    
                    // Header
                    fputcsv($output, ['Category', 'Metric', 'Value']);
                    
                    // Data
                    foreach ($stats as $category => $data) {
                        if (is_array($data)) {
                            foreach ($data as $key => $value) {
                                if (is_array($value)) {
                                    foreach ($value as $subKey => $subValue) {
                                        fputcsv($output, [$category, "{$key}.{$subKey}", $subValue]);
                                    }
                                } else {
                                    fputcsv($output, [$category, $key, $value]);
                                }
                            }
                        }
                    }
                    
                    fclose($output);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Unsupported format'
                    ], 400);
            }

            exit;

        } catch (\Exception $e) {
            $this->logger->error("Failed to export dashboard data", ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data'
            ], 500);
        }
    }
}
