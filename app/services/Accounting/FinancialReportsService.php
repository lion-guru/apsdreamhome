<?php

namespace App\Services\Accounting;

use App\Traits\ServiceTenantTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Financial Reports Service
 * Handles: Trial Balance, P&L, Balance Sheet, Cash Flow Statement, 3-way Recon
 */
class FinancialReportsService
{
    use ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    // ============================================================
    //  TRIAL BALANCE
    // ============================================================
    public function getTrialBalance(?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');
        $tid = TenantContext::getId();

        $sql = "SELECT
                    jel.account_id,
                    coa.account_type,
                    coa.account_name,
                    COALESCE(SUM(CASE WHEN je.entry_date <= ? THEN jel.debit_amount ELSE 0 END), 0) AS total_debit,
                    COALESCE(SUM(CASE WHEN je.entry_date <= ? THEN jel.credit_amount ELSE 0 END), 0) AS total_credit
                FROM journal_entry_lines jel
                JOIN journal_entries je ON je.id = jel.journal_entry_id
                JOIN chart_of_accounts coa ON coa.id = jel.account_id
                WHERE je.entry_date <= ?
                " . ($tid > 1 ? " AND je.tenant_id = ?" : "") .
                " GROUP BY jel.account_id, coa.account_type, coa.account_name
                HAVING ABS(COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0)) > 0.01
                ORDER BY coa.account_type, coa.account_name";

        $params = [$asOfDate, $asOfDate, $asOfDate];
        if ($tid > 1) $params[] = $tid;

        $rows = $this->db->fetchAll($sql, $params) ?: [];

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($rows as &$row) {
            $row['balance'] = (float)$row['total_debit'] - (float)$row['total_credit'];
            $totalDebit += (float)$row['total_debit'];
            $totalCredit += (float)$row['total_credit'];
        }

        return [
            'as_of_date'    => $asOfDate,
            'accounts'      => $rows,
            'total_debit'   => round($totalDebit, 2),
            'total_credit'  => round($totalCredit, 2),
            'balanced'      => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    // ============================================================
    //  PROFIT & LOSS STATEMENT
    // ============================================================
    public function getProfitLoss(string $fromDate, string $toDate): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "");
        $params = [$fromDate, $toDate];
        if ($tid > 1) $params[] = $tid;

        // Revenue accounts (type = 'revenue')
        $revenue = $this->db->fetchAll("
            SELECT coa.account_name, COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0) AS amount
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type = 'revenue' AND je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "") .
            " GROUP BY coa.account_name ORDER BY coa.account_name", $params) ?: [];

        // Expense accounts (type = 'expense')
        $expenses = $this->db->fetchAll("
            SELECT coa.account_name, COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0) AS amount
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type = 'expense' AND je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "") .
            " GROUP BY coa.account_name ORDER BY coa.account_name", $params) ?: [];

        // Cost of Goods Sold
        $cogs = $this->db->fetchAll("
            SELECT coa.account_name, COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0) AS amount
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type = 'cogs' AND je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "") .
            " GROUP BY coa.account_name ORDER BY coa.account_name", $params) ?: [];

        $totalRevenue = array_sum(array_column($revenue, 'amount'));
        $totalCogs = array_sum(array_column($cogs, 'amount'));
        $grossProfit = $totalRevenue - $totalCogs;
        $totalExpenses = array_sum(array_column($expenses, 'amount'));
        $netProfit = $grossProfit - $totalExpenses;

        return [
            'period'        => ['from' => $fromDate, 'to' => $toDate],
            'revenue'       => array_map(fn($r) => ['account' => $r['account_name'], 'amount' => round($r['amount'], 2)], $revenue),
            'total_revenue' => round($totalRevenue, 2),
            'cogs'          => array_map(fn($c) => ['account' => $c['account_name'], 'amount' => round($c['amount'], 2)], $cogs),
            'total_cogs'    => round($totalCogs, 2),
            'gross_profit'  => round($grossProfit, 2),
            'expenses'      => array_map(fn($e) => ['account' => $e['account_name'], 'amount' => round($e['amount'], 2)], $expenses),
            'total_expenses'=> round($totalExpenses, 2),
            'net_profit'    => round($netProfit, 2),
        ];
    }

    // ============================================================
    //  BALANCE SHEET
    // ============================================================
    public function getBalanceSheet(?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');
        $tid = TenantContext::getId();

        $where = "WHERE je.entry_date <= ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "");
        $params = [$asOfDate];
        if ($tid > 1) $params[] = $tid;

        // Assets
        $assets = $this->db->fetchAll("
            SELECT coa.account_name,
                   COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0) AS balance
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type = 'asset' AND je.entry_date <= ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "") .
            " GROUP BY coa.account_name ORDER BY coa.account_name", $params) ?: [];

        // Liabilities
        $liabilities = $this->db->fetchAll("
            SELECT coa.account_name,
                   COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0) AS balance
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type = 'liability' AND je.entry_date <= ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "") .
            " GROUP BY coa.account_name ORDER BY coa.account_name", $params) ?: [];

        // Equity
        $equity = $this->db->fetchAll("
            SELECT coa.account_name,
                   COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0) AS balance
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type = 'equity' AND je.entry_date <= ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "") .
            " GROUP BY coa.account_name ORDER BY coa.account_name", $params) ?: [];

        $totalAssets = array_sum(array_column($assets, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilities, 'balance'));
        $totalEquity = array_sum(array_column($equity, 'balance'));

        return [
            'as_of_date' => $asOfDate,
            'assets'     => array_map(fn($a) => ['account' => $a['account_name'], 'balance' => round($a['balance'], 2)], $assets),
            'total_assets' => round($totalAssets, 2),
            'liabilities' => array_map(fn($l) => ['account' => $l['account_name'], 'balance' => round($l['balance'], 2)], $liabilities),
            'total_liabilities' => round($totalLiabilities, 2),
            'equity'    => array_map(fn($e) => ['account' => $e['account_name'], 'balance' => round($e['balance'], 2)], $equity),
            'total_equity' => round($totalEquity, 2),
            'balanced'  => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }

    // ============================================================
    //  CASH FLOW STATEMENT
    // ============================================================
    public function getCashFlowStatement(string $fromDate, string $toDate): array
    {
        $tid = TenantContext::getId();
        $where = "WHERE je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "");
        $params = [$fromDate, $toDate];
        if ($tid > 1) $params[] = $tid;

        // Operating activities
        $operating = $this->db->fetchAll("
            SELECT coa.account_name,
                   SUM(jel.debit_amount) as debits,
                   SUM(jel.credit_amount) as credits
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type IN ('asset','liability','expense','revenue')
            AND je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : "") .
            " GROUP BY coa.account_name", $params) ?: [];

        // Simplified: Operating = Revenue - Expenses + Working Capital Changes
        $revenue = $this->db->fetchOne("
            SELECT COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0) AS amount
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type = 'revenue' AND je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : ""), $params);

        $expenses = $this->db->fetchOne("
            SELECT COALESCE(SUM(jel.debit_amount), 0) - COALESCE(SUM(jel.credit_amount), 0) AS amount
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type = 'expense' AND je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : ""), $params);

        $netOperating = (float)($revenue['amount'] ?? 0) - (float)($expenses['amount'] ?? 0);

        // Investing activities (simplified)
        $investing = $this->db->fetchOne("
            SELECT COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0) AS amount
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type = 'asset' AND je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : ""), $params);

        // Financing activities
        $financing = $this->db->fetchOne("
            SELECT COALESCE(SUM(jel.credit_amount), 0) - COALESCE(SUM(jel.debit_amount), 0) AS amount
            FROM journal_entry_lines jel
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts coa ON coa.id = jel.account_id
            WHERE coa.account_type IN ('liability','equity') AND je.entry_date BETWEEN ? AND ?" . ($tid > 1 ? " AND je.tenant_id = ?" : ""), $params);

        return [
            'period' => ['from' => $fromDate, 'to' => $toDate],
            'operating_activities' => [
                'net_income' => round((float)($revenue['amount'] ?? 0) - (float)($expenses['amount'] ?? 0), 2),
                'adjustments' => [],
                'net_cash_from_operations' => round($netOperating, 2),
            ],
            'investing_activities' => [
                'net_cash_from_investing' => round((float)($investing['amount'] ?? 0), 2),
            ],
            'financing_activities' => [
                'net_cash_from_financing' => round((float)($financing['amount'] ?? 0), 2),
            ],
            'net_change_in_cash' => round($netOperating + (float)($investing['amount'] ?? 0) + (float)($financing['amount'] ?? 0), 2),
        ];
    }

    // ============================================================
    //  THREE-WAY RECONCILIATION
    // ============================================================
    public function threeWayReconciliation(int $trustAccountId, ?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? date('Y-m-d');
        $tid = TenantContext::getId();

        // 1. Bank Statement Balance
        $bankStmt = $this->db->fetchOne("SELECT current_balance FROM bank_accounts_master WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""), $tid > 1 ? [$trustAccountId, $tid] : [$trustAccountId]);
        $bankBalance = $bankStmt ? (float)$bankStmt['current_balance'] : 0.0;

        // 2. Book Balance (Cash Book)
        $bookStmt = $this->db->fetchOne("
            SELECT COALESCE(SUM(CASE WHEN transaction_type = 'receipt' THEN amount ELSE -amount END), 0) AS balance
            FROM cash_book WHERE bank_account_id = ? AND transaction_date <= ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$trustAccountId, $asOfDate], $tid > 1 ? [$tid] : [])
        );
        $bookBalance = $bookStmt ? (float)$bookStmt['balance'] : 0.0;

        // 3. Trust Ledger Balance (client funds held)
        $trustStmt = $this->db->fetchOne("
            SELECT COALESCE(SUM(amount), 0) AS balance
            FROM trust_ledger WHERE account_id = ? AND entry_date <= ?" . ($tid > 1 ? " AND tenant_id = ?" : ""),
            array_merge([$trustAccountId, $asOfDate], $tid > 1 ? [$tid] : [])
        );
        $trustBalance = $trustStmt ? (float)$trustStmt['balance'] : 0.0;

        $discrepancy = $bankBalance - $bookBalance - $trustBalance;

        return [
            'as_of_date'      => $asOfDate,
            'trust_account_id'=> $trustAccountId,
            'bank_balance'    => round($bankBalance, 2),
            'book_balance'    => round($bookBalance, 2),
            'trust_balance'   => round($trustBalance, 2),
            'discrepancy'     => round($discrepancy, 2),
            'reconciled'      => abs($discrepancy) < 0.01,
        ];
    }
}