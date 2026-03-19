<?php

/**
 * CFO Dashboard Controller
 * MVC Pattern - Proper Role-based Dashboard Management
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\LoggingService;
use Exception;

class CFODashboardController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
    }

    /**
     * Show CFO dashboard
     */
    public function index()
    {
        try {
            // Get financial overview
            $financial_overview = $this->db->fetchOne(
                "SELECT 
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN amount END), 0) as total_revenue,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as total_transactions,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN amount END), 0) as pending_revenue,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_transactions
                FROM booking_payments
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );

            // Get expense statistics
            $expense_stats = $this->db->fetchOne(
                "SELECT 
                    COALESCE(SUM(amount), 0) as total_expenses,
                    COUNT(*) as total_expense_transactions,
                    COALESCE(AVG(amount), 0) as avg_expense
                FROM expenses
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );

            // Get commission statistics
            $commission_stats = $this->db->fetchOne(
                "SELECT 
                    COALESCE(SUM(amount), 0) as total_commissions,
                    COUNT(*) as total_commission_transactions,
                    COALESCE(AVG(amount), 0) as avg_commission
                FROM commissions
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );

            // Get profit analysis
            $profit_analysis = $this->db->fetchOne(
                "SELECT 
                    (COALESCE(SUM(CASE WHEN bp.status = 'completed' THEN bp.amount END), 0) - 
                     COALESCE(SUM(CASE WHEN e.status = 'completed' THEN e.amount END), 0) - 
                     COALESCE(SUM(CASE WHEN c.status = 'completed' THEN c.amount END), 0)) as net_profit,
                    COALESCE(SUM(CASE WHEN bp.status = 'completed' THEN bp.amount END), 0) as gross_revenue,
                    COALESCE(SUM(CASE WHEN e.status = 'completed' THEN e.amount END), 0) as total_expenses_paid,
                    COALESCE(SUM(CASE WHEN c.status = 'completed' THEN c.amount END), 0) as total_commissions_paid
                FROM booking_payments bp
                LEFT JOIN expenses e ON 1=1
                LEFT JOIN commissions c ON 1=1
                WHERE bp.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  OR e.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  OR c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );

            // Get recent financial activities
            $activities = $this->db->fetchAll(
                "SELECT * FROM financial_activities 
                ORDER BY created_at DESC 
                LIMIT 10"
            );

            $this->data = [
                'page_title' => 'CFO Dashboard',
                'financial_overview' => $financial_overview,
                'expense_stats' => $expense_stats,
                'commission_stats' => $commission_stats,
                'profit_analysis' => $profit_analysis,
                'activities' => $activities
            ];

            return $this->render('admin/dashboards/cfo');
        } catch (Exception $e) {
            $this->loggingService->error("CFO Dashboard Error: " . $e->getMessage());
            $this->setFlash('error', 'Dashboard loading failed');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Get financial analytics (AJAX)
     */
    public function getFinancialAnalytics()
    {
        try {
            $analytics = $this->db->query(
                "SELECT 
                    DATE(created_at) as date,
                    SUM(CASE WHEN status = 'completed' THEN amount END) as daily_revenue,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as daily_transactions
                FROM booking_payments
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC"
            )->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse(['success' => true, 'data' => $analytics]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Financial Analytics error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get expense breakdown (AJAX)
     */
    public function getExpenseBreakdown()
    {
        try {
            $breakdown = $this->db->query(
                "SELECT 
                    category,
                    COALESCE(SUM(amount), 0) as total_amount,
                    COUNT(*) as transaction_count
                FROM expenses
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY category
                ORDER BY total_amount DESC"
            )->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse(['success' => true, 'data' => $breakdown]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Expense Breakdown error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
