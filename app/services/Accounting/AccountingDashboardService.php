<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Accounting Dashboard Service
 * Handles dashboard statistics and summary data for accounting module
 */
class AccountingDashboardService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function getDashboardStats(): array
    {
        $tid = TenantContext::getId();
        $where = $tid > 1 ? " WHERE tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        // Bank balances
        $bankBalances = $this->db->fetchAll("SELECT SUM(current_balance) as total FROM bank_accounts_master WHERE active = 1" . $where, $params);
        $totalBankBalance = (float)($bankBalances[0]['total'] ?? 0);

        // Cash in hand
        $cashInHand = $this->db->fetchOne("SELECT COALESCE(SUM(CASE WHEN transaction_type = 'receipt' THEN amount ELSE -amount END), 0) AS balance FROM cash_book" . $where, $params);
        $cashBalance = (float)($cashInHand['balance'] ?? 0);

        // Outstanding receivables
        $receivables = $this->db->fetchOne("SELECT COALESCE(SUM(amount_due), 0) AS total FROM booking_payment_schedules WHERE status IN ('pending', 'partial')" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : []);
        $outstandingReceivables = (float)($receivables['total'] ?? 0);

        // Outstanding payables
        $payables = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM vendor_payments WHERE status = 'pending'" . $where, $params);
        $outstandingPayables = (float)($payables['total'] ?? 0);

        // Pending expenses
        $expenses = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM expenses WHERE status IN ('submitted', 'pending')" . $where, $params);
        $pendingExpenses = (float)($expenses['total'] ?? 0);

        // Overdue penalties
        $penalties = $this->db->fetchOne("SELECT COALESCE(SUM(accrued_penalty - paid_penalty), 0) AS total FROM penalty_audit WHERE status IN ('accrued', 'partial')" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : []);
        $overduePenalties = (float)($penalties['total'] ?? 0);

        // Pending collections
        $collections = $this->db->fetchOne("SELECT COALESCE(SUM(amount), 0) AS total FROM cash_collections WHERE status = 'pending'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : []);
        $pendingCollections = (float)($collections['total'] ?? 0);

        // TDS/GST pending
        $tdsPending = $this->db->fetchOne("SELECT COALESCE(SUM(tds_amount), 0) AS total FROM tds_register WHERE deposit_status = 'pending'" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : []);
        $tdsPendingAmt = (float)($tdsPending['total'] ?? 0);

        $gstPending = $this->db->fetchOne("SELECT COALESCE(SUM(total_gst), 0) AS total FROM gst_transactions WHERE transaction_type = 'input' AND itc_claimed = 0" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$tid] : []);
        $gstPendingAmt = (float)($gstPending['total'] ?? 0);

        return [
            'bank_balance'      => round($totalBankBalance, 2),
            'cash_in_hand'      => round($cashBalance, 2),
            'total_liquidity'   => round($totalBankBalance + $cashBalance, 2),
            'outstanding_receivables' => round($outstandingReceivables, 2),
            'outstanding_payables'   => round($outstandingPayables, 2),
            'pending_expenses'  => round($pendingExpenses, 2),
            'overdue_penalties' => round($overduePenalties, 2),
            'pending_collections'    => round($pendingCollections, 2),
            'tds_pending'       => round($tdsPendingAmt, 2),
            'gst_pending'       => round($gstPendingAmt, 2),
            'net_position'      => round($totalBankBalance + $cashBalance + $outstandingReceivables - $outstandingPayables - $pendingExpenses - $overduePenalties, 2),
        ];
    }

    public function getCashFlowSummary(int $days = 30): array
    {
        $tid = TenantContext::getId();
        $endDate = date('Y-m-d', strtotime("+$days days"));

        // Projected inflows
        $inflows = $this->db->fetchAll("
            SELECT 'EMI' as type, SUM(amount_due) as amount FROM booking_payment_schedules
            WHERE due_date BETWEEN CURDATE() AND ? AND status IN ('pending', 'partial')" . ($tid > 1 ? " AND tenant_id = ?" : "") .
            " UNION ALL
            SELECT 'Collections' as type, SUM(amount) as amount FROM cash_collections
            WHERE status = 'pending' AND collection_date BETWEEN CURDATE() AND ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$days, $days], $tid > 1 ? [$tid, $tid] : [])
        );

        // Projected outflows
        $outflows = $this->db->fetchAll("
            SELECT 'Vendors' as type, SUM(amount) as amount FROM vendor_payments
            WHERE due_date BETWEEN CURDATE() AND ? AND status = 'pending'" . ($tid > 1 ? " AND tenant_id = ?" : "") .
            " UNION ALL
            SELECT 'Expenses' as type, SUM(amount) as amount FROM expenses
            WHERE due_date BETWEEN CURDATE() AND ? AND status = 'pending'" . ($tid > 1 ? " AND tenant_id = ?" : "") .
            " UNION ALL
            SELECT 'TDS/GST' as type, SUM(tds_amount) as amount FROM tds_register
            WHERE deposit_status = 'pending' AND deposit_date BETWEEN CURDATE() AND ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$days, $days, $days], $tid > 1 ? [$tid, $tid, $tid] : [])
        );

        return [
            'period_days'   => $days,
            'end_date'      => date('Y-m-d', strtotime("+$days days")),
            'total_inflow'  => round(array_sum(array_column($inflows, 'amount')), 2),
            'total_outflow' => round(array_sum(array_column($outflows, 'amount')), 2),
            'net_position'  => round(array_sum(array_column($inflows, 'amount')) - array_sum(array_column($outflows, 'amount')), 2),
            'inflows'       => $inflows,
            'outflows'      => $outflows,
        ];
    }

    public function getAgingAnalysis(): array
    {
        $tid = TenantContext::getId();
        $where = $tid > 1 ? " WHERE tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        $receivables = [
            '0-30'   => $this->getAgingBucket('booking_payment_schedules', 'due_date', 0, 30, $where, $params),
            '31-60'  => $this->getAgingBucket('booking_payment_schedules', 'due_date', 31, 60, $where, $params),
            '61-90'  => $this->getAgingBucket('booking_payment_schedules', 'due_date', 61, 90, $where, $params),
            '90+'    => $this->getAgingBucket('booking_payment_schedules', 'due_date', 91, 9999, $where, $params),
        ];

        $payables = [
            '0-30'   => $this->getAgingBucket('vendor_payments', 'due_date', 0, 30, $where, $params),
            '31-60'  => $this->getAgingBucket('vendor_payments', 'due_date', 31, 60, $where, $params),
            '61-90'  => $this->getAgingBucket('vendor_payments', 'due_date', 61, 90, $where, $params),
            '90+'    => $this->getAgingBucket('vendor_payments', 'due_date', 91, 9999, $where, $params),
        ];

        return [
            'receivables' => $receivables,
            'payables'   => $payables,
            'total_receivable' => array_sum($receivables),
            'total_payable'    => array_sum($payables),
        ];
    }

    private function getAgingBucket(string $table, string $dateField, int $from, int $to, string $where, array $params): float
    {
        $today = date('Y-m-d');
        $fromDate = date('Y-m-d', strtotime("-$to days"));
        $toDate = date('Y-m-d', strtotime("-$from days"));

        $sql = "SELECT COALESCE(SUM(amount_due), 0) AS total FROM $table WHERE $dateField BETWEEN ? AND ? AND status IN ('pending', 'partial') $where";
        $params2 = array_merge([$fromDate, $toDate], $params);
        $result = $this->db->fetchOne($sql, $params2);
        return (float)($result['total'] ?? 0);
    }
}