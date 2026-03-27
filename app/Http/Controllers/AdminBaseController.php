<?php
/**
 * APS Dream Home - Enterprise Admin Base Controller
 * Handles role-based access control and dashboard rendering
 */

namespace App\Http\Controllers;

use App\Http\Middleware\RBACManager;
use App\Core\Database\Database;

class AdminBaseController extends BaseController
{
    protected $db;
    protected $currentUser;
    protected $currentRole;
    protected $dashboardType;
    protected $permissions = [];

    public function __construct()
    {
        parent::__construct();
        
        // Initialize database connection
        $this->db = Database::getInstance();
        
        // Get current user from session
        $this->currentUser = $this->getCurrentUser();
        $this->currentRole = $this->getCurrentUserRole();
        
        // Get dashboard type based on role
        $this->dashboardType = RBACManager::getDashboardType($this->currentRole);
        
        // Get user permissions
        $this->permissions = RBACManager::getRolePermissions($this->currentRole);
        
        // Check authentication for all admin routes
        $this->checkAdminAuth();
    }

    /**
     * Check if user is authenticated as admin
     */
    protected function checkAdminAuth(): bool
    {
        if (!$this->currentUser) {
            if ($this->isApiRequest()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                exit;
            }
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        return true;
    }

    /**
     * Check if request is API
     */
    protected function isApiRequest(): bool
    {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false;
    }

    /**
     * Get current logged in user
     */
    protected function getCurrentUser(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return null;
        }
        
        try {
            // Try users table first
            $user = $this->db->fetch(
                "SELECT * FROM users WHERE id = ? AND status = 'active'",
                [$userId]
            );
            
            if (!$user) {
                // Try admin_users table
                $user = $this->db->fetch(
                    "SELECT * FROM admin_users WHERE id = ? AND status = 'active'",
                    [$userId]
                );
            }
            
            if (!$user) {
                // Try employees table
                $user = $this->db->fetch(
                    "SELECT * FROM employees WHERE id = ? AND status = 'active'",
                    [$userId]
                );
            }
            
            return $user ?: null;
        } catch (\Exception $e) {
            error_log("Error getting current user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current user role
     */
    protected function getCurrentUserRole(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Try session first
        $role = $_SESSION['admin_role'] ?? $_SESSION['user_role'] ?? null;
        
        if ($role) {
            return $role;
        }
        
        // Get from database
        $user = $this->getCurrentUser();
        if ($user) {
            return $user['role'] ?? 'guest';
        }
        
        return 'guest';
    }

    /**
     * Check if user has permission
     */
    protected function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->currentRole === 'super_admin') {
            return true;
        }
        
        return in_array($permission, $this->permissions);
    }

    /**
     * Check if user has any permission
     */
    protected function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Require specific permission
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            if ($this->isApiRequest()) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Permission denied',
                    'required' => $permission
                ]);
                exit;
            }
            
            $_SESSION['error'] = 'You do not have permission to access this resource.';
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }
    }

    /**
     * Render admin view with layout
     */
    public function render($view, $data = [], $layout = null, $echo = true)
    {
        // Add common data
        $data['currentUser'] = $this->currentUser;
        $data['currentRole'] = $this->currentRole;
        $data['dashboardType'] = $this->dashboardType;
        $data['permissions'] = $this->permissions;
        $data['roleName'] = RBACManager::getRoleName($this->currentRole);
        $data['roleLevel'] = RBACManager::getRoleLevel($this->currentRole);
        $data['roleCategory'] = RBACManager::getRoleCategory($this->currentRole);
        
        // Get layout based on dashboard type
        $layoutPath = $layout ?? $this->getLayoutPath();
        
        // Add page data
        $data['page_title'] = $data['page_title'] ?? 'Admin Dashboard';
        $data['page_description'] = $data['page_description'] ?? '';
        
        // Extract data to make variables available in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view
        $viewBasePath = defined('VIEW_PATH') ? VIEW_PATH : (defined('APP_PATH') ? APP_PATH . '/views' : __DIR__ . '/../views');
        $viewPath = $viewBasePath . '/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "<div class='alert alert-danger'>View not found: $view</div>";
        }
        
        // Get content
        $content = ob_get_clean();
        
        // Include layout
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Get layout path based on dashboard type
     */
    protected function getLayoutPath(): string
    {
        $viewPath = defined('VIEW_PATH') ? VIEW_PATH : APP_PATH . '/views';
        $layoutMap = [
            'superadmin' => $viewPath . '/admin/layouts/superadmin.php',
            'executive' => $viewPath . '/admin/layouts/superadmin.php',
            'manager' => $viewPath . '/admin/layouts/manager.php',
            'team_lead' => $viewPath . '/admin/layouts/manager.php',
            'employee' => $viewPath . '/admin/layouts/employee.php',
            'associate' => $viewPath . '/admin/layouts/associate.php',
            'franchise' => $viewPath . '/admin/layouts/associate.php',
            'customer' => $viewPath . '/admin/layouts/default.php',
            'lead' => $viewPath . '/admin/layouts/default.php',
            'guest' => $viewPath . '/admin/layouts/default.php',
            'default' => $viewPath . '/admin/layouts/superadmin.php',
        ];
        
        return $layoutMap[$this->dashboardType] ?? $layoutMap['default'];
    }

    /**
     * Get admin menu based on role
     */
    protected function getAdminMenu(): array
    {
        $role = $this->currentRole;
        
        $allMenus = [
            // Dashboard Section
            'dashboard' => [
                'title' => 'Dashboard',
                'icon' => 'fas fa-home',
                'permissions' => ['dashboard.view'],
                'items' => [
                    'overview' => ['title' => 'Overview', 'url' => '/admin/dashboard', 'icon' => 'fas fa-chart-pie'],
                    'analytics' => ['title' => 'Analytics', 'url' => '/admin/analytics', 'icon' => 'fas fa-chart-line'],
                    'reports' => ['title' => 'Reports', 'url' => '/admin/reports', 'icon' => 'fas fa-file-alt'],
                ]
            ],
            
            // MLM Section
            'mlm' => [
                'title' => 'MLM Network',
                'icon' => 'fas fa-sitemap',
                'permissions' => ['mlm.tree.view', 'commission.view'],
                'items' => [
                    'network' => ['title' => 'Network Tree', 'url' => '/admin/mlm/tree', 'icon' => 'fas fa-project-diagram'],
                    'associates' => ['title' => 'Associates', 'url' => '/admin/mlm/associates', 'icon' => 'fas fa-users'],
                    'ranks' => ['title' => 'Ranks & Tiers', 'url' => '/admin/mlm/ranks', 'icon' => 'fas fa-medal'],
                    'commissions' => ['title' => 'Commissions', 'url' => '/admin/mlm/commissions', 'icon' => 'fas fa-percentage'],
                    'payouts' => ['title' => 'Payouts', 'url' => '/admin/mlm/payouts', 'icon' => 'fas fa-rupee-sign'],
                    'performance' => ['title' => 'Performance', 'url' => '/admin/mlm/performance', 'icon' => 'fas fa-chart-bar'],
                ]
            ],
            
            // CRM Section
            'crm' => [
                'title' => 'CRM & Leads',
                'icon' => 'fas fa-bullseye',
                'permissions' => ['leads.view.all', 'leads.view.team', 'leads.view.own'],
                'items' => [
                    'leads' => ['title' => 'All Leads', 'url' => '/admin/leads', 'icon' => 'fas fa-user-friends'],
                    'pipeline' => ['title' => 'Pipeline', 'url' => '/admin/leads/pipeline', 'icon' => 'fas fa-filter'],
                    'followups' => ['title' => 'Follow-ups', 'url' => '/admin/leads/followups', 'icon' => 'fas fa-phone-alt'],
                    'customers' => ['title' => 'Customers', 'url' => '/admin/customers', 'icon' => 'fas fa-user-check'],
                    'campaigns' => ['title' => 'Campaigns', 'url' => '/admin/campaigns', 'icon' => 'fas fa-bullhorn'],
                ]
            ],
            
            // Property Section
            'property' => [
                'title' => 'Properties',
                'icon' => 'fas fa-building',
                'permissions' => ['property.view', 'property.view.all'],
                'items' => [
                    'all' => ['title' => 'All Properties', 'url' => '/admin/properties', 'icon' => 'fas fa-home'],
                    'projects' => ['title' => 'Projects', 'url' => '/admin/projects', 'icon' => 'fas fa-city'],
                    'plots' => ['title' => 'Plots / Land', 'url' => '/admin/plots', 'icon' => 'fas fa-map'],
                    'residential' => ['title' => 'Residential', 'url' => '/admin/properties/residential', 'icon' => 'fas fa-home'],
                    'commercial' => ['title' => 'Commercial', 'url' => '/admin/properties/commercial', 'icon' => 'fas fa-store'],
                    'bookings' => ['title' => 'Bookings', 'url' => '/admin/bookings', 'icon' => 'fas fa-file-contract'],
                ]
            ],
            
            // Financial Section
            'financial' => [
                'title' => 'Financial',
                'icon' => 'fas fa-rupee-sign',
                'permissions' => ['financial.view', 'financial.view.all'],
                'items' => [
                    'transactions' => ['title' => 'Transactions', 'url' => '/admin/financial/transactions', 'icon' => 'fas fa-exchange-alt'],
                    'invoices' => ['title' => 'Invoices', 'url' => '/admin/financial/invoices', 'icon' => 'fas fa-file-invoice-dollar'],
                    'emi' => ['title' => 'EMI Management', 'url' => '/admin/financial/emi', 'icon' => 'fas fa-calendar'],
                    'expenses' => ['title' => 'Expenses', 'url' => '/admin/financial/expenses', 'icon' => 'fas fa-receipt'],
                    'payroll' => ['title' => 'Payroll', 'url' => '/admin/financial/payroll', 'icon' => 'fas fa-users-cog'],
                    'reports' => ['title' => 'Financial Reports', 'url' => '/admin/financial/reports', 'icon' => 'fas fa-chart-pie'],
                ]
            ],
            
            // Employee Section
            'employee' => [
                'title' => 'Team & HR',
                'icon' => 'fas fa-users-cog',
                'permissions' => ['employee.view.all', 'employee.view.team'],
                'items' => [
                    'staff' => ['title' => 'Staff Members', 'url' => '/admin/employees', 'icon' => 'fas fa-user-friends'],
                    'attendance' => ['title' => 'Attendance', 'url' => '/admin/attendance', 'icon' => 'fas fa-clock'],
                    'leaves' => ['title' => 'Leaves', 'url' => '/admin/leaves', 'icon' => 'fas fa-calendar-alt'],
                    'payroll' => ['title' => 'Payroll', 'url' => '/admin/payroll', 'icon' => 'fas fa-money-bill'],
                    'roles' => ['title' => 'Roles & Access', 'url' => '/admin/roles', 'icon' => 'fas fa-user-shield'],
                    'training' => ['title' => 'Training', 'url' => '/admin/training', 'icon' => 'fas fa-graduation-cap'],
                ]
            ],
            
            // Marketing Section
            'marketing' => [
                'title' => 'Marketing',
                'icon' => 'fas fa-bullhorn',
                'permissions' => ['marketing.view', 'campaign.view'],
                'items' => [
                    'campaigns' => ['title' => 'Campaigns', 'url' => '/admin/marketing/campaigns', 'icon' => 'fas fa-bullhorn'],
                    'email' => ['title' => 'Email Templates', 'url' => '/admin/marketing/email', 'icon' => 'fas fa-envelope'],
                    'sms' => ['title' => 'SMS Templates', 'url' => '/admin/marketing/sms', 'icon' => 'fas fa-comment-sms'],
                    'whatsapp' => ['title' => 'WhatsApp', 'url' => '/admin/marketing/whatsapp', 'icon' => 'fab fa-whatsapp'],
                    'social' => ['title' => 'Social Media', 'url' => '/admin/marketing/social', 'icon' => 'fas fa-share-alt'],
                ]
            ],
            
            // Content Section
            'content' => [
                'title' => 'Content',
                'icon' => 'fas fa-images',
                'permissions' => ['content.view', 'pages.manage', 'media.manage'],
                'items' => [
                    'media' => ['title' => 'Media Gallery', 'url' => '/admin/media', 'icon' => 'fas fa-image'],
                    'pages' => ['title' => 'Pages', 'url' => '/admin/pages', 'icon' => 'fas fa-file'],
                    'blog' => ['title' => 'Blog & News', 'url' => '/admin/blog', 'icon' => 'fas fa-newspaper'],
                    'testimonials' => ['title' => 'Testimonials', 'url' => '/admin/testimonials', 'icon' => 'fas fa-quote-left'],
                    'faq' => ['title' => 'FAQ', 'url' => '/admin/faq', 'icon' => 'fas fa-question-circle'],
                ]
            ],
            
            // AI Section
            'ai' => [
                'title' => 'AI Features',
                'icon' => 'fas fa-robot',
                'permissions' => ['ai.dashboard'],
                'items' => [
                    'dashboard' => ['title' => 'AI Dashboard', 'url' => '/admin/ai/dashboard', 'icon' => 'fas fa-brain'],
                    'valuation' => ['title' => 'Property Valuation', 'url' => '/admin/ai/valuation', 'icon' => 'fas fa-calculator'],
                    'lead-scoring' => ['title' => 'Lead Scoring', 'url' => '/admin/ai/lead-scoring', 'icon' => 'fas fa-magic'],
                    'chatbot' => ['title' => 'Chatbot', 'url' => '/admin/ai/chatbot', 'icon' => 'fas fa-comment-dots'],
                    'analytics' => ['title' => 'AI Analytics', 'url' => '/admin/ai/analytics', 'icon' => 'fas fa-chart-line'],
                ]
            ],
            
            // Reports Section
            'reports' => [
                'title' => 'Reports',
                'icon' => 'fas fa-chart-bar',
                'permissions' => ['reports.view'],
                'items' => [
                    'sales' => ['title' => 'Sales Reports', 'url' => '/admin/reports/sales', 'icon' => 'fas fa-chart-line'],
                    'mlm' => ['title' => 'MLM Reports', 'url' => '/admin/reports/mlm', 'icon' => 'fas fa-sitemap'],
                    'employee' => ['title' => 'Employee Reports', 'url' => '/admin/reports/employee', 'icon' => 'fas fa-users'],
                    'financial' => ['title' => 'Financial Reports', 'url' => '/admin/reports/financial', 'icon' => 'fas fa-rupee-sign'],
                    'custom' => ['title' => 'Custom Reports', 'url' => '/admin/reports/custom', 'icon' => 'fas fa-file-alt'],
                ]
            ],
            
            // System Section
            'system' => [
                'title' => 'System',
                'icon' => 'fas fa-cogs',
                'permissions' => ['system.settings', 'backup.manage', 'api.manage'],
                'items' => [
                    'settings' => ['title' => 'General Settings', 'url' => '/admin/settings', 'icon' => 'fas fa-cog'],
                    'users' => ['title' => 'User Management', 'url' => '/admin/users', 'icon' => 'fas fa-users-cog'],
                    'api' => ['title' => 'API Management', 'url' => '/admin/api', 'icon' => 'fas fa-code'],
                    'backup' => ['title' => 'Backup & Restore', 'url' => '/admin/backup', 'icon' => 'fas fa-database'],
                    'logs' => ['title' => 'System Logs', 'url' => '/admin/logs', 'icon' => 'fas fa-clipboard-list'],
                    'security' => ['title' => 'Security', 'url' => '/admin/security', 'icon' => 'fas fa-shield-alt'],
                    'cache' => ['title' => 'Cache Management', 'url' => '/admin/cache', 'icon' => 'fas fa-bolt'],
                    'health' => ['title' => 'System Health', 'url' => '/admin/health', 'icon' => 'fas fa-heartbeat'],
                ]
            ],
        ];
        
        // Filter menus based on permissions
        $filteredMenus = [];
        foreach ($allMenus as $key => $menu) {
            // Check if user has any permission for this menu
            if ($this->hasAnyPermission($menu['permissions'])) {
                $filteredMenus[$key] = $menu;
            }
        }
        
        return $filteredMenus;
    }

    /**
     * Get dashboard statistics
     */
    protected function getDashboardStats(): array
    {
        $stats = [];
        
        try {
            // Total Users
            $stats['total_users'] = $this->db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
            
            // Total Properties
            $stats['total_properties'] = $this->db->fetch("SELECT COUNT(*) as count FROM properties")['count'] ?? 0;
            
            // Active Properties
            $stats['active_properties'] = $this->db->fetch("SELECT COUNT(*) as count FROM properties WHERE status = 'active'")['count'] ?? 0;
            
            // Total Leads
            $stats['total_leads'] = $this->db->fetch("SELECT COUNT(*) as count FROM leads")['count'] ?? 0;
            
            // New Leads Today
            $stats['new_leads_today'] = $this->db->fetch(
                "SELECT COUNT(*) as count FROM leads WHERE DATE(created_at) = CURDATE()"
            )['count'] ?? 0;
            
            // Total Associates
            $stats['total_associates'] = $this->db->fetch(
                "SELECT COUNT(*) as count FROM users WHERE role IN ('associate', 'agent')"
            )['count'] ?? 0;
            
            // Commission Paid
            $stats['commission_paid'] = $this->db->fetch(
                "SELECT COALESCE(SUM(net_payout), 0) as total FROM mlm_payouts WHERE status = 'paid'"
            )['total'] ?? 0;
            
            // Revenue This Month
            $stats['revenue_month'] = $this->db->fetch(
                "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
            )['total'] ?? 0;
            
            // Employees
            $stats['total_employees'] = $this->db->fetch("SELECT COUNT(*) as count FROM employees")['count'] ?? 0;
            
            // Pending Bookings
            $stats['pending_bookings'] = $this->db->fetch(
                "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'"
            )['count'] ?? 0;
            
        } catch (\Exception $e) {
            error_log("Error getting dashboard stats: " . $e->getMessage());
        }
        
        return $stats;
    }

    /**
     * Get recent activities
     */
    protected function getRecentActivities(int $limit = 10): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT ?",
                [$limit]
            ) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * JSON response for API
     */
    protected function jsonResponse($data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Success JSON response
     */
    protected function successResponse(array $data = [], string $message = 'Success'): void
    {
        $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Error JSON response
     */
    protected function errorResponse(string $message, int $statusCode = 400, array $errors = []): void
    {
        $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }
}
