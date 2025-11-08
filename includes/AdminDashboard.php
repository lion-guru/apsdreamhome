<?php
/**
 * Admin Dashboard System
 * Complete admin panel with role-based access control
 */

class AdminDashboard {
    private $conn;
    private $logger;
    private $authManager;

    public function __construct($conn, $logger = null) {
        $this->conn = $conn;
        $this->logger = $logger;
        $this->authManager = new AuthManager($conn, $logger);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin($userId = null) {
        if (!$userId && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
        }

        if (!$userId) {
            return false;
        }

        $user = $this->authManager->getUserById($userId);
        return $user && $user['role'] === 'admin';
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [];

        // Total users
        $sql = "SELECT COUNT(*) as total_users FROM users";
        $result = $this->conn->query($sql);
        $stats['total_users'] = $result->fetch_assoc()['total_users'];

        // Active properties
        $sql = "SELECT COUNT(*) as active_properties FROM properties WHERE status = 'available'";
        $result = $this->conn->query($sql);
        $stats['active_properties'] = $result->fetch_assoc()['active_properties'];

        // Total properties
        $sql = "SELECT COUNT(*) as total_properties FROM properties";
        $result = $this->conn->query($sql);
        $stats['total_properties'] = $result->fetch_assoc()['total_properties'];

        // Total bookings
        $sql = "SELECT COUNT(*) as total_bookings FROM bookings";
        $result = $this->conn->query($sql);
        $stats['total_bookings'] = $result->fetch_assoc()['total_bookings'];

        // Recent bookings (last 30 days)
        $sql = "SELECT COUNT(*) as recent_bookings FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->conn->query($sql);
        $stats['recent_bookings'] = $result->fetch_assoc()['recent_bookings'];

        // Total leads
        $sql = "SELECT COUNT(*) as total_leads FROM leads";
        $result = $this->conn->query($sql);
        $stats['total_leads'] = $result->fetch_assoc()['total_leads'];

        // New leads (last 7 days)
        $sql = "SELECT COUNT(*) as new_leads FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $result = $this->conn->query($sql);
        $stats['new_leads'] = $result->fetch_assoc()['new_leads'];

        // Total revenue (from payments)
        $sql = "SELECT SUM(amount) as total_revenue FROM payments WHERE status = 'completed'";
        $result = $this->conn->query($sql);
        $stats['total_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;

        // Monthly revenue
        $sql = "SELECT SUM(amount) as monthly_revenue FROM payments WHERE status = 'completed' AND payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->conn->query($sql);
        $stats['monthly_revenue'] = $result->fetch_assoc()['monthly_revenue'] ?? 0;

        return $stats;
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 10) {
        $activities = [];

        // Recent user registrations
        $sql = "SELECT 'user_registered' as activity_type, full_name as description,
                       created_at as activity_time, id as reference_id
                FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();

        // Recent property additions
        $sql = "SELECT 'property_added' as activity_type, title as description,
                       created_at as activity_time, id as reference_id
                FROM properties WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();

        // Recent bookings
        $sql = "SELECT 'booking_created' as activity_type,
                       CONCAT('New booking for property ID: ', property_id) as description,
                       created_at as activity_time, id as reference_id
                FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        $stmt->close();

        // Sort by activity time
        usort($activities, function($a, $b) {
            return strtotime($b['activity_time']) - strtotime($a['activity_time']);
        });

        return array_slice($activities, 0, $limit);
    }

    /**
     * Get property analytics
     */
    public function getPropertyAnalytics() {
        $analytics = [];

        // Properties by type
        $sql = "SELECT pt.name, COUNT(p.id) as count
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                GROUP BY p.property_type_id, pt.name";
        $result = $this->conn->query($sql);

        $analytics['properties_by_type'] = [];
        while ($row = $result->fetch_assoc()) {
            $analytics['properties_by_type'][] = $row;
        }

        // Properties by status
        $sql = "SELECT status, COUNT(*) as count FROM properties GROUP BY status";
        $result = $this->conn->query($sql);

        $analytics['properties_by_status'] = [];
        while ($row = $result->fetch_assoc()) {
            $analytics['properties_by_status'][] = $row;
        }

        // Properties by location (top 10)
        $sql = "SELECT city, COUNT(*) as count FROM properties GROUP BY city ORDER BY count DESC LIMIT 10";
        $result = $this->conn->query($sql);

        $analytics['properties_by_location'] = [];
        while ($row = $result->fetch_assoc()) {
            $analytics['properties_by_location'][] = $row;
        }

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
        $result = $this->conn->query($sql);

        $analytics['properties_by_price_range'] = [];
        while ($row = $result->fetch_assoc()) {
            $analytics['properties_by_price_range'][] = $row;
        }

        return $analytics;
    }

    /**
     * Get user management data
     */
    public function getUserManagementData() {
        $data = [];

        // Users by role
        $sql = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
        $result = $this->conn->query($sql);

        $data['users_by_role'] = [];
        while ($row = $result->fetch_assoc()) {
            $data['users_by_role'][] = $row;
        }

        // Users by status
        $sql = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
        $result = $this->conn->query($sql);

        $data['users_by_status'] = [];
        while ($row = $result->fetch_assoc()) {
            $data['users_by_status'][] = $row;
        }

        // Recent users
        $sql = "SELECT id, username, full_name, role, status, created_at
                FROM users ORDER BY created_at DESC LIMIT 5";
        $result = $this->conn->query($sql);

        $data['recent_users'] = [];
        while ($row = $result->fetch_assoc()) {
            $data['recent_users'][] = $row;
        }

        return $data;
    }

    /**
     * Get lead management data
     */
    public function getLeadManagementData() {
        $data = [];

        // Leads by status
        $sql = "SELECT status, COUNT(*) as count FROM leads GROUP BY status";
        $result = $this->conn->query($sql);

        $data['leads_by_status'] = [];
        while ($row = $result->fetch_assoc()) {
            $data['leads_by_status'][] = $row;
        }

        // Leads by source
        $sql = "SELECT source, COUNT(*) as count FROM leads GROUP BY source";
        $result = $this->conn->query($sql);

        $data['leads_by_source'] = [];
        while ($row = $result->fetch_assoc()) {
            $data['leads_by_source'][] = $row;
        }

        // Recent leads
        $sql = "SELECT id, name, email, phone, source, status, created_at
                FROM leads ORDER BY created_at DESC LIMIT 5";
        $result = $this->conn->query($sql);

        $data['recent_leads'] = [];
        while ($row = $result->fetch_assoc()) {
            $data['recent_leads'][] = $row;
        }

        return $data;
    }

    /**
     * Get booking management data
     */
    public function getBookingManagementData() {
        $data = [];

        // Bookings by status
        $sql = "SELECT status, COUNT(*) as count FROM bookings GROUP BY status";
        $result = $this->conn->query($sql);

        $data['bookings_by_status'] = [];
        while ($row = $result->fetch_assoc()) {
            $data['bookings_by_status'][] = $row;
        }

        // Bookings by type
        $sql = "SELECT booking_type, COUNT(*) as count FROM bookings GROUP BY booking_type";
        $result = $this->conn->query($sql);

        $data['bookings_by_type'] = [];
        while ($row = $result->fetch_assoc()) {
            $data['bookings_by_type'][] = $row;
        }

        // Recent bookings
        $sql = "SELECT b.id, b.booking_type, b.status, b.created_at,
                       p.title as property_title, u.full_name as customer_name
                FROM bookings b
                LEFT JOIN properties p ON b.property_id = p.id
                LEFT JOIN users u ON b.user_id = u.id
                ORDER BY b.created_at DESC LIMIT 5";
        $result = $this->conn->query($sql);

        $data['recent_bookings'] = [];
        while ($row = $result->fetch_assoc()) {
            $data['recent_bookings'][] = $row;
        }

        return $data;
    }

    /**
     * Get system health status
     */
    public function getSystemHealth() {
        $health = [
            'status' => 'healthy',
            'checks' => []
        ];

        // Database connection check
        try {
            $this->conn->query("SELECT 1");
            $health['checks']['database'] = ['status' => 'ok', 'message' => 'Database connection healthy'];
        } catch (Exception $e) {
            $health['checks']['database'] = ['status' => 'error', 'message' => 'Database connection failed'];
            $health['status'] = 'unhealthy';
        }

        // Total properties check
        $sql = "SELECT COUNT(*) as count FROM properties";
        $result = $this->conn->query($sql);
        $totalProperties = $result->fetch_assoc()['count'];
        $health['checks']['properties'] = [
            'status' => $totalProperties > 0 ? 'ok' : 'warning',
            'message' => "Total properties: $totalProperties"
        ];

        // Total users check
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = $this->conn->query($sql);
        $totalUsers = $result->fetch_assoc()['count'];
        $health['checks']['users'] = [
            'status' => $totalUsers > 0 ? 'ok' : 'warning',
            'message' => "Total users: $totalUsers"
        ];

        // Recent errors check
        $sql = "SELECT COUNT(*) as count FROM error_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $result = $this->conn->query($sql);
        $recentErrors = $result->fetch_assoc()['count'];
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
    public function getAdminMenu($userRole) {
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
?>
