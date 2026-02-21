<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Budget Planning Model
 * Handles budget creation, monitoring, variance analysis, and forecasting
 */
class Budget extends Model
{
    protected $table = 'budgets';
    protected $fillable = [
        'budget_name',
        'period_type',
        'start_date',
        'end_date',
        'total_budget',
        'is_active',
        'created_by',
        'created_at',
        'updated_at'
    ];

    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_QUARTERLY = 'quarterly';
    const PERIOD_YEARLY = 'yearly';

    /**
     * Create a new budget
     */
    public function createBudget(array $budgetData, array $budgetItems): array
    {
        $totalBudget = array_sum(array_column($budgetItems, 'budgeted_amount'));

        $budgetRecord = [
            'budget_name' => $budgetData['budget_name'],
            'period_type' => $budgetData['period_type'] ?? self::PERIOD_MONTHLY,
            'start_date' => $budgetData['start_date'],
            'end_date' => $budgetData['end_date'] ?? null,
            'total_budget' => $totalBudget,
            'is_active' => $budgetData['is_active'] ?? 1,
            'created_by' => $budgetData['created_by'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $budgetId = $this->insert($budgetRecord);

        // Add budget items
        $this->addBudgetItems($budgetId, $budgetItems);

        return [
            'success' => true,
            'budget_id' => $budgetId,
            'message' => 'Budget created successfully'
        ];
    }

    /**
     * Update budget
     */
    public function updateBudget(int $budgetId, array $budgetData, array $budgetItems = null): array
    {
        $budget = $this->find($budgetId);
        if (!$budget) {
            return ['success' => false, 'message' => 'Budget not found'];
        }

        $budget = $budget->toArray();

        $updateData = [
            'budget_name' => $budgetData['budget_name'] ?? $budget['budget_name'],
            'period_type' => $budgetData['period_type'] ?? $budget['period_type'],
            'start_date' => $budgetData['start_date'] ?? $budget['start_date'],
            'end_date' => $budgetData['end_date'] ?? $budget['end_date'],
            'is_active' => $budgetData['is_active'] ?? $budget['is_active'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Recalculate total if items provided
        if ($budgetItems !== null) {
            $updateData['total_budget'] = array_sum(array_column($budgetItems, 'budgeted_amount'));
            $this->updateBudgetItems($budgetId, $budgetItems);
        }

        $this->update($budgetId, $updateData);

        return [
            'success' => true,
            'message' => 'Budget updated successfully'
        ];
    }

    public function getBudgetDetails(int $budgetId): ?array
    {
        $budget = $this->find($budgetId);
        if (!$budget) {
            return null;
        }

        $budget = $budget->toArray();

        // Get budget items with actual amounts
        $items = $this->getBudgetItemsWithActuals($budgetId, $budget['start_date'], $budget['end_date']);

        $budget['items'] = $items;
        $budget['total_actual'] = array_sum(array_column($items, 'actual_amount'));
        $budget['total_variance'] = $budget['total_budget'] - $budget['total_actual'];

        return $budget;
    }

    /**
     * Get budgets with filters
     */
    public function getBudgets(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $db = Database::getInstance();

        $query = "SELECT b.*, a.auser as created_by_name,
                         (SELECT SUM(bi.budgeted_amount) FROM budget_items bi WHERE bi.budget_id = b.id) as total_budgeted,
                         (SELECT SUM(bi.actual_amount) FROM budget_items bi WHERE bi.budget_id = b.id) as total_actual
                  FROM budgets b
                  LEFT JOIN admin a ON b.created_by = a.aid
                  WHERE 1=1";

        $params = [];

        if (!empty($filters['period_type'])) {
            $query .= " AND b.period_type = ?";
            $params[] = $filters['period_type'];
        }

        if (!empty($filters['is_active'])) {
            $query .= " AND b.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (!empty($filters['year'])) {
            $query .= " AND YEAR(b.start_date) = ?";
            $params[] = $filters['year'];
        }

        $query .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $db->query($query, $params)->fetchAll();
    }

    /**
     * Generate budget vs actual report
     */
    public function generateBudgetVarianceReport(int $budgetId, string $startDate = null, string $endDate = null): array
    {
        $budget = $this->find($budgetId);
        if (!$budget) {
            return ['success' => false, 'message' => 'Budget not found'];
        }

        $startDate = $startDate ?? $budget['start_date'];
        $endDate = $endDate ?? $budget['end_date'] ?? date('Y-m-d');

        $items = $this->getBudgetItemsWithActuals($budgetId, $startDate, $endDate);

        $totalBudgeted = array_sum(array_column($items, 'budgeted_amount'));
        $totalActual = array_sum(array_column($items, 'actual_amount'));
        $totalVariance = $totalBudgeted - $totalActual;
        $variancePercentage = $totalBudgeted > 0 ? ($totalVariance / $totalBudgeted) * 100 : 0;

        // Group by account type
        $varianceByCategory = [];
        foreach ($items as $item) {
            $account = $this->getAccountDetails($item['account_id']);
            $category = $account['account_type'] ?? 'other';

            if (!isset($varianceByCategory[$category])) {
                $varianceByCategory[$category] = [
                    'budgeted' => 0,
                    'actual' => 0,
                    'variance' => 0,
                    'variance_percentage' => 0,
                    'items' => []
                ];
            }

            $varianceByCategory[$category]['budgeted'] += $item['budgeted_amount'];
            $varianceByCategory[$category]['actual'] += $item['actual_amount'];
            $varianceByCategory[$category]['variance'] += $item['variance'];
            $varianceByCategory[$category]['items'][] = $item;
        }

        // Calculate category percentages
        foreach ($varianceByCategory as &$category) {
            $category['variance_percentage'] = $category['budgeted'] > 0
                ? ($category['variance'] / $category['budgeted']) * 100
                : 0;
        }

        return [
            'budget' => $budget,
            'period' => date('M d, Y', strtotime($startDate)) . ' - ' . date('M d, Y', strtotime($endDate)),
            'items' => $items,
            'summary' => [
                'total_budgeted' => $totalBudgeted,
                'total_actual' => $totalActual,
                'total_variance' => $totalVariance,
                'variance_percentage' => round($variancePercentage, 2)
            ],
            'by_category' => $varianceByCategory
        ];
    }

    /**
     * Create budget from historical data
     */
    public function createBudgetFromHistory(string $budgetName, string $startDate, string $endDate, float $adjustmentPercentage = 0): array
    {
        // Get historical data for the same period last year
        $lastYearStart = date('Y-m-d', strtotime($startDate . ' -1 year'));
        $lastYearEnd = date('Y-m-d', strtotime($endDate . ' -1 year'));

        $historicalData = $this->getHistoricalSpending($lastYearStart, $lastYearEnd);

        $budgetItems = [];
        foreach ($historicalData as $account) {
            $adjustedAmount = $account['total_amount'] * (1 + ($adjustmentPercentage / 100));
            $budgetItems[] = [
                'account_id' => $account['account_id'],
                'budgeted_amount' => round($adjustedAmount, 2)
            ];
        }

        return $this->createBudget([
            'budget_name' => $budgetName,
            'period_type' => self::PERIOD_YEARLY,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => 1,
            'created_by' => null // Would be current user
        ], $budgetItems);
    }

    /**
     * Generate budget forecast
     */
    public function generateBudgetForecast(string $startDate, int $months = 12): array
    {
        $forecast = [];
        $currentDate = new DateTime($startDate);

        for ($i = 0; $i < $months; $i++) {
            $monthStart = $currentDate->format('Y-m-01');
            $monthEnd = $currentDate->format('Y-m-t');

            // Get historical data for same month last year
            $lastYearStart = date('Y-m-d', strtotime($monthStart . ' -1 year'));
            $lastYearEnd = date('Y-m-d', strtotime($monthEnd . ' -1 year'));

            $historicalSpending = $this->getHistoricalSpending($lastYearStart, $lastYearEnd);
            $totalForecast = array_sum(array_column($historicalSpending, 'total_amount'));

            // Apply growth rate (simplified)
            $growthRate = 0.05; // 5% annual growth
            $adjustedForecast = $totalForecast * (1 + ($growthRate / 12));

            $forecast[] = [
                'month' => $currentDate->format('M Y'),
                'start_date' => $monthStart,
                'end_date' => $monthEnd,
                'historical_amount' => $totalForecast,
                'forecast_amount' => round($adjustedForecast, 2),
                'growth_rate' => $growthRate * 100
            ];

            $currentDate->modify('+1 month');
        }

        return $forecast;
    }

    /**
     * Get budget alerts (over-budget items)
     */
    public function getBudgetAlerts(): array
    {
        $db = Database::getInstance();

        $alerts = $db->query(
            "SELECT bi.*, b.budget_name, coa.account_name,
                    ((bi.actual_amount - bi.budgeted_amount) / bi.budgeted_amount * 100) as variance_percentage
             FROM budget_items bi
             LEFT JOIN budgets b ON bi.budget_id = b.id
             LEFT JOIN chart_of_accounts coa ON bi.account_id = coa.id
             WHERE b.is_active = 1
             AND bi.actual_amount > bi.budgeted_amount * 1.1  -- 10% over budget
             ORDER BY variance_percentage DESC"
        )->fetchAll();

        return $alerts;
    }

    /**
     * Get budget utilization report
     */
    public function getBudgetUtilizationReport(string $startDate, string $endDate): array
    {
        $budgets = $this->query(
            "SELECT * FROM budgets WHERE is_active = 1
             AND ((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?) OR (start_date <= ? AND end_date >= ?))",
            [$startDate, $endDate, $startDate, $endDate, $startDate, $endDate]
        )->fetchAll();

        $report = [];
        foreach ($budgets as $budget) {
            $budgetDetails = $this->getBudgetDetails($budget['id']);
            $utilizationRate = $budget['total_budget'] > 0
                ? ($budgetDetails['total_actual'] / $budget['total_budget']) * 100
                : 0;

            $report[] = [
                'budget' => $budget,
                'utilization_rate' => round($utilizationRate, 2),
                'remaining_budget' => $budget['total_budget'] - $budgetDetails['total_actual'],
                'status' => $this->getBudgetStatus($budgetDetails)
            ];
        }

        return $report;
    }

    /**
     * Helper: Add budget items
     */
    private function addBudgetItems(int $budgetId, array $items): void
    {
        $db = Database::getInstance();

        foreach ($items as $item) {
            $db->query(
                "INSERT INTO budget_items (budget_id, account_id, budgeted_amount, actual_amount, variance)
                 VALUES (?, ?, ?, 0, ?)",
                [
                    $budgetId,
                    $item['account_id'],
                    $item['budgeted_amount'],
                    -$item['budgeted_amount'] // Initial variance is negative (under budget)
                ]
            );
        }
    }

    /**
     * Helper: Update budget items
     */
    private function updateBudgetItems(int $budgetId, array $items): void
    {
        $db = Database::getInstance();

        // Delete existing items
        $db->query("DELETE FROM budget_items WHERE budget_id = ?", [$budgetId]);

        // Add new items
        $this->addBudgetItems($budgetId, $items);
    }

    /**
     * Helper: Get budget items with actual amounts
     */
    private function getBudgetItemsWithActuals(int $budgetId, string $startDate, string $endDate): array
    {
        $db = Database::getInstance();

        $items = $db->query(
            "SELECT bi.*, coa.account_name, coa.account_code, coa.account_type,
                    (bi.budgeted_amount - bi.actual_amount) as variance,
                    CASE
                        WHEN bi.budgeted_amount > 0 THEN ((bi.actual_amount / bi.budgeted_amount) * 100)
                        ELSE 0
                    END as utilization_percentage
             FROM budget_items bi
             LEFT JOIN chart_of_accounts coa ON bi.account_id = coa.id
             WHERE bi.budget_id = ?
             ORDER BY coa.account_type, coa.account_code",
            [$budgetId]
        )->fetchAll();

        // Update actual amounts based on actual transactions
        foreach ($items as &$item) {
            $actualAmount = $this->getActualAmountForAccount($item['account_id'], $startDate, $endDate);
            $item['actual_amount'] = $actualAmount;
            $item['variance'] = $item['budgeted_amount'] - $actualAmount;
            $item['utilization_percentage'] = $item['budgeted_amount'] > 0
                ? round(($actualAmount / $item['budgeted_amount']) * 100, 2)
                : 0;
        }

        return $items;
    }

    /**
     * Helper: Get actual amount for account in period
     */
    private function getActualAmountForAccount(int $accountId, string $startDate, string $endDate): float
    {
        $db = Database::getInstance();

        $account = $db->query("SELECT account_type FROM chart_of_accounts WHERE id = ?", [$accountId])->fetch();

        if (!$account) return 0;

        // For expense accounts, sum debits; for income accounts, sum credits
        if (in_array($account['account_type'], ['asset', 'expense'])) {
            $result = $db->query(
                "SELECT COALESCE(SUM(jel.debit_amount), 0) as amount
                 FROM journal_entry_lines jel
                 LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
                 WHERE jel.account_id = ? AND je.entry_date BETWEEN ? AND ? AND je.status = 'posted'",
                [$accountId, $startDate, $endDate]
            )->fetch();
        } else {
            $result = $db->query(
                "SELECT COALESCE(SUM(jel.credit_amount), 0) as amount
                 FROM journal_entry_lines jel
                 LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
                 WHERE jel.account_id = ? AND je.entry_date BETWEEN ? AND ? AND je.status = 'posted'",
                [$accountId, $startDate, $endDate]
            )->fetch();
        }

        return (float)($result['amount'] ?? 0);
    }

    /**
     * Helper: Get account details
     */
    private function getAccountDetails(int $accountId): ?array
    {
        return $this->query("SELECT * FROM chart_of_accounts WHERE id = ?", [$accountId])->fetch();
    }

    /**
     * Helper: Get historical spending data
     */
    private function getHistoricalSpending(string $startDate, string $endDate): array
    {
        $db = Database::getInstance();

        return $db->query(
            "SELECT coa.id as account_id, coa.account_name, coa.account_type,
                    COALESCE(SUM(
                        CASE
                            WHEN coa.account_type IN ('asset', 'expense') THEN jel.debit_amount
                            ELSE jel.credit_amount
                        END
                    ), 0) as total_amount
             FROM chart_of_accounts coa
             LEFT JOIN journal_entry_lines jel ON coa.id = jel.account_id
             LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
             WHERE je.entry_date BETWEEN ? AND ? AND je.status = 'posted'
             GROUP BY coa.id, coa.account_name, coa.account_type
             HAVING total_amount > 0
             ORDER BY total_amount DESC",
            [$startDate, $endDate]
        )->fetchAll();
    }

    /**
     * Helper: Get budget status
     */
    private function getBudgetStatus(array $budgetDetails): string
    {
        $utilizationRate = $budgetDetails['total_budget'] > 0
            ? ($budgetDetails['total_actual'] / $budgetDetails['total_budget']) * 100
            : 0;

        if ($utilizationRate >= 100) return 'exceeded';
        if ($utilizationRate >= 90) return 'critical';
        if ($utilizationRate >= 75) return 'warning';
        return 'good';
    }
}
