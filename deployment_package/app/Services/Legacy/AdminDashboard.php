<?php

namespace App\Services\Legacy;

/**
 * Admin Dashboard System
 * Complete admin panel with role-based access control
 */

class AdminDashboard
{
    private $db;
    private $logger;
    private $authManager;

    public function __construct($db = null, $logger = null)
    {
        $this->db = $db ?: \App\Core\App::database();
        $this->logger = $logger;
        $this->authManager = new AuthManager($this->db, $logger);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin($userId = null)
    {
        if (!$userId && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }

        if (!$userId) {
            return false;
        }

        $user = $this->authManager->getUserById($userId);
        return $user && ($user['role'] === 'admin');
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        $stats = [];

        // Total users
        $sql = "SELECT COUNT(*) as total_users FROM users";
        $result = $this->db->fetch($sql);
        $stats['total_users'] = $result['total_users'] ?? 0;

        // Active properties
        $sql = "SELECT COUNT(*) as active_properties FROM properties WHERE status = 'available'";
        $result = $this->db->fetch($sql);
        $stats['active_properties'] = $result['active_properties'] ?? 0;

        // Total properties
        $sql = "SELECT COUNT(*) as total_properties FROM properties";
        $result = $this->db->fetch($sql);
        $stats['total_properties'] = $result['total_properties'] ?? 0;

        // Total bookings
        $sql = "SELECT COUNT(*) as total_bookings FROM bookings";
        $result = $this->db->fetch($sql);
        $stats['total_bookings'] = $result['total_bookings'] ?? 0;

        // Recent bookings (last 30 days)
        $sql = "SELECT COUNT(*) as recent_bookings FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->db->fetch($sql);
        $stats['recent_bookings'] = $result['recent_bookings'] ?? 0;

        // Total leads
        $sql = "SELECT COUNT(*) as total_leads FROM leads";
        $result = $this->db->fetch($sql);
        $stats['total_leads'] = $result['total_leads'] ?? 0;

        // New leads (last 7 days)
        $sql = "SELECT COUNT(*) as new_leads FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $result = $this->db->fetch($sql);
        $stats['new_leads'] = $result['new_leads'] ?? 0;

        // Total revenue (from payments)
        $sql = "SELECT SUM(amount) as total_revenue FROM payments WHERE status = 'completed'";
        $result = $this->db->fetch($sql);
        $stats['total_revenue'] = $result['total_revenue'] ?? 0;

        // Monthly revenue
        $sql = "SELECT SUM(amount) as monthly_revenue FROM payments WHERE status = 'completed' AND payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->db->fetch($sql);
        $stats['monthly_revenue'] = $result['monthly_revenue'] ?? 0;

        return $stats;
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 10)
    {
        $activities = [];

        // Recent user registrations
        $sql = "SELECT 'user_registered' as activity_type, name as description,
                       created_at as activity_time, id as reference_id
                FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC LIMIT ?";

        $activities = array_merge($activities, $this->db->fetchAll($sql, [$limit]));

        // Recent property additions
        $sql = "SELECT 'property_added' as activity_type, title as description,
                       created_at as activity_time, id as reference_id
                FROM properties WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC LIMIT ?";

        $activities = array_merge($activities, $this->db->fetchAll($sql, [$limit]));

        // Recent bookings
        $sql = "SELECT 'booking_created' as activity_type,
                       CONCAT('New booking for property ID: ', property_id) as description,
                       created_at as activity_time, id as reference_id
                FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC LIMIT ?";

        $activities = array_merge($activities, $this->db->fetchAll($sql, [$limit]));

        // Sort by activity time
        usort($activities, function ($a, $b) {
            return strtotime($b['activity_time']) - strtotime($a['activity_time']);
        });

        return array_slice($activities, 0, $limit);
    }

    /**
     * Get property analytics
     */
    public function getPropertyAnalytics()
    {
        $analytics = [];

        // Properties by type
        $sql = "SELECT pt.name, COUNT(p.id) as count
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                GROUP BY p.property_type_id, pt.name";
        $analytics['properties_by_type'] = $this->db->fetchAll($sql);

        // Properties by status
        $sql = "SELECT status, COUNT(*) as count FROM properties GROUP BY status";
        $analytics['properties_by_status'] = $this->db->fetchAll($sql);

        // Properties by location (top 10)
        $sql = "SELECT city, COUNT(*) as count FROM properties GROUP BY city ORDER BY count DESC LIMIT 10";
        $analytics['properties_by_location'] = $this->db->fetchAll($sql);

        // Price ranges
        $sql = "SELECT
                CASE
                    WHEN price < 1000000 THEN 'Under 10L'
                    WHEN price < 5000000 THEN '10L-50L'
                    WHEN price < 10000000 THEN '50L-1Cr'
                    ELSE 'Above 1Cr'
                END as price_range,
                COUNT(*) as count
                FROM properties
                GROUP BY price_range";
        $analytics['properties_by_price_range'] = $this->db->fetchAll($sql);

        return $analytics;
    }

    /**
     * Get user management data
     */
    public function getUserManagementData()
    {
        $data = [];

        // Users by role
        $sql = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
        $data['users_by_role'] = $this->db->fetchAll($sql);

        // Users by status
        $sql = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
        $data['users_by_status'] = $this->db->fetchAll($sql);

        // Recent users
        $sql = "SELECT id, name as username, name as full_name, role, status, created_at
                FROM users ORDER BY created_at DESC LIMIT 5";
        $data['recent_users'] = $this->db->fetchAll($sql);

        return $data;
    }

    /**
     * Get lead management data
     */
    public function getLeadManagementData()
    {
        $data = [];

        // Leads by status
        $sql = "SELECT status, COUNT(*) as count FROM leads GROUP BY status";
        $data['leads_by_status'] = $this->db->fetchAll($sql);

        // Leads by source
        $sql = "SELECT source, COUNT(*) as count FROM leads GROUP BY source";
        $data['leads_by_source'] = $this->db->fetchAll($sql);

        // Recent leads
        $sql = "SELECT id, name, email, phone, source, status, created_at
                FROM leads ORDER BY created_at DESC LIMIT 5";
        $data['recent_leads'] = $this->db->fetchAll($sql);

        return $data;
    }

    /**
     * Get booking management data
     */
    public function getBookingManagementData()
    {
        $data = [];

        // Bookings by status
        $sql = "SELECT status, COUNT(*) as count FROM bookings GROUP BY status";
        $data['bookings_by_status'] = $this->db->fetchAll($sql);

        // Bookings by type
        $sql = "SELECT booking_type, COUNT(*) as count FROM bookings GROUP BY booking_type";
        $data['bookings_by_type'] = $this->db->fetchAll($sql);

        // Recent bookings
        $sql = "SELECT b.id, b.booking_type, b.status, b.created_at,
                       p.title as property_title, u.name as customer_name
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN users u ON b.user_id = u.id
                ORDER BY b.created_at DESC LIMIT 5";
        $data['recent_bookings'] = $this->db->fetchAll($sql);

        return $data;
    }

    /**
     * Get system health status
     */
    public function getSystemHealth()
    {
        $health = [
            'status' => 'healthy',
            'checks' => []
        ];

        // Database connection check
        try {
            $this->db->query("SELECT 1");
            $health['checks']['database'] = ['status' => 'ok', 'message' => 'Database connection healthy'];
        } catch (Exception $e) {
            $health['checks']['database'] = ['status' => 'error', 'message' => 'Database connection failed'];
            $health['status'] = 'unhealthy';
        }

        // Total properties check
        $sql = "SELECT COUNT(*) as count FROM properties";
        $totalProperties = $this->db->fetch($sql)['count'] ?? 0;
        $health['checks']['properties'] = [
            'status' => $totalProperties > 0 ? 'ok' : 'warning',
            'message' => "Total properties: $totalProperties"
        ];

        // Total users check
        $sql = "SELECT COUNT(*) as count FROM users";
        $totalUsers = $this->db->fetch($sql)['count'] ?? 0;
        $health['checks']['users'] = [
            'status' => $totalUsers > 0 ? 'ok' : 'warning',
            'message' => "Total users: $totalUsers"
        ];

        // Recent errors check
        $sql = "SELECT COUNT(*) as count FROM error_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $recentErrors = $this->db->fetch($sql)['count'] ?? 0;
        $health['checks']['errors'] = [
            'status' => $recentErrors == 0 ? 'ok' : 'warning',
            'message' => "Recent errors: $recentErrors"
        ];

        // File permissions check
        $uploadPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
        $writable = is_writable($uploadPath);
        $health['checks']['uploads'] = [
            'status' => $writable ? 'ok' : 'error',
            'message' => "Upload directory: " . ($writable ? 'writable' : 'not writable')
        ];

        return $health;
    }

    /**
     * Get admin menu items based on user role
     */
    public function getAdminMenu($userRole)
    {
        $menu = [
            'dashboard' => [
                'title' => 'Dashboard',
                'icon' => 'dashboard',
                'url' => '/admin/dashboard',
                'active' => true
            ],
            'properties' => [
                'title' => 'Properties',
                'icon' => 'home',
                'url' => '/admin/properties',
                'submenu' => [
                    ['title' => 'All Properties', 'url' => '/admin/properties'],
                    ['title' => 'Add Property', 'url' => '/admin/properties/add'],
                    ['title' => 'Property Types', 'url' => '/admin/property-types'],
                    ['title' => 'Featured Properties', 'url' => '/admin/properties/featured']
                ]
            ],
            'users' => [
                'title' => 'Users',
                'icon' => 'users',
                'url' => '/admin/users',
                'submenu' => [
                    ['title' => 'All Users', 'url' => '/admin/users'],
                    ['title' => 'Add User', 'url' => '/admin/users/add'],
                    ['title' => 'User Roles', 'url' => '/admin/roles']
                ]
            ],
            'leads' => [
                'title' => 'Leads',
                'icon' => 'user-plus',
                'url' => '/admin/leads'
            ],
            'bookings' => [
                'title' => 'Bookings',
                'icon' => 'calendar-check',
                'url' => '/admin/bookings'
            ],
            'analytics' => [
                'title' => 'Analytics',
                'icon' => 'chart-bar',
                'url' => '/admin/analytics'
            ]
        ];

        // Add admin-only menu items
        if ($userRole === 'admin') {
            $menu['system'] = [
                'title' => 'System',
                'icon' => 'cogs',
                'url' => '/admin/system',
                'submenu' => [
                    ['title' => 'Settings', 'url' => '/admin/settings'],
                    ['title' => 'Email Templates', 'url' => '/admin/email-templates'],
                    ['title' => 'API Keys', 'url' => '/admin/api-keys'],
                    ['title' => 'System Health', 'url' => '/admin/health'],
                    ['title' => 'Backup', 'url' => '/admin/backup']
                ]
            ];
        }

        return $menu;
    }
}
