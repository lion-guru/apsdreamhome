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
        try {
            $stmt2 = $this->db->prepare("
                SELECT ep.*, u.name as employee_name
                FROM employee_payslips ep
                LEFT JOIN employees e ON ep.employee_id = e.id
                LEFT JOIN users u ON e.user_id = u.id
                ORDER BY ep.period_year DESC, ep.period_month DESC, u.name
                LIMIT 12
            ");
            $stmt2->execute();
            $recent_payslips = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $recent_payslips = [];
        }
        return $this->render('admin/payroll/index', [
            'page_title' => 'Payroll Management',
            'payrolls' => $payrolls,
            'recent_payslips' => $recent_payslips,
            'batch_month' => date('Y-m')
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

    // ──────────────────────────────────────────────
    // MONTHLY PAYROLL BATCH (attendance + leave pro-rata,
    // PF/ESI/TDS/PT via DailyOperationsService::generatePayslip)
    // ──────────────────────────────────────────────

    public function generateBatch()
    {
        $this->requireAdmin();
        $monthInput = trim($_POST['month'] ?? '');
        if (!preg_match('/^(\d{4})-(\d{2})$/', $monthInput, $m)) {
            $this->setFlash('error', 'Invalid month. Use YYYY-MM format.');
            $this->redirect('/admin/payroll');
        }
        $year = (int)$m[1];
        $month = (int)$m[2];
        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            $this->setFlash('error', 'Invalid month value.');
            $this->redirect('/admin/payroll');
        }
        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        if ($totalDays <= 0) {
            $this->setFlash('error', 'Could not determine days in month.');
            $this->redirect('/admin/payroll');
        }
        try {
            $svc = new \App\Services\Backoffice\DailyOperationsService($this->db);
            $employees = $svc->getEmployees();
        } catch (\Exception $e) {
            error_log('PayrollController::generateBatch service init: ' . $e->getMessage());
            $employees = [];
        }
        if (empty($employees)) {
            $this->setFlash('error', 'No active employees found for payroll batch.');
            $this->redirect('/admin/payroll');
        }
        $generated = 0;
        $totalNet = 0.0;
        $errors = [];
        $skippedPaid = 0;
        // NEVER overwrite a paid payslip: collect users already paid for this month.
        $paidUserIds = [];
        try {
            $paidStmt = $this->db->prepare("SELECT e.user_id FROM employee_payslips ep JOIN employees e ON ep.employee_id = e.id WHERE ep.period_month = ? AND ep.period_year = ? AND ep.status = 'paid'");
            $paidStmt->execute([$month, $year]);
            $paidUserIds = array_map('intval', array_column($paidStmt->fetchAll(\PDO::FETCH_ASSOC), 'user_id'));
        } catch (\Exception $e) {
            error_log('PayrollController::generateBatch paid-guard: ' . $e->getMessage());
            $paidUserIds = [];
        }
        foreach ($employees as $emp) {
            $uid = (int)($emp['id'] ?? 0);
            if ($uid > 0 && in_array($uid, $paidUserIds, true)) {
                $skippedPaid++;
                continue;
            }
            try {
                $result = $svc->generatePayslip($uid, $month, $year);
                if (isset($result['error'])) {
                    $errors[] = ($emp['name'] ?? ('#' . $uid)) . ': ' . $result['error'];
                    continue;
                }
                $generated++;
                $totalNet += (float)($result['net_salary'] ?? 0);
            } catch (\Exception $e) {
                error_log('PayrollController::generateBatch employee: ' . $e->getMessage());
                $errors[] = ($emp['name'] ?? ('#' . $uid)) . ': ' . $e->getMessage();
            }
        }
        $msg = "Generated payroll for {$generated} employees for {$monthInput} ({$totalDays} days). Total Net: \u{20B9}" . number_format($totalNet, 2);
        if ($skippedPaid > 0) {
            $msg .= " | Skipped {$skippedPaid} already-paid (never overwritten)";
        }
        if (!empty($errors)) {
            $msg .= ' | Skipped ' . count($errors) . ': ' . implode('; ', array_slice($errors, 0, 3));
        }
        $this->setFlash(($generated > 0 || $skippedPaid > 0) ? 'success' : 'error', $msg);
        $this->redirect('/admin/payroll');
    }

    public function payslip($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        try {
            $svc = new \App\Services\Backoffice\DailyOperationsService($this->db);
            $payslip = $svc->getPayslipById($id);
        } catch (\Exception $e) {
            error_log('PayrollController::payslip: ' . $e->getMessage());
            $payslip = null;
        }
        if (!$payslip) {
            $this->setFlash('error', 'Payslip not found');
            $this->redirect('/admin/payroll');
        }
        $extra = [];
        try {
            $stmt = $this->db->prepare("SELECT employee_code, department, designation, pan_number, bank_account, bank_ifsc FROM employees WHERE id = ?");
            $stmt->execute([(int)($payslip['employee_id'] ?? 0)]);
            $extra = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log('PayrollController::payslip extra: ' . $e->getMessage());
            $extra = [];
        }
        $periodLabel = str_pad((string)($payslip['period_month'] ?? ''), 2, '0', STR_PAD_LEFT) . '/' . ($payslip['period_year'] ?? '');
        return $this->render('admin/payroll/payslip', [
            'page_title' => 'Payslip #' . $id . ' (' . $periodLabel . ')',
            'payslip' => $payslip,
            'extra' => $extra,
            'period_label' => $periodLabel
        ]);
    }

    public function downloadPayslipPdf($id)
    {
        $this->requireAdmin();
        $id = (int)$id;
        if ($id <= 0) {
            $this->setFlash('error', 'Invalid payslip ID');
            $this->redirect('/admin/payroll');
        }
        try {
            $pdfService = new \App\Services\PDF\PdfService();
            $result = $pdfService->generate('payslip', $id);
        } catch (\Exception $e) {
            error_log('PayrollController::downloadPayslipPdf: ' . $e->getMessage());
            $result = ['success' => false, 'error' => $e->getMessage()];
        }
        if (empty($result['success'])) {
            $this->setFlash('error', 'PDF generation failed: ' . ($result['error'] ?? 'unknown error'));
            $this->redirect('/admin/payroll/payslip/' . $id);
        }
        $path = $result['data']['path'] ?? '';
        if (!is_file($path)) {
            $this->setFlash('error', 'PDF file not found after generation');
            $this->redirect('/admin/payroll/payslip/' . $id);
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="payslip_' . $id . '.pdf"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}
