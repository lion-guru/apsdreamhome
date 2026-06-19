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
            // Get financial overview (booking_payments has no status column)
            try {
                $bp_revenue = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(payment_amount), 0) as total, COUNT(*) as count
                     FROM booking_payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                );
            } catch (Exception $e) {
                $bp_revenue = ['total' => 0, 'count' => 0];
            }

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
                    COALESCE(SUM(CASE WHEN status = 'paid' THEN amount END), 0) as total_commissions,
                    COUNT(*) as total_commission_transactions,
                    COALESCE(AVG(amount), 0) as avg_commission
                FROM commissions
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );

            // Get profit analysis (separate queries to avoid Cartesian product)
            try {
                $bp_sum = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(payment_amount), 0) as total
                     FROM booking_payments WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                );
            } catch (Exception $e) { $bp_sum = ['total' => 0]; }

            try {
                $exp_sum = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(amount), 0) as total
                     FROM expenses WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                );
            } catch (Exception $e) { $exp_sum = ['total' => 0]; }

            try {
                $comm_sum = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(CASE WHEN status = 'paid' THEN amount END), 0) as total
                     FROM commissions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
                );
            } catch (Exception $e) { $comm_sum = ['total' => 0]; }

            $profit_analysis = [
                'net_profit' => $bp_sum['total'] - $exp_sum['total'] - $comm_sum['total'],
                'gross_revenue' => $bp_sum['total'],
                'total_expenses_paid' => $exp_sum['total'],
                'total_commissions_paid' => $comm_sum['total']
            ];

            // Get recent financial activities
            $activities = $this->db->fetchAll(
                "SELECT id, action as type, description, created_at
                 FROM activity_logs_unified 
                 ORDER BY created_at DESC 
                 LIMIT 10"
            );

            // Get top performers (gamification)
            $top_performers = [];
            try {
                $svc = new \App\Services\GamificationService();
                $top_associate = $svc->getTopAssociate();
                $top_agent = $svc->getTopAgent();
                $top_employee = $svc->getTopEmployee();
                $top_performers = [
                    'associate' => $top_associate,
                    'agent' => $top_agent,
                    'employee' => $top_employee
                ];
            } catch (\Throwable $e) {
                error_log('Top performers error: ' . $e->getMessage());
                $top_performers = [
                    'associate' => ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'],
                    'agent' => ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A'],
                    'employee' => ['name' => 'N/A', 'level' => 'N/A', 'metric' => 'N/A']
                ];
            }

            $financial_overview = [
                'total_revenue' => $bp_revenue['total'],
                'total_transactions' => $bp_revenue['count'],
                'pending_revenue' => 0,
                'pending_transactions' => 0
            ];

            $this->data = [
                'page_title' => 'CFO Dashboard',
                'financial_overview' => $financial_overview,
                'expense_stats' => $expense_stats,
                'commission_stats' => $commission_stats,
                'profit_analysis' => $profit_analysis,
                'activities' => $activities,
                'top_performers' => $top_performers
            ];

            return $this->render('admin/dashboards/cfo');
        } catch (Exception $e) {
            error_log("CFO Dashboard Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
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
        header('Content-Type: application/json');
        try {
            $analytics = $this->db->query(
                "SELECT 
                    DATE(payment_date) as date,
                    SUM(payment_amount) as daily_revenue,
                    COUNT(*) as daily_transactions
                FROM booking_payments
                WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(payment_date)
                ORDER BY date DESC"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->loggingService->error("Get Financial Analytics error: " . $e->getMessage());
            $analytics = [];
        }
        echo json_encode(['success' => true, 'data' => $analytics ?? []]);
        exit;
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
