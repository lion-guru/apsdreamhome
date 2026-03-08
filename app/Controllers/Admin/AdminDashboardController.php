<?php

namespace App\Controllers\Admin;

use App\Services\Admin\AdminDashboardService;
use App\Services\Auth\AuthenticationService;
use App\Core\ViewRenderer;

/**
 * Admin Dashboard Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class AdminDashboardController
{
    private $dashboardService;
    private $authService;
    private $viewRenderer;

    public function __construct()
    {
        $this->dashboardService = new AdminDashboardService();
        $this->authService = new AuthenticationService();
        $this->viewRenderer = new ViewRenderer();
    }

    /**
     * Show admin dashboard
     */
    public function dashboard($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated()) {
            $_SESSION['errors'] = ['Please login to access dashboard'];
            $this->redirect('/login');
            return;
        }

        $user = $this->authService->getCurrentUser();
        if (!$this->isAdmin($user)) {
            $_SESSION['errors'] = ['Access denied. Admin privileges required.'];
            $this->redirect('/dashboard');
            return;
        }

        // Get dashboard data
        $statsResult = $this->dashboardService->getDashboardStats();
        $activitiesResult = $this->dashboardService->getRecentActivities(10);
        $quickStatsResult = $this->dashboardService->getQuickStats();

        $data = [
            'title' => 'Admin Dashboard - APS Dream Home',
            'user' => $user,
            'stats' => $statsResult['success'] ? $statsResult['data'] : [],
            'activities' => $activitiesResult['success'] ? $activitiesResult['data'] : [],
            'quick_stats' => $quickStatsResult['success'] ? $quickStatsResult['data'] : [],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('admin/dashboard', $data);
    }

    /**
     * Show analytics page
     */
    public function analytics($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $propertyAnalyticsResult = $this->dashboardService->getPropertyAnalytics();
        $userManagementResult = $this->dashboardService->getUserManagementData();
        $leadManagementResult = $this->dashboardService->getLeadManagementData();
        $bookingManagementResult = $this->dashboardService->getBookingManagementData();

        $data = [
            'title' => 'Analytics - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'property_analytics' => $propertyAnalyticsResult['success'] ? $propertyAnalyticsResult['data'] : [],
            'user_management' => $userManagementResult['success'] ? $userManagementResult['data'] : [],
            'lead_management' => $leadManagementResult['success'] ? $leadManagementResult['data'] : [],
            'booking_management' => $bookingManagementResult['success'] ? $bookingManagementResult['data'] : [],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('admin/analytics', $data);
    }

    /**
     * Show system health page
     */
    public function systemHealth($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $healthResult = $this->dashboardService->getSystemHealth();

        $data = [
            'title' => 'System Health - APS Dream Home',
            'user' => $this->authService->getCurrentUser(),
            'health' => $healthResult['success'] ? $healthResult['data'] : [],
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('admin/health', $data);
    }

    /**
     * Get dashboard stats (AJAX)
     */
    public function getDashboardStats($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->dashboardService->getDashboardStats();
    }

    /**
     * Get recent activities (AJAX)
     */
    public function getRecentActivities($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $limit = intval($request['get']['limit'] ?? 10);
        $limit = min(max($limit, 1), 50); // Limit between 1 and 50

        return $this->dashboardService->getRecentActivities($limit);
    }

    /**
     * Get property analytics (AJAX)
     */
    public function getPropertyAnalytics($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->dashboardService->getPropertyAnalytics();
    }

    /**
     * Get user management data (AJAX)
     */
    public function getUserManagementData($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->dashboardService->getUserManagementData();
    }

    /**
     * Get lead management data (AJAX)
     */
    public function getLeadManagementData($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->dashboardService->getLeadManagementData();
    }

    /**
     * Get booking management data (AJAX)
     */
    public function getBookingManagementData($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->dashboardService->getBookingManagementData();
    }

    /**
     * Get system health (AJAX)
     */
    public function getSystemHealth($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->dashboardService->getSystemHealth();
    }

    /**
     * Get admin menu (AJAX)
     */
    public function getAdminMenu($request)
    {
        // Check authentication
        if (!$this->authService->isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $user = $this->authService->getCurrentUser();
        return $this->dashboardService->getAdminMenu($user['role'] ?? 'user');
    }

    /**
     * Get quick stats (AJAX)
     */
    public function getQuickStats($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        return $this->dashboardService->getQuickStats();
    }

    /**
     * Refresh dashboard data (AJAX)
     */
    public function refreshDashboard($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $statsResult = $this->dashboardService->getDashboardStats();
        $activitiesResult = $this->dashboardService->getRecentActivities(10);
        $quickStatsResult = $this->dashboardService->getQuickStats();

        return [
            'success' => true,
            'data' => [
                'stats' => $statsResult['success'] ? $statsResult['data'] : [],
                'activities' => $activitiesResult['success'] ? $activitiesResult['data'] : [],
                'quick_stats' => $quickStatsResult['success'] ? $quickStatsResult['data'] : []
            ]
        ];
    }

    /**
     * Export dashboard data (AJAX)
     */
    public function exportDashboardData($request)
    {
        // Check authentication and admin access
        if (!$this->authService->isAuthenticated() || !$this->isAdmin($this->authService->getCurrentUser())) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $type = $request['post']['type'] ?? 'stats';
        $format = $request['post']['format'] ?? 'json';

        switch ($type) {
            case 'stats':
                $result = $this->dashboardService->getDashboardStats();
                break;
            case 'analytics':
                $result = $this->dashboardService->getPropertyAnalytics();
                break;
            case 'users':
                $result = $this->dashboardService->getUserManagementData();
                break;
            case 'leads':
                $result = $this->dashboardService->getLeadManagementData();
                break;
            case 'bookings':
                $result = $this->dashboardService->getBookingManagementData();
                break;
            default:
                return [
                    'success' => false,
                    'message' => 'Invalid export type'
                ];
        }

        if (!$result['success']) {
            return $result;
        }

        // Create export file
        $filename = "dashboard_export_{$type}_" . date('Y-m-d_H-i-s') . ".{$format}";
        $filepath = STORAGE_PATH . "/exports/" . $filename;

        // Ensure export directory exists
        $exportDir = dirname($filepath);
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        if ($format === 'json') {
            file_put_contents($filepath, json_encode($result['data'], JSON_PRETTY_PRINT));
        } elseif ($format === 'csv' && $type === 'stats') {
            // Convert stats to CSV
            $csvContent = "Metric,Value\n";
            foreach ($result['data'] as $key => $value) {
                $csvContent .= "$key,$value\n";
            }
            file_put_contents($filepath, $csvContent);
        } else {
            return [
                'success' => false,
                'message' => 'Unsupported format for this export type'
            ];
        }

        return [
            'success' => true,
            'file' => $filename,
            'path' => $filepath
        ];
    }

    /**
     * Check if user is admin
     */
    private function isAdmin($user)
    {
        return $user && ($user['role'] === 'admin' || $user['role'] === 'super_admin');
    }

    /**
     * Redirect helper
     */
    private function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
    }
}
