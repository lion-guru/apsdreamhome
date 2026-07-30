<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class PayrollController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT ep.*, u.name as employee_name, u.email as employee_email
                FROM employee_payroll ep
                LEFT JOIN users u ON ep.employee_id = u.id
                ORDER BY ep.created_at DESC
            ");
            $stmt->execute();
            $payrolls = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $payrolls = [];
        }
        return $this->render('admin/payroll/index', [
            'page_title' => 'Payroll Management',
            'payrolls' => $payrolls
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        try {
            $empStmt = $this->db->prepare("SELECT id, name, email FROM users WHERE role = 'employee' ORDER BY name ASC");
            $empStmt->execute();
            $users = $empStmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $users = [];
        }
        return $this->render('admin/payroll/create', [
            'page_title' => 'Add Payroll Record',
            'users' => $users
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $employee_id = $_POST['employee_id'] ?? 0;
        $basic_salary = $_POST['basic_salary'] ?? 0;
        $hra = $_POST['hra'] ?? 0;
        $allowance = $_POST['allowance'] ?? 0;
        $deduction = $_POST['deduction'] ?? 0;
        $net_salary = ($basic_salary + $hra + $allowance) - $deduction;
        $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
        $payment_status = $_POST['payment_status'] ?? 'pending';
        $notes = $_POST['notes'] ?? '';
        try {
            $stmt = $this->db->prepare("INSERT INTO employee_payroll (employee_id, basic_salary, hra, allowance, deduction, net_salary, payment_date, payment_status, notes, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$employee_id, $basic_salary, $hra, $allowance, $deduction, $net_salary, $payment_date, $payment_status, $notes, $this->tenantId()]);
            $this->setFlash('success', 'Payroll record created successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to create payroll record: ' . $e->getMessage());
        }
        $this->redirect('/admin/payroll');
    }

    public function edit($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT * FROM employee_payroll WHERE id = ?");
            $stmt->execute([$id]);
            $payroll = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $payroll = null;
        }
        if (!$payroll) {
            $this->setFlash('error', 'Payroll record not found');
            $this->redirect('/admin/payroll');
        }
        try {
            $empStmt = $this->db->prepare("SELECT id, name, email FROM users WHERE role = 'employee' ORDER BY name ASC");
            $empStmt->execute();
            $users = $empStmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $users = [];
        }
        return $this->render('admin/payroll/edit', [
            'page_title' => 'Edit Payroll Record',
            'payroll' => $payroll,
            'users' => $users
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $employee_id = $_POST['employee_id'] ?? 0;
        $basic_salary = $_POST['basic_salary'] ?? 0;
        $hra = $_POST['hra'] ?? 0;
        $allowance = $_POST['allowance'] ?? 0;
        $deduction = $_POST['deduction'] ?? 0;
        $net_salary = ($basic_salary + $hra + $allowance) - $deduction;
        $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
        $payment_status = $_POST['payment_status'] ?? 'pending';
        $notes = $_POST['notes'] ?? '';
        try {
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE employee_payroll SET employee_id = ?, basic_salary = ?, hra = ?, allowance = ?, deduction = ?, net_salary = ?, payment_date = ?, payment_status = ?, notes = ? WHERE id = ?" . $tenantSql);
            $stmt->execute(array_merge([$employee_id, $basic_salary, $hra, $allowance, $deduction, $net_salary, $payment_date, $payment_status, $notes, $id], $tenantParams));
            $this->setFlash('success', 'Payroll record updated successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update: ' . $e->getMessage());
        }
        $this->redirect('/admin/payroll');
    }

    public function advances()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT ep.*, u.name as employee_name
                FROM employee_payroll ep
                LEFT JOIN users u ON ep.employee_id = u.id
                WHERE ep.advance_amount IS NOT NULL AND ep.advance_amount > 0
                ORDER BY ep.created_at DESC
            ");
            $stmt->execute();
            $advances = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $advances = [];
        }
        return $this->render('admin/payroll/advances', [
            'page_title' => 'Salary Advances',
            'advances' => $advances
        ]);
    }

    public function addAdvance()
    {
        $this->requireAdmin();
        $employee_id = (int)($_POST['employee_id'] ?? 0);
        $advance_amount = $_POST['advance_amount'] ?? 0;
        $advance_reason = $_POST['advance_reason'] ?? '';
        $advance_approved_by = $_POST['advance_approved_by'] ?? '';
        $advance_repay_emi = $_POST['advance_repay_emi'] ?? 0;
        if (!$employee_id) { $this->setFlash('error', 'Employee ID required'); $this->redirect('/admin/payroll/advances'); }
        try {
            $stmt = $this->db->prepare("INSERT INTO employee_payroll (employee_id, advance_amount, advance_reason, advance_approved_by, advance_repay_emi, payment_status, tenant_id, created_at) VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())");
            $stmt->execute([$employee_id, $advance_amount, $advance_reason, $advance_approved_by, $advance_repay_emi, $this->tenantId()]);
            $this->setFlash('success', 'Advance added successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to add advance: ' . $e->getMessage());
        }
        $this->redirect('/admin/payroll/advances');
    }
}
