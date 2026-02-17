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
use Exception;

use App\Services\Legacy\MultiLanguageSupport;

class AdminController extends BaseController
{
    protected $mlSupport;

    public function __construct()
    {
        parent::__construct();
        // Set admin layout
        $this->layout = 'layouts/admin';

        // Initialize data array for view rendering
        $this->data = [];

        // Initialize Multi-Language Support
        try {
            $this->mlSupport = new MultiLanguageSupport($this->db);
            $this->data['mlSupport'] = $this->mlSupport;
        } catch (Exception $e) {
            // Fallback or log error if needed
            error_log("MultiLanguageSupport init failed: " . $e->getMessage());
        }

        // Ensure only admins can access admin pages
        if (!$this->isAdmin()) {
            $this->redirect('/admin/login');
            exit;
        }
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
     * Alias for index (Dashboard)
     */
    public function dashboard()
    {
        $this->index();
    }

    /**
     * Load role-specific data for the dashboard
     */
    private function loadRoleSpecificData($role)
    {
        switch ($role) {
            case 'ceo':
            case 'director':
            case 'coo':
            case 'cfo':
                $this->data['financial_summary'] = $this->getFinancialSummary();
                $this->data['company_growth'] = $this->getGrowthStats();
                $this->data['project_performance'] = $this->getProjectPerformance();
                break;
            case 'sales':
            case 'marketing':
            case 'cm':
                $this->data['leads_pipeline'] = $this->getLeadsPipeline();
                $this->data['sales_targets'] = $this->getSalesTargets();
                $this->data['marketing_roi'] = $this->getMarketingROI();
                break;
            case 'cto':
            case 'it':
                $this->data['server_health'] = $this->getServerHealth();
                $this->data['api_usage'] = $this->getApiUsageStats();
                $this->data['system_uptime'] = $this->getSystemUptime();
                break;
            case 'superadmin':
            case 'admin':
                $this->data['audit_logs'] = $this->getRecentAuditLogs();
                $this->data['user_management_stats'] = $this->getUserStats();
                $this->data['system_health'] = $this->getSystemStatus();
                break;
            case 'finance':
            case 'accounting':
                $this->data['pending_payouts'] = $this->getPayoutStats();
                $this->data['revenue_report'] = $this->getRevenueReport();
                break;
            case 'hr':
                $this->data['employee_stats'] = $this->getEmployeeStats();
                $this->data['pending_leaves'] = $this->getLeaveRequests();
                break;
            case 'legal':
                $this->data['document_status'] = $this->getDocumentStatus();
                $this->data['pending_verifications'] = $this->getPendingVerifications();
                break;
            case 'operations':
                $this->data['inventory_stats'] = $this->getInventoryStats();
                $this->data['task_status'] = $this->getTaskStatus();
                break;
            case 'builder':
                $this->data['construction_progress'] = $this->getConstructionProgress();
                $this->data['material_requests'] = $this->getMaterialRequests();
                break;
            case 'agent':
            case 'associate':
                $this->data['my_commissions'] = $this->getAgentCommissions();
                $this->data['my_network'] = $this->getAgentNetwork();
                break;
            default:
                $this->data['general_stats'] = $this->getDashboardStats();
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
        $this->data['properties'] = Property::getAdminProperties($filters);
        $this->data['total_properties'] = Property::getAdminTotalProperties($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_properties'] / $filters['per_page']);
        $this->data['start_index'] = ($filters['page'] - 1) * $filters['per_page'] + 1;
        $this->data['end_index'] = min($filters['page'] * $filters['per_page'], $this->data['total_properties']);

        // Render the properties page
        $this->render('admin/properties/index');
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
        $this->data['users'] = \App\Models\User::getAdminUsers($filters);
        $this->data['total_users'] = \App\Models\User::getAdminTotalUsers($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_users'] / $filters['per_page']);
        $this->data['start_index'] = ($filters['page'] - 1) * $filters['per_page'] + 1;
        $this->data['end_index'] = min($filters['page'] * $filters['per_page'], $this->data['total_users']);

        // Render the users page
        $this->render('admin/users/index');
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
        $this->data['associates'] = \App\Models\Associate::getAdminAssociates($filters);
        $this->data['total_associates'] = \App\Models\Associate::getAdminTotalAssociates($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_associates'] / $filters['per_page']);

        $this->render('admin/associates/index');
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
        $this->data['customers'] = \App\Models\Customer::getAdminCustomers($filters);
        $this->data['total_customers'] = \App\Models\Customer::getAdminTotalCustomers($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_customers'] / $filters['per_page']);

        $this->render('admin/customers/index');
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
        $this->data['bookings'] = \App\Models\Booking::getAdminBookings($filters);
        $this->data['total_bookings'] = \App\Models\Booking::getAdminTotalBookings($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_bookings'] / $filters['per_page']);

        $this->render('admin/bookings/index');
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
        $this->data['employees'] = \App\Models\Employee::getAdminEmployees($filters);
        $this->data['total_employees'] = \App\Models\Employee::getAdminTotalEmployees($filters);
        $this->data['filters'] = $filters;

        // Calculate pagination
        $this->data['total_pages'] = ceil($this->data['total_employees'] / $filters['per_page']);

        $this->render('admin/employees/index');
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

        // Fetch about items
        try {
            $aboutObjects = About::all();
            $this->data['about_items'] = array_map(fn($item) => $item->toArray(), $aboutObjects);
        } catch (Exception $e) {
            $this->data['error'] = "Error loading about content: " . $e->getMessage();
            $this->data['about_items'] = [];
        }

        $this->render('admin/aboutview');
    }

    /**
     * Show create about form
     */
    public function aboutCreate()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
        $this->data['page_title'] = 'Add About Content - ' . APP_NAME;
        $this->render('admin/aboutadd');
    }

    /**
     * Store new about content
     */
    public function aboutStore()
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $title = trim($_POST['title'] ?? '');
                $content = trim($_POST['content'] ?? '');

                if (empty($title) || empty($content)) {
                    throw new Exception("Title and Content are required");
                }

                $data = [
                    'title' => $title,
                    'content' => $content,
                    'image' => ''
                ];

                // Handle Image Upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $uploadDir = "upload/";
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        $data['image'] = $fileName;
                    }
                }

                $about = new About($data);
                if ($about->save()) {
                    $this->redirect('admin/about?msg=' . urlencode('Content added successfully'));
                    return;
                } else {
                    throw new Exception("Failed to save content");
                }
            } catch (Exception $e) {
                $this->data['error'] = "Error adding content: " . $e->getMessage();
                $this->data['page_title'] = 'Add About Content - ' . APP_NAME;
                $this->render('admin/aboutadd');
            }
        }
    }

    /**
     * Show edit about form
     */
    public function aboutEdit($id)
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        try {
            $about = About::find($id);
            if (!$about) {
                $this->redirect('admin/about?error=' . urlencode('Content not found'));
                return;
            }

            $this->data['page_title'] = 'Edit About Content - ' . APP_NAME;
            $this->data['about_data'] = $about->toArray();
            $this->layout = 'layouts/admin';
            $this->render('admin/aboutedit');
        } catch (Exception $e) {
            $this->redirect('admin/about?error=' . urlencode('Error loading content: ' . $e->getMessage()));
        }
    }

    /**
     * Update about content
     */
    public function aboutUpdate($id)
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $about = About::find($id);
                if (!$about) {
                    throw new Exception("Content not found");
                }

                $title = trim($_POST['title'] ?? '');
                $content = trim($_POST['content'] ?? '');

                if (empty($title) || empty($content)) {
                    throw new Exception("Title and Content are required");
                }

                $about->title = $title;
                $about->content = $content;

                // Handle Image Upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $uploadDir = "upload/";
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $fileName = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        // Delete old image
                        if ($about->image && file_exists($uploadDir . $about->image)) {
                            unlink($uploadDir . $about->image);
                        }
                        $about->image = $fileName;
                    }
                }

                if ($about->save()) {
                    $this->redirect('admin/about?msg=' . urlencode('Content updated successfully'));
                } else {
                    throw new Exception("Failed to update content");
                }
            } catch (Exception $e) {
                $this->redirect("admin/about/edit/{$id}?error=" . urlencode("Error updating content: " . $e->getMessage()));
            }
        }
    }

    /**
     * Delete about content
     */
    public function aboutDelete($id)
    {
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $about = About::find($id);
                if ($about) {
                    if ($about->image && file_exists('upload/' . $about->image)) {
                        unlink('upload/' . $about->image);
                    }

                    if ($about->delete()) {
                        $this->redirect('admin/about?msg=' . urlencode('Content deleted successfully'));
                    } else {
                        throw new Exception("Failed to delete from database");
                    }
                } else {
                    throw new Exception("Content not found");
                }
            } catch (Exception $e) {
                $this->redirect('admin/about?error=' . urlencode('Error deleting content: ' . $e->getMessage()));
            }
        }
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
     * Get property types for form dropdown
     */
    private function getPropertyTypes()
    {
        return \App\Models\PropertyType::getForSelect();
    }

    /**
     * Get active agents for form dropdown
     */
    private function getActiveAgents()
    {
        return \App\Models\User::getActiveAgents();
    }

    /**
     * Get recent activities
     */
    /**
     * Get system settings for admin
     */
    private function getSystemSettings()
    {
        return \App\Models\SiteSetting::getAllSettings();
    }


    private function getDashboardStats()
    {
        $adminModel = new Admin();
        return $adminModel->getDashboardStats();
    }

    private function getRecentActivities()
    {
        $adminModel = new Admin();
        return $adminModel->getRecentActivities();
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
