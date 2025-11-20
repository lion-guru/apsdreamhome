<?php
/**
 * Admin Controller
 * Handles admin dashboard, property management, user management, and settings
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Exception;

class AdminController extends BaseController {
    public function __construct() {
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Display admin dashboard
     */
    public function index() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        // Set page data
        $this->data['page_title'] = 'Admin Dashboard - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Dashboard', 'url' => $this->getBaseUrl() . 'admin']
        ];

        // Get dashboard statistics
        $this->data['stats'] = $this->getDashboardStats();

        // Get recent activities
        $this->data['recent_activities'] = $this->getRecentActivities();

        // Get system status
        $this->data['system_status'] = $this->getSystemStatus();

        // Render the dashboard
        $this->render('admin/dashboard');
    }

    /**
     * Display properties management page
     */
    public function properties() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        // Set page data
        $this->data['page_title'] = 'Properties Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Properties', 'url' => $this->getBaseUrl() . 'admin/properties']
        ];

        // Get filter parameters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'type' => $_GET['type'] ?? '',
            'featured' => $_GET['featured'] ?? '',
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 10),
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Get properties data
        $this->data['properties'] = $this->getAdminProperties($filters);
        $this->data['total_properties'] = $this->getAdminTotalProperties($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_properties'] / $filters['per_page']);
        $this->data['start_index'] = ($filters['page'] - 1) * $filters['per_page'] + 1;
        $this->data['end_index'] = min($filters['page'] * $filters['per_page'], $this->data['total_properties']);

        // Render the properties page
        $this->render('admin/properties');
    }

    /**
     * Display users management page
     */
    public function users() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        // Set page data
        $this->data['page_title'] = 'Users Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Users', 'url' => $this->getBaseUrl() . 'admin/users']
        ];

        // Get filter parameters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? 'active',
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 10),
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Get users data
        $this->data['users'] = $this->getAdminUsers($filters);
        $this->data['total_users'] = $this->getAdminTotalUsers($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_users'] / $filters['per_page']);
        $this->data['start_index'] = ($filters['page'] - 1) * $filters['per_page'] + 1;
        $this->data['end_index'] = min($filters['page'] * $filters['per_page'], $this->data['total_users']);

        // Render the users page
        $this->render('admin/users');
    }

    /**
     * Display settings page
     */
    public function settings() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        // Set page data
        $this->data['page_title'] = 'Settings - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Settings', 'url' => $this->getBaseUrl() . 'admin/settings']
        ];

        // Get current settings
        $this->data['settings'] = $this->getSystemSettings();

        // Check for success/error messages
        $this->data['success'] = $_GET['success'] ?? '';
        $this->data['error'] = $_GET['error'] ?? '';

        // Render the settings page
        $this->render('admin/settings');
    }

    /**
     * Display create property form
     */
    public function createProperty() {
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        // Set page data
        $this->data['page_title'] = 'Create Property - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Properties', 'url' => $this->getBaseUrl() . 'admin/properties'],
            ['title' => 'Create', 'url' => $this->getBaseUrl() . 'admin/properties/create']
        ];

        // Get property types and agents for form
        $this->data['property_types'] = $this->getPropertyTypes();
        $this->data['agents'] = $this->getActiveAgents();

        // Render the create property form
        $this->render('admin/create_property');
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin() {
        // For now, we'll use a simple check - in production this should be more sophisticated
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats() {
        try {
            global $pdo;
            if (!$pdo) {
                return $this->getDefaultStats();
            }

            $stats = [];

            // Total properties
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM properties WHERE status = 'available'");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['total_properties'] = (int)($result['total'] ?? 0);

            // Featured properties
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM properties WHERE status = 'available' AND featured = 1");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['featured_properties'] = (int)($result['total'] ?? 0);

            // Total users
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['total_users'] = (int)($result['total'] ?? 0);

            // Total agents
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['total_agents'] = (int)($result['total'] ?? 0);

            // Recent inquiries (if table exists)
            $stats['recent_inquiries'] = 0;

            // System health
            $stats['system_health'] = 'Good';

            return $stats;

        } catch (Exception $e) {
            error_log('Dashboard stats query error: ' . $e->getMessage());
            return $this->getDefaultStats();
        }
    }

    /**
     * Get default stats when database is unavailable
     */
    private function getDefaultStats() {
        return [
            'total_properties' => 0,
            'featured_properties' => 0,
            'total_users' => 0,
            'total_agents' => 0,
            'recent_inquiries' => 0,
            'system_health' => 'Maintenance'
        ];
    }

    /**
     * Get recent activities for dashboard
     */
    private function getRecentActivities() {
        return [
            ['type' => 'property', 'action' => 'created', 'title' => 'Luxury Apartment', 'time' => '2 hours ago'],
            ['type' => 'user', 'action' => 'registered', 'title' => 'New User Registration', 'time' => '4 hours ago'],
            ['type' => 'property', 'action' => 'updated', 'title' => 'Villa in City Center', 'time' => '6 hours ago'],
        ];
    }

    /**
     * Get system status
     */
    private function getSystemStatus() {
        return [
            'database' => 'Connected',
            'cache' => 'Active',
            'storage' => '85% Used',
            'last_backup' => '2 days ago'
        ];
    }

    /**
     * Get properties for admin with filters and pagination
     */
    private function getAdminProperties($filters) {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $where_conditions = [];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.city LIKE ?)";
                $search_term = '%' . $filters['search'] . '%';
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "p.status = ?";
                $params[] = $filters['status'];
            }

            // Featured filter
            if ($filters['featured'] !== '') {
                $where_conditions[] = "p.featured = ?";
                $params[] = (int)$filters['featured'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            // Build ORDER BY clause
            $allowed_sorts = ['id', 'title', 'price', 'created_at', 'status'];
            $sort = in_array($filters['sort'], $allowed_sorts) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $order_clause = "ORDER BY p.{$sort} {$order}";

            $sql = "
                SELECT
                    p.id,
                    p.title,
                    p.price,
                    p.status,
                    p.featured,
                    p.city,
                    p.created_at,
                    pt.name as property_type
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                {$where_clause}
                {$order_clause}
                LIMIT {$filters['per_page']} OFFSET " . (($filters['page'] - 1) * $filters['per_page']);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Admin properties query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total properties count for pagination
     */
    private function getAdminTotalProperties($filters) {
        try {
            global $pdo;
            if (!$pdo) {
                return 0;
            }

            $where_conditions = [];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.city LIKE ?)";
                $search_term = '%' . $filters['search'] . '%';
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "p.status = ?";
                $params[] = $filters['status'];
            }

            // Featured filter
            if ($filters['featured'] !== '') {
                $where_conditions[] = "p.featured = ?";
                $params[] = (int)$filters['featured'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            $sql = "SELECT COUNT(*) as total FROM properties p {$where_clause}";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['total'] ?? 0);

        } catch (Exception $e) {
            error_log('Admin total properties query error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get users for admin with filters and pagination
     */
    private function getAdminUsers($filters) {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $where_conditions = [];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $search_term = '%' . $filters['search'] . '%';
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "u.status = ?";
                $params[] = $filters['status'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            // Build ORDER BY clause
            $allowed_sorts = ['id', 'name', 'email', 'created_at', 'status'];
            $sort = in_array($filters['sort'], $allowed_sorts) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $order_clause = "ORDER BY u.{$sort} {$order}";

            $sql = "
                SELECT
                    u.id,
                    u.name,
                    u.email,
                    u.phone,
                    u.status as role,
                    u.status,
                    u.created_at,
                    (SELECT COUNT(*) FROM properties p WHERE p.created_by = u.id) as properties_count
                FROM users u
                {$where_clause}
                {$order_clause}
                LIMIT {$filters['per_page']} OFFSET " . (($filters['page'] - 1) * $filters['per_page']);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Admin users query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total users count for pagination
     */
    private function getAdminTotalUsers($filters) {
        try {
            global $pdo;
            if (!$pdo) {
                return 0;
            }

            $where_conditions = [];
            $params = [];

            // Search filter
            if (!empty($filters['search'])) {
                $where_conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
                $search_term = '%' . $filters['search'] . '%';
                $params[] = $search_term;
                $params[] = $search_term;
                $params[] = $search_term;
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "u.status = ?";
                $params[] = $filters['status'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            $sql = "SELECT COUNT(*) as total FROM users u {$where_clause}";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['total'] ?? 0);

        } catch (Exception $e) {
            error_log('Admin total users query error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get system settings for admin
     */
    private function getSystemSettings() {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->query("SELECT setting_name, setting_value FROM site_settings ORDER BY setting_name");
            $settings = [];
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $settings[$row['setting_name']] = $row;
            }
            return $settings;

        } catch (Exception $e) {
            error_log('System settings query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property types for form dropdown
     */
    private function getPropertyTypes() {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->query("SELECT id, name FROM property_types ORDER BY name");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Property types query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active agents for form dropdown
     */
    private function getActiveAgents() {
        try {
            global $pdo;
            if (!$pdo) {
                return [];
            }

            $stmt = $pdo->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log('Active agents query error: ' . $e->getMessage());
            return [];
        }
    }

}

?>
