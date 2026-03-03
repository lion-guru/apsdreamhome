<?php

namespace App\Http\Controllers\Admin;

class AdminDashboardController extends AdminController
{
    /**
     * Legacy Dashboard Controller
     * Now inherits from AdminController to consolidate logic
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display admin dashboard
     */
    public function index()
    {
        parent::index();
    }

    /**
     * Alias for index (Dashboard)
     */
    public function dashboard()
    {
        parent::dashboard();
    }

    /**
     * Get dashboard statistics (AJAX)
     * Replaces legacy get_dashboard_stats.php
     */
    public function stats()
    {
        // CSRF validation
        if (!$this->validateCsrfToken($this->request->get('csrf_token'))) {
            return $this->jsonError('Security validation failed', 403);
        }

        try {
            $db = \App\Core\Database::getInstance();

            // Get total users
            $users = $db->fetch("SELECT COUNT(*) as total FROM users u LEFT JOIN mlm_profiles a ON u.id = a.user_id WHERE COALESCE(a.status, 'active') = 'active'");

            // Get total properties
            $properties = $db->fetch("SELECT COUNT(*) as total FROM properties WHERE status = 'active'");

            // Get total bookings
            $bookings = $db->fetch("SELECT COUNT(*) as total FROM bookings WHERE status != 'cancelled'");

            // Get total revenue (sum of booking amounts)
            $revenue = $db->fetch("SELECT COALESCE(SUM(total_amount), 0) as total FROM bookings WHERE status = 'confirmed' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");

            // Get recent activity count
            $activity = $db->fetch("SELECT COUNT(*) as total FROM user_activity WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");

            return $this->jsonResponse([
                'success' => true,
                'stats' => [
                    'total_users' => $users['total'] ?? 0,
                    'total_properties' => $properties['total'] ?? 0,
                    'total_bookings' => $bookings['total'] ?? 0,
                    'total_revenue' => $revenue['total'] ?? 0,
                    'recent_activity' => $activity['total'] ?? 0
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Error retrieving dashboard statistics: ' . $e->getMessage(), 500);
        }
    }
}
