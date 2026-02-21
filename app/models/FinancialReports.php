<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Financial Reports Model
 * Handles Profit & Loss, Balance Sheet, and other financial statements
 */
class FinancialReports extends Model
{
    protected $table = 'journal_entries';

    /**
     * Generate Profit & Loss statement
     */
    public function generateProfitLoss(string $startDate, string $endDate): array
    {
        $db = Database::getInstance();

        // Get income accounts
        $incomeAccounts = $db->query(
            "SELECT coa.account_code, coa.account_name, coa.account_subtype,
                    COALESCE(SUM(jel.credit_amount - jel.debit_amount), 0) as amount
             FROM chart_of_accounts coa
             LEFT JOIN journal_entry_lines jel ON coa.id = jel.account_id
             LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
             WHERE coa.account_type = 'income'
             AND (je.entry_date BETWEEN ? AND ? OR je.entry_date IS NULL)
             AND je.status = 'posted'
             GROUP BY coa.id, coa.account_code, coa.account_name, coa.account_subtype
             ORDER BY coa.account_code",
            [$startDate, $endDate]
        )->fetchAll();

        // Get expense accounts
        $expenseAccounts = $db->query(
            "SELECT coa.account_code, coa.account_name, coa.account_subtype,
                    COALESCE(SUM(jel.debit_amount - jel.credit_amount), 0) as amount
             FROM chart_of_accounts coa
             LEFT JOIN journal_entry_lines jel ON coa.id = jel.account_id
             LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
             WHERE coa.account_type = 'expense'
             AND (je.entry_date BETWEEN ? AND ? OR je.entry_date IS NULL)
             AND je.status = 'posted'
             GROUP BY coa.id, coa.account_code, coa.account_name, coa.account_subtype
             ORDER BY coa.account_code",
            [$startDate, $endDate]
        )->fetchAll();

        // Calculate totals
        $totalIncome = array_sum(array_column($incomeAccounts, 'amount'));
        $totalExpenses = array_sum(array_column($expenseAccounts, 'amount'));
        $netProfit = $totalIncome - $totalExpenses;

        // Group by categories
        $incomeByCategory = $this->groupByCategory($incomeAccounts, 'income');
        $expensesByCategory = $this->groupByCategory($expenseAccounts, 'expense');

        return [
            'period' => date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate)),
            'income' => [
                'accounts' => $incomeAccounts,
                'by_category' => $incomeByCategory,
                'total' => $totalIncome
            ],
            'expenses' => [
                'accounts' => $expenseAccounts,
                'by_category' => $expensesByCategory,
                'total' => $totalExpenses
            ],
            'net_profit' => $netProfit,
            'profit_margin' => $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 2) : 0
        ];
    }

    /**
     * Generate Balance Sheet
     */
    public function generateBalanceSheet(string $asOfDate): array
    {
        $db = Database::getInstance();

        // Get asset accounts
        $assetAccounts = $db->query(
            "SELECT coa.account_code, coa.account_name, coa.account_subtype,
                    (coa.opening_balance + COALESCE(SUM(jel.debit_amount - jel.credit_amount), 0)) as balance
             FROM chart_of_accounts coa
             LEFT JOIN journal_entry_lines jel ON coa.id = jel.account_id
             LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
             WHERE coa.account_type = 'asset'
             AND (je.entry_date <= ? OR je.entry_date IS NULL)
             AND (je.status = 'posted' OR je.status IS NULL)
             GROUP BY coa.id, coa.account_code, coa.account_name, coa.account_subtype, coa.opening_balance
             HAVING balance != 0
             ORDER BY coa.account_code",
            [$asOfDate]
        )->fetchAll();

        // Get liability accounts
        $liabilityAccounts = $db->query(
            "SELECT coa.account_code, coa.account_name, coa.account_subtype,
                    (coa.opening_balance + COALESCE(SUM(jel.credit_amount - jel.debit_amount), 0)) as balance
             FROM chart_of_accounts coa
             LEFT JOIN journal_entry_lines jel ON coa.id = jel.account_id
             LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
             WHERE coa.account_type = 'liability'
             AND (je.entry_date <= ? OR je.entry_date IS NULL)
             AND (je.status = 'posted' OR je.status IS NULL)
             GROUP BY coa.id, coa.account_code, coa.account_name, coa.account_subtype, coa.opening_balance
             HAVING balance != 0
             ORDER BY coa.account_code",
            [$asOfDate]
        )->fetchAll();

        // Get equity accounts
        $equityAccounts = $db->query(
            "SELECT coa.account_code, coa.account_name, coa.account_subtype,
                    (coa.opening_balance + COALESCE(SUM(jel.credit_amount - jel.debit_amount), 0)) as balance
             FROM chart_of_accounts coa
             LEFT JOIN journal_entry_lines jel ON coa.id = jel.account_id
             LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
             WHERE coa.account_type = 'equity'
             AND (je.entry_date <= ? OR je.entry_date IS NULL)
             AND (je.status = 'posted' OR je.status IS NULL)
             GROUP BY coa.id, coa.account_code, coa.account_name, coa.account_subtype, coa.opening_balance
             HAVING balance != 0
             ORDER BY coa.account_code",
            [$asOfDate]
        )->fetchAll();

        // Calculate totals
        $totalAssets = array_sum(array_column($assetAccounts, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilityAccounts, 'balance'));
        $totalEquity = array_sum(array_column($equityAccounts, 'balance'));

        // Group by categories
        $assetsByCategory = $this->groupByCategory($assetAccounts, 'asset');
        $liabilitiesByCategory = $this->groupByCategory($liabilityAccounts, 'liability');
        $equityByCategory = $this->groupByCategory($equityAccounts, 'equity');

        // Check if balanced (Assets = Liabilities + Equity)
        $isBalanced = abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01;

        return [
            'as_of_date' => date('M d, Y', strtotime($asOfDate)),
            'assets' => [
                'accounts' => $assetAccounts,
                'by_category' => $assetsByCategory,
                'total' => $totalAssets
            ],
            'liabilities' => [
                'accounts' => $liabilityAccounts,
                'by_category' => $liabilitiesByCategory,
                'total' => $totalLiabilities
            ],
            'equity' => [
                'accounts' => $equityAccounts,
                'by_category' => $equityByCategory,
                'total' => $totalEquity
            ],
            'liabilities_and_equity_total' => $totalLiabilities + $totalEquity,
            'is_balanced' => $isBalanced,
            'difference' => $totalAssets - ($totalLiabilities + $totalEquity)
        ];
    }

    /**
     * Generate Cash Flow statement
     */
    public function generateCashFlow(string $startDate, string $endDate): array
    {
        $db = Database::getInstance();

        // Get cash/bank transactions
        $cashTransactions = $db->query(
            "SELECT je.entry_date, je.description, jel.debit_amount, jel.credit_amount,
                    coa.account_name, coa.account_type
             FROM journal_entry_lines jel
             LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
             LEFT JOIN chart_of_accounts coa ON jel.account_id = coa.id
             WHERE coa.account_code IN ('1001', '1002') -- Cash and Bank accounts
             AND je.entry_date BETWEEN ? AND ?
             AND je.status = 'posted'
             ORDER BY je.entry_date",
            [$startDate, $endDate]
        )->fetchAll();

        // Separate inflows and outflows
        $operatingInflows = [];
        $operatingOutflows = [];
        $investingInflows = [];
        $investingOutflows = [];
        $financingInflows = [];
        $financingOutflows = [];

        foreach ($cashTransactions as $transaction) {
            $amount = $transaction['debit_amount'] > 0 ? $transaction['debit_amount'] : $transaction['credit_amount'];
            $isDebit = $transaction['debit_amount'] > 0;

            // Simplified categorization - in real implementation, this would be more complex
            if (strpos(strtolower($transaction['description']), 'sale') !== false ||
                strpos(strtolower($transaction['description']), 'revenue') !== false) {
                if ($isDebit) $operatingInflows[] = $transaction;
                else $operatingOutflows[] = $transaction;
            } elseif (strpos(strtolower($transaction['description']), 'equipment') !== false ||
                     strpos(strtolower($transaction['description']), 'asset') !== false) {
                if ($isDebit) $investingOutflows[] = $transaction;
                else $investingInflows[] = $transaction;
            } elseif (strpos(strtolower($transaction['description']), 'loan') !== false ||
                     strpos(strtolower($transaction['description']), 'capital') !== false) {
                if ($isDebit) $financingInflows[] = $transaction;
                else $financingOutflows[] = $transaction;
            } else {
                // Default to operating
                if ($isDebit) $operatingInflows[] = $transaction;
                else $operatingOutflows[] = $transaction;
            }
        }

        // Calculate totals
        $netOperatingCash = array_sum(array_column($operatingInflows, 'debit_amount')) -
                           array_sum(array_column($operatingOutflows, 'credit_amount'));
        $netInvestingCash = array_sum(array_column($investingInflows, 'credit_amount')) -
                           array_sum(array_column($investingOutflows, 'debit_amount'));
        $netFinancingCash = array_sum(array_column($financingInflows, 'credit_amount')) -
                           array_sum(array_column($financingOutflows, 'debit_amount'));

        $netCashFlow = $netOperatingCash + $netInvestingCash + $netFinancingCash;

        return [
            'period' => date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate)),
            'operating_activities' => [
                'inflows' => $operatingInflows,
                'outflows' => $operatingOutflows,
                'net_cash' => $netOperatingCash
            ],
            'investing_activities' => [
                'inflows' => $investingInflows,
                'outflows' => $investingOutflows,
                'net_cash' => $netInvestingCash
            ],
            'financing_activities' => [
                'inflows' => $financingInflows,
                'outflows' => $financingOutflows,
                'net_cash' => $netFinancingCash
            ],
            'net_cash_flow' => $netCashFlow
        ];
    }

    /**
     * Create journal entry
     */
    public function createJournalEntry(array $entryData, array $lines): array
    {
        $db = Database::getInstance();

        // Validate that debits equal credits
        $totalDebit = array_sum(array_column($lines, 'debit_amount'));
        $totalCredit = array_sum(array_column($lines, 'credit_amount'));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return ['success' => false, 'message' => 'Debits must equal credits'];
        }

        // Create entry number
        $entryNumber = $this->generateEntryNumber();

        // Insert journal entry
        $entryId = $db->query(
            "INSERT INTO journal_entries (entry_number, entry_date, description, reference_type, reference_id, total_debit, total_credit, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $entryNumber,
                $entryData['entry_date'],
                $entryData['description'],
                $entryData['reference_type'] ?? 'journal',
                $entryData['reference_id'] ?? null,
                $totalDebit,
                $totalCredit,
                $entryData['status'] ?? 'draft',
                date('Y-m-d H:i:s')
            ]
        );

        // Insert journal lines
        foreach ($lines as $index => $line) {
            $db->query(
                "INSERT INTO journal_entry_lines (journal_entry_id, account_id, debit_amount, credit_amount, description, line_order)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    $entryId,
                    $line['account_id'],
                    $line['debit_amount'] ?? 0,
                    $line['credit_amount'] ?? 0,
                    $line['description'] ?? null,
                    $index
                ]
            );

            // Update account balance
            $this->updateAccountBalance($line['account_id'], $line['debit_amount'] ?? 0, $line['credit_amount'] ?? 0);
        }

        return [
            'success' => true,
            'entry_id' => $entryId,
            'entry_number' => $entryNumber,
            'message' => 'Journal entry created successfully'
        ];
    }

    /**
     * Post journal entry
     */
    public function postJournalEntry(int $entryId): array
    {
        $entry = $this->find($entryId);
        if (!$entry) {
            return ['success' => false, 'message' => 'Journal entry not found'];
        }

        if ($entry['status'] !== 'draft') {
            return ['success' => false, 'message' => 'Entry is not in draft status'];
        }

        $this->update($entryId, [
            'status' => 'posted',
            'posted_by' => null, // Would be current user ID
            'posted_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'success' => true,
            'message' => 'Journal entry posted successfully'
        ];
    }

    /**
     * Get chart of accounts
     */
    public function getChartOfAccounts(): array
    {
        $db = Database::getInstance();

        return $db->query(
            "SELECT * FROM chart_of_accounts WHERE is_active = 1 ORDER BY account_type, account_code"
        )->fetchAll();
    }

    /**
     * Get trial balance
     */
    public function getTrialBalance(string $asOfDate): array
    {
        $db = Database::getInstance();

        $accounts = $db->query(
            "SELECT coa.account_code, coa.account_name, coa.account_type,
                    (coa.opening_balance + COALESCE(SUM(
                        CASE
                            WHEN coa.account_type IN ('asset', 'expense') THEN jel.debit_amount - jel.credit_amount
                            ELSE jel.credit_amount - jel.debit_amount
                        END
                    ), 0)) as balance
             FROM chart_of_accounts coa
             LEFT JOIN journal_entry_lines jel ON coa.id = jel.account_id
             LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
             WHERE coa.is_active = 1
             AND (je.entry_date <= ? OR je.entry_date IS NULL)
             AND (je.status = 'posted' OR je.status IS NULL)
             GROUP BY coa.id, coa.account_code, coa.account_name, coa.account_type, coa.opening_balance
             HAVING balance != 0
             ORDER BY coa.account_code",
            [$asOfDate]
        )->fetchAll();

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($accounts as &$account) {
            if (in_array($account['account_type'], ['asset', 'expense'])) {
                $account['debit_balance'] = $account['balance'];
                $account['credit_balance'] = 0;
                $totalDebit += $account['balance'];
            } else {
                $account['debit_balance'] = 0;
                $account['credit_balance'] = $account['balance'];
                $totalCredit += $account['balance'];
            }
        }

        return [
            'as_of_date' => date('M d, Y', strtotime($asOfDate)),
            'accounts' => $accounts,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced' => abs($totalDebit - $totalCredit) < 0.01
        ];
    }

    /**
     * Helper: Group accounts by category
     */
    private function groupByCategory(array $accounts, string $accountType): array
    {
        $grouped = [];

        foreach ($accounts as $account) {
            $category = $account['account_subtype'] ?? 'other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'accounts' => [],
                    'total' => 0
                ];
            }
            $grouped[$category]['accounts'][] = $account;
            $grouped[$category]['total'] += $account['amount'] ?? $account['balance'];
        }

        return $grouped;
    }

    /**
     * Helper: Generate entry number
     */
    private function generateEntryNumber(): string
    {
        $date = date('Ym');
        $prefix = 'JE-' . $date . '-';

        $existing = $this->query(
            "SELECT entry_number FROM journal_entries
             WHERE entry_number LIKE ?
             ORDER BY entry_number DESC LIMIT 1",
            [$prefix . '%']
        )->fetch();

        if ($existing) {
            $lastNumber = (int)substr($existing['entry_number'], -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Helper: Update account balance
     */
    private function updateAccountBalance(int $accountId, float $debit, float $credit): void
    {
        $db = Database::getInstance();

        $account = $db->query("SELECT account_type FROM chart_of_accounts WHERE id = ?", [$accountId])->fetch();

        if ($account) {
            $balanceChange = 0;

            if (in_array($account['account_type'], ['asset', 'expense'])) {
                $balanceChange = $debit - $credit;
            } else {
                $balanceChange = $credit - $debit;
            }

            $db->query(
                "UPDATE chart_of_accounts SET current_balance = current_balance + ? WHERE id = ?",
                [$balanceChange, $accountId]
            );
        }
    }
}
