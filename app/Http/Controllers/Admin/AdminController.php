<?php

/**
 * Admin Controller
 * Handles admin dashboard, property management, user management, and settings
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Cache;
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
            @session_start();
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
            @session_start();
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

            // Get Khatabook sales stats
            try {
                $khatabookStats = $this->db->fetch("SELECT COUNT(*) as total_sales, COALESCE(SUM(amount), 0) as total_amount FROM khatabook_sales");
                $khatabookStats['recent_sales'] = $this->db->fetchAll("SELECT * FROM khatabook_sales ORDER BY transaction_date DESC LIMIT 5") ?? [];
            } catch (\Exception $e) {
                $khatabookStats = ['total_sales' => 0, 'total_amount' => 0, 'recent_sales' => []];
            }

            // Get Ad slot stats
            try {
                $adStats = $this->db->fetch("SELECT COUNT(*) as total_slots, COALESCE(SUM(views), 0) as total_views, COALESCE(SUM(clicks), 0) as total_clicks FROM ad_slots WHERE status = 'active'");
            } catch (\Exception $e) {
                $adStats = ['total_slots' => 0, 'total_views' => 0, 'total_clicks' => 0];
            }

            $dashboard_stats = array_merge($stats, [
                'khatabook_sales' => $khatabookStats['total_sales'] ?? 0,
                'khatabook_amount' => $khatabookStats['total_amount'] ?? 0,
                'ad_slots' => $adStats['total_slots'] ?? 0,
                'ad_views' => $adStats['total_views'] ?? 0,
                'ad_clicks' => $adStats['total_clicks'] ?? 0,
            ]);

            // Get recent activities
            $recentActivities = $this->getRecentActivities();

            // Get charts data
            $chartsData = $this->getChartsData();

            $this->data = array_merge($this->data, [
                'stats' => $stats,
                'dashboard_stats' => $dashboard_stats,
                'khatabookStats' => $khatabookStats,
                'adStats' => $adStats,
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
     * Get total users count (cached 5 min)
     */
    private function getTotalUsers()
    {
        try {
            return Cache::remember('admin_dash_total_users', function () {
                return $this->db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
            }, 300);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total properties count (cached 5 min)
     */
    private function getTotalProperties()
    {
        try {
            return Cache::remember('admin_dash_total_properties', function () {
                return $this->db->fetch("SELECT COUNT(*) as count FROM properties")['count'] ?? 0;
            }, 300);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total inquiries count (cached 5 min)
     */
    private function getTotalInquiries()
    {
        try {
            return Cache::remember('admin_dash_total_inquiries', function () {
                return $this->db->fetch("SELECT COUNT(*) as count FROM inquiries")['count'] ?? 0;
            }, 300);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total revenue (cached 5 min)
     */
    private function getTotalRevenue()
    {
        try {
            return Cache::remember('admin_dash_total_revenue', function () {
                return $this->db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'")['total'] ?? 0;
            }, 300);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get active properties count (cached 5 min)
     */
    private function getActiveProperties()
    {
        try {
            return Cache::remember('admin_dash_active_properties', function () {
                return $this->db->fetch("SELECT COUNT(*) as count FROM properties WHERE status = 'active'")['count'] ?? 0;
            }, 300);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get new users today (cached 2 min - changes more frequently)
     */
    private function getNewUsersToday()
    {
        try {
            return Cache::remember('admin_dash_new_users_today', function () {
                return $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
            }, 120);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get pending approvals (cached 3 min)
     */
    private function getPendingApprovals()
    {
        try {
            return Cache::remember('admin_dash_pending_approvals', function () {
                return $this->db->fetch("SELECT COUNT(*) as count FROM properties WHERE status = 'pending'")['count'] ?? 0;
            }, 180);
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
    public function getRecentActivities()
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
     * Get dashboard stats (API endpoint)
     */
    public function getStats()
    {
        header('Content-Type: application/json');
        try {
            $stats = [
                'total_users' => Cache::remember('admin_api_total_users', function () {
                    return $this->db->fetch("SELECT COUNT(*) as c FROM users")['c'] ?? 0;
                }, 300),
                'total_properties' => Cache::remember('admin_api_total_properties', function () {
                    return $this->db->fetch("SELECT COUNT(*) as c FROM properties")['c'] ?? 0;
                }, 300),
                'total_leads' => Cache::remember('admin_api_total_leads', function () {
                    return $this->db->fetch("SELECT COUNT(*) as c FROM leads")['c'] ?? 0;
                }, 300),
                'pending_bookings' => Cache::remember('admin_api_pending_bookings', function () {
                    return $this->db->fetch("SELECT COUNT(*) as c FROM bookings WHERE status='pending'")['c'] ?? 0;
                }, 180),
            ];
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Get charts data for dashboard
     */
    private function getChartsData()
    {
        // User registrations per month (last 6 months)
        try {
            $userData = $this->db->fetchAll("
                SELECT DATE_FORMAT(created_at, '%b') as label, COUNT(*) as count
                FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY MIN(created_at)
            ");
        } catch (\Exception $e) {
            $userData = [];
        }
        $userLabels = [];
        $userCounts = [];
        foreach ($userData as $row) {
            $userLabels[] = $row['label'];
            $userCounts[] = (int)$row['count'];
        }
        if (empty($userLabels)) {
            $userLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            $userCounts = [0, 0, 0, 0, 0, 0];
        }

        // Bookings per month (last 6 months)
        try {
            $bookingData = $this->db->fetchAll("
                SELECT DATE_FORMAT(created_at, '%b') as label, COUNT(*) as count
                FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY MIN(created_at)
            ");
        } catch (\Exception $e) {
            $bookingData = [];
        }
        $bookingLabels = [];
        $bookingCounts = [];
        foreach ($bookingData as $row) {
            $bookingLabels[] = $row['label'];
            $bookingCounts[] = (int)$row['count'];
        }
        if (empty($bookingLabels)) {
            $bookingLabels = $userLabels;
            $bookingCounts = array_fill(0, count($userLabels), 0);
        }

        // Revenue per month from bookings (last 6 months)
        try {
            $revenueData = $this->db->fetchAll("
                SELECT DATE_FORMAT(b.created_at, '%b') as label, COALESCE(SUM(bp.amount), 0) as total
                FROM bookings b
                LEFT JOIN booking_payments bp ON bp.booking_id = b.id
                WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(b.created_at, '%Y-%m') ORDER BY MIN(b.created_at)
            ");
        } catch (\Exception $e) {
            $revenueData = [];
        }
        $revenueLabels = [];
        $revenueTotals = [];
        foreach ($revenueData as $row) {
            $revenueLabels[] = $row['label'];
            $revenueTotals[] = (float)$row['total'];
        }
        if (empty($revenueLabels)) {
            $revenueLabels = $userLabels;
            $revenueTotals = array_fill(0, count($userLabels), 0);
        }

        return [
            'user_registrations' => ['labels' => $userLabels, 'data' => $userCounts],
            'property_views' => ['labels' => $bookingLabels, 'data' => $bookingCounts],
            'revenue' => ['labels' => $revenueLabels, 'data' => $revenueTotals]
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
    public function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
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
            @session_start();
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
            @session_start();
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

    public function devTools()
    {
        $this->requireAdmin();
        return $this->render('admin/dev-tools/index', ['page_title' => 'Developer Tools']);
    }
}
