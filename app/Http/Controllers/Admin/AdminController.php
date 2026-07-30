<?php

/**
 * Admin Controller
 * Handles admin dashboard, property management, user management, and settings
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Cache;
use App\Models\Property;
use App\Models\User;
use \App\Traits\TenantAwareTrait;

use Exception;

class AdminController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        // Set admin layout
        $this->layout = 'layouts/admin';

        // Initialize data array for view rendering
        $this->data = [];
    }

    /**
     * Enterprise Dashboard
     */
    public function enterpriseDashboard()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        error_log("enterpriseDashboard: admin_id=" . ($_SESSION['admin_id'] ?? 'NOT SET') . ", admin_role=" . ($_SESSION['admin_role'] ?? 'NOT SET') . ", role=" . ($_SESSION['role'] ?? 'NOT SET') . ", session_id=" . session_id());

        // Check if admin is logged in — allow any role with RBAC menu permissions
        if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'] ?? $_SESSION['role'] ?? '', self::ADMIN_ROLES)) {
            error_log("enterpriseDashboard: FAILED auth check, redirecting to login");
            $_SESSION['error'] = 'Admin access required';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        try {
            // Get dashboard statistics
            $stats = [
                'total_users' => $this->getTotalUsers(),
                'total_properties' => $this->getTotalProperties(),
                'total_inquiries' => $this->getTotalInquiries(),
                'total_revenue' => $this->getTotalRevenue(),
                'active_properties' => $this->getActiveProperties(),
                'new_users_today' => $this->getNewUsersToday(),
                'pending_approvals' => $this->getPendingApprovals(),
                'system_health' => $this->getSystemHealth()
            ];

            // Get recent activities
            $recentActivities = $this->getRecentActivities();

            // Get charts data
            $chartsData = $this->getChartsData();

            // Get recent leads for dashboard widget
            $recentLeads = [];
            try {
                $tid = (int)$this->tenantId();
                $tidSql = $tid > 1 ? " AND tenant_id = $tid" : "";
                $recentLeads = $this->db->fetchAll("SELECT * FROM leads WHERE 1=1{$tidSql} ORDER BY created_at DESC LIMIT 5") ?: [];
            } catch (\Exception $e) {
                $recentLeads = [];
            }

            $this->data = array_merge($this->data, [
                'stats' => $stats,
                'recent_activities' => $recentActivities,
                'charts_data' => $chartsData,
                'recent_leads' => $recentLeads,
                'page_title' => 'Enterprise Dashboard - ' . $this->getConfig('app_name'),
                'page_description' => 'SuperAdmin Control Center'
            ]);

            return $this->render('admin/dashboard', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading enterprise dashboard: ' . $e->getMessage());
            return $this->render('admin/dashboard', [
                'page_title' => 'Enterprise Dashboard - ' . $this->getConfig('app_name'),
                'error' => true
            ]);
        }
    }

    /**
     * Admin Dashboard — redirects to Unified ERP Overview
     */
    public function dashboard()
    {
        $this->redirect(BASE_URL . '/admin/erp');
        return;
    }

    /**
     * Unified ERP Overview Dashboard
     * Shows ALL 5 modules on one page: Land, Sales, Money, MLM, Backoffice
     */
    public function erpOverview()
    {
        $this->requireAdmin();

        $stats = [];

        // System Overview Stats
        try {
            $pdo = $this->db->getConnection();
            $stats['database_tables'] = (int)($pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn() ?? 0);
        } catch (\Exception $e) { $stats['database_tables'] = 0; }

        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $totalUsers = (int)($this->db->fetch("SELECT COUNT(*) AS cnt FROM users WHERE role IN ('admin','super_admin','manager','employee','telecaller','associate','agent','customer'){$tidSql}", $tidParams)['cnt'] ?? 0);
            $activeUsers = (int)($this->db->fetch("SELECT COUNT(*) AS cnt FROM users WHERE role IN ('admin','super_admin','manager','employee','telecaller','associate','agent','customer') AND is_active = 1{$tidSql}", $tidParams)['cnt'] ?? 0);
            $stats['total_users'] = $totalUsers;
            $stats['active_users'] = $activeUsers;
            $stats['active_users_pct'] = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0;
        } catch (\Exception $e) { 
            $stats['total_users'] = 0;
            $stats['active_users'] = 0;
            $stats['active_users_pct'] = 0;
        }

        // System Health - based on error logs in last 24h
        try {
            $totalLogs = (int)($this->db->fetch("SELECT COUNT(*) AS cnt FROM user_activity_logs_unified WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")['cnt'] ?? 0);
            $errorLogs = (int)($this->db->fetch("SELECT COUNT(*) AS cnt FROM user_activity_logs_unified WHERE action LIKE '%error%' OR action LIKE '%fail%' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")['cnt'] ?? 0);
            $stats['system_health_pct'] = $totalLogs > 0 ? max(0, 100 - round(($errorLogs / $totalLogs) * 100, 1)) : 99.9;
        } catch (\Exception $e) {
            $stats['system_health_pct'] = 99.9;
        }
        try {
            $stats['land_active_leads'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM land_leads WHERE status NOT IN ('acquired','rejected')")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['land_active_leads'] = 0; }

        try {
            $stats['land_acquisitions'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM land_acquisitions")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['land_acquisitions'] = 0; }

        // Module 2: Sales + Allotment
        try {
            $stats['sales_active_bookings'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM plot_bookings WHERE status NOT IN ('cancelled','transferred')")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['sales_active_bookings'] = 0; }

        try {
            $stats['sales_booking_value'] = (float) ($this->db->fetch("SELECT COALESCE(SUM(total_plot_value),0) AS total FROM plot_bookings WHERE status NOT IN ('cancelled','transferred')")['total'] ?? 0);
        } catch (\Exception $e) { $stats['sales_booking_value'] = 0; }

        // Module 3: Money Workflow + Accounting
        try {
            $stats['money_today_collections'] = (float) ($this->db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM daily_cash_book WHERE transaction_date = CURDATE() AND transaction_type='receipt'")['total'] ?? 0);
        } catch (\Exception $e) { $stats['money_today_collections'] = 0; }

        try {
            $stats['money_today_payments'] = (float) ($this->db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM daily_cash_book WHERE transaction_date = CURDATE() AND transaction_type='payment'")['total'] ?? 0);
        } catch (\Exception $e) { $stats['money_today_payments'] = 0; }

        try {
            $stats['money_total_cash_flow'] = (float) ($this->db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM daily_cash_book")['total'] ?? 0);
        } catch (\Exception $e) { $stats['money_total_cash_flow'] = 0; }

        try {
            $stats['money_bounced_cheques'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM cheque_register WHERE status='bounced'")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['money_bounced_cheques'] = 0; }

        try {
            $stats['money_pending_tds'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM tds_register WHERE status='pending'")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['money_pending_tds'] = 0; }

        // EMI Dunning (Phase 30)
        try {
            $stats['emi_overdue_count'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM booking_payment_schedules WHERE status IN ('overdue','pending') AND due_date < DATE_SUB(CURDATE(), INTERVAL 5 DAY)")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['emi_overdue_count'] = 0; }

        try {
            $stats['emi_overdue_amount'] = (float) ($this->db->fetch("SELECT COALESCE(SUM(amount),0) AS total FROM booking_payment_schedules WHERE status IN ('overdue','pending') AND due_date < DATE_SUB(CURDATE(), INTERVAL 5 DAY)")['total'] ?? 0);
        } catch (\Exception $e) { $stats['emi_overdue_amount'] = 0; }

        try {
            $stats['emi_total_penalties'] = (float) ($this->db->fetch("SELECT COALESCE(SUM(accrued_penalty),0) AS total FROM booking_payment_schedules WHERE accrued_penalty > 0")['total'] ?? 0);
        } catch (\Exception $e) { $stats['emi_total_penalties'] = 0; }

        try {
            $stats['emi_defaulted_count'] = (int) ($this->db->fetch("SELECT COUNT(DISTINCT booking_id) AS cnt FROM booking_payment_schedules WHERE status IN ('overdue','pending') AND due_date < DATE_SUB(CURDATE(), INTERVAL 90 DAY)")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['emi_defaulted_count'] = 0; }

        // Module 4: MLM Network
        try {
            $stats['mlm_commissions_paid'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM mlm_commission_ledger WHERE status='paid' AND MONTH(created_at)=MONTH(CURDATE())")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['mlm_commissions_paid'] = 0; }

        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stats['mlm_pending_payouts'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM mlm_payouts WHERE status='pending'{$tidSql}", $tidParams)['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['mlm_pending_payouts'] = 0; }

        // Module 5: Backoffice + Daily Operations
        try {
            $stats['backoffice_active_leads'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM lead_pipeline WHERE status NOT IN ('closed_won','closed_lost')")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['backoffice_active_leads'] = 0; }

        try {
            $stats['backoffice_present_today'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM employee_attendance WHERE attendance_date=CURDATE() AND status='present'")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['backoffice_present_today'] = 0; }

        try {
            $stats['backoffice_pending_leaves'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM employee_leave_requests WHERE status='pending'")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['backoffice_pending_leaves'] = 0; }

        try {
            $stats['backoffice_today_operations'] = (int) ($this->db->fetch("SELECT COUNT(*) AS cnt FROM daily_operations_log WHERE log_date=CURDATE()")['cnt'] ?? 0);
        } catch (\Exception $e) { $stats['backoffice_today_operations'] = 0; }

        // Recent activity feed (last 10 from daily_operations_log + daily_cash_book combined)
        $recentActivity = [];
        try {
            $recentActivity = $this->db->fetchAll("
                SELECT 'operation' AS source, id, log_date AS activity_date, operation_type AS type, description, status
                FROM daily_operations_log
                UNION ALL
                SELECT 'finance' AS source, id, transaction_date AS activity_date, transaction_type AS type, description, transaction_mode AS status
                FROM daily_cash_book
                ORDER BY activity_date DESC
                LIMIT 10
            ") ?? [];
        } catch (\Exception $e) { $recentActivity = []; }

        // Cash flow chart data (last 7 days)
        $cashFlowChart = [];
        try {
            $cashFlowChart = $this->db->fetchAll("
                SELECT transaction_date,
                       COALESCE(SUM(CASE WHEN transaction_type='receipt' THEN amount ELSE 0 END), 0) AS receipts,
                       COALESCE(SUM(CASE WHEN transaction_type='payment' THEN amount ELSE 0 END), 0) AS payments
                FROM daily_cash_book
                WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY transaction_date
                ORDER BY transaction_date ASC
            ") ?? [];
        } catch (\Exception $e) { $cashFlowChart = []; }

        // Lead pipeline chart data
        $leadPipelineChart = [];
        try {
            $leadPipelineChart = $this->db->fetchAll("
                SELECT status, COUNT(*) AS cnt
                FROM lead_pipeline
                GROUP BY status
                ORDER BY cnt DESC
            ") ?? [];
        } catch (\Exception $e) { $leadPipelineChart = []; }

        return $this->render('admin/erp/overview', [
            'page_title' => 'ERP Overview — APS Dream Home',
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'cash_flow_chart' => $cashFlowChart,
            'lead_pipeline_chart' => $leadPipelineChart,
            'updated_at' => date('d M Y, h:i A'),
        ]);
    }

    /**
     * Get total users count (cached 5 min)
     */
    private function getTotalUsers()
    {
        try {
            return Cache::remember('admin_dash_total_users', function () {
                $tid = (int)$this->tenantId();
                $tidWhere = $tid > 1 ? " WHERE tenant_id = ?" : "";
                return $this->db->fetch("SELECT COUNT(*) as count FROM users{$tidWhere}", $tid > 1 ? [$tid] : [])['count'] ?? 0;
            }, 300);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get total properties count (cached 5 min)
     */
    private function getTotalProperties()
    {
        try {
            return Cache::remember('admin_dash_total_properties', function () {
                return $this->db->fetch("SELECT COUNT(*) as count FROM properties")['count'] ?? 0;
            }, 300);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get total inquiries count (cached 5 min)
     */
    private function getTotalInquiries()
    {
        try {
            return Cache::remember('admin_dash_total_inquiries', function () {
                return $this->db->fetch("SELECT COUNT(*) as count FROM inquiries")['count'] ?? 0;
            }, 300);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get total revenue (cached 5 min)
     */
    private function getTotalRevenue()
    {
        try {
            return Cache::remember('admin_dash_total_revenue', function () {
                return $this->db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'")['total'] ?? 0;
            }, 300);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get active properties count (cached 5 min)
     */
    private function getActiveProperties()
    {
        try {
            return Cache::remember('admin_dash_active_properties', function () {
                return $this->db->fetch("SELECT COUNT(*) as count FROM properties WHERE status = 'active'")['count'] ?? 0;
            }, 300);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get new users today (cached 2 min - changes more frequently)
     */
    private function getNewUsersToday()
    {
        try {
            return Cache::remember('admin_dash_new_users_today', function () {
                $tid = (int)$this->tenantId();
                $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
                return $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE(){$tidSql}", $tid > 1 ? [$tid] : [])['count'] ?? 0;
            }, 120);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get pending approvals (cached 3 min)
     */
    private function getPendingApprovals()
    {
        try {
            return Cache::remember('admin_dash_pending_approvals', function () {
                return $this->db->fetch("SELECT COUNT(*) as count FROM properties WHERE status = 'pending'")['count'] ?? 0;
            }, 180);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get system health status
     */
    private function getSystemHealth()
    {
        return [
            'database' => 'Healthy',
            'server' => 'Optimal',
            'storage' => '78% Used',
            'memory' => '62% Used'
        ];
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities()
    {
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            return $this->db->fetchAll("
                SELECT 'user' as type, name, created_at as date, 'registered' as action 
                FROM users 
                WHERE DATE(created_at) = CURDATE(){$tidSql}
                ORDER BY created_at DESC 
                LIMIT 5
            ", $tidParams);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get dashboard stats (API endpoint)
     */
    public function getStats()
    {
        header('Content-Type: application/json');
        try {
            $stats = [
                'total_users' => Cache::remember('admin_api_total_users', function () {
                    $tid = (int)$this->tenantId();
                    $tidWhere = $tid > 1 ? " WHERE tenant_id = ?" : "";
                    return $this->db->fetch("SELECT COUNT(*) as c FROM users{$tidWhere}", $tid > 1 ? [$tid] : [])['c'] ?? 0;
                }, 300),
                'total_properties' => Cache::remember('admin_api_total_properties', function () {
                    return $this->db->fetch("SELECT COUNT(*) as c FROM properties")['c'] ?? 0;
                }, 300),
                'total_leads' => Cache::remember('admin_api_total_leads_' . ($this->tenantId()), function () {
                    $tid = (int)$this->tenantId();
                    $tidSql = $tid > 1 ? " WHERE tenant_id = $tid" : "";
                    return $this->db->fetch("SELECT COUNT(*) as c FROM leads{$tidSql}")['c'] ?? 0;
                }, 300),
                'pending_bookings' => Cache::remember('admin_api_pending_bookings', function () {
                    return $this->db->fetch("SELECT COUNT(*) as c FROM bookings WHERE status='pending'")['c'] ?? 0;
                }, 180),
            ];
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Get charts data for dashboard
     */
    private function getChartsData()
    {
        // User registrations per month (last 6 months)
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $userData = $this->db->fetchAll("
                SELECT DATE_FORMAT(created_at, '%b') as label, COUNT(*) as count
                FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH){$tidSql}
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY MIN(created_at)
            ", $tidParams);
        } catch (\Exception $e) {
            $userData = [];
        }
        $userLabels = [];
        $userCounts = [];
        foreach ($userData as $row) {
            $userLabels[] = $row['label'];
            $userCounts[] = (int)$row['count'];
        }
        if (empty($userLabels)) {
            $userLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            $userCounts = [0, 0, 0, 0, 0, 0];
        }

        // Bookings per month (last 6 months)
        try {
            $bookingData = $this->db->fetchAll("
                SELECT DATE_FORMAT(created_at, '%b') as label, COUNT(*) as count
                FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY MIN(created_at)
            ");
        } catch (\Exception $e) {
            $bookingData = [];
        }
        $bookingLabels = [];
        $bookingCounts = [];
        foreach ($bookingData as $row) {
            $bookingLabels[] = $row['label'];
            $bookingCounts[] = (int)$row['count'];
        }
        if (empty($bookingLabels)) {
            $bookingLabels = $userLabels;
            $bookingCounts = array_fill(0, count($userLabels), 0);
        }

        // Revenue per month from bookings (last 6 months)
        try {
            $revenueData = $this->db->fetchAll("
                SELECT DATE_FORMAT(b.created_at, '%b') as label, COALESCE(SUM(bp.amount), 0) as total
                FROM bookings b
                LEFT JOIN booking_payments bp ON bp.booking_id = b.id
                WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(b.created_at, '%Y-%m') ORDER BY MIN(b.created_at)
            ");
        } catch (\Exception $e) {
            $revenueData = [];
        }
        $revenueLabels = [];
        $revenueTotals = [];
        foreach ($revenueData as $row) {
            $revenueLabels[] = $row['label'];
            $revenueTotals[] = (float)$row['total'];
        }
        if (empty($revenueLabels)) {
            $revenueLabels = $userLabels;
            $revenueTotals = array_fill(0, count($userLabels), 0);
        }

        return [
            'user_registrations' => ['labels' => $userLabels, 'data' => $userCounts],
            'property_views' => ['labels' => $bookingLabels, 'data' => $bookingCounts],
            'revenue' => ['labels' => $revenueLabels, 'data' => $revenueTotals]
        ];
    }

    /**
     * Reports page
     */
    public function reports()
    {
        $reports = $this->getChartsData();

        return $this->render('admin/reports', [
            'page_title' => 'Reports & Analytics - APS Dream Home',
            'page_description' => 'View system reports and analytics',
            'reports' => $reports
        ]);
    }

    /**
     * Get users list
     */
    public function getUsersList()
    {
        try {
            $tid = (int)$this->tenantId();
            $tidWhere = $tid > 1 ? " WHERE tenant_id = ?" : "";
            return $this->db->fetchAll("SELECT id, name, email, role, status, created_at FROM users{$tidWhere} ORDER BY created_at DESC LIMIT 50", $tid > 1 ? [$tid] : []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * JSON response helper
     */
    public function jsonResponse($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error response helper
     */
    protected function jsonError($message, $statusCode = 400)
    {
        $this->jsonResponse(['success' => false, 'message' => $message], $statusCode);
    }

    /**
     * Flash message helper (compatibility wrapper)
     * Usage: $this->flashMessage('Saved!', 'success')
     * Stores in $_SESSION['flash_success'] / $_SESSION['flash_error'] for layout rendering
     */
    protected function flashMessage(string $message, string $type = 'info'): void
    {
        $_SESSION['flash_' . $type] = $message;
    }

    /**
     * Validate CSRF token or die with 403
     */
    protected function validateCsrfOrFail(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
            exit;
        }
    }

    /**
     * Update page content (AJAX)
     */
    public function updatePageContent($id, $content)
    {
        // For now, satisfy the call. In a production environment,
        // this would update a database or storage.
        try {
            // Log the update if needed
            // error_log("Updating content for ID: $id");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * User Network Management
     */
    public function users()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // Check if admin is logged in
        if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id']) || !in_array($_SESSION['admin_role'] ?? $_SESSION['role'] ?? '', self::ADMIN_ROLES)) {
            $_SESSION['error'] = 'Admin access required';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        // Get users list
        $users = $this->getUsersList();

        // Load users view
        //require_once APP_PATH . '/views/admin/users.php';
        $this->render('admin/modules/accounts/users', [
            'page_title' => 'User Network - APS Dream Home',
            'page_description' => 'Manage all users in the APS Dream Home network',
            'users' => $users
        ]);
    }

    /**
     * Properties Management
     */
    public function properties()
    {
        $this->requireAdmin();
        $properties = $this->getPropertiesList();
        $this->render('admin/properties/index', [
            'page_title' => 'Properties - Admin',
            'properties' => $properties,
        ], 'layouts/admin');
    }

    /**
     * Get properties list
     */
    public function getPropertiesList()
    {
        try {
            return $this->db->fetchAll("SELECT id, title, location, price, status, featured, created_at FROM properties ORDER BY created_at DESC LIMIT 50");
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Require admin authentication.
     * Also enforces per-route RBAC: non-admin roles are checked against admin_role_menu_permissions
     * using the current request URI matched to admin_menu_items.url.
     * super_admin and admin always pass. Unknown URLs (not in menu table) pass by default.
     */
    public function requireAdmin()
    {
        // Allow any role with RBAC menu permissions — sidebar handles item-level filtering
        $role = $_SESSION['role'] ?? $_SESSION['admin_role'] ?? '';
        if (!$this->isLoggedIn() || !in_array($role, self::ADMIN_ROLES)) {
            $this->setFlash('error', 'Admin access required');
            $this->redirect('/admin/login');
            return;
        }

        // Per-route RBAC: check current URL against admin_role_menu_permissions
        // super_admin and admin always pass; others are checked
        if (!in_array($role, ['super_admin', 'admin'])) {
            $url = $this->getCurrentMenuUrl();
            if ($url !== null && !$this->checkMenuPermission($url, $role)) {
                $this->setFlash('error', 'You do not have permission to access this page.');
                $this->redirect('/admin/dashboard');
            }
        }
    }

    /**
     * Extract the current request path (relative to BASE_URL) for RBAC matching.
     * Strips query string and leading/trailing slashes.
     *
     * @return string|null Normalized path (e.g. '/admin/finance/cash-book'), or null if not parseable
     */
    private function getCurrentMenuUrl(): ?string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        // Strip query string
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        // Strip BASE_URL prefix if present
        $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        if ($baseUrl && strpos($path, $baseUrl) === 0) {
            $path = substr($path, strlen($baseUrl));
        }
        // Normalize: ensure starts with /, strip trailing slash (except root)
        $path = '/' . ltrim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        return $path;
    }

    /**
     * Check if a role has permission for a specific menu URL.
     * super_admin and admin always return true.
     *
     * @param string $url  The menu item URL to check (e.g. '/admin/finance/cash-book')
     * @param string $role The user role to check (optional; reads from session if empty)
     * @return bool True if permitted, false otherwise
     */
    public function checkMenuPermission(string $url, string $role = ''): bool
    {
        if (empty($role)) {
            $role = $_SESSION['role'] ?? '';
        }
        if (in_array($role, ['super_admin', 'admin'])) {
            return true;
        }
        $cacheKey = "admin_menu_perm_" . md5($role . '|' . $url);
        return \App\Core\Cache::remember($cacheKey, function () use ($role, $url) {
            try {
                $db = $this->db ?? \App\Core\Database::getInstance()->getPdo();
                $stmt = $db->prepare(
                    "SELECT COUNT(*) FROM admin_role_menu_permissions rp
                     JOIN admin_menu_items mi ON mi.id = rp.menu_item_id
                     WHERE rp.role = ? AND mi.url = ? AND mi.is_active = 1"
                );
                $stmt->execute([$role, $url]);
                return (int)$stmt->fetchColumn() > 0;
            } catch (\Exception $e) {
                error_log('checkMenuPermission error: ' . $e->getMessage());
                return false;
            }
        }, 300);
    }

    public function devTools()
    {
        $this->requireAdmin();
        return $this->render('admin/dev-tools/index', ['page_title' => 'Developer Tools']);
    }

    /**
     * Cache management page
     */
    public function cache()
    {
        $this->requireAdmin();
        return $this->render('admin/cache', ['page_title' => 'Cache Management']);
    }

    /**
     * WhatsApp Integration
     */
    public function whatsappIntegration()
    {
        $this->requireAdmin();
        return $this->render('admin/whatsapp_integration', ['page_title' => 'WhatsApp Integration']);
    }

    /**
     * WhatsApp Web (QR scan)
     */
    public function whatsappWeb()
    {
        $this->requireAdmin();
        return $this->render('admin/whatsapp-web/index', ['page_title' => 'WhatsApp Web']);
    }

    /**
     * WhatsApp Web Manage (redirect to WhatsApp service)
     */
    public function whatsappWebManage()
    {
        $this->requireAdmin();
        header('Location: ' . WHATSAPP_SERVICE_URL);
        exit;
    }

    /**
     * Render a stub/placeholder admin page.
     */
    public function renderStub($title, $message = 'This section is under development.')
    {
        $this->requireAdmin();
        return $this->render('admin/stub_page', [
            'page_title' => $title,
            'page_message' => $message
        ]);
    }

    // --- Legacy stub redirects: map old "under development" URLs to real features ---
    // These were placeholder pages; the functionality now lives in dedicated modules.
    private const STUB_REDIRECTS = [
        'marketing-strategies'         => '/admin/mlm',
        'marketing-marketplace'        => '/admin/mlm',
        'agent-commission-rates'       => '/admin/commission/rules',
        'associate-commission-structure' => '/admin/commission',
        'associate-commission-calculations' => '/admin/commission/calculations',
        'commission-bonuses'            => '/admin/commission',
        'mlm-commission-levels'         => '/admin/mlm-settings/levels',
        'mlm-commission-records'        => '/admin/commission',
        'mlm-commission-analytics'      => '/admin/mlm',
        'daily-revenue'                 => '/admin/financial-reports',
        'telecaller-commission-rules'   => '/admin/commission/telecaller/commissions',
        'telecaller-commissions'        => '/admin/commission/telecaller/commissions',
        'mlm-rank-criteria'             => '/admin/mlm/ranks',
        'mlm-upgrades'                  => '/admin/mlm-rewards/upgrades',
        'mlm-withdrawals'               => '/admin/mlm-rewards/withdrawals',
        'mlm-rewards'                   => '/admin/mlm-rewards',
        'api-integrations'              => '/admin/api/integrations',
    ];

    public function stubRedirect()
    {
        $this->requireAdmin();
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '/';
        $uri = preg_replace('#^/apsdreamhome#', '', $uri);
        $key = ltrim($uri, '/');
        $key = preg_replace('#^admin/#', '', $key);
        $target = self::STUB_REDIRECTS[$key] ?? '/admin/dashboard';
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . $target);
        exit;
    }

    public function apiIntegrations()
    {
        $this->requireAdmin();
        header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/api/developers');
        exit;
    }

    /**
     * GET /admin/api-docs
     * Swagger UI rendered within the admin layout.
     */
    public function apiDocs()
    {
        $this->requireAdmin();
        try {
            $docService = new \App\Services\ApiDocService();
            $groups = $docService->getEndpoints();
            $total  = array_sum(array_map('count', $groups));
            $base   = BASE_URL;

            return $this->render('admin/api-docs', [
                'page_title'    => 'API Documentation',
                'page_heading'  => 'API Documentation',
                'groups'        => $groups,
                'total'         => $total,
                'specUrl'       => $base . '/api/docs/spec',
                'activeVersion' => 'v2',
            ]);
        } catch (\Exception $e) {
            error_log('ApiDocs error: ' . $e->getMessage());
            return $this->render('admin/stub_page', [
                'page_title'   => 'API Documentation',
                'page_message' => 'Error generating API docs: ' . $e->getMessage(),
            ]);
        }
    }
}
