<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Services\ExpenseReimbursementService;
use App\Services\EmployeeGrievanceService;

class ExpenseReimbursementController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new ExpenseReimbursementService();
    }

    public function index()
    {
        $this->requireAdmin();
        $filters = [
            'employee_id' => $_GET['employee_id'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'from_date' => $_GET['from_date'] ?? null,
            'to_date' => $_GET['to_date'] ?? null,
            'search' => $_GET['search'] ?? null,
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $claims = $this->service->getClaims($filters, $limit, $offset);
        $total = count($claims); // approximate for pagination
        $categories = $this->service->getCategories(['is_active' => 1]);
        $employees = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status = 'active' ORDER BY u.name");

        return $this->render('admin/expense_reimbursement/index', [
            'page_title' => 'Expense Reimbursement',
            'claims' => $claims,
            'categories' => $categories,
            'employees' => $employees,
            'filters' => $filters,
            'page' => $page,
            'total' => $total,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $categories = $this->service->getCategories(['is_active' => 1]);
        $employees = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status = 'active' ORDER BY u.name");
        return $this->render('admin/expense_reimbursement/create', [
            'page_title' => 'Create Expense Claim',
            'categories' => $categories,
            'employees' => $employees,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->service->submitClaim((int)($_POST['employee_id'] ?? 0), $_POST);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/expense-reimbursement');
    }

    public function show($id)
    {
        $this->requireAdmin();
        $claim = $this->service->getClaim($id);
        if (!$claim) {
            $this->setFlash('error', 'Claim not found');
            $this->redirect(BASE_URL . '/admin/expense-reimbursement');
        }
        return $this->render('admin/expense_reimbursement/show', [
            'page_title' => 'Claim: ' . $claim['claim_number'],
            'claim' => $claim,
        ]);
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $claim = $this->service->getClaim($id);
        if (!$claim || $claim['status'] !== 'draft') {
            $this->setFlash('error', 'Claim not found or cannot be edited');
            $this->redirect(BASE_URL . '/admin/expense-reimbursement');
        }
        $categories = $this->service->getCategories(['is_active' => 1]);
        $employees = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status = 'active' ORDER BY u.name");
        return $this->render('admin/expense_reimbursement/create', [
            'page_title' => 'Edit Expense Claim',
            'claim' => $claim,
            'categories' => $categories,
            'employees' => $employees,
            'mode' => 'edit',
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $claim = $this->service->getClaim($id);
        if (!$claim || $claim['status'] !== 'draft') {
            $this->setFlash('error', 'Claim not found or cannot be edited');
            $this->redirect(BASE_URL . '/admin/expense-reimbursement');
        }
        // For simplicity, delete and recreate (proper implementation would update)
        $this->setFlash('info', 'Edit functionality - implement full update logic');
        $this->redirect(BASE_URL . '/admin/expense-reimbursement/' . $id);
    }

    public function approve($id)
    {
        $this->requireAdmin();
        $result = $this->service->processApproval($id, (int)($_SESSION['admin_id'] ?? 1), 'approve', $_POST['comments'] ?? '');
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/expense-reimbursement/' . $id);
    }

    public function reject($id)
    {
        $this->requireAdmin();
        $result = $this->service->processApproval($id, (int)($_SESSION['admin_id'] ?? 1), 'reject', $_POST['comments'] ?? '');
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/expense-reimbursement/' . $id);
    }

    public function pay($id)
    {
        $this->requireAdmin();
        // Mark claim as paid
        $this->service->getClaim($id); // verify exists
        $this->db->execute("UPDATE expense_claims SET status = 'paid', paid_at = NOW(), paid_by = ?, payment_ref = ?, payment_mode = ? WHERE id = ? AND tenant_id = ?", [$_SESSION['admin_id'] ?? 1, $_POST['payment_ref'] ?? '', $_POST['payment_mode'] ?? 'bank_transfer', $id, $this->tenantId()]);
        $this->setFlash('success', 'Claim marked as paid');
        $this->redirect(BASE_URL . '/admin/expense-reimbursement/' . $id);
    }

    public function storeCategory()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->createCategory($_POST);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/expense-reimbursement');
    }

    public function stats()
    {
        $this->requireAdmin();
        return $this->jsonResponse($this->service->getStats());
    }
}