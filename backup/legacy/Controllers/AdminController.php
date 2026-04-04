<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

/**
 * Admin Controller
 * Handles all admin panel operations
 */
class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
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
