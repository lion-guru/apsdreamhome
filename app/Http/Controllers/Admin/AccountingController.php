<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Accounting Controller - Custom MVC Implementation
 * Handles accounting and financial management operations in Admin panel
 */
class AccountingController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
        
        // Register middlewares
        $this->middleware('csrf', ['only' => ['storeIncome', 'storeExpense', 'update', 'destroy']]);
    }

    /**
     * Display accounting dashboard
     */
    public function index()
    {
        try {
            $today = date('Y-m-d');
            $thisMonth = date('Y-m-01');
            $lastMonth = date('Y-m-01', strtotime('-1 month'));

            // Today's income
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM income_records WHERE DATE(income_date) = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$today]);
            $todayIncome = $stmt->fetch()['total'] ?? 0;

            // Today's expenses
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM expense_records WHERE DATE(expense_date) = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$today]);
            $todayExpenses = $stmt->fetch()['total'] ?? 0;

            // This month income
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM income_records WHERE income_date >= ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$thisMonth]);
            $thisMonthIncome = $stmt->fetch()['total'] ?? 0;

            // This month expenses
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM expense_records WHERE expense_date >= ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$thisMonth]);
            $thisMonthExpenses = $stmt->fetch()['total'] ?? 0;

            // Total income
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM income_records";
            $result = $this->db->fetchOne($sql);
            $totalIncome = $result['total'] ?? 0;

            // Total expenses
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM expense_records";
            $result = $this->db->fetchOne($sql);
            $totalExpenses = $result['total'] ?? 0;

            // Recent transactions
            $sql = "(SELECT id, 'income' as type, amount, description, income_date as transaction_date, created_at 
                     FROM income_records ORDER BY created_at DESC LIMIT 5)
                    UNION ALL
                    (SELECT id, 'expense' as type, amount, description, expense_date as transaction_date, created_at 
                     FROM expense_records ORDER BY created_at DESC LIMIT 5)
                    ORDER BY created_at DESC LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $recentTransactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Accounting Dashboard - APS Dream Home',
                'active_page' => 'accounting',
                'today_income' => $todayIncome,
                'today_expenses' => $todayExpenses,
                'this_month_income' => $thisMonthIncome,
                'this_month_expenses' => $thisMonthExpenses,
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'net_profit' => $totalIncome - $totalExpenses,
                'recent_transactions' => $recentTransactions
            ];

            return $this->render('admin/accounting/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Accounting Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load accounting dashboard');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Display income records
     */
    public function income()
    {
        try {
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT * FROM income_records WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (description LIKE ? OR amount LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($category)) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }

            $sql .= " ORDER BY income_date DESC";

            // Count total
            $countSql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $incomeRecords = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Income Records - APS Dream Home',
                'active_page' => 'accounting',
                'income_records' => $incomeRecords,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'category' => $category
                ]
            ];

            return $this->render('admin/accounting/income', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Accounting Income error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load income records');
            return $this->redirect('admin/accounting');
        }
    }

    /**
     * Display expense records
     */
    public function expenses()
    {
        try {
            $search = $_GET['search'] ?? '';
            $category = $_GET['category'] ?? '';
            $page = (int)($_GET['page'] ?? 1);
            $perPage = (int)($_GET['per_page'] ?? 20);

            $offset = ($page - 1) * $perPage;

            // Build query
            $sql = "SELECT * FROM expense_records WHERE 1=1";
            $params = [];

            // Apply filters
            if (!empty($search)) {
                $sql .= " AND (description LIKE ? OR amount LIKE ?)";
                $searchParam = '%' . $search . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($category)) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }

            $sql .= " ORDER BY expense_date DESC";

            // Count total
            $countSql = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];

            // Apply pagination
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $perPage;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $expenseRecords = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $data = [
                'page_title' => 'Expense Records - APS Dream Home',
                'active_page' => 'accounting',
                'expense_records' => $expenseRecords,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'search' => $search,
                    'category' => $category
                ]
            ];

            return $this->render('admin/accounting/expenses', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Accounting Expenses error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load expense records');
            return $this->redirect('admin/accounting');
        }
    }

    /**
     * Store a new income record
     */
    public function storeIncome()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['amount', 'description', 'income_date', 'category'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate amount
            $amount = floatval($data['amount']);
            if ($amount <= 0) {
                return $this->jsonError('Amount must be greater than 0', 400);
            }

            // Validate date
            if (!strtotime($data['income_date'])) {
                return $this->jsonError('Invalid income date format', 400);
            }

            // Insert income record
            $sql = "INSERT INTO income_records 
                    (amount, description, category, income_date, created_at)
                    VALUES (?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $amount,
                CoreFunctionsServiceCustom::validateInput($data['description'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['category'], 'string'),
                $data['income_date']
            ]);

            if ($result) {
                $incomeId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'income_recorded', [
                    'income_id' => $incomeId,
                    'amount' => $amount,
                    'category' => $data['category']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Income recorded successfully',
                    'income_id' => $incomeId
                ]);
            }

            return $this->jsonError('Failed to record income', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Store Income error: " . $e->getMessage());
            return $this->jsonError('Failed to record income', 500);
        }
    }

    /**
     * Store a new expense record
     */
    public function storeExpense()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            // Validate required fields
            $required = ['amount', 'description', 'expense_date', 'category'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            // Validate amount
            $amount = floatval($data['amount']);
            if ($amount <= 0) {
                return $this->jsonError('Amount must be greater than 0', 400);
            }

            // Validate date
            if (!strtotime($data['expense_date'])) {
                return $this->jsonError('Invalid expense date format', 400);
            }

            // Insert expense record
            $sql = "INSERT INTO expense_records 
                    (amount, description, category, expense_date, created_at)
                    VALUES (?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $amount,
                CoreFunctionsServiceCustom::validateInput($data['description'], 'string'),
                CoreFunctionsServiceCustom::validateInput($data['category'], 'string'),
                $data['expense_date']
            ]);

            if ($result) {
                $expenseId = $this->db->lastInsertId();

                // Log activity
                $this->loggingService->logUserActivity($_SESSION['user_id'] ?? 0, 'expense_recorded', [
                    'expense_id' => $expenseId,
                    'amount' => $amount,
                    'category' => $data['category']
                ]);

                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Expense recorded successfully',
                    'expense_id' => $expenseId
                ]);
            }

            return $this->jsonError('Failed to record expense', 500);
        } catch (Exception $e) {
            $this->loggingService->error("Store Expense error: " . $e->getMessage());
            return $this->jsonError('Failed to record expense', 500);
        }
    }

    /**
     * Get accounting statistics
     */
    public function getStats()
    {
        try {
            $stats = [];

            // Total income and expenses
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM income_records";
            $result = $this->db->fetchOne($sql);
            $stats['total_income'] = (float)($result['total'] ?? 0);

            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM expense_records";
            $result = $this->db->fetchOne($sql);
            $stats['total_expenses'] = (float)($result['total'] ?? 0);

            $stats['net_profit'] = $stats['total_income'] - $stats['total_expenses'];

            // This month stats
            $thisMonth = date('Y-m-01');
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM income_records WHERE income_date >= ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$thisMonth]);
            $stats['this_month_income'] = (float)($stmt->fetch()['total'] ?? 0);

            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM expense_records WHERE expense_date >= ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$thisMonth]);
            $stats['this_month_expenses'] = (float)($stmt->fetch()['total'] ?? 0);

            // Income by category
            $sql = "SELECT category, COALESCE(SUM(amount), 0) as total FROM income_records GROUP BY category";
            $result = $this->db->fetchAll($sql);
            $stats['income_by_category'] = $result ?: [];

            // Expenses by category
            $sql = "SELECT category, COALESCE(SUM(amount), 0) as total FROM expense_records GROUP BY category";
            $result = $this->db->fetchAll($sql);
            $stats['expenses_by_category'] = $result ?: [];

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Accounting Stats error: " . $e->getMessage());
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch stats'
            ], 500);
        }
    }

    /**
     * Export accounting data
     */
    public function export()
    {
        try {
            $type = $_GET['type'] ?? 'all';
            $format = $_GET['format'] ?? 'json';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-d');

            $data = [];

            if ($type === 'all' || $type === 'income') {
                $sql = "SELECT * FROM income_records WHERE income_date BETWEEN ? AND ? ORDER BY income_date DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$startDate, $endDate]);
                $data['income'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            if ($type === 'all' || $type === 'expenses') {
                $sql = "SELECT * FROM expense_records WHERE expense_date BETWEEN ? AND ? ORDER BY expense_date DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$startDate, $endDate]);
                $data['expenses'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }

            if ($format === 'csv') {
                $filename = 'accounting_export_' . date('Y-m-d_H-i-s') . '.csv';
                
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                
                $output = fopen('php://output', 'w');
                
                // CSV header
                fputcsv($output, ['Type', 'Amount', 'Description', 'Category', 'Date', 'Created At']);
                
                // Income data
                if (isset($data['income'])) {
                    foreach ($data['income'] as $record) {
                        fputcsv($output, [
                            'Income',
                            $record['amount'],
                            $record['description'],
                            $record['category'],
                            $record['income_date'],
                            $record['created_at']
                        ]);
                    }
                }
                
                // Expense data
                if (isset($data['expenses'])) {
                    foreach ($data['expenses'] as $record) {
                        fputcsv($output, [
                            'Expense',
                            $record['amount'],
                            $record['description'],
                            $record['category'],
                            $record['expense_date'],
                            $record['created_at']
                        ]);
                    }
                }
                
                fclose($output);
                exit;
            } else {
                return $this->jsonResponse([
                    'success' => true,
                    'data' => $data,
                    'exported_at' => date('Y-m-d H:i:s'),
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ]);
            }
        } catch (Exception $e) {
            $this->loggingService->error("Export Accounting error: " . $e->getMessage());
            return $this->jsonError('Failed to export accounting data', 500);
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * JSON error helper
     */
    private function jsonError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}