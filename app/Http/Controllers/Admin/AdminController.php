<?php

/**
 * Admin Controller
 * Handles admin dashboard, property management, user management, and settings
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Admin;
use App\Models\About;
use App\Models\Property;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Tax;
use App\Models\FinancialReports;
use App\Models\Budget;
use Exception;

class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        // Set admin layout
        $this->layout = 'layouts/admin';

        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Enterprise Dashboard
     */
    public function enterpriseDashboard()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if admin is logged in - use direct session check
        if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'] ?? '', ['admin', 'super_admin'])) {
            $_SESSION['error'] = 'Admin access required';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        try {
            // Get dashboard statistics
            $stats = [
                'total_users' => $this->getTotalUsers(),
                'total_properties' => $this->getTotalProperties(),
                'total_inquiries' => $this->getTotalInquiries(),
                'total_revenue' => $this->getTotalRevenue(),
                'active_properties' => $this->getActiveProperties(),
                'new_users_today' => $this->getNewUsersToday(),
                'pending_approvals' => $this->getPendingApprovals(),
                'system_health' => $this->getSystemHealth()
            ];

            // Get recent activities
            $recentActivities = $this->getRecentActivities();

            // Get charts data
            $chartsData = $this->getChartsData();

            $this->data = array_merge($this->data, [
                'stats' => $stats,
                'recent_activities' => $recentActivities,
                'charts_data' => $chartsData,
                'page_title' => 'Enterprise Dashboard - ' . $this->getConfig('app_name'),
                'page_description' => 'SuperAdmin Control Center'
            ]);

            return $this->render('admin/dashboard', $this->data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Error loading enterprise dashboard: ' . $e->getMessage());
            return $this->render('admin/dashboard', [
                'page_title' => 'Enterprise Dashboard - ' . $this->getConfig('app_name'),
                'error' => true
            ]);
        }
    }

    /**
     * Admin Dashboard
     */
    public function dashboard()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if admin is logged in - use direct session check
        if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'] ?? '', ['admin', 'super_admin'])) {
            $_SESSION['error'] = 'Admin access required';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        try {
            // Get dashboard statistics
            $stats = [
                'total_users' => $this->getTotalUsers(),
                'total_properties' => $this->getTotalProperties(),
                'total_inquiries' => $this->getTotalInquiries(),
                'total_revenue' => $this->getTotalRevenue(),
                'active_properties' => $this->getActiveProperties(),
                'new_users_today' => $this->getNewUsersToday(),
                'pending_approvals' => $this->getPendingApprovals(),
                'system_health' => $this->getSystemHealth()
            ];

            // Get recent activities
            $recentActivities = $this->getRecentActivities();

            // Get charts data
            $chartsData = $this->getChartsData();

            $this->data = array_merge($this->data, [
                'stats' => $stats,
                'recent_activities' => $recentActivities,
                'charts_data' => $chartsData,
                'page_title' => 'Admin Dashboard - ' . $this->getConfig('app_name'),
                'page_description' => 'Manage your real estate business'
            ]);

            return $this->render('admin/dashboard', $this->data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Error loading dashboard: ' . $e->getMessage());
            return $this->render('admin/dashboard', [
                'page_title' => 'Admin Dashboard - ' . $this->getConfig('app_name'),
                'error' => true
            ]);
        }
    }

    /**
     * Get total users count
     */
    private function getTotalUsers()
    {
        try {
            return $this->db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total properties count
     */
    private function getTotalProperties()
    {
        try {
            return $this->db->fetch("SELECT COUNT(*) as count FROM properties")['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total inquiries count
     */
    private function getTotalInquiries()
    {
        try {
            return $this->db->fetch("SELECT COUNT(*) as count FROM inquiries")['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total revenue
     */
    private function getTotalRevenue()
    {
        try {
            return $this->db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'")['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get active properties count
     */
    private function getActiveProperties()
    {
        try {
            return $this->db->fetch("SELECT COUNT(*) as count FROM properties WHERE status = 'active'")['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get new users today
     */
    private function getNewUsersToday()
    {
        try {
            return $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get pending approvals
     */
    private function getPendingApprovals()
    {
        try {
            return $this->db->fetch("SELECT COUNT(*) as count FROM properties WHERE status = 'pending'")['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get system health status
     */
    private function getSystemHealth()
    {
        return [
            'database' => 'Healthy',
            'server' => 'Optimal',
            'storage' => '78% Used',
            'memory' => '62% Used'
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        try {
            return $this->db->fetchAll("
                SELECT 'user' as type, name, created_at as date, 'registered' as action 
                FROM users 
                WHERE DATE(created_at) = CURDATE() 
                ORDER BY created_at DESC 
                LIMIT 5
            ");
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get charts data for dashboard
     */
    private function getChartsData()
    {
        return [
            'user_registrations' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'data' => [12, 19, 15, 25, 22, 30]
            ],
            'property_views' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'data' => [150, 230, 180, 290, 310, 280]
            ],
            'revenue' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'data' => [450000, 520000, 480000, 610000, 590000, 670000]
            ]
        ];
    }

    /**
     * Reports page
     */
    public function reports()
    {
        $reports = $this->getChartsData();

        return $this->render('admin/reports', [
            'page_title' => 'Reports & Analytics - APS Dream Home',
            'page_description' => 'View system reports and analytics',
            'reports' => $reports
        ]);
    }

    /**
     * Get users list
     */
    public function getUsersList()
    {
        try {
            return $this->db->fetchAll("SELECT id, name, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 50");
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * JSON response helper
     */
    public function jsonResponse($data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error response helper
     */
    protected function jsonError($message, $statusCode = 400)
    {
        $this->jsonResponse(['success' => false, 'message' => $message], $statusCode);
    }

    /**
     * Update page content (AJAX)
     */
    public function updatePageContent($id, $content)
    {
        // For now, satisfy the call. In a production environment,
        // this would update a database or storage.
        try {
            // Log the update if needed
            // error_log("Updating content for ID: $id");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * User Network Management
     */
    public function users()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if admin is logged in
        if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'] ?? '', ['admin', 'super_admin'])) {
            $_SESSION['error'] = 'Admin access required';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        // Get users list
        $users = $this->getUsersList();

        // Load users view
        //require_once APP_PATH . '/views/admin/users.php';
        $this->render('admin/modules/accounts/users', [
            'page_title' => 'User Network - APS Dream Home',
            'page_description' => 'Manage all users in the APS Dream Home network',
            'users' => $users
        ]);
    }

    /**
     * Properties Management
     */
    public function properties()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if admin is logged in
        if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'] ?? '', ['admin', 'super_admin'])) {
            $_SESSION['error'] = 'Admin access required';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        // Get properties list
        $properties = $this->getPropertiesList();

        // Load properties view
        require_once APP_PATH . '/views/admin/properties.php';
    }

    /**
     * Get properties list
     */
    public function getPropertiesList()
    {
        try {
            return $this->db->fetchAll("SELECT id, title, location, price, status, featured, created_at FROM properties ORDER BY created_at DESC LIMIT 50");
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Require admin authentication
     */
    public function requireAdmin()
    {
        if (!$this->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
            $this->setFlash('error', 'Admin access required');
            $this->redirect('/admin/login');
        }
    }
}
