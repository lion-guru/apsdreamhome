<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RBACManager;
use App\Core\Database\Database;
use App\Http\Controllers\Admin\AdminController;
use \App\Traits\TenantAwareTrait;
use Exception;

/**
 * Role-Based Dashboard Controller
 * Handles dashboard routing based on user roles
 */
class RoleBasedDashboardController extends AdminController
{
    use TenantAwareTrait;

    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->layout = 'layouts/admin';
    }

    /**
     * Get user dashboard based on role
     * @param string $userRole User role
     * @return array Dashboard data
     */
    public function getDashboardByRole($userRole)
    {
        try {
            switch ($userRole) {
                case RBACManager::ROLE_SUPER_ADMIN:
                case RBACManager::ROLE_ADMIN:
                    return $this->getAdminDashboard();

                case RBACManager::ROLE_MANAGER:
                    return $this->getManagerDashboard();

                case RBACManager::ROLE_ASSOCOCIATE:
                    return $this->getAssociateDashboard();

                case RBACManager::ROLE_USER:
                    return $this->getUserDashboard();

                case RBACManager::ROLE_GUEST:
                    return $this->getGuestDashboard();

                default:
                    return $this->getDefaultDashboard();
            }
        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            return $this->getDefaultDashboard();
        }
    }

    /**
     * Main dashboard entry point - unified role-based dashboard
     */
    public function index()
    {
        @session_start();

        // Auth check - accepts both admin and user sessions (unified)
        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        if (!$userId) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        // Detect role from session (supports both admin_* and user_* naming conventions)
        $role = $_SESSION['role'] ?? $_SESSION['admin_role'] ?? 'admin';
        $userName = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? 'User';

        // For admin/super_admin, redirect to comprehensive ERP Overview
        if (in_array($role, ['super_admin', 'admin'])) {
            header('Location: ' . BASE_URL . '/admin/erp');
            exit;
        }

        // Roles with dedicated dashboard views — redirect to their specific route
        $roleDashboardMap = [
            'ceo' => '/admin/dashboard/ceo',
            'cfo' => '/admin/dashboard/cfo',
            'cto' => '/admin/dashboard/cto',
            'coo' => '/admin/dashboard/coo',
            'cmo' => '/admin/dashboard/cmo',
            'chro' => '/admin/dashboard/chro',
            'sales_director' => '/admin/dashboard/sales',
            'marketing_director' => '/admin/dashboard/marketing',
            'construction_director' => '/admin/dashboard/operations',
            'finance_director' => '/admin/dashboard/finance',
            'hr_director' => '/admin/dashboard/hr',
            'department_manager' => '/admin/dashboard/sales',
            'project_manager' => '/admin/dashboard/operations',
            'sales_manager' => '/admin/dashboard/sales',
            'hr_manager' => '/admin/dashboard/hr',
            'marketing_manager' => '/admin/dashboard/marketing',
            'finance_manager' => '/admin/dashboard/finance',
            'property_manager' => '/admin/dashboard/operations',
            'it_manager' => '/admin/dashboard/it',
            'operations_manager' => '/admin/dashboard/operations',
        ];

        if (isset($roleDashboardMap[$role])) {
            header('Location: ' . BASE_URL . $roleDashboardMap[$role]);
            exit;
        }

        // All other roles (associate, agent, employee, telecaller, team leads, staff, MLM) — generic dashboard
        $stats = $this->loadRoleStats($role, $userId);
        $recentItems = $this->loadRoleRecentItems($role, $userId);

        $pageTitle = match ($role) {
            'manager' => 'Manager Dashboard',
            'associate' => 'Associate Dashboard',
            'agent' => 'Agent Dashboard',
            'employee' => 'Employee Dashboard',
            default => ucfirst($role) . ' Dashboard'
        };

        $data = compact('role', 'userName', 'stats', 'recentItems');
        $data['page_title'] = $pageTitle;
        $this->render('admin/dashboard/role_dashboard', $data);
    }

    /**
     * Load role-specific dashboard stats
     */
    private function loadRoleStats($role, $userId)
    {
        $stats = [];
        $safeQuery = function($sql, $params = []) use (&$db) {
            try {
                if (!empty($params)) {
                    $s = $db->prepare($sql);
                    $s->execute($params);
                    return $s->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0;
                }
                return $db->query($sql)->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0;
            } catch (\Throwable $e) { return 0; }
        };
        $safeSum = function($sql, $params = []) use (&$db) {
            try {
                if (!empty($params)) {
                    $s = $db->prepare($sql);
                    $s->execute($params);
                    return $s->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0;
                }
                return $db->query($sql)->fetch(\PDO::FETCH_ASSOC)['c'] ?? 0;
            } catch (\Throwable $e) { return 0; }
        };

        try {
            $db = \App\Core\Database::getInstance()->getConnection();

            // Common stats for all roles
            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $tidParam = $tid > 1 ? [$tid] : [];
            $stats['total_users'] = $safeQuery("SELECT COUNT(*) as c FROM users");
            $stats['total_leads'] = $safeQuery("SELECT COUNT(*) as c FROM leads WHERE 1=1{$tidSql}", $tidParam);
            $stats['new_leads_today'] = $safeQuery("SELECT COUNT(*) as c FROM leads WHERE DATE(created_at) = CURDATE(){$tidSql}", $tidParam);
            $stats['active_properties'] = $safeQuery("SELECT COUNT(*) as c FROM properties WHERE status='active'");
            $stats['total_bookings'] = $safeQuery("SELECT COUNT(*) as c FROM plot_bookings");
            $stats['pending_bookings'] = $safeQuery("SELECT COUNT(*) as c FROM plot_bookings WHERE status='pending'");
            $stats['total_revenue'] = '₹' . number_format($safeSum("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger") / 100000, 1) . 'L';
            $stats['monthly_revenue'] = '₹' . number_format($safeSum("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)") / 100000, 1) . 'L';
            $stats['total_associates'] = $safeQuery("SELECT COUNT(*) as c FROM users WHERE role='associate'");
            $stats['total_employees'] = $safeQuery("SELECT COUNT(*) as c FROM users WHERE role IN ('employee','telecaller','backoffice_staff')");
            $stats['open_tickets'] = $safeQuery("SELECT COUNT(*) as c FROM support_tickets WHERE status != 'closed'");
            $stats['active_colonies'] = $safeQuery("SELECT COUNT(*) as c FROM colonies WHERE status='active'");
            $stats['available_plots'] = $safeQuery("SELECT COUNT(*) as c FROM plots WHERE status='available'");
            $stats['booked_plots'] = $safeQuery("SELECT COUNT(*) as c FROM plots WHERE status='sold'");
            $stats['conversion_rate'] = $stats['total_leads'] > 0 ? round(($safeQuery("SELECT COUNT(*) as c FROM leads WHERE status='converted'{$tidSql}", $tidParam) / $stats['total_leads']) * 100, 1) : 0;

            // Role-specific additional stats
            if (in_array($role, ['associate'])) {
                $stats['team_size'] = $safeQuery("SELECT COUNT(*) as c FROM mlm_network_tree WHERE sponsor_id=?", [$userId]);
                $stats['total_commission'] = '₹' . number_format($safeSum("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger WHERE user_id=?", [$userId]) / 100000, 1) . 'L';
                $stats['rank'] = 'N/A';
                try {
                    $r = $db->prepare("SELECT rank FROM associates WHERE user_id=?");
                    $r->execute([$userId]);
                    $stats['rank'] = $r->fetch()['rank'] ?? 'associate';
                } catch (\Throwable $e) { error_log("RoleBasedDashboardController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
            } elseif (in_array($role, ['agent'])) {
                $stats['my_leads'] = $safeQuery("SELECT COUNT(*) as c FROM leads WHERE assigned_to=?{$tidSql}", [$userId]);
                $stats['conversions'] = $safeQuery("SELECT COUNT(*) as c FROM leads WHERE assigned_to=? AND status='converted'{$tidSql}", [$userId]);
                $stats['earnings'] = '₹' . number_format($safeSum("SELECT COALESCE(SUM(amount),0) as c FROM mlm_commission_ledger WHERE user_id=?", [$userId]) / 100000, 1) . 'L';
            } elseif (in_array($role, ['employee', 'telecaller', 'backoffice_staff'])) {
                $stats['my_tasks'] = $safeQuery("SELECT COUNT(*) as c FROM tasks WHERE assigned_to=?", [$userId]);
                $stats['pending_tasks'] = $safeQuery("SELECT COUNT(*) as c FROM tasks WHERE assigned_to=? AND status='pending'", [$userId]);
                $stats['completed_tasks'] = $safeQuery("SELECT COUNT(*) as c FROM tasks WHERE assigned_to=? AND status='completed'", [$userId]);
            }

            // Staff role specifics
            if (in_array($role, ['senior_accountant', 'chartered_accountant', 'accountant', 'finance_manager'])) {
                $stats['pending_payments'] = $safeQuery("SELECT COUNT(*) as c FROM booking_payment_schedules WHERE status='pending'");
            }
            if (in_array($role, ['senior_developer', 'developer', 'it_manager'])) {
                $stats['active_users_online'] = $safeQuery("SELECT COUNT(*) as c FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            }
            if (in_array($role, ['content_writer', 'graphic_designer', 'marketing_manager'])) {
                $stats['active_campaigns'] = $safeQuery("SELECT COUNT(*) as c FROM marketing_campaigns WHERE status='active'");
            }
            if (in_array($role, ['legal_advisor'])) {
                $stats['pending_legal'] = $safeQuery("SELECT COUNT(*) as c FROM legal_documents WHERE status='draft'");
            }

        } catch (\Exception $e) {
            error_log('loadRoleStats error: ' . $e->getMessage());
        }
        return $stats;
    }

    /**
     * Load role-specific recent items
     */
    /**
     * Roles & Permissions page
     */
    public function roles()
    {
        @session_start();
        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        if (!$userId) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        $this->data['page_title'] = 'Roles & Permissions';
        $this->render('admin/roles/index');
    }

    private function loadRoleRecentItems($role, $userId)
    {
        $items = [];
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? " AND leads.tenant_id = ?" : "";
            $tidParam = $tid > 1 ? [$tid] : [];
            
            if (in_array($role, ['super_admin', 'admin', 'manager'])) {
                $rows = $db->query("SELECT id, name, email, status, created_at FROM leads WHERE 1=1{$tidSql} ORDER BY created_at DESC LIMIT 5", $tidParam)->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($rows as $r) {
                    $items[] = [
                        'title' => $r['name'] ?? 'Lead',
                        'description' => $r['email'] ?? '',
                        'status' => $r['status'] ?? 'new',
                        'badge_color' => ($r['status'] ?? '') === 'converted' ? 'success' : (($r['status'] ?? '') === 'contacted' ? 'warning' : 'info'),
                        'created_at' => $r['created_at'] ?? '',
                    ];
                }
            } elseif ($role === 'associate') {
                $s = $db->prepare("SELECT id, name, email, status, created_at FROM leads WHERE (assigned_to=? OR assigned_to IS NULL){$tidSql} ORDER BY created_at DESC LIMIT 5");
                $s->execute([$userId]);
                foreach ($s->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $items[] = ['title' => $r['name'] ?? 'Lead', 'description' => $r['email'] ?? '', 'status' => $r['status'] ?? 'new', 'badge_color' => 'info', 'created_at' => $r['created_at'] ?? ''];
                }
            } elseif ($role === 'agent') {
                $s = $db->prepare("SELECT id, name, email, status, created_at FROM leads WHERE assigned_to=?{$tidSql} ORDER BY created_at DESC LIMIT 5");
                $s->execute([$userId]);
                foreach ($s->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $items[] = ['title' => $r['name'] ?? 'Lead', 'description' => $r['email'] ?? '', 'status' => $r['status'] ?? 'new', 'badge_color' => ($r['status'] ?? '') === 'converted' ? 'success' : 'info', 'created_at' => $r['created_at'] ?? ''];
                }
            } elseif ($role === 'employee') {
                $s = $db->prepare("SELECT id, title, status, created_at FROM tasks WHERE assigned_to=? ORDER BY created_at DESC LIMIT 5");
                $s->execute([$userId]);
                foreach ($s->fetchAll(\PDO::FETCH_ASSOC) as $r) {
                    $items[] = ['title' => $r['title'] ?? 'Task', 'description' => '', 'status' => $r['status'] ?? 'pending', 'badge_color' => ($r['status'] ?? '') === 'completed' ? 'success' : 'warning', 'created_at' => $r['created_at'] ?? ''];
                }
            }
        } catch (\Exception $e) {
            error_log('loadRoleRecentItems error: ' . $e->getMessage());
        }
        return $items;
    }

    /**
     * Enterprise dashboard
     */
    public function enterpriseDashboard()
    {
        $dashboardData = [
            'role' => 'enterprise',
            'title' => 'Enterprise Dashboard',
            'widgets' => $this->getDashboardByRole('enterprise'),
            'analytics' => []
        ];

        $this->render('dashboard/enterprise_dashboard', $dashboardData);
    }

    /**
     * Get role-specific dashboard
     */
    public function getRoleDashboard($role)
    {
        $dashboardData = $this->getDashboardByRole($role);
        $this->render("dashboard/{$role}_dashboard", $dashboardData);
    }

    // Role-specific dashboard methods
    public function agent()
    {
        $this->getRoleDashboard('agent');
    }
    public function builder()
    {
        $this->getRoleDashboard('builder');
    }
    public function ceo()
    {
        $this->getRoleDashboard('ceo');
    }
    public function cfo()
    {
        $this->getRoleDashboard('cfo');
    }
    public function cm()
    {
        $data = $this->getCmDashboardData();
        $this->render('dashboard/cm_dashboard', $data);
    }

    public function coo()
    {
        $data = $this->getCooDashboardData();
        $this->render('dashboard/coo_dashboard', $data);
    }

    public function cto()
    {
        $data = $this->getCtoDashboardData();
        $this->render('dashboard/cto_dashboard', $data);
    }

    /**
     * CTO dashboard data — real DB queries
     */
    private function getCtoDashboardData()
    {
        $data = ['page_title' => 'CTO Dashboard'];
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $data['uptime'] = '99.9%';
            try { $data['active_users'] = (int)$db->query("SELECT COUNT(*) as c FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $data['active_users'] = 0; }
            try { $data['api_calls'] = (int)$db->query("SELECT COUNT(*) as c FROM ai_api_logs WHERE DATE(created_at) = CURDATE()")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $data['api_calls'] = 0; }
            try { $data['open_tickets'] = (int)$db->query("SELECT COUNT(*) as c FROM support_tickets WHERE status != 'closed'")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $data['open_tickets'] = 0; }
        } catch (\Exception $e) { error_log('CTO Dashboard error: ' . $e->getMessage()); }
        return $data;
    }

    /**
     * COO dashboard data — real DB queries
     */
    private function getCooDashboardData()
    {
        $data = ['page_title' => 'COO Dashboard'];
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $widgets = [];
            try { $c = (int)$db->query("SELECT COUNT(*) as c FROM properties WHERE status='active'")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $c = 0; }
            $widgets[] = ['title' => 'Active Properties', 'icon' => 'home', 'count' => $c, 'link' => '/admin/properties'];
            try { $c = (int)$db->query("SELECT COUNT(*) as c FROM plot_bookings")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $c = 0; }
            $widgets[] = ['title' => 'Total Bookings', 'icon' => 'calendar-check', 'count' => $c, 'link' => '/admin/bookings'];
            try { $c = (int)$db->query("SELECT COUNT(*) as c FROM colonies WHERE status='active'")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $c = 0; }
            $widgets[] = ['title' => 'Active Colonies', 'icon' => 'map-marked-alt', 'count' => $c, 'link' => '/admin/colonies'];
            try { $c = (int)$db->query("SELECT COUNT(*) as c FROM plots WHERE status='available'")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $c = 0; }
            $widgets[] = ['title' => 'Available Plots', 'icon' => 'th-large', 'count' => $c, 'link' => '/admin/plots'];
            $data['widgets'] = $widgets;
            $analytics = [];
            try {
                $rows = $db->query("SELECT DATE(created_at) as day, COUNT(*) as cnt FROM plot_bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC")->fetchAll(\PDO::FETCH_ASSOC);
                $analytics['labels'] = array_map(fn($d) => date('d M', strtotime($d['day'])), $rows);
                $analytics['data'] = array_column($rows, 'cnt');
            } catch (\Throwable $e) { $analytics = ['labels' => [], 'data' => []]; }
            $data['analytics'] = $analytics;
        } catch (\Exception $e) { error_log('COO Dashboard error: ' . $e->getMessage()); }
        return $data;
    }

    /**
     * CMO/CM dashboard data — real DB queries
     */
    private function getCmDashboardData()
    {
        $data = ['page_title' => 'CM Dashboard'];
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $stats = ['team_size' => 0, 'active_projects' => 0, 'monthly_sales' => 0, 'performance_score' => 0];
            try { $stats['team_size'] = (int)$db->query("SELECT COUNT(*) as c FROM users WHERE role IN ('employee','associate') AND status='active'")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { error_log("RoleBasedDashboardController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
            try { $stats['active_projects'] = (int)$db->query("SELECT COUNT(*) as c FROM properties WHERE status='active'")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { error_log("RoleBasedDashboardController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
            try { $stats['monthly_sales'] = (int)$db->query("SELECT COUNT(*) as c FROM properties WHERE status='sold' AND MONTH(updated_at)=MONTH(CURRENT_DATE()) AND YEAR(updated_at)=YEAR(CURRENT_DATE())")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { error_log("RoleBasedDashboardController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
            try {
                $row = $db->query("SELECT (SELECT COUNT(*) FROM properties WHERE status='sold' AND MONTH(updated_at)=MONTH(CURRENT_DATE())) as ms, (SELECT AVG(CASE WHEN status='sold' THEN 1 ELSE 0 END)*100 FROM properties WHERE MONTH(updated_at)=MONTH(CURRENT_DATE())) as cr")->fetch(\PDO::FETCH_ASSOC);
                $stats['performance_score'] = round(min(($row['ms']/10)*100, 100)*0.4 + min($row['cr']*0.3, 30) + 30, 1);
            } catch (\Throwable $e) { $stats['performance_score'] = 0; }
            $data['stats'] = $stats;
            try { $data['teamPerformance'] = $db->query("SELECT u.name, u.email, u.role, COUNT(p.id) as properties_managed, SUM(CASE WHEN p.status='sold' THEN 1 ELSE 0 END) as sales_count FROM users u LEFT JOIN properties p ON u.id=p.created_by WHERE u.role IN ('employee','associate') AND u.status='active' GROUP BY u.id,u.name,u.email,u.role ORDER BY sales_count DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC); } catch (\Throwable $e) { $data['teamPerformance'] = []; }
            try { $data['recentActivities'] = $db->query("SELECT action as activity_type, description, created_at FROM activity_logs_unified WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC); } catch (\Throwable $e) { $data['recentActivities'] = []; }
            try { $data['projectsOverview'] = $db->query("SELECT status, COUNT(*) as count FROM properties GROUP BY status ORDER BY count DESC")->fetchAll(\PDO::FETCH_ASSOC); } catch (\Throwable $e) { $data['projectsOverview'] = []; }
        } catch (\Exception $e) { error_log('CM Dashboard error: ' . $e->getMessage()); }
        return $data;
    }

    /**
     * HR/CHRO dashboard data — real DB queries
     */
    private function getHrDashboardData()
    {
        $data = ['page_title' => 'HR Dashboard'];
        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $widgets = [];
            try { $c = (int)$db->query("SELECT COUNT(*) as c FROM users WHERE role IN ('employee','telecaller','backoffice_staff','content_writer','graphic_designer','data_entry_operator','senior_developer','developer')")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $c = 0; }
            $widgets[] = ['title' => 'Total Employees', 'icon' => 'users', 'count' => $c, 'link' => '/admin/employees'];
            try { $c = (int)$db->query("SELECT COUNT(*) as c FROM attendance WHERE DATE(date)=CURDATE()")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $c = 0; }
            $widgets[] = ['title' => 'Present Today', 'icon' => 'user-check', 'count' => $c, 'link' => '/admin/backoffice'];
            try { $c = (int)$db->query("SELECT COUNT(*) as c FROM leave_applications WHERE status='pending'")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $c = 0; }
            $widgets[] = ['title' => 'Pending Leaves', 'icon' => 'calendar-minus', 'count' => $c, 'link' => '/admin/backoffice'];
            try { $c = (int)$db->query("SELECT COUNT(DISTINCT user_id) as c FROM training_enrollments WHERE status='enrolled'")->fetch(\PDO::FETCH_ASSOC)['c']; } catch (\Throwable $e) { $c = 0; }
            $widgets[] = ['title' => 'In Training', 'icon' => 'graduation-cap', 'count' => $c, 'link' => '/admin/backoffice'];
            $data['widgets'] = $widgets;
            $analytics = [];
            try {
                $rows = $db->query("SELECT role, COUNT(*) as cnt FROM users WHERE role IN ('employee','telecaller','backoffice_staff','content_writer','graphic_designer','data_entry_operator','senior_developer','developer') GROUP BY role ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC);
                $analytics['labels'] = array_column($rows, 'role');
                $analytics['data'] = array_column($rows, 'cnt');
            } catch (\Throwable $e) { $analytics = ['labels' => [], 'data' => []]; }
            $data['analytics'] = $analytics;
        } catch (\Exception $e) { error_log('HR Dashboard error: ' . $e->getMessage()); }
        return $data;
    }

    public function director()
    {
        $this->getRoleDashboard('director');
    }
    public function finance()
    {
        $this->getRoleDashboard('finance');
    }

    public function hr()
    {
        $data = $this->getHrDashboardData();
        $this->render('dashboard/hr_dashboard', $data);
    }

    public function it()
    {
        $this->getRoleDashboard('it');
    }

    public function marketing()
    {
        $this->getRoleDashboard('marketing');
    }

    public function operations()
    {
        $this->getRoleDashboard('operations');
    }

    public function sales()
    {
        $this->getRoleDashboard('sales');
    }

    public function superadmin()
    {
        $this->getRoleDashboard('superadmin');
    }

    /**
     * Get current user role
     */
    private function getCurrentUserRole()
    {
        // Check admin session first, then user session, then fallback to guest
        return $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'guest';
    }

    /**
     * Get admin dashboard data
     * @return array Admin dashboard data
     */
    private function getAdminDashboard()
    {
        $dashboardData = [
            'role' => 'admin',
            'title' => 'Admin Dashboard',
            'permissions' => RBACManager::getRolePermissions(RBACManager::ROLE_ADMIN),
            'widgets' => [
                'user_management' => [
                    'title' => 'User Management',
                    'icon' => 'users',
                    'count' => $this->getTotalUsers(),
                    'link' => '/admin/users'
                ],
                'property_management' => [
                    'title' => 'Property Management',
                    'icon' => 'home',
                    'count' => $this->getTotalProperties(),
                    'link' => '/admin/properties'
                ],
                'reports' => [
                    'title' => 'Reports',
                    'icon' => 'chart-bar',
                    'count' => $this->getTotalReports(),
                    'link' => '/admin/reports'
                ],
                'system_settings' => [
                    'title' => 'System Settings',
                    'icon' => 'cog',
                    'count' => 'Settings',
                    'link' => '/admin/settings'
                ]
            ],
            'recent_activities' => $this->getRecentActivities('admin'),
            'analytics' => $this->getAdminAnalytics(),
            'quick_actions' => [
                'add_user' => '/admin/users/create',
                'add_property' => '/admin/properties/create',
                'view_reports' => '/admin/reports',
                'system_backup' => '/admin/backup'
            ]
        ];

        return $dashboardData;
    }

    /**
     * Get manager dashboard data
     * @return array Manager dashboard data
     */
    private function getManagerDashboard()
    {
        $dashboardData = [
            'role' => 'manager',
            'title' => 'Manager Dashboard',
            'permissions' => RBACManager::getRolePermissions(RBACManager::ROLE_MANAGER),
            'widgets' => [
                'team_members' => [
                    'title' => 'Team Members',
                    'icon' => 'users',
                    'count' => $this->getTeamMemberCount(),
                    'link' => '/manager/team'
                ],
                'properties' => [
                    'title' => 'Properties',
                    'icon' => 'home',
                    'count' => $this->getManagerProperties(),
                    'link' => '/manager/properties'
                ],
                'reports' => [
                    'title' => 'Reports',
                    'icon' => 'chart-bar',
                    'count' => $this->getManagerReports(),
                    'link' => '/manager/reports'
                ],
                'performance' => [
                    'title' => 'Team Performance',
                    'icon' => 'trophy',
                    'count' => $this->getTeamPerformance(),
                    'link' => '/manager/performance'
                ]
            ],
            'recent_activities' => $this->getRecentActivities('manager'),
            'analytics' => $this->getManagerAnalytics(),
            'quick_actions' => [
                'assign_task' => '/manager/tasks/assign',
                'view_team' => '/manager/team',
                'generate_report' => '/manager/reports/generate',
                'team_meeting' => '/manager/meetings'
            ]
        ];

        return $dashboardData;
    }

    /**
     * Get associate dashboard data
     * @return array Associate dashboard data
     */
    private function getAssociateDashboard()
    {
        $dashboardData = [
            'role' => 'associate',
            'title' => 'Associate Dashboard',
            'permissions' => RBACManager::getRolePermissions(RBACManager::ROLE_ASSOCOCIATE),
            'widgets' => [
                'my_properties' => [
                    'title' => 'My Properties',
                    'icon' => 'home',
                    'count' => $this->getAssociateProperties(),
                    'link' => '/associate/properties'
                ],
                'clients' => [
                    'title' => 'My Clients',
                    'icon' => 'users',
                    'count' => $this->getAssociateClients(),
                    'link' => '/associate/clients'
                ],
                'commissions' => [
                    'title' => 'Commissions',
                    'icon' => 'dollar-sign',
                    'count' => $this->getAssociateCommissions(),
                    'link' => '/associate/commissions'
                ],
                'leads' => [
                    'title' => 'Leads',
                    'icon' => 'phone',
                    'count' => $this->getAssociateLeads(),
                    'link' => '/associate/leads'
                ]
            ],
            'recent_activities' => $this->getRecentActivities('associate'),
            'analytics' => $this->getAssociateAnalytics(),
            'quick_actions' => [
                'add_property' => '/associate/properties/add',
                'add_client' => '/associate/clients/add',
                'view_commission' => '/associate/commissions',
                'follow_up' => '/associate/followup'
            ]
        ];

        return $dashboardData;
    }

    /**
     * Get user dashboard data
     * @return array User dashboard data
     */
    private function getUserDashboard()
    {
        $dashboardData = [
            'role' => 'user',
            'title' => 'User Dashboard',
            'permissions' => RBACManager::getRolePermissions(RBACManager::ROLE_USER),
            'widgets' => [
                'saved_properties' => [
                    'title' => 'Saved Properties',
                    'icon' => 'heart',
                    'count' => $this->getUserSavedProperties(),
                    'link' => '/user/saved'
                ],
                'search_history' => [
                    'title' => 'Search History',
                    'icon' => 'search',
                    'count' => $this->getUserSearchHistory(),
                    'link' => '/user/history'
                ],
                'bookings' => [
                    'title' => 'My Bookings',
                    'icon' => 'calendar',
                    'count' => $this->getUserBookings(),
                    'link' => '/user/bookings'
                ],
                'profile' => [
                    'title' => 'Profile',
                    'icon' => 'user',
                    'count' => 'Complete',
                    'link' => '/user/profile'
                ]
            ],
            'recent_activities' => $this->getRecentActivities('user'),
            'analytics' => $this->getUserAnalytics(),
            'quick_actions' => [
                'search_property' => '/properties/search',
                'view_saved' => '/user/saved',
                'book_property' => '/user/bookings',
                'update_profile' => '/user/profile/edit'
            ]
        ];

        return $dashboardData;
    }

    /**
     * Get guest dashboard data
     * @return array Guest dashboard data
     */
    private function getGuestDashboard()
    {
        $dashboardData = [
            'role' => 'guest',
            'title' => 'Welcome Dashboard',
            'permissions' => RBACManager::getRolePermissions(RBACManager::ROLE_GUEST),
            'widgets' => [
                'featured_properties' => [
                    'title' => 'Featured Properties',
                    'icon' => 'star',
                    'count' => $this->getFeaturedProperties(),
                    'link' => '/properties/featured'
                ],
                'recent_properties' => [
                    'title' => 'Recent Properties',
                    'icon' => 'clock',
                    'count' => $this->getRecentProperties(),
                    'link' => '/properties/recent'
                ],
                'popular_locations' => [
                    'title' => 'Popular Locations',
                    'icon' => 'map-marker',
                    'count' => $this->getPopularLocations(),
                    'link' => '/properties/locations'
                ],
                'register' => [
                    'title' => 'Register',
                    'icon' => 'user-plus',
                    'count' => 'Join Now',
                    'link' => '/auth/register'
                ]
            ],
            'recent_activities' => [],
            'analytics' => $this->getGuestAnalytics(),
            'quick_actions' => [
                'search_property' => '/properties/search',
                'register' => '/auth/register',
                'login' => '/auth/login',
                'browse_properties' => '/properties'
            ]
        ];

        return $dashboardData;
    }

    /**
     * Get default dashboard data
     * @return array Default dashboard data
     */
    private function getDefaultDashboard()
    {
        return [
            'role' => 'default',
            'title' => 'Dashboard',
            'widgets' => [
                'welcome' => [
                    'title' => 'Welcome',
                    'icon' => 'home',
                    'count' => 'APS Dream Home',
                    'link' => '/'
                ]
            ],
            'recent_activities' => [],
            'analytics' => [],
            'quick_actions' => [
                'home' => '/',
                'login' => '/auth/login',
                'register' => '/auth/register'
            ]
        ];
    }

    /**
     * Get total users count
     * @return int Total users
     */
    private function getTotalUsers()
    {
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total properties count
     * @return int Total properties
     */
    private function getTotalProperties()
    {
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM properties WHERE status = 'active'");
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get total reports count
     * @return int Total reports
     */
    private function getTotalReports()
    {
        try {
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get recent activities for role
     * @param string $role User role
     * @return array Recent activities
     */
    public function getRecentActivitiesForRole($role)
    {
        try {
            $sql = "SELECT ual.*, u.name as user_name FROM user_activity_logs_unified ual LEFT JOIN users u ON ual.user_id = u.id ORDER BY ual.created_at DESC LIMIT 10";
            return $this->db->fetchAll($sql);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get admin analytics
     * @return array Admin analytics
     */
    private function getAdminAnalytics()
    {
        return [
            'user_growth' => $this->getUserGrowthData(),
            'property_stats' => $this->getPropertyStats(),
            'revenue_data' => $this->getRevenueData(),
            'system_health' => $this->getSystemHealth()
        ];
    }

    /**
     * Get manager analytics
     * @return array Manager analytics
     */
    private function getManagerAnalytics()
    {
        return [
            'team_performance' => $this->getTeamPerformanceData(),
            'property_sales' => $this->getPropertySalesData(),
            'client_satisfaction' => $this->getClientSatisfactionData(),
            'target_achievement' => $this->getTargetAchievementData()
        ];
    }

    /**
     * Get associate analytics
     * @return array Associate analytics
     */
    private function getAssociateAnalytics()
    {
        return [
            'sales_performance' => $this->getSalesPerformanceData(),
            'client_conversion' => $this->getClientConversionData(),
            'commission_earned' => $this->getCommissionEarnedData(),
            'lead_conversion' => $this->getLeadConversionData()
        ];
    }

    /**
     * Get user analytics
     * @return array User analytics
     */
    private function getUserAnalytics()
    {
        return [
            'property_views' => $this->getPropertyViewsData(),
            'search_patterns' => $this->getSearchPatternsData(),
            'booking_history' => $this->getBookingHistoryData(),
            'preferences' => $this->getPreferencesData()
        ];
    }

    /**
     * Get guest analytics
     * @return array Guest analytics
     */
    private function getGuestAnalytics()
    {
        return [
            'popular_properties' => $this->getPopularPropertiesData(),
            'trending_locations' => $this->getTrendingLocationsData(),
            'market_insights' => $this->getMarketInsightsData(),
            'featured_listings' => $this->getFeaturedListingsData()
        ];
    }

    private function getTeamMemberCount()
    {
        try { return (int)$this->db->fetch("SELECT COUNT(*) c FROM users WHERE role IN ('associate','agent','employee')")['c']; } catch (\Exception $e) { return 0; }
    }
    private function getManagerProperties()
    {
        try { return (int)$this->db->fetch("SELECT COUNT(*) c FROM properties WHERE status='active'")['c']; } catch (\Exception $e) { return 0; }
    }
    private function getManagerReports()
    {
        try { return (int)$this->db->fetch("SELECT COUNT(*) c FROM reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['c']; } catch (\Exception $e) { return 0; }
    }
    private function getAssociateProperties()
    {
        try { $uid = $_SESSION['user_id'] ?? 0; return (int)$this->db->fetch("SELECT COUNT(*) c FROM user_properties WHERE user_id=?", [$uid])['c']; } catch (\Exception $e) { return 0; }
    }
    private function getAssociateClients()
    {
        try { $uid = $_SESSION['user_id'] ?? 0; $tid = (int)$this->tenantId(); $tidSql = $tid > 1 ? ' AND tenant_id = ?' : ''; $tidP = $tid > 1 ? [$tid] : []; return (int)$this->db->fetch("SELECT COUNT(*) c FROM leads WHERE assigned_to=?{$tidSql}", array_merge([$uid], $tidP))['c']; } catch (\Exception $e) { return 0; }
    }
    private function getAssociateCommissions()
    {
        try { $uid = $_SESSION['user_id'] ?? 0; return (float)($this->db->fetch("SELECT COALESCE(SUM(amount),0) c FROM mlm_commission_ledger WHERE beneficiary_user_id=?", [$uid])['c'] ?? 0); } catch (\Exception $e) { return 0; }
    }
    private function getAssociateLeads()
    {
        try { $uid = $_SESSION['user_id'] ?? 0; $tid = (int)$this->tenantId(); $tidSql = $tid > 1 ? ' AND tenant_id = ?' : ''; $tidP = $tid > 1 ? [$tid] : []; return (int)$this->db->fetch("SELECT COUNT(*) c FROM leads WHERE assigned_to=?{$tidSql}", array_merge([$uid], $tidP))['c']; } catch (\Exception $e) { return 0; }
    }
    private function getUserSavedProperties()
    {
        try { $uid = $_SESSION['user_id'] ?? 0; return (int)$this->db->fetch("SELECT COUNT(*) c FROM saved_searches WHERE user_id=?", [$uid])['c']; } catch (\Exception $e) { return 0; }
    }
    private function getUserSearchHistory()
    {
        try { $uid = $_SESSION['user_id'] ?? 0; return (int)$this->db->fetch("SELECT COUNT(*) c FROM saved_searches WHERE user_id=?", [$uid])['c']; } catch (\Exception $e) { return 0; }
    }
    private function getUserBookings()
    {
        try { $uid = $_SESSION['user_id'] ?? 0; return (int)$this->db->fetch("SELECT COUNT(*) c FROM plot_bookings WHERE customer_id=?", [$uid])['c']; } catch (\Exception $e) { return 0; }
    }
    private function getFeaturedProperties()
    {
        try { return (int)$this->db->fetch("SELECT COUNT(*) c FROM properties WHERE status='active' AND is_featured=1")['c']; } catch (\Exception $e) { return 0; }
    }
    private function getRecentProperties()
    {
        try { return (int)$this->db->fetch("SELECT COUNT(*) c FROM properties WHERE status='active' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['c']; } catch (\Exception $e) { return 0; }
    }
    private function getPopularLocations()
    {
        try { return (int)$this->db->fetch("SELECT COUNT(DISTINCT location) c FROM properties WHERE status='active' AND location IS NOT NULL AND location != ''")['c']; } catch (\Exception $e) { return 0; }
    }
    private function getUserGrowthData()
    {
        try {
            $rows = $this->db->fetchAll("SELECT DATE(created_at) as day, COUNT(*) as cnt FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC");
            return ['labels' => array_map(fn($d) => date('d M', strtotime($d['day'])), $rows), 'data' => array_column($rows, 'cnt')];
        } catch (\Exception $e) { return ['labels' => [], 'data' => []]; }
    }
    private function getPropertyStats()
    {
        try {
            $total = (int)$this->db->fetch("SELECT COUNT(*) c FROM properties")['c'];
            $active = (int)$this->db->fetch("SELECT COUNT(*) c FROM properties WHERE status='active'")['c'];
            $sold = (int)$this->db->fetch("SELECT COUNT(*) c FROM properties WHERE status='sold'")['c'];
            return ['total' => $total, 'active' => $active, 'sold' => $sold, 'inactive' => $total - $active - $sold];
        } catch (\Exception $e) { return ['total' => 0, 'active' => 0, 'sold' => 0, 'inactive' => 0]; }
    }
    private function getRevenueData()
    {
        try {
            $rows = $this->db->fetchAll("SELECT DATE(created_at) as day, COALESCE(SUM(amount),0) as total FROM payment_transactions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC");
            return ['labels' => array_map(fn($d) => date('d M', strtotime($d['day'])), $rows), 'data' => array_column($rows, 'total')];
        } catch (\Exception $e) { return ['labels' => [], 'data' => []]; }
    }
    private function getSystemHealth()
    {
        return ['cpu' => rand(20, 60), 'memory' => rand(40, 80), 'disk' => rand(30, 70), 'uptime' => '99.9%'];
    }
    private function getTeamPerformanceData()
    {
        try {
            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? " AND l.tenant_id = ?" : "";
            $tidParam = $tid > 1 ? [$tid] : [];
            $rows = $this->db->fetchAll("SELECT u.name, COUNT(l.id) as leads, SUM(CASE WHEN l.status='converted' THEN 1 ELSE 0 END) as conversions FROM users u LEFT JOIN leads l ON l.assigned_to=u.id WHERE u.role IN ('associate','agent'){$tidSql} GROUP BY u.id ORDER BY leads DESC LIMIT 10", $tidParam);
            return ['members' => $rows];
        } catch (\Exception $e) { return ['members' => []]; }
    }
    private function getPropertySalesData()
    {
        try {
            $rows = $this->db->fetchAll("SELECT DATE(created_at) as day, COUNT(*) as cnt FROM plot_bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC");
            return ['labels' => array_map(fn($d) => date('d M', strtotime($d['day'])), $rows), 'data' => array_column($rows, 'cnt')];
        } catch (\Exception $e) { return ['labels' => [], 'data' => []]; }
    }
    private function getClientSatisfactionData()
    {
        try { $nps = (int)($this->db->fetch("SELECT COALESCE(AVG(rating),0) c FROM testimonials WHERE status='approved'")['c'] ?? 0); return ['nps' => $nps, 'total_reviews' => (int)$this->db->fetch("SELECT COUNT(*) c FROM testimonials WHERE status='approved'")['c']]; } catch (\Exception $e) { return ['nps' => 0, 'total_reviews' => 0]; }
    }
    private function getTargetAchievementData()
    {
        try {
            $target = (float)($this->db->fetch("SELECT COALESCE(SUM(target_amount),0) c FROM sales_targets WHERE MONTH(target_date)=MONTH(NOW()) AND YEAR(target_date)=YEAR(NOW())")['c'] ?? 0);
            $actual = (float)($this->db->fetch("SELECT COALESCE(SUM(amount),0) c FROM payment_transactions WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")['c'] ?? 0);
            return ['target' => $target, 'actual' => $actual, 'percentage' => $target > 0 ? round(($actual / $target) * 100) : 0];
        } catch (\Exception $e) { return ['target' => 0, 'actual' => 0, 'percentage' => 0]; }
    }
    private function getSalesPerformanceData()
    {
        try {
            $uid = $_SESSION['user_id'] ?? 0;
            $total = (float)($this->db->fetch("SELECT COALESCE(SUM(amount),0) c FROM mlm_commission_ledger WHERE beneficiary_user_id=?", [$uid])['c'] ?? 0);
            $month = (float)($this->db->fetch("SELECT COALESCE(SUM(amount),0) c FROM mlm_commission_ledger WHERE beneficiary_user_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [$uid])['c'] ?? 0);
            return ['total_earned' => $total, 'month_earned' => $month];
        } catch (\Exception $e) { return ['total_earned' => 0, 'month_earned' => 0]; }
    }
    private function getClientConversionData()
    {
        try {
            $uid = $_SESSION['user_id'] ?? 0;
            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $tidParam = $tid > 1 ? [$tid] : [];
            $total = (int)$this->db->fetch("SELECT COUNT(*) c FROM leads WHERE assigned_to=?{$tidSql}", array_merge([$uid], $tidParam))['c'];
            $converted = (int)$this->db->fetch("SELECT COUNT(*) c FROM leads WHERE assigned_to=? AND status='converted'{$tidSql}", array_merge([$uid], $tidParam))['c'];
            return ['total' => $total, 'converted' => $converted, 'rate' => $total > 0 ? round(($converted / $total) * 100) : 0];
        } catch (\Exception $e) { return ['total' => 0, 'converted' => 0, 'rate' => 0]; }
    }
    private function getCommissionEarnedData()
    {
        try {
            $uid = $_SESSION['user_id'] ?? 0;
            $rows = $this->db->fetchAll("SELECT DATE(created_at) as day, SUM(amount) as total FROM mlm_commission_ledger WHERE beneficiary_user_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC", [$uid]);
            return ['labels' => array_map(fn($d) => date('d M', strtotime($d['day'])), $rows), 'data' => array_column($rows, 'total')];
        } catch (\Exception $e) { return ['labels' => [], 'data' => []]; }
    }
    private function getLeadConversionData()
    {
        try {
            $tid = (int)$this->tenantId();
            $tidSql = $tid > 1 ? ' WHERE tenant_id = ?' : '';
            $tidParam = $tid > 1 ? [$tid] : [];
            $rows = $this->db->fetchAll("SELECT status, COUNT(*) as cnt FROM leads{$tidSql} GROUP BY status ORDER BY cnt DESC", $tidParam);
            return ['labels' => array_column($rows, 'status'), 'data' => array_column($rows, 'cnt')];
        } catch (\Exception $e) { return ['labels' => [], 'data' => []]; }
    }
    private function getPropertyViewsData()
    {
        try {
            $rows = $this->db->fetchAll("SELECT DATE(created_at) as day, COUNT(*) as cnt FROM property_views WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC");
            return ['labels' => array_map(fn($d) => date('d M', strtotime($d['day'])), $rows), 'data' => array_column($rows, 'cnt')];
        } catch (\Exception $e) { return ['labels' => [], 'data' => []]; }
    }
    private function getSearchPatternsData()
    {
        try {
            $rows = $this->db->fetchAll("SELECT search_term, COUNT(*) as cnt FROM saved_searches WHERE search_term IS NOT NULL GROUP BY search_term ORDER BY cnt DESC LIMIT 10");
            return ['terms' => array_column($rows, 'search_term'), 'counts' => array_column($rows, 'cnt')];
        } catch (\Exception $e) { return ['terms' => [], 'counts' => []]; }
    }
    private function getBookingHistoryData()
    {
        try {
            $uid = $_SESSION['user_id'] ?? 0;
            $rows = $this->db->fetchAll("SELECT DATE(created_at) as day, COUNT(*) as cnt FROM plot_bookings WHERE customer_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC", [$uid]);
            return ['labels' => array_map(fn($d) => date('d M', strtotime($d['day'])), $rows), 'data' => array_column($rows, 'cnt')];
        } catch (\Exception $e) { return ['labels' => [], 'data' => []]; }
    }
    private function getPreferencesData()
    {
        return ['notifications' => true, 'email_alerts' => true, 'sms_alerts' => false];
    }
    private function getPopularPropertiesData()
    {
        try {
            $rows = $this->db->fetchAll("SELECT title, price, location FROM properties WHERE status='active' ORDER BY views DESC LIMIT 5");
            return $rows;
        } catch (\Exception $e) { return []; }
    }
    private function getTrendingLocationsData()
    {
        try {
            $rows = $this->db->fetchAll("SELECT location, COUNT(*) as cnt FROM properties WHERE status='active' AND location IS NOT NULL GROUP BY location ORDER BY cnt DESC LIMIT 5");
            return $rows;
        } catch (\Exception $e) { return []; }
    }
    private function getMarketInsightsData()
    {
        try {
            $avgPrice = (float)($this->db->fetch("SELECT COALESCE(AVG(price),0) c FROM properties WHERE status='active'")['c'] ?? 0);
            $totalProperties = (int)$this->db->fetch("SELECT COUNT(*) c FROM properties WHERE status='active'")['c'];
            return ['avg_price' => $avgPrice, 'total_properties' => $totalProperties, 'market_trend' => 'stable'];
        } catch (\Exception $e) { return ['avg_price' => 0, 'total_properties' => 0, 'market_trend' => 'unknown']; }
    }
    private function getFeaturedListingsData()
    {
        try {
            $rows = $this->db->fetchAll("SELECT id, title, price, location, image FROM properties WHERE status='active' AND is_featured=1 LIMIT 5");
            return $rows;
        } catch (\Exception $e) { return []; }
    }

    public function getPerformanceData($role = null)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'role' => $role,
            'data' => [
                'leads' => 0,
                'conversions' => 0,
                'revenue' => 0,
                'properties' => 0
            ]
        ]);
        exit;
    }

    public function getAnalytics($role = null)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'role' => $role,
            'data' => [
                'views' => 0,
                'inquiries' => 0,
                'bookings' => 0
            ]
        ]);
        exit;
    }

    /**
     * Get network tree (API)
     */
    public function getNetworkTree()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => []]);
        exit;
    }

    /**
     * Get revenue analytics (API)
     */
    public function getRevenueAnalytics()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => ['total_revenue' => 0, 'monthly' => []]]);
        exit;
    }

    /**
     * Get team performance (was private, now public for API route)
     */
    public function getTeamPerformance()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => ['members' => 0, 'performance' => []]]);
        exit;
    }

    /**
     * Get financial analytics (API)
     */
    public function getFinancialAnalytics()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => ['total_revenue' => 0, 'expenses' => 0, 'profit' => 0]]);
        exit;
    }

    /**
     * Get expense breakdown (API)
     */
    public function getExpenseBreakdown()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => ['categories' => []]]);
        exit;
    }

    /**
     * Get construction analytics (API)
     */
    public function getConstructionAnalytics()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => ['projects' => 0, 'milestones' => []]]);
        exit;
    }

    /**
     * Get material status (API)
     */
    public function getMaterialStatus()
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => ['materials' => []]]);
        exit;
    }
}
