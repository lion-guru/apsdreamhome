<?php
/**
 * Admin Dashboard Controller
 * Main dashboard for all admin roles
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Http\Middleware\RBACManager;

class AdminDashboardController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Main dashboard
     */
    public function index()
    {
        $this->data['active_page'] = 'dashboard';
        $this->data['page_title'] = 'Dashboard';
        $this->data['page_description'] = 'Welcome to your dashboard';
        
        $this->data['stats'] = $this->getDashboardStats();
        $this->data['menus'] = $this->getAdminMenu();
        $this->data['recent_activities'] = $this->getRecentActivities(5);
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * Super Admin Dashboard
     */
    public function superadmin()
    {
        $this->requirePermission('system.settings');
        
        $this->data['active_page'] = 'superadmin';
        $this->data['page_title'] = 'Super Admin Dashboard';
        $this->data['page_description'] = 'Full system overview and control';
        
        $this->data['stats'] = $this->getFullStats();
        $this->data['menus'] = $this->getAdminMenu();
        $this->data['system_health'] = $this->getSystemHealth();
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * Admin Dashboard
     */
    public function admin()
    {
        $this->requirePermission('dashboard.view');
        
        $this->data['active_page'] = 'admin';
        $this->data['page_title'] = 'Admin Dashboard';
        $this->data['menus'] = $this->getAdminMenu();
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * Manager Dashboard
     */
    public function manager()
    {
        $this->requirePermission('employee.view.team');
        
        $this->data['active_page'] = 'manager';
        $this->data['page_title'] = 'Manager Dashboard';
        $this->data['team_stats'] = $this->getTeamStats();
        $this->data['menus'] = $this->getAdminMenu();
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * Employee Dashboard
     */
    public function employee()
    {
        $this->data['active_page'] = 'employee';
        $this->data['page_title'] = 'Employee Dashboard';
        $this->data['my_tasks'] = $this->getMyTasks();
        $this->data['my_leads'] = $this->getMyLeads();
        $this->data['menus'] = $this->getAdminMenu();
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * Associate Dashboard (MLM)
     */
    public function associate()
    {
        $this->data['active_page'] = 'associate';
        $this->data['page_title'] = 'Associate Dashboard';
        $this->data['mlm_stats'] = $this->getMLMStats();
        $this->data['my_network'] = $this->getMyNetwork();
        $this->data['my_commissions'] = $this->getMyCommissions();
        $this->data['menus'] = $this->getAdminMenu();
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * Customer Dashboard
     */
    public function customer()
    {
        $this->data['active_page'] = 'customer';
        $this->data['page_title'] = 'My Dashboard';
        $this->data['my_bookings'] = $this->getMyBookings();
        $this->data['my_payments'] = $this->getMyPayments();
        $this->data['menus'] = $this->getAdminMenu();
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * Sales Dashboard
     */
    public function sales()
    {
        $this->requirePermission('leads.view.all');
        
        $this->data['active_page'] = 'sales';
        $this->data['page_title'] = 'Sales Dashboard';
        $this->data['sales_stats'] = $this->getSalesStats();
        $this->data['pipeline'] = $this->getSalesPipeline();
        $this->data['menus'] = $this->getAdminMenu();
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * Financial Dashboard
     */
    public function financial()
    {
        $this->requirePermission('financial.view.all');
        
        $this->data['active_page'] = 'financial';
        $this->data['page_title'] = 'Financial Dashboard';
        $this->data['financial_stats'] = $this->getFinancialStats();
        $this->data['menus'] = $this->getAdminMenu();
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * HR Dashboard
     */
    public function hr()
    {
        $this->requirePermission('employee.view.all');
        
        $this->data['active_page'] = 'hr';
        $this->data['page_title'] = 'HR Dashboard';
        $this->data['hr_stats'] = $this->getHRStats();
        $this->data['menus'] = $this->getAdminMenu();
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * Marketing Dashboard
     */
    public function marketing()
    {
        $this->requirePermission('marketing.view');
        
        $this->data['active_page'] = 'marketing';
        $this->data['page_title'] = 'Marketing Dashboard';
        $this->data['marketing_stats'] = $this->getMarketingStats();
        $this->data['menus'] = $this->getAdminMenu();
        
        $this->render('admin/dashboard', $this->data);
    }

    /**
     * Get full stats for super admin
     */
    protected function getFullStats(): array
    {
        $stats = $this->getDashboardStats();
        
        $stats['database_tables'] = $this->db->fetch("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()")['count'] ?? 0;
        $stats['active_users'] = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0;
        $stats['pending_tasks'] = $this->db->fetch("SELECT COUNT(*) as count FROM tasks WHERE status = 'pending'")['count'] ?? 0;
        $stats['system_logs'] = $this->db->fetch("SELECT COUNT(*) as count FROM logs WHERE DATE(created_at) = CURDATE()")['count'] ?? 0;
        
        return $stats;
    }

    /**
     * Get system health metrics
     */
    protected function getSystemHealth(): array
    {
        return [
            'database' => 'healthy',
            'cache' => 'healthy',
            'storage' => '80%',
            'memory' => '45%',
            'uptime' => '99.9%',
        ];
    }

    /**
     * Get team stats for managers
     */
    protected function getTeamStats(): array
    {
        $userId = $this->currentUser['id'] ?? 0;
        
        $stats = [];
        $stats['team_size'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM employees WHERE manager_id = ?",
            [$userId]
        )['count'] ?? 0;
        
        $stats['team_leads'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM leads WHERE assigned_to IN (SELECT id FROM employees WHERE manager_id = ?)",
            [$userId]
        )['count'] ?? 0;
        
        $stats['team_sales'] = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) as total FROM bookings WHERE assigned_to IN (SELECT id FROM employees WHERE manager_id = ?)",
            [$userId]
        )['total'] ?? 0;
        
        return $stats;
    }

    /**
     * Get MLM stats for associates
     */
    protected function getMLMStats(): array
    {
        $userId = $this->currentUser['id'] ?? 0;
        
        $stats = [];
        
        $stats['direct_referrals'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM users WHERE referrer_id = ?",
            [$userId]
        )['count'] ?? 0;
        
        $stats['total_downline'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM mlm_associates WHERE upline_id = ?",
            [$userId]
        )['count'] ?? 0;
        
        $stats['total_commission'] = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commissions WHERE user_id = ?",
            [$userId]
        )['total'] ?? 0;
        
        $stats['pending_payout'] = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) as total FROM mlm_payouts WHERE user_id = ? AND status = 'pending'",
            [$userId]
        )['total'] ?? 0;
        
        $stats['current_rank'] = $this->db->fetch(
            "SELECT rank_name FROM mlm_associates WHERE user_id = ?",
            [$userId]
        )['rank_name'] ?? 'Associate';
        
        return $stats;
    }

    /**
     * Get my network (downline tree)
     */
    protected function getMyNetwork(): array
    {
        $userId = $this->currentUser['id'] ?? 0;
        
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, ma.rank_name, ma.created_at 
             FROM users u 
             JOIN mlm_associates ma ON u.id = ma.user_id 
             WHERE ma.upline_id = ? 
             ORDER BY ma.created_at DESC",
            [$userId]
        ) ?? [];
    }

    /**
     * Get my commissions
     */
    protected function getMyCommissions(): array
    {
        $userId = $this->currentUser['id'] ?? 0;
        
        return $this->db->fetchAll(
            "SELECT * FROM mlm_commissions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
            [$userId]
        ) ?? [];
    }

    /**
     * Get my tasks
     */
    protected function getMyTasks(): array
    {
        $userId = $this->currentUser['id'] ?? 0;
        
        return $this->db->fetchAll(
            "SELECT * FROM tasks WHERE assigned_to = ? AND status != 'completed' ORDER BY due_date ASC LIMIT 10",
            [$userId]
        ) ?? [];
    }

    /**
     * Get my leads
     */
    protected function getMyLeads(): array
    {
        $userId = $this->currentUser['id'] ?? 0;
        
        return $this->db->fetchAll(
            "SELECT * FROM leads WHERE assigned_to = ? ORDER BY created_at DESC LIMIT 10",
            [$userId]
        ) ?? [];
    }

    /**
     * Get my bookings
     */
    protected function getMyBookings(): array
    {
        $userId = $this->currentUser['id'] ?? 0;
        
        return $this->db->fetchAll(
            "SELECT b.*, p.name as property_name 
             FROM bookings b 
             JOIN properties p ON b.property_id = p.id 
             WHERE b.user_id = ? 
             ORDER BY b.created_at DESC",
            [$userId]
        ) ?? [];
    }

    /**
     * Get my payments
     */
    protected function getMyPayments(): array
    {
        $userId = $this->currentUser['id'] ?? 0;
        
        return $this->db->fetchAll(
            "SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
            [$userId]
        ) ?? [];
    }

    /**
     * Get sales stats
     */
    protected function getSalesStats(): array
    {
        $stats = [];
        
        $stats['total_leads'] = $this->db->fetch("SELECT COUNT(*) as count FROM leads")['count'] ?? 0;
        $stats['hot_leads'] = $this->db->fetch("SELECT COUNT(*) as count FROM leads WHERE status = 'hot'")['count'] ?? 0;
        $stats['conversions'] = $this->db->fetch("SELECT COUNT(*) as count FROM leads WHERE status = 'converted'")['count'] ?? 0;
        $stats['conversion_rate'] = $stats['total_leads'] > 0 
            ? round(($stats['conversions'] / $stats['total_leads']) * 100, 1) 
            : 0;
        
        return $stats;
    }

    /**
     * Get sales pipeline
     */
    protected function getSalesPipeline(): array
    {
        return $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM leads GROUP BY status"
        ) ?? [];
    }

    /**
     * Get financial stats
     */
    protected function getFinancialStats(): array
    {
        $stats = [];
        
        $stats['total_revenue'] = $this->db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM payments")['total'] ?? 0;
        $stats['monthly_revenue'] = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE MONTH(created_at) = MONTH(CURDATE())"
        )['total'] ?? 0;
        $stats['pending_payments'] = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'pending'"
        )['total'] ?? 0;
        $stats['total_commissions'] = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) as total FROM mlm_commissions"
        )['total'] ?? 0;
        
        return $stats;
    }

    /**
     * Get HR stats
     */
    protected function getHRStats(): array
    {
        $stats = [];
        
        $stats['total_employees'] = $this->db->fetch("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")['count'] ?? 0;
        $stats['on_leave'] = $this->db->fetch("SELECT COUNT(*) as count FROM leaves WHERE status = 'approved' AND CURDATE() BETWEEN from_date AND to_date")['count'] ?? 0;
        $stats['new_hires'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM employees WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
        )['count'] ?? 0;
        
        return $stats;
    }

    /**
     * Get marketing stats
     */
    protected function getMarketingStats(): array
    {
        $stats = [];
        
        $stats['active_campaigns'] = $this->db->fetch("SELECT COUNT(*) as count FROM campaigns WHERE status = 'active'")['count'] ?? 0;
        $stats['emails_sent'] = $this->db->fetch("SELECT COUNT(*) as count FROM email_logs")['count'] ?? 0;
        $stats['sms_sent'] = $this->db->fetch("SELECT COUNT(*) as count FROM sms_logs")['count'] ?? 0;
        $stats['leads_from_marketing'] = $this->db->fetch(
            "SELECT COUNT(*) as count FROM leads WHERE source = 'marketing'"
        )['count'] ?? 0;
        
        return $stats;
    }
}
