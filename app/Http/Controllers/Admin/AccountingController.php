<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use Exception;

class AccountingController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Add CSRF protection to store methods
        $this->middleware('csrf', ['only' => ['storeIncome', 'storeExpense']]);
    }

    public function index()
    {
        $this->data['page_title'] = $this->mlSupport->translate('Accounting Dashboard');

        try {
            $db = $this->db;
            $today = date('Y-m-d');

            // Fetch today's income from income_records
            $incomeQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM income_records WHERE DATE(income_date) = ?";
            $income_data = $db->fetchOne($incomeQuery, [$today]);
            $today_income = $income_data['total'] ?? 0;

            // Fetch today's expenses from expenses
            $expensesQuery = "SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE DATE(expense_date) = ?";
            $expenses_data = $db->fetchOne($expensesQuery, [$today]);
            $today_expenses = $expenses_data['total'] ?? 0;

            // Fetch recent transactions (Income + Expenses)
            $recentQuery = "
                (SELECT income_date as date, CONCAT('Income: ', description) as description, amount, 'Income' as type
                 FROM income_records
                 ORDER BY income_date DESC LIMIT 5)
                UNION ALL
                (SELECT expense_date as date, description, amount, 'Expense' as type
                 FROM expenses
                 ORDER BY expense_date DESC LIMIT 5)
                ORDER BY date DESC LIMIT 10";

            $recent_transactions = $db->fetchAll($recentQuery);
        } catch (Exception $e) {
            error_log("Accounting Dashboard Error: " . $e->getMessage());
            $today_income = 0;
            $today_expenses = 0;
            $recent_transactions = [];
        }

        $this->data['today_income'] = $today_income;
        $this->data['today_expenses'] = $today_expenses;
        $this->data['recent_transactions'] = $recent_transactions;

        return $this->render('admin/accounting/index');
    }

    public function addIncome()
    {
        $this->data['page_title'] = $this->mlSupport->translate('Add Income');

        // Fetch customers and projects for dropdowns
        try {
            $this->data['customers'] = $this->db->fetchAll("SELECT id, name FROM customers ORDER BY name ASC");
            $this->data['projects'] = $this->db->fetchAll("SELECT id, pname FROM projects ORDER BY pname ASC");
        } catch (Exception $e) {
            $this->data['customers'] = [];
            $this->data['projects'] = [];
        }

        return $this->render('admin/accounting/add_income');
    }

    public function storeIncome()
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect('admin/accounting/income/add');
        }

        // CSRF check is handled by middleware, but we can double check if needed
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token.'));
            return $this->redirect('admin/accounting/income/add');
        }

        // Basic validation
        $amount = floatval($this->request->post('amount') ?? 0);
        $income_date = $this->request->post('income_date') ?? date('Y-m-d');
        $category = $this->request->post('category') ?? '';
        $description = $this->request->post('description') ?? '';
        $payment_method = $this->request->post('payment_method') ?? 'cash';
        $customer_id = !empty($this->request->post('customer_id')) ? intval($this->request->post('customer_id')) : null;
        $project_id = !empty($this->request->post('project_id')) ? intval($this->request->post('project_id')) : null;

        $created_by = $this->session->get('user_id', 1);

        if ($amount <= 0 || empty($category) || empty($income_date)) {
            $this->setFlash('error', $this->mlSupport->translate('Please fill in all required fields correctly.'));
            return $this->redirect('admin/accounting/income/add');
        }

        try {
            // Generate unique income number
            $income_number = "INC-" . date('Ymd') . "-" . strtoupper(substr(md5(uniqid()), 0, 4));

            $sql = "INSERT INTO income_records (income_number, income_date, income_category, amount, description, payment_method, customer_id, project_id, created_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'received')";

            $this->db->execute($sql, [$income_number, $income_date, $category, $amount, $description, $payment_method, $customer_id, $project_id, $created_by]);

            $this->setFlash('success', $this->mlSupport->translate('Income recorded successfully.'));
            return $this->redirect('admin/accounting');
        } catch (Exception $e) {
            error_log("Store Income Error: " . $e->getMessage());
            $this->setFlash('error', $this->mlSupport->translate('Error recording income: ') . $e->getMessage());
            return $this->redirect('admin/accounting/income/add');
        }
    }

    public function addExpense()
    {
        $this->data['page_title'] = $this->mlSupport->translate('Add Expense');
        return $this->render('admin/accounting/add_expenses');
    }

    public function storeExpense()
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->redirect('admin/accounting/expenses/add');
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid CSRF token.'));
            return $this->redirect('admin/accounting/expenses/add');
        }

        $amount = floatval($this->request->post('amount') ?? 0);
        $expense_date = $this->request->post('expense_date') ?? date('Y-m-d');
        $source = $this->request->post('source') ?? ''; // This seems to be 'category' or 'payee'
        $description = $this->request->post('description') ?? '';
        $user_id = $this->session->get('user_id', 1);

        if ($amount <= 0 || empty($source) || empty($expense_date)) {
            $this->setFlash('error', $this->mlSupport->translate('Please fill in all required fields correctly.'));
            return $this->redirect('admin/accounting/expenses/add');
        }

        try {
            $sql = "INSERT INTO expenses (user_id, amount, source, expense_date, description) VALUES (?, ?, ?, ?, ?)";
            $this->db->execute($sql, [$user_id, $amount, $source, $expense_date, $description]);

            $this->setFlash('success', $this->mlSupport->translate('Expense recorded successfully.'));
            return $this->redirect('admin/accounting');
        } catch (Exception $e) {
            error_log("Store Expense Error: " . $e->getMessage());
            $this->setFlash('error', $this->mlSupport->translate('Error recording expense: ') . $e->getMessage());
            return $this->redirect('admin/accounting/expenses/add');
        }
    }

    public function transactions()
    {
        $this->data['page_title'] = $this->mlSupport->translate('All Transactions');

        try {
            $db = $this->db;

            // Fetch all transactions (Income + Expenses)
            $query = "
                (SELECT income_date as date, CONCAT('Income: ', description) as description, amount, 'Income' as type, income_category as category
                 FROM income_records
                 ORDER BY income_date DESC LIMIT 100)
                UNION ALL
                (SELECT expense_date as date, description, amount, 'Expense' as type, source as category
                 FROM expenses
                 ORDER BY expense_date DESC LIMIT 100)
                ORDER BY date DESC LIMIT 200";

            $transactions = $db->fetchAll($query);
            $this->data['transactions'] = $transactions;
        } catch (Exception $e) {
            error_log("Transactions Error: " . $e->getMessage());
            $this->data['transactions'] = [];
        }

        return $this->render('admin/accounting/transactions');
    }
}
