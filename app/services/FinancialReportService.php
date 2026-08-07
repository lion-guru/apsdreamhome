<?php
/**
 * Financial Report Service
 * 
 * Generates P&L, Balance Sheet, Cash Flow reports
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

class FinancialReportService
{
    use ServiceTenantTrait;
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get Profit & Loss data
     */
    public function getProfitLoss(string $startDate, string $endDate): array
    {
        $tid = $this->tenantId();
        $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid, $startDate, $endDate] : [$startDate, $endDate];

        // Revenue from bookings
        $revenue = $this->db->fetchOne(
            "SELECT COALESCE(SUM(total_amount), 0) as total 
             FROM bookings 
             WHERE status NOT IN ('cancelled') 
             AND DATE(created_at) BETWEEN ? AND ? {$tSql}",
            $params
        )['total'] ?? 0;

        // EMI collections
        $emiCollected = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM booking_payment_receipts 
             WHERE DATE(receipt_date) BETWEEN ? AND ? {$tSql}",
            $params
        )['total'] ?? 0;

        // Expenses
        $expenses = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM expenses 
             WHERE DATE(expense_date) BETWEEN ? AND ? {$tSql}",
            $params
        )['total'] ?? 0;

        // Commissions paid
        $commissions = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM mlm_commission_ledger 
             WHERE status = 'paid' 
             AND DATE(created_at) BETWEEN ? AND ? {$tSql}",
            $params
        )['total'] ?? 0;

        // Salaries paid
        $salaries = $this->db->fetchOne(
            "SELECT COALESCE(SUM(net_salary), 0) as total 
             FROM salary_payments 
             WHERE status = 'paid' 
             AND DATE(payment_date) BETWEEN ? AND ? {$tSql}",
            $params
        )['total'] ?? 0;

        $totalRevenue = (float) $revenue + (float) $emiCollected;
        $totalExpenses = (float) $expenses + (float) $commissions + (float) $salaries;
        $netProfit = $totalRevenue - $totalExpenses;

        return [
            'revenue' => [
                'bookings' => (float) $revenue,
                'emi_collections' => (float) $emiCollected,
                'total' => $totalRevenue,
            ],
            'expenses' => [
                'operational' => (float) $expenses,
                'commissions' => (float) $commissions,
                'salaries' => (float) $salaries,
                'total' => $totalExpenses,
            ],
            'net_profit' => $netProfit,
            'profit_margin' => $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Get Balance Sheet data
     */
    public function getBalanceSheet(string $asOfDate): array
    {
        $tid = $this->tenantId();
        $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid, $asOfDate] : [$asOfDate];

        // Assets
        $cashInBank = $this->db->fetchOne(
            "SELECT COALESCE(SUM(balance), 0) as total FROM bank_accounts_master WHERE 1=1 {$tSql}",
            $tid > 1 ? [$tid] : []
        )['total'] ?? 0;

        $receivables = $this->db->fetchOne(
            "SELECT COALESCE(SUM(remaining_amount), 0) as total 
             FROM bookings 
             WHERE status NOT IN ('cancelled', 'completed') 
             AND DATE(created_at) <= ? {$tSql}",
            $params
        )['total'] ?? 0;

        $totalAssets = (float) $cashInBank + (float) $receivables;

        // Liabilities
        $pendingPayouts = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM mlm_commission_ledger 
             WHERE status = 'pending' 
             AND DATE(created_at) <= ? {$tSql}",
            $params
        )['total'] ?? 0;

        $pendingSalaries = $this->db->fetchOne(
            "SELECT COALESCE(SUM(net_salary), 0) as total 
             FROM salary_payments 
             WHERE status = 'pending' 
             AND DATE(payment_date) <= ? {$tSql}",
            $params
        )['total'] ?? 0;

        $totalLiabilities = (float) $pendingPayouts + (float) $pendingSalaries;

        // Equity
        $equity = $totalAssets - $totalLiabilities;

        return [
            'assets' => [
                'cash_in_bank' => (float) $cashInBank,
                'receivables' => (float) $receivables,
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'pending_payouts' => (float) $pendingPayouts,
                'pending_salaries' => (float) $pendingSalaries,
                'total' => $totalLiabilities,
            ],
            'equity' => $equity,
            'as_of_date' => $asOfDate,
        ];
    }

    /**
     * Get Cash Flow data
     */
    public function getCashFlow(string $startDate, string $endDate): array
    {
        $tid = $this->tenantId();
        $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid, $startDate, $endDate] : [$startDate, $endDate];

        // Inflows
        $bookingPayments = $this->db->fetchOne(
            "SELECT COALESCE(SUM(total_amount), 0) as total 
             FROM bookings 
             WHERE status = 'token_paid' 
             AND DATE(created_at) BETWEEN ? AND ? {$tSql}",
            $params
        )['total'] ?? 0;

        $emiReceipts = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM booking_payment_receipts 
             WHERE DATE(receipt_date) BETWEEN ? AND ? {$tSql}",
            $params
        )['total'] ?? 0;

        // Outflows
        $expensePayments = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM expenses 
             WHERE DATE(expense_date) BETWEEN ? AND ? 
             AND status = 'approved' {$tSql}",
            $params
        )['total'] ?? 0;

        $commissionPayouts = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total 
             FROM mlm_commission_ledger 
             WHERE status = 'paid' 
             AND DATE(created_at) BETWEEN ? AND ? {$tSql}",
            $params
        )['total'] ?? 0;

        $salaryPayments = $this->db->fetchOne(
            "SELECT COALESCE(SUM(net_salary), 0) as total 
             FROM salary_payments 
             WHERE status = 'paid' 
             AND DATE(payment_date) BETWEEN ? AND ? {$tSql}",
            $params
        )['total'] ?? 0;

        $totalInflows = (float) $bookingPayments + (float) $emiReceipts;
        $totalOutflows = (float) $expensePayments + (float) $commissionPayouts + (float) $salaryPayments;
        $netCashFlow = $totalInflows - $totalOutflows;

        return [
            'inflows' => [
                'booking_payments' => (float) $bookingPayments,
                'emi_receipts' => (float) $emiReceipts,
                'total' => $totalInflows,
            ],
            'outflows' => [
                'expenses' => (float) $expensePayments,
                'commissions' => (float) $commissionPayouts,
                'salaries' => (float) $salaryPayments,
                'total' => $totalOutflows,
            ],
            'net_cash_flow' => $netCashFlow,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    /**
     * Get monthly revenue trend (for charts)
     */
    public function getMonthlyRevenue(int $months = 12): array
    {
        $tid = $this->tenantId();
        $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        return $this->db->fetchAll(
            "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as booking_count,
                COALESCE(SUM(total_amount), 0) as revenue
             FROM bookings 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
             AND status != 'cancelled' {$tSql}
             GROUP BY DATE_FORMAT(created_at, '%Y-%m')
             ORDER BY month ASC",
            array_merge([$months], $params)
        ) ?? [];
    }
}
