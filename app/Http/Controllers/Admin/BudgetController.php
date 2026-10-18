<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\BudgetService;
use App\Core\Middleware\TenantContext;
use Exception;

class BudgetController extends AdminController
{
    protected $budgetService;

    public function __construct()
    {
        parent::__construct();
        $this->budgetService = new BudgetService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $fiscalYear = $_GET['fiscal_year'] ?? '';
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;

            $budgets = $this->budgetService->getAll($status ?: null, $fiscalYear ?: null);
            $total = count($budgets);
            $paginatedBudgets = array_slice($budgets, $offset, $perPage);

            $fiscalYears = $this->budgetService->getFiscalYears();

            $data = [
                'page_title' => 'Budget Management - APS Dream Home',
                'active_page' => 'budgets',
                'budgets' => $paginatedBudgets,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage),
                'filters' => [
                    'status' => $status,
                    'fiscal_year' => $fiscalYear
                ],
                'fiscal_years' => $fiscalYears,
                'statuses' => ['draft', 'approved', 'active', 'closed']
            ];

            return $this->render('admin/budgets/index', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load budgets: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        $departments = $this->budgetService->getDepartments();
        $fiscalYears = $this->budgetService->getFiscalYears();
        $currentYear = date('Y');

        $data = [
            'page_title' => 'Create Budget - APS Dream Home',
            'active_page' => 'budgets',
            'departments' => $departments,
            'fiscal_years' => $fiscalYears,
            'current_year' => $currentYear,
            'categories' => ['operating', 'capital', 'marketing', 'hr', 'it', 'maintenance', 'other']
        ];
        return $this->render('admin/budgets/create', $data);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            $required = ['budget_name', 'fiscal_year', 'period_start', 'period_end', 'allocated_amount'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            $budgetId = $this->budgetService->create($data);

            $this->logActivity('budget_created', [
                'budget_id' => $budgetId,
                'budget_name' => $data['budget_name'],
                'allocated_amount' => $data['allocated_amount']
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Budget created successfully',
                'budget_id' => $budgetId,
                'redirect' => BASE_URL . '/admin/budgets'
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to create budget: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $budget = $this->budgetService->getById((int)$id);
            if (!$budget) {
                $this->setFlash('error', 'Budget not found');
                return $this->redirect('/admin/budgets');
            }

            $expenses = $this->budgetService->getExpenses((int)$id);
            $variance = $this->budgetService->getVarianceReport((int)$id);

            $data = [
                'page_title' => 'Budget Details: ' . $budget['budget_name'],
                'active_page' => 'budgets',
                'budget' => $budget,
                'expenses' => $expenses,
                'variance' => $variance[0] ?? [],
                'statuses' => ['pending', 'approved', 'paid', 'rejected']
            ];
            return $this->render('admin/budgets/show', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load budget: ' . $e->getMessage());
            return $this->redirect('/admin/budgets');
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        try {
            $budget = $this->budgetService->getById((int)$id);
            if (!$budget) {
                $this->setFlash('error', 'Budget not found');
                return $this->redirect('/admin/budgets');
            }

            $departments = $this->budgetService->getDepartments();
            $fiscalYears = $this->budgetService->getFiscalYears();

            $data = [
                'page_title' => 'Edit Budget: ' . $budget['budget_name'],
                'active_page' => 'budgets',
                'budget' => $budget,
                'departments' => $departments,
                'fiscal_years' => $fiscalYears,
                'categories' => ['operating', 'capital', 'marketing', 'hr', 'it', 'maintenance', 'other']
            ];
            return $this->render('admin/budgets/edit', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load budget: ' . $e->getMessage());
            return $this->redirect('/admin/budgets');
        }
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;
            $success = $this->budgetService->update((int)$id, $data);

            if ($success) {
                $this->logActivity('budget_updated', [
                    'budget_id' => $id,
                    'changes' => $data
                ]);
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Budget updated successfully',
                    'redirect' => BASE_URL . '/admin/budgets'
                ]);
            }
            return $this->jsonError('Budget not found', 404);
        } catch (Exception $e) {
            return $this->jsonError('Failed to update budget: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $this->budgetService->delete((int)$id);

            $this->logActivity('budget_deleted', ['budget_id' => $id]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Budget deleted successfully'
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to delete budget: ' . $e->getMessage(), 500);
        }
    }

    public function approve($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $userId = $_SESSION['user_id'] ?? 1;
            $success = $this->budgetService->approve((int)$id, $userId);

            if ($success) {
                $this->logActivity('budget_approved', ['budget_id' => $id]);
                return $this->jsonResponse(['success' => true, 'message' => 'Budget approved']);
            }
            return $this->jsonError('Budget not found', 404);
        } catch (Exception $e) {
            return $this->jsonError('Failed to approve: ' . $e->getMessage(), 500);
        }
    }

    public function activate($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $success = $this->budgetService->activate((int)$id);
            if ($success) {
                $this->logActivity('budget_activated', ['budget_id' => $id]);
                return $this->jsonResponse(['success' => true, 'message' => 'Budget activated']);
            }
            return $this->jsonError('Budget not found', 404);
        } catch (Exception $e) {
            return $this->jsonError('Failed to activate: ' . $e->getMessage(), 500);
        }
    }

    public function close($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $success = $this->budgetService->close((int)$id);
            if ($success) {
                $this->logActivity('budget_closed', ['budget_id' => $id]);
                return $this->jsonResponse(['success' => true, 'message' => 'Budget closed']);
            }
            return $this->jsonError('Budget not found', 404);
        } catch (Exception $e) {
            return $this->jsonError('Failed to close: ' . $e->getMessage(), 500);
        }
    }

    // Expense management
    public function addExpense($budgetId)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;

            $required = ['expense_date', 'amount'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return $this->jsonError(ucfirst(str_replace('_', ' ', $field)) . ' is required', 400);
                }
            }

            $expenseId = $this->budgetService->addExpense((int)$budgetId, $data);

            $this->logActivity('budget_expense_added', [
                'budget_id' => $budgetId,
                'expense_id' => $expenseId,
                'amount' => $data['amount']
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Expense added successfully',
                'expense_id' => $expenseId
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to add expense: ' . $e->getMessage(), 500);
        }
    }

    public function updateExpense($expenseId)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;
            $success = $this->budgetService->updateExpense((int)$expenseId, $data);

            if ($success) {
                $this->logActivity('budget_expense_updated', [
                    'expense_id' => $expenseId,
                    'changes' => $data
                ]);
                return $this->jsonResponse(['success' => true, 'message' => 'Expense updated']);
            }
            return $this->jsonError('Expense not found', 404);
        } catch (Exception $e) {
            return $this->jsonError('Failed to update expense: ' . $e->getMessage(), 500);
        }
    }

    public function deleteExpense($expenseId)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $success = $this->budgetService->deleteExpense((int)$expenseId);
            if ($success) {
                $this->logActivity('budget_expense_deleted', ['expense_id' => $expenseId]);
                return $this->jsonResponse(['success' => true, 'message' => 'Expense deleted']);
            }
            return $this->jsonError('Expense not found', 404);
        } catch (Exception $e) {
            return $this->jsonError('Failed to delete expense: ' . $e->getMessage(), 500);
        }
    }

    // Variance Report
    public function varianceReport()
    {
        $this->requireAdmin();
        try {
            $fiscalYear = $_GET['fiscal_year'] ?? '';
            $budgetId = !empty($_GET['budget_id']) ? (int)$_GET['budget_id'] : null;

            $variance = $this->budgetService->getVarianceReport($budgetId, $fiscalYear ?: null);
            $deptSummary = $this->budgetService->getDepartmentBudgetSummary($fiscalYear ?: null);
            $fiscalYears = $this->budgetService->getFiscalYears();

            $data = [
                'page_title' => 'Budget Variance Report - APS Dream Home',
                'active_page' => 'budget_variance',
                'variance' => $variance,
                'dept_summary' => $deptSummary,
                'fiscal_years' => $fiscalYears,
                'filters' => [
                    'fiscal_year' => $fiscalYear,
                    'budget_id' => $budgetId
                ]
            ];
            return $this->render('admin/budgets/variance', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load variance report: ' . $e->getMessage());
            return $this->redirect('/admin/budgets');
        }
    }

    // Budget Planning
    public function planning()
    {
        $this->requireAdmin();
        try {
            $fiscalYear = $_GET['fiscal_year'] ?? '';
            $departmentId = !empty($_GET['department_id']) ? (int)$_GET['department_id'] : null;

            $planning = $this->budgetService->getPlanning($fiscalYear ?: null, $departmentId);
            $departments = $this->budgetService->getDepartments();
            $fiscalYears = $this->budgetService->getFiscalYears();

            $data = [
                'page_title' => 'Budget Planning - APS Dream Home',
                'active_page' => 'budget_planning',
                'planning' => $planning,
                'departments' => $departments,
                'fiscal_years' => $fiscalYears,
                'filters' => [
                    'fiscal_year' => $fiscalYear,
                    'department_id' => $departmentId
                ]
            ];
            return $this->render('admin/budgets/planning', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load budget planning: ' . $e->getMessage());
            return $this->redirect('/admin/budgets');
        }
    }

    public function savePlanning()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;
            $planningId = $this->budgetService->savePlanning($data);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Planning item saved successfully',
                'planning_id' => $planningId
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed to save planning: ' . $e->getMessage(), 500);
        }
    }

    public function updatePlanning($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $data = $_POST;
            $success = $this->budgetService->updatePlanning((int)$id, $data);

            if ($success) {
                return $this->jsonResponse(['success' => true, 'message' => 'Planning updated']);
            }
            return $this->jsonError('Planning not found', 404);
        } catch (Exception $e) {
            return $this->jsonError('Failed to update planning: ' . $e->getMessage(), 500);
        }
    }

    public function deletePlanning($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request method', 400);
        }

        try {
            $success = $this->budgetService->deletePlanning((int)$id);
            if ($success) {
                return $this->jsonResponse(['success' => true, 'message' => 'Planning deleted']);
            }
            return $this->jsonError('Planning not found', 404);
        } catch (Exception $e) {
            return $this->jsonError('Failed to delete planning: ' . $e->getMessage(), 500);
        }
    }
}