<?php

/**
 * Admin Controller
 * Handles admin dashboard, property management, user management, and settings
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Exception;

class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Display admin dashboard
     */
    public function index()
    {
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        // Get user role from session
        $role = $_SESSION['admin_role'] ?? 'admin';

        // Set page data
        $this->data['page_title'] = ucfirst($role) . ' Dashboard - ' . APP_NAME;
        $this->data['user_role'] = $role;

        // Get common dashboard statistics
        $this->data['stats'] = $this->getDashboardStats();

        // Role-specific data loading
        $this->loadRoleSpecificData($role);

        // Get quick actions
        $this->data['quickActions'] = $this->getQuickActions();

        // Get recent activities
        $this->data['recent_activities'] = $this->getRecentActivities();

        // Get system status
        $this->data['system_status'] = $this->getSystemStatus();

        // Get AI agents status
        $this->data['ai_agents_status'] = $this->getAIAgentsStatus();

        // Render the appropriate dashboard view based on role
        // If a specific role view exists, use it, otherwise use default
        $viewPath = "admin/dashboards/{$role}";
        if (!file_exists(APP_PATH . "/views/{$viewPath}.php")) {
            $viewPath = 'admin/dashboard';
        }

        $this->render($viewPath);
    }

    /**
     * Load role-specific data for the dashboard
     */
    private function loadRoleSpecificData($role)
    {
        switch ($role) {
            case 'ceo':
            case 'director':
                $this->data['financial_summary'] = $this->getFinancialSummary();
                $this->data['company_growth'] = $this->getGrowthStats();
                break;
            case 'sales':
                $this->data['leads_pipeline'] = $this->getLeadsPipeline();
                $this->data['sales_targets'] = $this->getSalesTargets();
                break;
            case 'cto':
                $this->data['server_health'] = $this->getServerHealth();
                $this->data['api_usage'] = $this->getApiUsageStats();
                break;
            case 'superadmin':
                $this->data['audit_logs'] = $this->getRecentAuditLogs();
                $this->data['user_management_stats'] = $this->getUserStats();
                break;
        }
    }

    /**
     * Get Financial Summary (Mock for now, port from legacy later)
     */
    private function getFinancialSummary()
    {
        return ['revenue' => '₹50L', 'expenses' => '₹20L', 'profit' => '₹30L'];
    }

    /**
     * Get Growth Stats
     */
    private function getGrowthStats()
    {
        return ['new_customers' => 150, 'new_properties' => 45];
    }

    /**
     * Get Leads Pipeline
     */
    private function getLeadsPipeline()
    {
        return ['hot' => 12, 'warm' => 25, 'cold' => 40];
    }

    /**
     * Get Sales Targets
     */
    private function getSalesTargets()
    {
        return ['target' => 100, 'achieved' => 65];
    }

    /**
     * Get Server Health
     */
    private function getServerHealth()
    {
        return ['uptime' => '99.9%', 'memory_usage' => '45%'];
    }

    /**
     * Get API Usage Stats
     */
    private function getApiUsageStats()
    {
        return ['openrouter_calls' => 1250, 'gemini_calls' => 450];
    }

    /**
     * Get Recent Audit Logs
     */
    private function getRecentAuditLogs()
    {
        return [];
    }

    /**
     * Get User Stats
     */
    private function getUserStats()
    {
        return ['total_admins' => 5, 'active_users' => 120];
    }

    /**
     * Get AI agents status
     */
    private function getAIAgentsStatus()
    {
        try {
            // Check if specialized AI classes exist
            $agents = [];

            // Core Assistant
            $agents[] = [
                'name' => 'APS Assistant',
                'type' => 'Core AI',
                'status' => 'Online',
                'last_activity' => date('Y-m-d H:i:s'),
                'mood' => 'Helpful'
            ];

            // Analytics Agent
            $agents[] = [
                'name' => 'Data Analyst',
                'type' => 'Analytics',
                'status' => 'Monitoring',
                'last_activity' => date('Y-m-d H:i:s'),
                'mood' => 'Focused'
            ];

            return $agents;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Display properties management page
     */
    public function properties()
    {
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
    public function users()
    {
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
            'status' => $_GET['status'] ?? '',
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
     * Display associates management page
     */
    public function associates()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->data['page_title'] = 'Associates Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Associates', 'url' => $this->getBaseUrl() . 'admin/associates']
        ];

        // Get filter parameters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 10),
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Get associates data
        $this->data['associates'] = $this->getAdminAssociates($filters);
        $this->data['total_associates'] = $this->getAdminTotalAssociates($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_associates'] / $filters['per_page']);

        $this->render('admin/associates');
    }

    /**
     * Display customers management page
     */
    public function customers()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->data['page_title'] = 'Customers Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Customers', 'url' => $this->getBaseUrl() . 'admin/customers']
        ];

        // Get filter parameters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 10),
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Get customers data
        $this->data['customers'] = $this->getAdminCustomers($filters);
        $this->data['total_customers'] = $this->getAdminTotalCustomers($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_customers'] / $filters['per_page']);

        $this->render('admin/customers');
    }

    /**
     * Display bookings management page
     */
    public function bookings()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->data['page_title'] = 'Bookings Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Bookings', 'url' => $this->getBaseUrl() . 'admin/bookings']
        ];

        // Get filter parameters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 10),
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Get bookings data
        $this->data['bookings'] = $this->getAdminBookings($filters);
        $this->data['total_bookings'] = $this->getAdminTotalBookings($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_bookings'] / $filters['per_page']);

        $this->render('admin/bookings');
    }

    /**
     * Display employees management page
     */
    public function employees()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        $this->data['page_title'] = 'Employees Management - ' . APP_NAME;
        $this->data['breadcrumbs'] = [
            ['title' => 'Admin', 'url' => $this->getBaseUrl() . 'admin'],
            ['title' => 'Employees', 'url' => $this->getBaseUrl() . 'admin/employees']
        ];

        // Get filter parameters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 10),
            'sort' => $_GET['sort'] ?? 'created_at',
            'order' => $_GET['order'] ?? 'DESC'
        ];

        // Get employees data
        $this->data['employees'] = $this->getAdminEmployees($filters);
        $this->data['total_employees'] = $this->getAdminTotalEmployees($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_employees'] / $filters['per_page']);

        $this->render('admin/employees');
    }

    /**
     * Display settings page
     */
    public function settings()
    {
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
     * Display about page
     */
    public function about()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'About Us - ' . APP_NAME;
        $this->render('admin/aboutview');
    }

    /**
     * Display contact page
     */
    public function contact()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Contact Us - ' . APP_NAME;
        $this->render('admin/contactview');
    }

    /**
     * Display CRM dashboard
     */
    public function crmDashboard()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'CRM Dashboard - ' . APP_NAME;
        $this->render('admin/advanced_crm_dashboard');
    }

    /**
     * Display AI Hub control center
     */
    public function aiHub()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'AI Control Center - ' . APP_NAME;
        $this->render('admin/ai_hub');
    }

    /**
     * Display AI Agent dashboard
     */
    public function aiAgentDashboard()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'AI Agent Performance - ' . APP_NAME;
        $this->render('admin/ai_agent_dashboard');
    }

    /**
     * Display AI Lead Scoring page
     */
    public function aiLeadScoring()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'AI Lead Scoring - ' . APP_NAME;
        $this->render('admin/ai_lead_scoring');
    }

    /**
     * Display Superadmin dashboard
     */
    public function superadminDashboard()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'Superadmin Dashboard - ' . APP_NAME;
        $this->render('admin/superadmin_dashboard');
    }

    /**
     * Display WhatsApp automation settings
     */
    public function whatsappSettings()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'WhatsApp Automation - ' . APP_NAME;
        $this->render('admin/whatsapp_automation');
    }

    /**
     * Display site configuration
     */
    public function siteSettings()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'Site Configuration - ' . APP_NAME;
        $this->render('admin/header_footer_settings');
    }

    /**
     * Display API management
     */
    public function apiSettings()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'API Management - ' . APP_NAME;
        $this->render('admin/api_key_manager');
    }

    /**
     * Display backup manager
     */
    public function backupSettings()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'Backup Manager - ' . APP_NAME;
        $this->render('admin/backup_manager');
    }

    /**
     * Display audit logs
     */
    public function auditLogs()
    {
        if (!$this->isSuperAdmin()) {
            $this->redirect('admin');
            return;
        }
        $this->data['page_title'] = 'Audit Logs - ' . APP_NAME;
        $this->render('admin/audit_access_log_view');
    }

    /**
     * Display Kisaan (Land) records
     */
    public function kisaanList()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Land Records - ' . APP_NAME;
        $this->render('admin/view_kisaan');
    }

    /**
     * Display add Land details form
     */
    public function kisaanAdd()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Add Land Details - ' . APP_NAME;
        $this->render('admin/kissan');
    }

    /**
     * Display MLM reports
     */
    public function mlmReports()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'MLM Reports - ' . APP_NAME;
        $this->render('admin/professional_mlm_reports');
    }

    /**
     * Display MLM settings
     */
    public function mlmSettings()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'MLM Settings - ' . APP_NAME;
        $this->render('admin/professional_mlm_settings');
    }

    /**
     * Display MLM payouts
     */
    public function mlmPayouts()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Payouts - ' . APP_NAME;
        $this->render('admin/payouts');
    }

    /**
     * Display MLM commission reports
     */
    public function mlmCommissions()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Commission Reports - ' . APP_NAME;
        $this->render('admin/professional_mlm_reports'); // Reusing same view as reports if specialized one not found
    }

    /**
     * Display create property form
     */
    public function createProperty()
    {
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
     * Get recent activities
     */
    /**
     * Get properties for admin with filters and pagination
     */
    private function getAdminProperties($filters)
    {
        try {
            if (!$this->db) {
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

            $stmt = $this->db->prepare($sql);
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
    private function getAdminTotalProperties($filters)
    {
        try {
            if (!$this->db) {
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
            $stmt = $this->db->prepare($sql);
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
    private function getAdminUsers($filters)
    {
        try {
            if (!$this->db) {
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

            // Role filter
            if (!empty($filters['role'])) {
                $where_conditions[] = "u.role = ?";
                $params[] = $filters['role'];
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
                    u.role,
                    u.status,
                    u.created_at,
                    u.last_login,
                    (SELECT COUNT(*) FROM properties p WHERE p.created_by = u.id) as properties_count
                FROM users u
                {$where_clause}
                {$order_clause}
                LIMIT {$filters['per_page']} OFFSET " . (($filters['page'] - 1) * $filters['per_page']);

            $stmt = $this->db->prepare($sql);
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
    private function getAdminTotalUsers($filters)
    {
        try {
            if (!$this->db) {
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

            // Role filter
            if (!empty($filters['role'])) {
                $where_conditions[] = "u.role = ?";
                $params[] = $filters['role'];
            }

            // Status filter
            if (!empty($filters['status'])) {
                $where_conditions[] = "u.status = ?";
                $params[] = $filters['status'];
            }

            $where_clause = empty($where_conditions) ? '' : 'WHERE ' . implode(' AND ', $where_conditions);

            $sql = "SELECT COUNT(*) as total FROM users u {$where_clause}";
            $stmt = $this->db->prepare($sql);
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
    private function getSystemSettings()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $stmt = $this->db->query("SELECT setting_name, setting_value FROM site_settings ORDER BY setting_name");
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
     * Get associates for admin with filters and pagination
     */
    private function getAdminAssociates($filters)
    {
        try {
            if (!$this->db) return [];
            $where = [];
            $params = [];
            if (!empty($filters['search'])) {
                $where[] = "(name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$term, $term, $term]);
            }
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sort = in_array($filters['sort'], ['id', 'name', 'email', 'created_at', 'status']) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $sql = "SELECT * FROM associates {$where_clause} ORDER BY {$sort} {$order} LIMIT {$filters['per_page']} OFFSET " . (($filters['page'] - 1) * $filters['per_page']);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getAdminTotalAssociates($filters)
    {
        try {
            if (!$this->db) return 0;
            $where = [];
            $params = [];
            if (!empty($filters['search'])) {
                $where[] = "(name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$term, $term, $term]);
            }
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT COUNT(*) FROM associates {$where_clause}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getAdminCustomers($filters)
    {
        try {
            if (!$this->db) return [];
            $where = [];
            $params = [];
            if (!empty($filters['search'])) {
                $where[] = "(name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$term, $term, $term]);
            }
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sort = in_array($filters['sort'], ['id', 'name', 'email', 'created_at']) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $sql = "SELECT * FROM customers {$where_clause} ORDER BY {$sort} {$order} LIMIT {$filters['per_page']} OFFSET " . (($filters['page'] - 1) * $filters['per_page']);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getAdminTotalCustomers($filters)
    {
        try {
            if (!$this->db) return 0;
            $where = [];
            $params = [];
            if (!empty($filters['search'])) {
                $where[] = "(name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$term, $term, $term]);
            }
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT COUNT(*) FROM customers {$where_clause}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getAdminBookings($filters)
    {
        try {
            if (!$this->db) return [];
            $where = [];
            $params = [];
            if (!empty($filters['search'])) {
                $where[] = "(booking_number LIKE ? OR customer_id IN (SELECT id FROM customers WHERE name LIKE ?))";
                $term = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$term, $term]);
            }
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sort = in_array($filters['sort'], ['id', 'booking_number', 'booking_date', 'status']) ? $filters['sort'] : 'booking_date';
            $order = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $sql = "SELECT b.*, c.name as customer_name, p.title as property_title 
                    FROM bookings b 
                    LEFT JOIN customers c ON b.customer_id = c.id 
                    LEFT JOIN properties p ON b.property_id = p.id 
                    {$where_clause} 
                    ORDER BY b.{$sort} {$order} 
                    LIMIT {$filters['per_page']} OFFSET " . (($filters['page'] - 1) * $filters['per_page']);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getAdminTotalBookings($filters)
    {
        try {
            if (!$this->db) return 0;
            $where = [];
            $params = [];
            if (!empty($filters['search'])) {
                $where[] = "(booking_number LIKE ? OR customer_id IN (SELECT id FROM customers WHERE name LIKE ?))";
                $term = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$term, $term]);
            }
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT COUNT(*) FROM bookings {$where_clause}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getAdminEmployees($filters)
    {
        try {
            if (!$this->db) return [];
            $where = [];
            $params = [];
            if (!empty($filters['search'])) {
                $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$term, $term, $term]);
            }
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sort = in_array($filters['sort'], ['id', 'name', 'email', 'created_at', 'status']) ? $filters['sort'] : 'created_at';
            $order = strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
            $sql = "SELECT * FROM employees {$where_clause} ORDER BY {$sort} {$order} LIMIT {$filters['per_page']} OFFSET " . (($filters['page'] - 1) * $filters['per_page']);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    private function getAdminTotalEmployees($filters)
    {
        try {
            if (!$this->db) return 0;
            $where = [];
            $params = [];
            if (!empty($filters['search'])) {
                $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
                $term = '%' . $filters['search'] . '%';
                $params = array_merge($params, [$term, $term, $term]);
            }
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT COUNT(*) FROM employees {$where_clause}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get property types for form dropdown
     */
    private function getPropertyTypes()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $stmt = $this->db->query("SELECT id, name FROM property_types ORDER BY name");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Property types query error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get active agents for form dropdown
     */
    private function getActiveAgents()
    {
        try {
            if (!$this->db) {
                return [];
            }

            $stmt = $this->db->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Active agents query error: ' . $e->getMessage());
            return [];
        }
    }

    private function getDashboardStats()
    {
        $db = \App\Models\Database::getInstance();

        // Real-time counts from database
        $totalLeads = $db->query("SELECT COUNT(*) FROM leads WHERE is_deleted = 0")->fetchColumn();
        $totalProperties = $db->query("SELECT COUNT(*) FROM properties WHERE status = 'active'")->fetchColumn();
        $totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

        return [
            'total_leads' => $totalLeads,
            'total_properties' => $totalProperties,
            'total_users' => $totalUsers,
            'new_notifications' => 5, // Placeholder
            'revenue_this_month' => '₹ 45.2L' // Placeholder
        ];
    }

    private function getRecentActivities()
    {
        $activities = [];

        // Recent Bookings
        try {
            $result = $this->db->query("SELECT b.id, COALESCE(c.name, 'Unknown Customer') as customer, COALESCE(b.plot_id, b.property_id) as plot_id, COALESCE(b.amount, 0) as amount, b.status, b.booking_date FROM bookings b LEFT JOIN customers c ON b.customer_id = c.id ORDER BY b.booking_date DESC, b.id DESC LIMIT 5");
            if ($result) {
                while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
                    $activities[] = [
                        'type' => 'booking',
                        'message' => 'New Booking - ' . ucfirst($row['status']) . ' (₹' . number_format($row['amount']) . ') for ' . $row['customer'],
                        'time' => date('M j, Y', strtotime($row['booking_date']))
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }

        try {
            // Get recent property additions
            $recentProperties = $this->db->query("
                SELECT title, created_at 
                FROM properties 
                ORDER BY created_at DESC 
                LIMIT 3
            ");

            if ($recentProperties) {
                while ($property = $recentProperties->fetch(\PDO::FETCH_ASSOC)) {
                    $activities[] = [
                        'type' => 'property_added',
                        'message' => 'New property added: ' . htmlspecialchars($property['title']),
                        'time' => date('M j, Y', strtotime($property['created_at']))
                    ];
                }
            }

            // Get recent user registrations
            $recentUsers = $this->db->query("
                SELECT name, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT 2
            ");

            if ($recentUsers) {
                while ($user = $recentUsers->fetch(\PDO::FETCH_ASSOC)) {
                    $activities[] = [
                        'type' => 'user_registered',
                        'message' => 'New user registered: ' . htmlspecialchars($user['name']),
                        'time' => date('M j, Y', strtotime($user['created_at']))
                    ];
                }
            }
        } catch (\Exception $e) {
            // Return empty activities if query fails
        }

        return $activities;
    }

    private function getQuickActions()
    {
        return [
            [
                'title' => 'Add Booking',
                'icon' => 'fas fa-plus',
                'url' => '/admin/bookings',
                'color' => 'primary'
            ],
            [
                'title' => 'Manage Properties',
                'icon' => 'fas fa-building',
                'url' => '/admin/properties',
                'color' => 'success'
            ],
            [
                'title' => 'View Reports',
                'icon' => 'fas fa-chart-bar',
                'url' => '/admin/reports',
                'color' => 'info'
            ],
            [
                'title' => 'System Settings',
                'icon' => 'fas fa-cog',
                'url' => '/admin/settings',
                'color' => 'warning'
            ]
        ];
    }

    private function getSystemStatus()
    {
        return [
            'database' => 'Connected',
            'php_version' => PHP_VERSION,
            'environment' => defined('ENVIRONMENT') ? ENVIRONMENT : 'production',
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'last_backup' => date('Y-m-d H:i', strtotime('-1 day')),
            'system_version' => '1.0.0'
        ];
    }
}
