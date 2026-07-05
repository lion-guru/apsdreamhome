<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

/**
 * Admin Controller
 * Handles all admin panel operations
 */
class AdminController extends BaseController
{
    private static $skipAdminCheck = false;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';

        // RBAC: Enforce admin-only access for ALL admin controllers
        if (!self::$skipAdminCheck && !$this->isAdmin()) {
            // API calls get JSON error, page loads get redirect
            $isApi = strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
            if ($isApi) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Admin access required', 'redirect' => '/admin/login']);
                exit;
            }
            $_SESSION['error_message'] = 'Admin access required. Please login as admin.';
            $this->redirect('/admin/login');
        }
    }

    /**
     * Allow specific admin controller methods to skip admin check
     */
    protected function skipAdminCheck()
    {
        self::$skipAdminCheck = true;
    }

    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        // Check if admin is logged in
        if (!$this->get('admin_id')) {
            echo "<script>window.location.href='/admin/login';</script>";
            return;
        }

        // Simple dashboard data for demo
        $stats = [
            'total_users' => 150,
            'total_properties' => 85,
            'total_bookings' => 42,
            'total_revenue' => '₹2,45,000'
        ];

        $recentActivities = [
            ['user' => 'John Doe', 'action' => 'Registered', 'time' => '2 hours ago'],
            ['user' => 'Jane Smith', 'action' => 'Property Booking', 'time' => '3 hours ago'],
            ['user' => 'Mike Wilson', 'action' => 'Login', 'time' => '5 hours ago']
        ];

        echo "<h1>Admin Dashboard</h1>";
        echo "<h2>Statistics</h2>";
        echo "<ul>";
        foreach ($stats as $key => $value) {
            echo "<li><strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> " . $value . "</li>";
        }
        echo "</ul>";

        echo "<h2>Recent Activities</h2>";
        echo "<ul>";
        foreach ($recentActivities as $activity) {
            echo "<li>" . $activity['user'] . " - " . $activity['action'] . " (" . $activity['time'] . ")</li>";
        }
        echo "</ul>";

        echo "<p><a href='/admin/login'>Logout</a></p>";
    }

    /**
     * Check if admin is logged in
     */
    public function isLoggedIn()
    {
        return $this->get('admin_id') !== null;
    }
}
