<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

/**
 * CA (Chartered Accountant) Controller
 * Handles financial management, tax compliance, and budget control
 */
class CAController extends BaseController
{
    protected $db;
    protected $employeeId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->initializeEmployeeSession();
    }

    /**
     * Initialize employee session
     */
    private function initializeEmployeeSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->employeeId = $_SESSION['employee_id'] ?? null;

        if (!$this->employeeId) {
            header('Location: ' . BASE_URL . '/employee/login');
            exit;
        }
    }

    /**
     * CA Dashboard
     */
    public function dashboard()
    {
        try {
            // Get monthly financial summary
            $monthlySummary = $this->getMonthlyFinancialSummary();

            // Get pending invoices
            $pendingInvoices = $this->getPendingInvoices();

            // Get tax deadlines
            $taxDeadlines = $this->getTaxDeadlines();

            // Get budget variance
            $budgetVariance = $this->getBudgetVariance();

            // Get financial metrics
            $financialMetrics = $this->getFinancialMetrics();

            // Get audit status
            $auditStatus = $this->getAuditStatus();

            $this->render('employee/ca_dashboard', [
                'page_title' => 'CA Dashboard - APS Dream Home',
                'monthly_summary' => $monthlySummary,
                'pending_invoices' => $pendingInvoices,
                'tax_deadlines' => $taxDeadlines,
                'budget_variance' => $budgetVariance,
                'financial_metrics' => $financialMetrics,
                'audit_status' => $auditStatus
            ]);
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Get monthly financial summary
     */
    private function getMonthlyFinancialSummary()
    {
        $query = "SELECT 
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense,
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) - 
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as net_profit,
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN category = 'property_sale' THEN amount ELSE 0 END) as property_income,
                    SUM(CASE WHEN category = 'service_fee' THEN amount ELSE 0 END) as service_income,
                    SUM(CASE WHEN category = 'salary' THEN amount ELSE 0 END) as salary_expense,
                    SUM(CASE WHEN category = 'operational' THEN amount ELSE 0 END) as operational_expense
                 FROM financial_transactions 
                 WHERE MONTH(transaction_date) = MONTH(CURDATE())
                 AND YEAR(transaction_date) = YEAR(CURDATE())";

        $summary = $this->db->fetchOne($query);

        // Get previous month for comparison
        $previousMonthQuery = "SELECT 
                                  SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                                  SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
                               FROM financial_transactions 
                               WHERE MONTH(transaction_date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                               AND YEAR(transaction_date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";

        $previousMonth = $this->db->fetchOne($previousMonthQuery);

        // Calculate growth percentages
        $incomeGrowth = $previousMonth['income'] > 0 ?
            (($summary['total_income'] - $previousMonth['income']) / $previousMonth['income']) * 100 : 0;

        $expenseGrowth = $previousMonth['expense'] > 0 ?
            (($summary['total_expense'] - $previousMonth['expense']) / $previousMonth['expense']) * 100 : 0;

        return [
            'summary' => $summary,
            'previous_month' => $previousMonth,
            'income_growth' => round($incomeGrowth, 2),
            'expense_growth' => round($expenseGrowth, 2),
            'profit_margin' => $summary['total_income'] > 0 ?
                round(($summary['net_profit'] / $summary['total_income']) * 100, 2) : 0
        ];
    }

    /**
     * Get pending invoices
     */
    private function getPendingInvoices()
    {
        $query = "SELECT i.*, 
                        c.name as client_name,
                        p.title as property_title,
                        DATEDIFF(i.due_date, CURDATE()) as days_overdue
                 FROM invoices i
                 LEFT JOIN clients c ON i.client_id = c.id
                 LEFT JOIN properties p ON i.property_id = p.id
                 WHERE i.status = 'pending'
                 AND i.due_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                 ORDER BY i.due_date ASC
                 LIMIT 15";

        return $this->db->fetchAll($query);
    }

    /**
     * Get tax deadlines
     */
    private function getTaxDeadlines()
    {
        $query = "SELECT tr.*, 
                        t.type as tax_type,
                        t.frequency,
                        DATEDIFF(tr.due_date, CURDATE()) as days_until_due
                 FROM tax_reminders tr
                 LEFT JOIN tax_types t ON tr.tax_type_id = t.id
                 WHERE tr.due_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                 AND tr.status = 'pending'
                 ORDER BY tr.due_date ASC
                 LIMIT 20";

        return $this->db->fetchAll($query);
    }

    /**
     * Get budget variance
     */
    private function getBudgetVariance()
    {
        $query = "SELECT db.*, 
                        d.name as department_name,
                        db.budget_amount - db.spent_amount as variance,
                        ((db.budget_amount - db.spent_amount) / db.budget_amount) * 100 as variance_percentage
                 FROM department_budgets db
                 JOIN departments d ON db.department_id = d.id
                 WHERE db.fiscal_year = YEAR(CURDATE())
                 ORDER BY ABS(variance) DESC
                 LIMIT 10";

        return $this->db->fetchAll($query);
    }

    /**
     * Get financial metrics
     */
    private function getFinancialMetrics()
    {
        // Quarterly performance
        $quarterlyQuery = "SELECT 
                             QUARTER(transaction_date) as quarter,
                             SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                             SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense,
                             SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) - 
                             SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as profit
                          FROM financial_transactions 
                          WHERE YEAR(transaction_date) = YEAR(CURDATE())
                          GROUP BY QUARTER(transaction_date)
                          ORDER BY quarter";

        $quarterlyPerformance = $this->db->fetchAll($quarterlyQuery);

        // Revenue by category
        $revenueByCategoryQuery = "SELECT 
                                      category,
                                      SUM(amount) as total_amount,
                                      COUNT(*) as transaction_count
                                   FROM financial_transactions 
                                   WHERE type = 'income'
                                   AND MONTH(transaction_date) = MONTH(CURDATE())
                                   AND YEAR(transaction_date) = YEAR(CURDATE())
                                   GROUP BY category
                                   ORDER BY total_amount DESC";

        $revenueByCategory = $this->db->fetchAll($revenueByCategoryQuery);

        // Expense by category
        $expenseByCategoryQuery = "SELECT 
                                      category,
                                      SUM(amount) as total_amount,
                                      COUNT(*) as transaction_count
                                   FROM financial_transactions 
                                   WHERE type = 'expense'
                                   AND MONTH(transaction_date) = MONTH(CURDATE())
                                   AND YEAR(transaction_date) = YEAR(CURDATE())
                                   GROUP BY category
                                   ORDER BY total_amount DESC";

        $expenseByCategory = $this->db->fetchAll($expenseByCategoryQuery);

        // Cash flow analysis
        $cashFlowQuery = "SELECT 
                             transaction_date,
                             SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as daily_income,
                             SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as daily_expense,
                             (SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) - 
                              SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END)) as net_cash_flow
                          FROM financial_transactions 
                          WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                          GROUP BY transaction_date
                          ORDER BY transaction_date DESC";

        $cashFlow = $this->db->fetchAll($cashFlowQuery);

        return [
            'quarterly_performance' => $quarterlyPerformance,
            'revenue_by_category' => $revenueByCategory,
            'expense_by_category' => $expenseByCategory,
            'cash_flow' => $cashFlow
        ];
    }

    /**
     * Get audit status
     */
    private function getAuditStatus()
    {
        $query = "SELECT * FROM audit_schedules 
                  WHERE fiscal_year = YEAR(CURDATE())
                  ORDER BY scheduled_date ASC";

        return $this->db->fetchAll($query);
    }

    /**
     * Process invoice
     */
    public function processInvoice($invoiceId, $action, $notes = '')
    {
        try {
            // Get invoice details
            $invoiceQuery = "SELECT * FROM invoices WHERE id = ?";
            $invoice = $this->db->fetchOne($invoiceQuery, [$invoiceId]);

            if (!$invoice) {
                throw new Exception("Invoice not found");
            }

            // Update invoice status
            $query = "UPDATE invoices 
                      SET status = ?, notes = ?, processed_by = ?, processed_at = NOW()
                      WHERE id = ?";

            $this->db->execute($query, [$action, $notes, $this->employeeId, $invoiceId]);

            // If approved, create financial transaction
            if ($action === 'approved') {
                $this->createFinancialTransaction($invoice);
            }

            // Log activity
            $this->logFinancialActivity(
                'invoice_processed',
                "Invoice #{$invoiceId} {$action}",
                $invoiceId
            );

            // Notify client
            $this->notifyInvoiceUpdate($invoice['client_id'], $invoice, $action);

            return [
                'success' => true,
                'message' => "Invoice {$action} successfully"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create financial transaction from invoice
     */
    private function createFinancialTransaction($invoice)
    {
        $query = "INSERT INTO financial_transactions (
                    type, category, amount, description, transaction_date,
                    reference_id, reference_type, created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $this->db->execute($query, [
            'income',
            $invoice['category'] ?? 'property_sale',
            $invoice['amount'],
            "Invoice payment - {$invoice['description']}",
            $invoice['due_date'],
            $invoice['id'],
            'invoice',
            $this->employeeId
        ]);
    }

    /**
     * Update tax compliance
     */
    public function updateTaxCompliance($taxId, $complianceData)
    {
        try {
            // Get tax details
            $taxQuery = "SELECT tr.*, t.type as tax_type, t.frequency
                        FROM tax_reminders tr
                        LEFT JOIN tax_types t ON tr.tax_type_id = t.id
                        WHERE tr.id = ?";

            $tax = $this->db->fetchOne($taxQuery, [$taxId]);

            if (!$tax) {
                throw new Exception("Tax reminder not found");
            }

            // Update tax compliance
            $query = "UPDATE tax_reminders 
                      SET status = ?, paid_amount = ?, paid_date = ?, 
                          notes = ?, updated_by = ?, updated_at = NOW()
                      WHERE id = ?";

            $this->db->execute($query, [
                $complianceData['status'],
                $complianceData['paid_amount'] ?? 0,
                $complianceData['paid_date'] ?? null,
                $complianceData['notes'] ?? '',
                $this->employeeId,
                $taxId
            ]);

            // Create expense transaction if paid
            if ($complianceData['status'] === 'paid' && !empty($complianceData['paid_amount'])) {
                $this->createTaxTransaction($tax, $complianceData['paid_amount']);
            }

            // Schedule next tax reminder if applicable
            if ($tax['frequency'] !== 'once') {
                $this->scheduleNextTaxReminder($tax);
            }

            // Log activity
            $this->logFinancialActivity(
                'tax_compliance',
                "Tax {$tax['tax_type']} compliance updated: {$complianceData['status']}",
                $taxId
            );

            return [
                'success' => true,
                'message' => "Tax compliance updated successfully"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create tax transaction
     */
    private function createTaxTransaction($tax, $amount)
    {
        $query = "INSERT INTO financial_transactions (
                    type, category, amount, description, transaction_date,
                    reference_id, reference_type, created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $this->db->execute($query, [
            'expense',
            'tax',
            $amount,
            "Tax payment - {$tax['tax_type']}",
            $tax['paid_date'] ?? date('Y-m-d'),
            $tax['id'],
            'tax_payment',
            $this->employeeId
        ]);
    }

    /**
     * Schedule next tax reminder
     */
    private function scheduleNextTaxReminder($tax)
    {
        $nextDueDate = $this->calculateNextTaxDueDate($tax['due_date'], $tax['frequency']);

        $query = "INSERT INTO tax_reminders (
                    tax_type_id, due_date, amount, frequency, status, created_at
                ) VALUES (?, ?, ?, ?, 'pending', NOW())";

        $this->db->execute($query, [
            $tax['tax_type_id'],
            $nextDueDate,
            $tax['amount'],
            $tax['frequency']
        ]);
    }

    /**
     * Calculate next tax due date
     */
    private function calculateNextTaxDueDate($currentDate, $frequency)
    {
        switch ($frequency) {
            case 'monthly':
                return date('Y-m-d', strtotime($currentDate . ' +1 month'));
            case 'quarterly':
                return date('Y-m-d', strtotime($currentDate . ' +3 months'));
            case 'annually':
                return date('Y-m-d', strtotime($currentDate . ' +1 year'));
            default:
                return $currentDate;
        }
    }

    /**
     * Update budget
     */
    public function updateBudget($budgetId, $budgetData)
    {
        try {
            // Get budget details
            $budgetQuery = "SELECT * FROM department_budgets WHERE id = ?";
            $budget = $this->db->fetchOne($budgetQuery, [$budgetId]);

            if (!$budget) {
                throw new Exception("Budget not found");
            }

            // Update budget
            $query = "UPDATE department_budgets 
                      SET budget_amount = ?, notes = ?, updated_by = ?, updated_at = NOW()
                      WHERE id = ?";

            $this->db->execute($query, [
                $budgetData['budget_amount'],
                $budgetData['notes'] ?? '',
                $this->employeeId,
                $budgetId
            ]);

            // Log activity
            $this->logFinancialActivity(
                'budget_updated',
                "Budget updated for department {$budget['department_id']}",
                $budgetId
            );

            return [
                'success' => true,
                'message' => "Budget updated successfully"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate financial report
     */
    public function generateFinancialReport($reportType, $filters = [])
    {
        try {
            switch ($reportType) {
                case 'profit_loss':
                    return $this->generateProfitLossReport($filters);
                case 'cash_flow':
                    return $this->generateCashFlowReport($filters);
                case 'budget_variance':
                    return $this->generateBudgetVarianceReport($filters);
                case 'tax_summary':
                    return $this->generateTaxSummaryReport($filters);
                default:
                    throw new Exception("Invalid report type");
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate profit and loss report
     */
    private function generateProfitLossReport($filters)
    {
        $dateFrom = $filters['date_from'] ?? date('Y-m-01');
        $dateTo = $filters['date_to'] ?? date('Y-m-t');

        $query = "SELECT 
                    category,
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
                 FROM financial_transactions 
                 WHERE transaction_date BETWEEN ? AND ?
                 GROUP BY category
                 ORDER BY income DESC, expense DESC";

        $reportData = $this->db->fetchAll($query, [$dateFrom, $dateTo]);

        // Calculate totals
        $totalIncome = array_sum(array_column($reportData, 'income'));
        $totalExpense = array_sum(array_column($reportData, 'expense'));
        $netProfit = $totalIncome - $totalExpense;

        return [
            'success' => true,
            'report_type' => 'profit_loss',
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_profit' => $netProfit,
                'profit_margin' => $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 2) : 0
            ],
            'data' => $reportData,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate cash flow report
     */
    private function generateCashFlowReport($filters)
    {
        $dateFrom = $filters['date_from'] ?? date('Y-m-01');
        $dateTo = $filters['date_to'] ?? date('Y-m-t');

        $query = "SELECT 
                    transaction_date,
                    SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as daily_income,
                    SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as daily_expense,
                    (SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) - 
                     SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END)) as net_cash_flow
                 FROM financial_transactions 
                 WHERE transaction_date BETWEEN ? AND ?
                 GROUP BY transaction_date
                 ORDER BY transaction_date ASC";

        $reportData = $this->db->fetchAll($query, [$dateFrom, $dateTo]);

        return [
            'success' => true,
            'report_type' => 'cash_flow',
            'period' => ['from' => $dateFrom, 'to' => $dateTo],
            'data' => $reportData,
            'summary' => [
                'total_income' => array_sum(array_column($reportData, 'daily_income')),
                'total_expense' => array_sum(array_column($reportData, 'daily_expense')),
                'net_cash_flow' => array_sum(array_column($reportData, 'net_cash_flow'))
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate budget variance report
     */
    private function generateBudgetVarianceReport($filters)
    {
        $fiscalYear = $filters['fiscal_year'] ?? date('Y');

        $query = "SELECT db.*, 
                        d.name as department_name,
                        db.budget_amount - db.spent_amount as variance,
                        ((db.budget_amount - db.spent_amount) / db.budget_amount) * 100 as variance_percentage
                 FROM department_budgets db
                 JOIN departments d ON db.department_id = d.id
                 WHERE db.fiscal_year = ?
                 ORDER BY ABS(variance) DESC";

        $reportData = $this->db->fetchAll($query, [$fiscalYear]);

        return [
            'success' => true,
            'report_type' => 'budget_variance',
            'fiscal_year' => $fiscalYear,
            'data' => $reportData,
            'summary' => [
                'total_budget' => array_sum(array_column($reportData, 'budget_amount')),
                'total_spent' => array_sum(array_column($reportData, 'spent_amount')),
                'total_variance' => array_sum(array_column($reportData, 'variance'))
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Generate tax summary report
     */
    private function generateTaxSummaryReport($filters)
    {
        $year = $filters['year'] ?? date('Y');

        $query = "SELECT tr.*, t.type as tax_type, t.frequency
                 FROM tax_reminders tr
                 LEFT JOIN tax_types t ON tr.tax_type_id = t.id
                 WHERE YEAR(tr.due_date) = ?
                 ORDER BY tr.due_date ASC";

        $reportData = $this->db->fetchAll($query, [$year]);

        return [
            'success' => true,
            'report_type' => 'tax_summary',
            'year' => $year,
            'data' => $reportData,
            'summary' => [
                'total_tax_obligations' => array_sum(array_column($reportData, 'amount')),
                'total_paid' => array_sum(array_column($reportData, 'paid_amount')),
                'pending_taxes' => count(array_filter($reportData, fn($r) => $r['status'] === 'pending'))
            ],
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Notify invoice update
     */
    private function notifyInvoiceUpdate($clientId, $invoice, $action)
    {
        $message = "Invoice #{$invoice['id']} has been {$action}";
        $this->createNotification($clientId, 'invoice_update', $message, $invoice['id']);
    }

    /**
     * Create notification
     */
    private function createNotification($recipientId, $type, $message, $relatedId = null)
    {
        $query = "INSERT INTO notifications (
                    recipient_id, type, message, related_id, created_at, status
                ) VALUES (?, ?, ?, ?, NOW(), 'unread')";

        $this->db->execute($query, [$recipientId, $type, $message, $relatedId]);
    }

    /**
     * Log financial activity
     */
    private function logFinancialActivity($activityType, $description, $relatedId = null)
    {
        $query = "INSERT INTO financial_activities (
                    activity_type, description, related_id, 
                    performed_by, created_at
                ) VALUES (?, ?, ?, ?, NOW())";

        $this->db->execute($query, [$activityType, $description, $relatedId, $this->employeeId]);
    }

    /**
     * Handle errors
     */
    private function handleError($message)
    {
        error_log("CA Controller Error: " . $message);

        $_SESSION['error'] = "Unable to load CA dashboard. Please try again.";
        header('Location: ' . BASE_URL . '/employee/dashboard');
        exit;
    }
}
