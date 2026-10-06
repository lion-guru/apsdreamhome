<?php

namespace App\Http\Controllers\Admin;

use App\Traits\TenantAwareTrait;
use App\Http\Controllers\Admin\AdminController;

class SalaryController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    // ──────────────────────────────────────────────
    // DASHBOARD / OVERVIEW
    // ──────────────────────────────────────────────

    public function index()
    {
        $this->requireAdmin();
        try {
            $totalPaid = $this->db->fetch("SELECT COALESCE(SUM(net_amount),0) as total FROM salary_payments WHERE payment_status='paid' AND MONTH(payment_date)=MONTH(CURDATE()) AND YEAR(payment_date)=YEAR(CURDATE())")['total'] ?? 0;
            $pendingCount = $this->db->fetch("SELECT COUNT(*) as c FROM salary_payments WHERE payment_status='pending'")['c'] ?? 0;
            $employeeCount = $this->db->fetch("SELECT COUNT(DISTINCT employee_id) as c FROM salary_structures WHERE status='active'")['c'] ?? 0;
            $pendingAmount = $this->db->fetch("SELECT COALESCE(SUM(net_amount),0) as total FROM salary_payments WHERE payment_status='pending'")['total'] ?? 0;

            $recentPayments = $this->db->fetchAll("
                SELECT sp.*, u.name as employee_name
                FROM salary_payments sp
                LEFT JOIN users u ON sp.employee_id = u.id
                ORDER BY sp.created_at DESC LIMIT 10
            ") ?? [];
        } catch (\Exception $e) {
            $totalPaid = 0; $pendingCount = 0; $employeeCount = 0; $pendingAmount = 0; $recentPayments = [];
        }
        return $this->render('admin/salary/index', [
            'page_title' => 'Salary Management',
            'total_paid' => $totalPaid,
            'pending_count' => $pendingCount,
            'employee_count' => $employeeCount,
            'pending_amount' => $pendingAmount,
            'recent_payments' => $recentPayments
        ]);
    }

    // ──────────────────────────────────────────────
    // PAYROLL PERIODS (lock guard visibility + reopen)
    // ──────────────────────────────────────────────

    public function periods()
    {
        $this->requireAdmin();
        try {
            $svc = new \App\Services\PayrollPeriodService();
            $periods = $svc->getAllPeriods();
        } catch (\Exception $e) {
            $periods = [];
        }
        return $this->render('admin/salary/periods', [
            'page_title' => 'Payroll Periods',
            'periods' => $periods
        ]);
    }

    public function reopenPeriod()
    {
        $this->requireAdmin();
        $month = (int)($_POST['month'] ?? 0);
        $year = (int)($_POST['year'] ?? 0);
        if (!$month || !$year) { $this->setFlash('error', 'Month and year required'); $this->redirect('/admin/salary/periods'); }
        try {
            $svc = new \App\Services\PayrollPeriodService();
            if ($svc->reopenPeriod($month, $year)) {
                $this->setFlash('success', "Period $month/$year reopened");
            } else {
                $this->setFlash('error', 'Period not found');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
        }
        $this->redirect('/admin/salary/periods');
    }

    // ──────────────────────────────────────────────
    // ASSOCIATE SALARY DASHBOARD
    // ──────────────────────────────────────────────

    public function associateDashboard()
    {
        $this->requireAdmin();
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            // Get associate salary statistics
            $totalAssociates = $this->db->fetch("SELECT COUNT(*) as c FROM associates WHERE status='active' {$tidSql}", $tidParams)['c'] ?? 0;
            $salaryEligible = $this->db->fetch("SELECT COUNT(*) as c FROM associates WHERE salary_eligible=1 AND status='active' {$tidSql}", $tidParams)['c'] ?? 0;
            $targetBonusEligible = $this->db->fetch("SELECT COUNT(*) as c FROM associates WHERE target_bonus_eligible=1 AND status='active' {$tidSql}", $tidParams)['c'] ?? 0;
            $totalSalaryAmount = $this->db->fetch("SELECT COALESCE(SUM(salary_amount),0) as total FROM associates WHERE salary_eligible=1 AND status='active' {$tidSql}", $tidParams)['total'] ?? 0;
            $totalTargetBonus = $this->db->fetch("SELECT COALESCE(SUM(target_bonus_amount),0) as total FROM associates WHERE target_bonus_eligible=1 AND status='active' {$tidSql}", $tidParams)['total'] ?? 0;

            // Get associates with registration status
            $associates = $this->db->fetchAll("
                SELECT a.*, u.name as user_name, u.email as user_email,
                       (a.registration_count >= a.required_registrations) as registration_complete,
                       (a.required_registrations - a.registration_count) as pending_registrations
                FROM associates a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.status='active' {$tidSql}
                ORDER BY a.registration_count DESC, a.total_sales DESC
                LIMIT 50
            ", $tidParams) ?? [];
        } catch (\Exception $e) {
            $totalAssociates = 0; $salaryEligible = 0; $targetBonusEligible = 0;
            $totalSalaryAmount = 0; $totalTargetBonus = 0; $associates = [];
        }
        return $this->render('admin/salary/associate_dashboard', [
            'page_title' => 'Associate Salary Dashboard',
            'total_associates' => $totalAssociates,
            'salary_eligible' => $salaryEligible,
            'target_bonus_eligible' => $targetBonusEligible,
            'total_salary_amount' => $totalSalaryAmount,
            'total_target_bonus' => $totalTargetBonus,
            'associates' => $associates
        ]);
    }

    public function updateAssociateSalary()
    {
        $this->requireAdmin();
        $associateId = (int)($_POST['associate_id'] ?? 0);
        $salaryAmount = (float)($_POST['salary_amount'] ?? 0);
        $salaryEligible = isset($_POST['salary_eligible']) ? 1 : 0;
        $targetBonusAmount = (float)($_POST['target_bonus_amount'] ?? 0);
        $targetBonusEligible = isset($_POST['target_bonus_eligible']) ? 1 : 0;

        if ($associateId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid associate ID']);
            exit;
        }

        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $this->db->execute("
                UPDATE associates
                SET salary_amount = ?, salary_eligible = ?, target_bonus_amount = ?, target_bonus_eligible = ?
                WHERE id = ? {$tidSql}
            ", array_merge([$salaryAmount, $salaryEligible, $targetBonusAmount, $targetBonusEligible, $associateId], $tidParams));

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Associate salary updated successfully']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function processAssociateSalary()
    {
        $this->requireAdmin();
        $associateId = (int)($_POST['associate_id'] ?? 0);
        $paymentMonth = (int)($_POST['payment_month'] ?? date('n'));
        $paymentYear = (int)($_POST['payment_year'] ?? date('Y'));

        if ($associateId <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid associate ID']);
            exit;
        }

        try {
            // Get associate details
            [$tidSql, $tidParams] = $this->tenantWhere();
            $associate = $this->db->fetch("
                SELECT a.*, u.name as user_name
                FROM associates a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.id = ? AND a.salary_eligible = 1 {$tidSql}
            ", array_merge([$associateId], $tidParams));

            if (!$associate) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Associate not found or not salary eligible']);
                exit;
            }

            // Fallback defaults for settings if not in DB
            $tid = (int)$this->tenantId();
            $bonusPerLead = (float)($this->db->fetch("SELECT setting_value FROM settings WHERE setting_key='crm_conversion_bonus' AND tenant_id=?", [$tid])['setting_value'] ?? 500);

            // 1. Calculate CRM Conversion Bonus
            $crmBonus = 0;
            // Find employee_id for this associate's user_id
            $emp = $this->db->fetch("SELECT id FROM employees WHERE user_id=?", [$associate['user_id']]);
            $employeeId = $emp['id'] ?? 0;
            $convertedLeads = 0;
            
            if ($employeeId > 0) {
                $convertedLeads = (int)($this->db->fetch("
                    SELECT COUNT(*) as cnt 
                    FROM leads 
                    WHERE assigned_to=? AND status='converted' 
                    AND MONTH(updated_at)=? AND YEAR(updated_at)=?
                ", [$employeeId, $paymentMonth, $paymentYear])['cnt'] ?? 0);
                
                if ($convertedLeads > 0) {
                    $crmBonus = $convertedLeads * $bonusPerLead;
                }
            }

            // Calculate final amounts
            $baseSalary = (float)$associate['salary_amount'];
            $grossAmount = $baseSalary + $crmBonus;
            
            // Note: associate['target_bonus_amount'] exists but is handled separately or added here?
            // Let's just add CRM Bonus for now.
            $remarks = sprintf("Associate Salary Processed. Base: %.2f. CRM Bonus: +%.2f (%d leads).", 
                                $baseSalary, $crmBonus, $convertedLeads);

            // Create salary payment record
            $params = [
                $this->tenantId(),
                $associateId,
                $paymentMonth,
                $paymentYear,
                $baseSalary,
                $grossAmount,
                $grossAmount, // net amount (associates might not have deductions here)
                $remarks
            ];
            $this->db->execute("
                INSERT INTO salary_payments
                (tenant_id, associate_id, payment_month, payment_year, payment_date,
                 basic_amount, gross_amount, net_amount, payment_status, remarks, created_at, updated_at)
                VALUES (?, ?, ?, ?, CURDATE(), ?, ?, ?, 'pending', ?, NOW(), NOW())
            ", $params);

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Salary payment processed successfully']);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function stats()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        try {
            $monthly = $this->db->fetchAll("
                SELECT DATE_FORMAT(payment_date,'%b') as label, COALESCE(SUM(net_amount),0) as total
                FROM salary_payments WHERE payment_status='paid' AND payment_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(payment_date,'%Y-%m') ORDER BY MIN(payment_date)
            ") ?: [];
            echo json_encode(['success' => true, 'data' => $monthly]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // ──────────────────────────────────────────────
    // SALARY STRUCTURES
    // ──────────────────────────────────────────────

    public function structures()
    {
        $this->requireAdmin();
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            $structures = $this->db->fetchAll("
                SELECT s.*, u.name as employee_name, u.email as employee_email
                FROM salary_structures s
                LEFT JOIN users u ON s.employee_id = u.id
                ORDER BY s.status DESC, s.created_at DESC
            ") ?? [];
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role='employee' {$tidSql} ORDER BY name", $tidParams) ?? [];
        } catch (\Exception $e) {
            $structures = []; $users = [];
        }
        return $this->render('admin/salary/structures', [
            'page_title' => 'Salary Structures',
            'structures' => $structures,
            'users' => $users
        ]);
    }

    public function storeStructure()
    {
        $this->requireAdmin();
        $employee_id = (int)($_POST['employee_id'] ?? 0);
        $basic = ($_POST['basic_salary'] ?? '') === '' ? null : (float)($_POST['basic_salary'] ?? 0);
        $hra = ($_POST['hra'] ?? '') === '' ? null : (float)($_POST['hra'] ?? 0);
        $conveyance = ($_POST['conveyance'] ?? '') === '' ? null : (float)($_POST['conveyance'] ?? 0);
        $medical = ($_POST['medical_allowance'] ?? '') === '' ? null : (float)($_POST['medical_allowance'] ?? 0);
        $special = ($_POST['special_allowance'] ?? '') === '' ? null : (float)($_POST['special_allowance'] ?? 0);
        $other_allowances = ($_POST['other_allowances'] ?? '') === '' ? null : (float)($_POST['other_allowances'] ?? 0);
        $pf_employee = ($_POST['pf_employee'] ?? '') === '' ? null : (float)($_POST['pf_employee'] ?? 0);
        $tds = ($_POST['tds'] ?? '') === '' ? null : (float)($_POST['tds'] ?? 0);

        // NOTE: gross_salary/total_deductions/net_salary are STORED GENERATED
        // columns — never write them; MySQL computes them from the base columns.
        $eff = $_POST['effective_date'] ?? date('Y-m-d');
        $tid = (int)$this->tenantId();
        try {
            $stmt = $this->db->prepare("INSERT INTO salary_structures (employee_id, basic_salary, hra, conveyance, medical_allowance, special_allowance, other_allowances, pf_employee, tds, effective_date, status, tenant_id, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,'active',?,NOW())");
            $stmt->execute([$employee_id, $basic, $hra, $conveyance, $medical, $special, $other_allowances, $pf_employee, $tds, $eff, $tid]);
            $sid = $this->db->lastInsertId();
            $this->logHistory($employee_id, 'salary_structure_created', '0', (string)$sid, (int)($_SESSION['admin_id'] ?? 0));
            $this->setFlash('success', 'Salary structure created');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/structures');
    }

    public function editStructure($id)
    {
        $this->requireAdmin();
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            $structure = $this->db->fetch("SELECT s.*, u.name as employee_name FROM salary_structures s LEFT JOIN users u ON s.employee_id=u.id WHERE s.id=?", [$id]);
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role='employee' {$tidSql} ORDER BY name", $tidParams) ?? [];
        } catch (\Exception $e) {
            $structure = null; $users = [];
        }
        if (!$structure) { $this->setFlash('error', 'Not found'); $this->redirect('/admin/salary/structures'); }
        return $this->render('admin/salary/structures', [
            'page_title' => 'Edit Salary Structure',
            'edit_structure' => $structure,
            'structures' => $this->db->fetchAll("SELECT s.*, u.name as en FROM salary_structures s LEFT JOIN users u ON s.employee_id=u.id ORDER BY s.created_at DESC") ?? [],
            'users' => $users
        ]);
    }

    public function updateStructure($id)
    {
        $this->requireAdmin();
        $basic = ($_POST['basic_salary'] ?? '') === '' ? null : (float)($_POST['basic_salary'] ?? 0);
        $hra = ($_POST['hra'] ?? '') === '' ? null : (float)($_POST['hra'] ?? 0);
        $conveyance = ($_POST['conveyance'] ?? '') === '' ? null : (float)($_POST['conveyance'] ?? 0);
        $medical = ($_POST['medical_allowance'] ?? '') === '' ? null : (float)($_POST['medical_allowance'] ?? 0);
        $special = ($_POST['special_allowance'] ?? '') === '' ? null : (float)($_POST['special_allowance'] ?? 0);
        $other_allowances = ($_POST['other_allowances'] ?? '') === '' ? null : (float)($_POST['other_allowances'] ?? 0);
        $pf_employee = ($_POST['pf_employee'] ?? '') === '' ? null : (float)($_POST['pf_employee'] ?? 0);
        $tds = ($_POST['tds'] ?? '') === '' ? null : (float)($_POST['tds'] ?? 0);

        // NOTE: gross_salary/total_deductions/net_salary are STORED GENERATED
        // columns — never write them; MySQL recomputes them automatically.
        $eff = $_POST['effective_date'] ?? date('Y-m-d');
        $tid = (int)$this->tenantId();
        try {
            $this->db->execute("UPDATE salary_structures SET basic_salary=?, hra=?, conveyance=?, medical_allowance=?, special_allowance=?, other_allowances=?, pf_employee=?, tds=?, effective_date=? WHERE id=? AND tenant_id=?", [$basic, $hra, $conveyance, $medical, $special, $other_allowances, $pf_employee, $tds, $eff, $id, $tid]);
            $this->setFlash('success', 'Structure updated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/structures');
    }

    // ──────────────────────────────────────────────
    // SALARY PAYMENTS
    // ──────────────────────────────────────────────

    public function payments()
    {
        $this->requireAdmin();
        $status = $_GET['status'] ?? '';
        $employee_id = (int)($_GET['employee_id'] ?? 0);
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            $where = []; $params = [];
            if ($status) { $where[] = 'sp.status=?'; $params[] = $status; }
            if ($employee_id) { $where[] = 'sp.employee_id=?'; $params[] = $employee_id; }
            $sql = "SELECT sp.*, u.name as employee_name FROM salary_payments sp LEFT JOIN users u ON sp.employee_id=u.id";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY sp.created_at DESC LIMIT 100";
            $payments = $this->db->fetchAll($sql, $params) ?? [];
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role='employee' {$tidSql} ORDER BY name", $tidParams) ?? [];
        } catch (\Exception $e) {
            $payments = []; $users = [];
        }
        return $this->render('admin/salary/payments', [
            'page_title' => 'Salary Payments',
            'payments' => $payments,
            'users' => $users,
            'filter_status' => $status,
            'filter_employee' => $employee_id
        ]);
    }

    public function createPayment()
    {
        $this->requireAdmin();
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            $users = $this->db->fetchAll("SELECT DISTINCT u.id, u.name FROM users u JOIN salary_structures s ON u.id=s.employee_id WHERE s.status='active' AND u.role='employee' {$tidSql} ORDER BY u.name", $tidParams) ?? [];
            $structures = $this->db->fetchAll("SELECT s.*, u.name as employee_name FROM salary_structures s LEFT JOIN users u ON s.employee_id=u.id WHERE s.status='active' ORDER BY u.name") ?? [];
        } catch (\Exception $e) {
            $users = []; $structures = [];
        }
        return $this->render('admin/salary/payment_create', [
            'page_title' => 'Create Salary Payment',
            'users' => $users,
            'structures' => $structures
        ]);
    }

    public function storePayment()
    {
        $this->requireAdmin();
        $employee_id = (int)($_POST['employee_id'] ?? 0);
        $structure_id = (int)($_POST['structure_id'] ?? 0);
        
        $basic = (float)($_POST['basic_amount'] ?? 0);
        $gross = (float)($_POST['gross_amount'] ?? 0);
        $deductions = (float)($_POST['deduction_amount'] ?? 0);
        $net = $gross - $deductions;
        $payment_date = $_POST['payment_date'] ?? date('Y-m-d');
        $month = (int)date('m', strtotime($payment_date));
        $year = (int)date('Y', strtotime($payment_date));
        
        $method = $_POST['payment_method'] ?? 'bank_transfer';
        $txn = $_POST['transaction_id'] ?? '';
        $status = $_POST['payment_status'] ?? 'pending';
        $notes = $_POST['remarks'] ?? '';
        $tid = (int)$this->tenantId();
        try {
            $stmt = $this->db->prepare("INSERT INTO salary_payments (employee_id, salary_structure_id, payment_month, payment_year, payment_date, basic_amount, gross_amount, deduction_amount, net_amount, payment_method, transaction_id, payment_status, created_by, remarks, tenant_id, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())");
            $stmt->execute([$employee_id, $structure_id ?: null, $month, $year, $payment_date, $basic, $gross, $deductions, $net, $method, $txn, $status, (int)($_SESSION['admin_id'] ?? 0), $notes, $tid]);
            $this->logHistory($employee_id, 'payment_created', '0', number_format($net, 2), (int)($_SESSION['admin_id'] ?? 0));
            $this->setFlash('success', 'Payment record created');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/payments');
    }

    public function viewPayment($id)
    {
        $this->requireAdmin();
        try {
            $payment = $this->db->fetch("SELECT sp.*, u.name as employee_name, u.email as employee_email, u.phone as employee_phone FROM salary_payments sp LEFT JOIN users u ON sp.employee_id=u.id WHERE sp.id=?", [$id]);
        } catch (\Exception $e) {
            $payment = null;
        }
        if (!$payment) { $this->setFlash('error', 'Payment not found'); $this->redirect('/admin/salary/payments'); }
        return $this->render('admin/salary/payment_view', [
            'page_title' => 'Payment Details',
            'payment' => $payment
        ]);
    }

    public function markPaymentPaid($id)
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        try {
            $p = $this->db->fetch("SELECT id, payment_status FROM salary_payments WHERE id=? AND tenant_id=?", [$id, $tid]);
            if (!$p) { $this->setFlash('error', 'Payment not found'); $this->redirect('/admin/salary/payments'); }
            if ($p['payment_status'] === 'paid') { $this->setFlash('error', 'Payment is already paid'); $this->redirect('/admin/salary/payments'); }
            if ($p['payment_status'] === 'cancelled') { $this->setFlash('error', 'Cancelled payment cannot be marked paid'); $this->redirect('/admin/salary/payments'); }
            $this->db->execute("UPDATE salary_payments SET payment_status='paid', payment_date=CURDATE() WHERE id=? AND tenant_id=?", [$id, $tid]);
            $this->setFlash('success', 'Payment #' . (int)$id . ' marked as paid');
        } catch (\Exception $e) {
            error_log("[SalaryController] " . __METHOD__ . "() exception: " . $e->getMessage());
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/payments');
    }

    public function cancelPayment($id)
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        try {
            $p = $this->db->fetch("SELECT id, payment_status FROM salary_payments WHERE id=? AND tenant_id=?", [$id, $tid]);
            if (!$p) { $this->setFlash('error', 'Payment not found'); $this->redirect('/admin/salary/payments'); }
            if ($p['payment_status'] === 'paid') { $this->setFlash('error', 'Paid payment cannot be cancelled'); $this->redirect('/admin/salary/payments'); }
            if ($p['payment_status'] === 'cancelled') { $this->setFlash('error', 'Payment is already cancelled'); $this->redirect('/admin/salary/payments'); }
            $this->db->execute("UPDATE salary_payments SET payment_status='cancelled' WHERE id=? AND tenant_id=?", [$id, $tid]);
            $this->setFlash('success', 'Payment #' . (int)$id . ' cancelled');
        } catch (\Exception $e) {
            error_log("[SalaryController] " . __METHOD__ . "() exception: " . $e->getMessage());
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/payments');
    }

    public function processBulk()
    {
        $this->requireAdmin();
        $month = (int)($_POST['month'] ?? 0);
        $year = (int)($_POST['year'] ?? 0);
        if (!$month || !$year) { $this->setFlash('error', 'Month and year required'); $this->redirect('/admin/salary/payments'); }
        
        // Check payroll period lock
        $period = $this->db->fetch("SELECT status FROM payroll_periods WHERE tenant_id=? AND period_month=? AND period_year=?", [(int)$this->tenantId(), $month, $year]);
        if ($period && in_array($period['status'], ['locked', 'closed'])) {
            $this->setFlash('error', "Payroll period $month/$year is {$period['status']}. Cannot process payments.");
            $this->redirect('/admin/salary/payments');
        }
        
        $tid = (int)$this->tenantId();
        // Fallback defaults for settings if not in DB
        $bonusPerLead = (float)($this->db->fetch("SELECT setting_value FROM settings WHERE setting_key='crm_conversion_bonus' AND tenant_id=?", [$tid])['setting_value'] ?? 500);
        
        $totalDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        try {
            $users = $this->db->fetchAll("SELECT DISTINCT s.employee_id, s.id as structure_id, s.basic_salary, s.gross_salary, s.total_deductions, s.net_salary FROM salary_structures s WHERE s.status='active' AND s.employee_id NOT IN (SELECT employee_id FROM salary_payments WHERE payment_month=? AND payment_year=? AND payment_status='paid')", [$month, $year]) ?? [];
            $count = 0;
            foreach ($users as $emp) {
                $employeeId = $emp['employee_id'];
                $basic = (float)$emp['basic_salary'];
                $gross = (float)$emp['gross_salary'];
                $deductions = (float)$emp['total_deductions'];
                
                // 1. Calculate Attendance & LOP (Loss of Pay)
                // We count present, holiday, leave. We also count explicit 'absent' and 'half_day'.
                $attStats = $this->db->fetch("
                    SELECT 
                        SUM(CASE WHEN attendance_status='present' THEN 1 ELSE 0 END) as present_days,
                        SUM(CASE WHEN attendance_status='leave' THEN 1 ELSE 0 END) as leave_days,
                        SUM(CASE WHEN attendance_status='holiday' THEN 1 ELSE 0 END) as holiday_days,
                        SUM(CASE WHEN attendance_status='absent' THEN 1 ELSE 0 END) as explicit_absences,
                        SUM(CASE WHEN attendance_status='half_day' THEN 1 ELSE 0 END) as half_days
                    FROM employee_attendance 
                    WHERE employee_id=? AND MONTH(attendance_date)=? AND YEAR(attendance_date)=?
                ", [$employeeId, $month, $year]);
                
                $presentDays = (int)($attStats['present_days'] ?? 0);
                $leaveDays = (int)($attStats['leave_days'] ?? 0);
                $holidayDays = (int)($attStats['holiday_days'] ?? 0);
                $explicitAbsences = (int)($attStats['explicit_absences'] ?? 0);
                $halfDays = (int)($attStats['half_days'] ?? 0);
                
                $paidDays = $presentDays + $leaveDays + $holidayDays + ($halfDays * 0.5);
                
                // Calculate LOP based on explicit absences first. If no attendance records exist, 
                // we assume full working month (to prevent 100% LOP if attendance module is unused).
                $totalAbsences = max(0, $totalDaysInMonth - $paidDays);
                $lopAmount = 0;
                
                if ($totalAbsences > 0) {
                    $perDaySalary = $basic / $totalDaysInMonth;
                    $lopAmount = $perDaySalary * $totalAbsences;
                }
                
                // 2. Calculate CRM Conversion Bonus
                $crmBonus = 0;
                $convertedLeads = (int)($this->db->fetch("
                    SELECT COUNT(*) as cnt 
                    FROM leads 
                    WHERE assigned_to=? AND status='converted' 
                    AND MONTH(updated_at)=? AND YEAR(updated_at)=?
                ", [$employeeId, $month, $year])['cnt'] ?? 0);
                
                if ($convertedLeads > 0) {
                    $crmBonus = $convertedLeads * $bonusPerLead;
                }
                
                // 3. Advance/Loan Recovery (logs recovery row for this month)
                $advanceRecovery = 0;
                try {
                    $advService = new \App\Services\AdvanceRecoveryService();
                    $advanceRecovery = $advService->calculateRecovery($employeeId, $month, $year);
                } catch (\Throwable $e) {
                    error_log('SalaryController::processBulk advance recovery: ' . $e->getMessage());
                }

                // 4. Finalize Net Salary
                // Gross = Base Gross - LOP + CRM Bonus; Deductions += Advance Recovery
                $finalGross = ($gross - $lopAmount) + $crmBonus;
                $finalDeductions = $deductions + $advanceRecovery;
                $net = $finalGross - $finalDeductions;

                $remarks = sprintf("Bulk processed. LOP: -%.2f (%s absent). CRM Bonus: +%.2f (%d leads). Advance Recovery: -%.2f.",
                                    $lopAmount, $totalAbsences, $crmBonus, $convertedLeads, $advanceRecovery);

                // Get or create payroll period
                $period = $this->db->fetch("SELECT id FROM payroll_periods WHERE tenant_id=? AND period_month=? AND period_year=?", [$tid, $month, $year]);
                $periodId = $period['id'] ?? null;
                if (!$periodId) {
                    $this->db->execute("INSERT INTO payroll_periods (tenant_id, period_month, period_year, status) VALUES (?,?,?,'open')", [$tid, $month, $year]);
                    $periodId = (int)$this->db->lastInsertId();
                }

                $this->db->execute("INSERT INTO salary_payments (employee_id, salary_structure_id, payment_month, payment_year, payment_date, basic_amount, gross_amount, deduction_amount, net_amount, payment_status, created_by, remarks, tenant_id, payroll_period_id, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())", [$employeeId, $emp['structure_id'], $month, $year, $year . '-' . str_pad($month,2,'0',STR_PAD_LEFT) . '-01', $basic, $finalGross, $finalDeductions, $net, 'pending', (int)($_SESSION['admin_id'] ?? 0), $remarks, $tid, $periodId]);
                $count++;
            }
            $this->setFlash('success', "Bulk processed $count users with LOP & CRM Bonuses");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Bulk failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/payments');
    }

    // ──────────────────────────────────────────────
    // SALARY PAYOUTS
    // ──────────────────────────────────────────────

    public function payouts()
    {
        $this->requireAdmin();
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            $batches = $this->db->fetchAll("
                SELECT payout_batch_id, COUNT(*) as total, SUM(amount) as total_amount,
                       SUM(CASE WHEN status='processed' THEN 1 ELSE 0 END) as processed_count,
                       SUM(CASE WHEN status='failed' THEN 1 ELSE 0 END) as failed_count,
                       MIN(payout_date) as payout_date, MAX(created_at) as created_at
                FROM salary_payouts GROUP BY payout_batch_id ORDER BY MAX(created_at) DESC
            ") ?? [];
            $payouts = $this->db->fetchAll("SELECT po.*, u.name as employee_name FROM salary_payouts po LEFT JOIN users u ON po.employee_id=u.id ORDER BY po.created_at DESC LIMIT 100") ?? [];
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role='employee' AND status='active' {$tidSql} ORDER BY name", $tidParams) ?? [];
        } catch (\Exception $e) {
            $batches = []; $payouts = []; $users = [];
        }
        return $this->render('admin/salary/payouts', [
            'page_title' => 'Salary Payouts',
            'batches' => $batches,
            'payouts' => $payouts,
            'users' => $users
        ]);
    }

    public function createPayout()
    {
        $this->requireAdmin();
        $employee_ids = $_POST['employee_ids'] ?? [];
        $amounts = $_POST['amounts'] ?? [];
        $method = $_POST['payment_method'] ?? 'bank_transfer';
        $notes = $_POST['notes'] ?? '';
        if (empty($employee_ids)) { $this->setFlash('error', 'Select at least one employee'); $this->redirect('/admin/salary/payouts'); }
        try {
            $batch_id = 'BATCH-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            foreach ($employee_ids as $i => $eid) {
                $amt = (float)($amounts[$i] ?? 0);
                if ($amt <= 0) continue;
                $this->db->execute("INSERT INTO salary_payouts (payout_batch_id, employee_id, amount, payout_date, status, payment_method, notes, tenant_id, created_at) VALUES (?,?,?,?,?,?,?, ?,NOW())", [$batch_id, (int)$eid, $amt, date('Y-m-d'), 'pending', $method, $notes, (int)$this->tenantId()]);
            }
            $this->setFlash('success', "Payout batch $batch_id created");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/payouts');
    }

    public function processPayout($id)
    {
        $this->requireAdmin();
        try {
            $payout = $this->db->fetch("SELECT * FROM salary_payouts WHERE id=?", [$id]);
            if (!$payout) { $this->setFlash('error', 'Payout not found'); $this->redirect('/admin/salary/payouts'); }
            $ref = $_POST['reference_no'] ?? 'REF-' . $payout['payout_batch_id'] . '-' . $id;
            $tid = (int)$this->tenantId();
            $this->db->execute("UPDATE salary_payouts SET status='processed', reference_no=?, payout_date=CURDATE() WHERE id=? AND tenant_id=?", [$ref, $id, $tid]);
            $this->setFlash('success', 'Payout processed');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/payouts');
    }

    // ──────────────────────────────────────────────
    // ADVANCES (salary advance / loan: create + approve/reject)
    // ──────────────────────────────────────────────

    public function advances()
    {
        $this->requireAdmin();
        $status = $_GET['status'] ?? '';
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            $where = []; $params = [];
            if ($status) { $where[] = 'a.status=?'; $params[] = $status; }
            $sql = "SELECT a.*, u.name as employee_name, e.employee_code FROM employee_advances a LEFT JOIN employees e ON a.employee_id=e.id LEFT JOIN users u ON e.user_id=u.id";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY a.created_at DESC LIMIT 200";
            $advances = $this->db->fetchAll($sql, $params) ?? [];
            $users = $this->db->fetchAll("SELECT e.id, u.name, e.employee_code FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status='active' {$tidSql} ORDER BY u.name", $tidParams) ?? [];
            $summary = $this->db->fetch("SELECT COUNT(*) as total, COALESCE(SUM(CASE WHEN status='pending' THEN amount ELSE 0 END),0) as pending_amount, COALESCE(SUM(CASE WHEN status='active' THEN amount ELSE 0 END),0) as active_amount FROM employee_advances") ?? [];
        } catch (\Exception $e) {
            $advances = []; $users = []; $summary = [];
        }
        return $this->render('admin/salary/advances', [
            'page_title' => 'Salary Advances',
            'advances' => $advances,
            'users' => $users,
            'summary' => $summary,
            'filter_status' => $status,
        ]);
    }

    public function createAdvance()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/salary/advances'); }
        try {
            $svc = new \App\Services\AdvanceRecoveryService();
            $result = $svc->createAdvance([
                'employee_id' => (int)($_POST['employee_id'] ?? 0),
                'amount' => (float)($_POST['amount'] ?? 0),
                'reason' => trim($_POST['reason'] ?? ''),
                'repay_months' => (int)($_POST['repay_months'] ?? 0),
                'interest_rate' => (float)($_POST['interest_rate'] ?? 0),
                'start_month' => (int)($_POST['start_month'] ?? date('n')),
                'start_year' => (int)($_POST['start_year'] ?? date('Y')),
            ]);
            if ($result['success']) {
                $this->setFlash('success', "Advance {$result['advance_no']} created. EMI: ₹" . number_format($result['emi'], 2));
            } else {
                $this->setFlash('error', 'Failed: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/advances');
    }

    public function approveAdvance($id)
    {
        $this->requireAdmin();
        try {
            $svc = new \App\Services\AdvanceRecoveryService();
            if ($svc->approveAdvance((int)$id, (int)($_SESSION['admin_id'] ?? 0))) {
                $this->setFlash('success', 'Advance approved. Recovery starts from its start month.');
            } else {
                $this->setFlash('error', 'Advance not found or already processed');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/advances');
    }

    public function rejectAdvance($id)
    {
        $this->requireAdmin();
        try {
            $tid = (int)$this->tenantId();
            $this->db->execute("UPDATE employee_advances SET status='rejected' WHERE id=? AND status='pending' AND tenant_id=?", [(int)$id, $tid]);
            $this->setFlash('success', 'Advance rejected');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/advances');
    }

    // ──────────────────────────────────────────────
    // REIMBURSEMENTS (claims: approve/reject/mark-paid)
    // ──────────────────────────────────────────────

    public function reimbursements()
    {
        $this->requireAdmin();
        $status = $_GET['status'] ?? '';
        try {
            $where = []; $params = [];
            if ($status) { $where[] = 'r.status=?'; $params[] = $status; }
            $sql = "SELECT r.*, u.name as employee_name, e.employee_code FROM employee_reimbursements r LEFT JOIN employees e ON r.employee_id=e.id LEFT JOIN users u ON e.user_id=u.id";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY r.created_at DESC LIMIT 200";
            $claims = $this->db->fetchAll($sql, $params) ?? [];
            $summary = $this->db->fetch("SELECT COUNT(*) as total, COALESCE(SUM(CASE WHEN status='pending' THEN amount ELSE 0 END),0) as pending_amount, COALESCE(SUM(CASE WHEN status='paid' THEN amount ELSE 0 END),0) as paid_amount FROM employee_reimbursements") ?? [];
        } catch (\Exception $e) {
            $claims = []; $summary = [];
        }
        return $this->render('admin/salary/reimbursements', [
            'page_title' => 'Reimbursement Claims',
            'claims' => $claims,
            'summary' => $summary,
            'filter_status' => $status,
        ]);
    }

    public function approveReimbursement($id)
    {
        $this->requireAdmin();
        try {
            $tid = (int)$this->tenantId();
            $this->db->execute("UPDATE employee_reimbursements SET status='approved', approved_by=?, approved_at=NOW() WHERE id=? AND status='pending' AND tenant_id=?", [(int)($_SESSION['admin_id'] ?? 0), (int)$id, $tid]);
            $this->setFlash('success', 'Claim approved');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/reimbursements');
    }

    public function rejectReimbursement($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/salary/reimbursements'); }
        try {
            $tid = (int)$this->tenantId();
            $this->db->execute("UPDATE employee_reimbursements SET status='rejected', rejection_reason=?, approved_by=?, approved_at=NOW() WHERE id=? AND status='pending' AND tenant_id=?", [trim($_POST['rejection_reason'] ?? ''), (int)($_SESSION['admin_id'] ?? 0), (int)$id, $tid]);
            $this->setFlash('success', 'Claim rejected');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/reimbursements');
    }

    public function payReimbursement($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/salary/reimbursements'); }
        try {
            $tid = (int)$this->tenantId();
            $this->db->execute("UPDATE employee_reimbursements SET status='paid', paid_at=NOW(), payment_reference=? WHERE id=? AND status='approved' AND tenant_id=?", [trim($_POST['payment_reference'] ?? ('PAY-' . date('YmdHis'))), (int)$id, $tid]);
            $this->setFlash('success', 'Claim marked as paid');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/reimbursements');
    }

    // ──────────────────────────────────────────────
    // SALARY HISTORY
    // ──────────────────────────────────────────────

    public function history()
    {
        $this->requireAdmin();
        $employee_id = (int)($_GET['employee_id'] ?? 0);
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            $where = []; $params = [];
            if ($employee_id) { $where[] = 'sh.employee_id=?'; $params[] = $employee_id; }
            $sql = "SELECT sh.*, u.name as employee_name, uc.name as changed_by_name FROM salary_history sh LEFT JOIN users u ON sh.employee_id=u.id LEFT JOIN users uc ON sh.approved_by=uc.id";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY sh.created_at DESC LIMIT 200";
            $history = $this->db->fetchAll($sql, $params) ?? [];
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role='employee' {$tidSql} ORDER BY name", $tidParams) ?? [];
        } catch (\Exception $e) {
            $history = []; $users = [];
        }
        return $this->render('admin/salary/history', [
            'page_title' => 'Salary History',
            'history' => $history,
            'users' => $users,
            'filter_employee' => $employee_id
        ]);
    }

    public function historyByEmployee($id)
    {
        $this->requireAdmin();
        try {
            $employee = $this->db->fetch("SELECT id, name, email FROM users WHERE id=?", [$id]);
            $history = $this->db->fetchAll("SELECT sh.*, uc.name as changed_by_name FROM salary_history sh LEFT JOIN users uc ON sh.approved_by=uc.id WHERE sh.employee_id=? ORDER BY sh.created_at DESC LIMIT 200", [$id]) ?? [];
        } catch (\Exception $e) {
            $employee = null; $history = [];
        }
        if (!$employee) { $this->setFlash('error', 'Employee not found'); $this->redirect('/admin/salary/history'); }
        return $this->render('admin/salary/history', [
            'page_title' => 'History - ' . htmlspecialchars($employee['name'] ?? ''),
            'history' => $history,
            'users' => [$employee],
            'filter_employee' => $id
        ]);
    }

    // ──────────────────────────────────────────────
    // SALARY CONTRACTS
    // ──────────────────────────────────────────────

    public function contracts()
    {
        $this->requireAdmin();
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            $contracts = $this->db->fetchAll("
                SELECT c.*, u.name as employee_name
                FROM salary_contracts c
                LEFT JOIN users u ON c.employee_id = u.id
                ORDER BY c.created_at DESC
            ") ?? [];
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role='employee' {$tidSql} ORDER BY name", $tidParams) ?? [];
        } catch (\Exception $e) {
            $contracts = []; $users = [];
        }
        return $this->render('admin/salary/contracts', [
            'page_title' => 'Salary Contracts',
            'contracts' => $contracts,
            'users' => $users
        ]);
    }

    public function storeContract()
    {
        $this->requireAdmin();
        $employee_id = (int)($_POST['employee_id'] ?? 0);
        $type = $_POST['contract_type'] ?? 'permanent';
        $start = $_POST['start_date'] ?? date('Y-m-d');
        $end = $_POST['end_date'] ?? null;
        $amount = (float)($_POST['salary_amount'] ?? 0);
        $bonus = (float)($_POST['signing_bonus'] ?? 0);
        $terms = $_POST['terms'] ?? '';
        // signing_bonus has no column: preserve it inside terms instead of dropping it.
        if ($bonus > 0) $terms .= ($terms !== '' ? "\n" : '') . 'Signing bonus: Rs.' . number_format($bonus, 2);
        $tid = (int)$this->tenantId();
        try {
            $contractNo = 'CNT-' . date('Ym') . '-' . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $this->db->execute("INSERT INTO salary_contracts (contract_number, employee_id, start_date, end_date, ctc, basic_salary, terms, status, tenant_id, created_at) VALUES (?,?,?,?,?,?,?,'active',?,NOW())", [$contractNo, $employee_id, $start, $end ?: null, $amount, $amount, $terms, $tid]);
            $this->logHistory($employee_id, 'contract_created', '0', "type=$type amount=$amount", (int)($_SESSION['admin_id'] ?? 0));
            $this->setFlash('success', 'Contract created');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/contracts');
    }

    public function viewContract($id)
    {
        $this->requireAdmin();
        try {
            $contract = $this->db->fetch("SELECT c.*, u.name as employee_name, u.email as employee_email FROM salary_contracts c LEFT JOIN users u ON c.employee_id=u.id WHERE c.id=?", [$id]);
        } catch (\Exception $e) {
            $contract = null;
        }
        if (!$contract) { $this->setFlash('error', 'Contract not found'); $this->redirect('/admin/salary/contracts'); }
        return $this->render('admin/salary/contract_view', [
            'page_title' => 'Contract Details',
            'contract' => $contract
        ]);
    }

    public function terminateContract($id)
    {
        $this->requireAdmin();
        try {
            $contract = $this->db->fetch("SELECT * FROM salary_contracts WHERE id=?", [$id]);
            if (!$contract) { $this->setFlash('error', 'Not found'); $this->redirect('/admin/salary/contracts'); }
            $tid = (int)$this->tenantId();
            $this->db->execute("UPDATE salary_contracts SET status='terminated', end_date=CURDATE() WHERE id=? AND tenant_id=?", [$id, $tid]);
            $this->logHistory($contract['employee_id'], 'contract_terminated', 'active', 'terminated', (int)($_SESSION['admin_id'] ?? 0));
            $this->setFlash('success', 'Contract terminated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/contracts');
    }

    // ──────────────────────────────────────────────
    // SALARY PLANS
    // ──────────────────────────────────────────────

    public function plans()
    {
        $this->requireAdmin();
        try {
            $plans = $this->db->fetchAll("SELECT * FROM salary_plans ORDER BY created_at DESC") ?? [];
        } catch (\Exception $e) {
            $plans = [];
        }
        return $this->render('admin/salary/plans', [
            'page_title' => 'Salary Plans',
            'plans' => $plans
        ]);
    }

    public function storePlan()
    {
        $this->requireAdmin();
        $name = $_POST['name'] ?? '';
        $desc = $_POST['description'] ?? '';
        $base = (float)($_POST['base_salary'] ?? 0);
        $bonus = (float)($_POST['bonus_percent'] ?? 0);
        $comm = (float)($_POST['commission_percent'] ?? 0);
        $benefits = $_POST['benefits_json'] ?? '';
        if (!$name) { $this->setFlash('error', 'Plan name required'); $this->redirect('/admin/salary/plans'); }
        $tid = (int)$this->tenantId();
        try {
            $this->db->execute("INSERT INTO salary_plans (name, description, base_salary, bonus_percent, commission_percent, benefits_json, is_active, tenant_id, created_at) VALUES (?,?,?,?,?,?,1,?,NOW())", [$name, $desc, $base, $bonus, $comm, $benefits, $tid]);
            $this->setFlash('success', 'Plan created');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/plans');
    }

    public function updatePlan($id)
    {
        $this->requireAdmin();
        $name = $_POST['name'] ?? '';
        $desc = $_POST['description'] ?? '';
        $base = (float)($_POST['base_salary'] ?? 0);
        $bonus = (float)($_POST['bonus_percent'] ?? 0);
        $comm = (float)($_POST['commission_percent'] ?? 0);
        $benefits = $_POST['benefits_json'] ?? '';
        $active = (int)($_POST['is_active'] ?? 1);
        if (!$name) { $this->setFlash('error', 'Plan name required'); $this->redirect('/admin/salary/plans'); }
        $tid = (int)$this->tenantId();
        try {
            $this->db->execute("UPDATE salary_plans SET name=?, description=?, base_salary=?, bonus_percent=?, commission_percent=?, benefits_json=?, is_active=? WHERE id=? AND tenant_id=?", [$name, $desc, $base, $bonus, $comm, $benefits, $active, $id, $tid]);
            $this->setFlash('success', 'Plan updated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/plans');
    }

    // ──────────────────────────────────────────────
    // SALARY RECORDS
    // ──────────────────────────────────────────────

    public function records()
    {
        $this->requireAdmin();
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            $records = $this->db->fetchAll("
                SELECT r.*, u.name as employee_name
                FROM salary_records r
                LEFT JOIN users u ON r.employee_id = u.id
                ORDER BY r.pay_date DESC, u.name ASC LIMIT 200
            ") ?? [];
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role='employee' {$tidSql} ORDER BY name", $tidParams) ?? [];
            $months = [];
            foreach ($records as $r) {
                $key = substr($r['pay_date'] ?? '', 0, 7);
                if ($key === '') continue;
                $months[$key] = ['year' => (int)substr($key, 0, 4), 'month' => (int)substr($key, 5, 2)];
            }
        } catch (\Exception $e) {
            $records = []; $users = []; $months = [];
        }
        return $this->render('admin/salary/records', [
            'page_title' => 'Salary Records',
            'records' => $records,
            'users' => $users,
            'months' => $months
        ]);
    }

    public function recordByMonth($year, $month)
    {
        $this->requireAdmin();
        try {
            $records = $this->db->fetchAll("
                SELECT r.*, u.name as employee_name
                FROM salary_records r
                LEFT JOIN users u ON r.employee_id = u.id
                WHERE YEAR(r.pay_date)=? AND MONTH(r.pay_date)=?
                ORDER BY u.name ASC
            ", [(int)$year, (int)$month]) ?? [];
        } catch (\Exception $e) {
            $records = [];
        }
        return $this->render('admin/salary/records', [
            'page_title' => "Salary Records - $month/$year",
            'records' => $records,
            'filter_year' => $year,
            'filter_month' => $month
        ]);
    }

    // ──────────────────────────────────────────────
    // SALARY TRACKER
    // ──────────────────────────────────────────────

    public function tracker()
    {
        $this->requireAdmin();
        $employee_id = (int)($_GET['employee_id'] ?? 0);
        $month = (int)($_GET['month'] ?? 0);
        $year = (int)($_GET['year'] ?? 0);
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            // salary_tracker table belongs to the MLM leadership system; the payroll
            // tracker reads the canonical salary_payments ledger with view-compatible aliases.
            $sql = "SELECT sp.*, u.name as employee_name, sp.payment_month AS month, sp.payment_year AS year, sp.gross_amount AS total_earnings, sp.deduction_amount AS total_deductions, sp.net_amount AS net_pay, CASE WHEN sp.payment_status='paid' THEN sp.net_amount ELSE 0 END AS paid_amount, CASE WHEN sp.payment_status='paid' THEN 0 ELSE sp.net_amount END AS due_amount FROM salary_payments sp LEFT JOIN users u ON sp.employee_id=u.id";
            $where = []; $params = [];
            if ($employee_id) { $where[] = 'sp.employee_id=?'; $params[] = $employee_id; }
            if ($month) { $where[] = 'sp.payment_month=?'; $params[] = $month; }
            if ($year) { $where[] = 'sp.payment_year=?'; $params[] = $year; }
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY sp.payment_year DESC, sp.payment_month DESC LIMIT 200";
            $tracker = $this->db->fetchAll($sql, $params) ?? [];
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role='employee' {$tidSql} ORDER BY name", $tidParams) ?? [];
        } catch (\Exception $e) {
            $tracker = []; $users = [];
        }
        return $this->render('admin/salary/tracker', [
            'page_title' => 'Salary Tracker',
            'tracker' => $tracker,
            'users' => $users,
            'filter_employee' => $employee_id,
            'filter_month' => $month,
            'filter_year' => $year
        ]);
    }

    public function updateTracker($id)
    {
        $this->requireAdmin();
        $status = $_POST['payment_status'] ?? 'pending';
        // salary_payments.payment_status enum: pending/processed/paid/failed/cancelled
        $allowed = ['pending', 'processed', 'paid', 'failed', 'cancelled'];
        if (!in_array($status, $allowed, true)) $status = 'pending';
        $date = $_POST['payment_date'] ?? date('Y-m-d');
        try {
            $current = $this->db->fetch("SELECT * FROM salary_payments WHERE id=?", [$id]);
            if (!$current) { $this->setFlash('error', 'Not found'); $this->redirect('/admin/salary/tracker'); }
            $tid = (int)$this->tenantId();
            $this->db->execute("UPDATE salary_payments SET payment_status=?, payment_date=? WHERE id=? AND tenant_id=?", [$status, $date, $id, $tid]);
            $this->logHistory($current['employee_id'], 'tracker_updated', $current['payment_status'] ?? '', $status, (int)($_SESSION['admin_id'] ?? 0));
            $this->setFlash('success', 'Tracker updated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/salary/tracker');
    }

    // ──────────────────────────────────────────────
    // REPORTS
    // ──────────────────────────────────────────────

    public function report()
    {
        $this->requireAdmin();
        $month = (int)($_GET['month'] ?? 0);
        $year = (int)($_GET['year'] ?? 0);
        $employee_id = (int)($_GET['employee_id'] ?? 0);
        [$tidSql, $tidParams] = $this->tenantWhere();
        try {
            $where = []; $params = [];
            if ($month) { $where[] = 'MONTH(sp.payment_date)=?'; $params[] = $month; }
            if ($year) { $where[] = 'YEAR(sp.payment_date)=?'; $params[] = $year; }
            if ($employee_id) { $where[] = 'sp.employee_id=?'; $params[] = $employee_id; }
            $sql = "SELECT sp.*, u.name as employee_name, u.email as employee_email FROM salary_payments sp LEFT JOIN users u ON sp.employee_id=u.id";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY sp.payment_date DESC";
            $payments = $this->db->fetchAll($sql, $params) ?? [];
            // View compat: report.php reads legacy keys.
            foreach ($payments as &$pr) {
                $pr['gross_salary'] = $pr['gross_amount'] ?? 0;
                $pr['total_deductions'] = $pr['deduction_amount'] ?? 0;
                $pr['net_salary'] = $pr['net_amount'] ?? 0;
                $pr['status'] = $pr['payment_status'] ?? 'pending';
            }
            unset($pr);
            $total_gross = array_sum(array_column($payments, 'gross_amount'));
            $total_net = array_sum(array_column($payments, 'net_amount'));
            $total_ded = array_sum(array_column($payments, 'deduction_amount'));
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role='employee' {$tidSql} ORDER BY name", $tidParams) ?? [];
        } catch (\Exception $e) {
            $payments = []; $total_gross = 0; $total_net = 0; $total_ded = 0; $users = [];
        }
        return $this->render('admin/salary/report', [
            'page_title' => 'Salary Report',
            'payments' => $payments,
            'total_gross' => $total_gross,
            'total_net' => $total_net,
            'total_ded' => $total_ded,
            'users' => $users,
            'filter_month' => $month,
            'filter_year' => $year,
            'filter_employee' => $employee_id
        ]);
    }

    public function exportCSV()
    {
        $this->requireAdmin();
        $month = (int)($_GET['month'] ?? 0);
        $year = (int)($_GET['year'] ?? 0);
        $employee_id = (int)($_GET['employee_id'] ?? 0);
        try {
            $where = []; $params = [];
            if ($month) { $where[] = 'MONTH(sp.payment_date)=?'; $params[] = $month; }
            if ($year) { $where[] = 'YEAR(sp.payment_date)=?'; $params[] = $year; }
            if ($employee_id) { $where[] = 'sp.employee_id=?'; $params[] = $employee_id; }
            $sql = "SELECT sp.*, u.name as employee_name, u.email as employee_email FROM salary_payments sp LEFT JOIN users u ON sp.employee_id=u.id";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY sp.payment_date DESC";
            $rows = $this->db->fetchAll($sql, $params) ?? [];
            // View compat: report.php reads legacy keys.
            foreach ($rows as &$rr) {
                $rr['gross_salary'] = $rr['gross_amount'] ?? 0;
                $rr['total_deductions'] = $rr['deduction_amount'] ?? 0;
                $rr['net_salary'] = $rr['net_amount'] ?? 0;
                $rr['status'] = $rr['payment_status'] ?? 'pending';
            }
            unset($rr);
        } catch (\Exception $e) {
            $rows = [];
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="salary_report_' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Employee', 'Email', 'Gross', 'Deductions', 'Net', 'Date', 'Method', 'Status']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['id'], $r['employee_name'] ?? '', $r['employee_email'] ?? '', $r['gross_amount'] ?? 0, $r['deduction_amount'] ?? 0, $r['net_amount'] ?? 0, $r['payment_date'] ?? '', $r['payment_method'] ?? '', $r['payment_status'] ?? '']);
        }
        fclose($out);
        exit;
    }

    // ──────────────────────────────────────────────
    // PAYROLL INTEGRATION
    // ──────────────────────────────────────────────

    public function payrollIntegration()
    {
        $this->requireAdmin();
        try {
            $payrollCount = $this->db->fetch("SELECT COUNT(*) as c FROM employee_payroll")['c'] ?? 0;
            $payrollTotal = $this->db->fetch("SELECT COALESCE(SUM(net_salary),0) as total FROM employee_payroll WHERE payment_status='paid'")['total'] ?? 0;
        } catch (\Exception $e) {
            $payrollCount = 0; $payrollTotal = 0;
        }
        return $this->render('admin/salary/payroll_integration', [
            'page_title' => 'Payroll Integration',
            'payroll_count' => $payrollCount,
            'payroll_total' => $payrollTotal
        ]);
    }

    // ──────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────

    private function logHistory($employee_id, $field, $old, $new, $changed_by)
    {
        // Real salary_history schema: change_type is a strict ENUM, amounts are
        // DECIMAL, effective_date is NOT NULL. Map internal event names so the
        // audit trail actually writes instead of dying on 1265/1364.
        $map = [
            'salary_structure_created' => 'revision',
            'payment_created' => 'adjustment',
            'contract_created' => 'adjustment',
            'contract_terminated' => 'adjustment',
            'tracker_updated' => 'adjustment',
        ];
        $type = $map[$field] ?? 'adjustment';
        $oldNum = is_numeric($old) ? (float)$old : null;
        $newNum = is_numeric(str_replace([','], [''], (string)$new)) ? (float)str_replace([','], [''], (string)$new) : 0;
        try {
            $this->db->execute("INSERT INTO salary_history (employee_id, change_type, old_salary, new_salary, effective_date, reason, approved_by, tenant_id, created_at) VALUES (?,?,?,?,CURDATE(),?,?,?,?,NOW())", [$employee_id, $type, $oldNum, $newNum, "event=$field old=$old new=$new", $changed_by, (int)$this->tenantId()]);
        } catch (\Exception $e) { error_log('SalaryController logHistory: ' . $e->getMessage()); }
    }

    // ──────────────────────────────────────────────
    // PAYROLL BATCH (Indian PF/ESI/TDS)
    // ──────────────────────────────────────────────

    public function batchPreview()
    {
        $this->requireAdmin();
        $month = (int)($_GET['month'] ?? date('n'));
        $year = (int)($_GET['year'] ?? date('Y'));

        $batchService = new \App\Services\PayrollBatchService();
        $preview = $batchService->previewMonth($month, $year);

        return $this->render('admin/salary/batch_preview', [
            'page_title' => "Payroll Preview — $month/$year",
            'month' => $month,
            'year' => $year,
            'entries' => $preview['entries'] ?? [],
            'total_employees' => $preview['total_employees'] ?? 0,
            'total_gross' => $preview['total_gross'] ?? 0,
            'total_deductions' => $preview['total_deductions'] ?? 0,
            'total_net' => $preview['total_net'] ?? 0,
        ]);
    }

    public function batchGenerate()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/admin/salary/batch/preview'); exit; }

        $month = (int)($_POST['month'] ?? date('n'));
        $year = (int)($_POST['year'] ?? date('Y'));
        $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

        try {
            $batchService = new \App\Services\PayrollBatchService();
            $result = $batchService->generateMonth($month, $year, $adminId);
            $_SESSION['success'] = "Generated {$result['generated']} payslips for $month/$year. Total: ₹" . number_format($result['total_net']);
            if (!empty($result['errors'])) {
                $_SESSION['warning'] = count($result['errors']) . " errors: " . implode('; ', array_slice($result['errors'], 0, 3));
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/salary/payments');
        exit;
    }

    public function batchProcess()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . BASE_URL . '/admin/salary/payments'); exit; }

        $paymentIds = $_POST['payment_ids'] ?? [];
        $method = $_POST['payment_method'] ?? 'bank_transfer';
        $reference = $_POST['bank_reference'] ?? '';
        $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

        if (empty($paymentIds)) {
            $_SESSION['error'] = 'No payments selected';
            header('Location: ' . BASE_URL . '/admin/salary/payments');
            exit;
        }

        try {
            $batchService = new \App\Services\PayrollBatchService();
            $updated = $batchService->processPayments($paymentIds, $method, $reference, $adminId);
            $_SESSION['success'] = "Processed $updated payments via $method";
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/salary/payments');
        exit;
    }

    public function batchHistory()
    {
        $this->requireAdmin();
        try {
            $history = $this->db->fetchAll("
                SELECT payment_month, payment_year, COUNT(*) as entries, 
                       SUM(gross_amount) as total_gross, SUM(net_amount) as total_net,
                       payment_status, MIN(created_at) as generated_at
                FROM salary_payments 
                GROUP BY payment_year, payment_month, payment_status
                ORDER BY payment_year DESC, payment_month DESC, payment_status
            ") ?? [];
        } catch (\Exception $e) {
            $history = [];
        }

        return $this->render('admin/salary/batch_history', [
            'page_title' => 'Payroll Batch History',
            'history' => $history,
        ]);
    }

    // ──────────────────────────────────────────────
    // ARREARS ENGINE (Mid-year Salary Revision)
    // ──────────────────────────────────────────────

    public function arrearsPreview()
    {
        $this->requireAdmin();
        // Preview form POSTs these fields (no query string on its action).
        $employeeId = (int)($_GET['employee_id'] ?? $_POST['employee_id'] ?? 0);
        $month = (int)($_GET['month'] ?? $_POST['month'] ?? date('n'));
        $year = (int)($_GET['year'] ?? $_POST['year'] ?? date('Y'));

        if (!$employeeId) {
            $this->setFlash('error', 'Employee ID required');
            $this->redirect('/admin/salary/structures');
        }

        try {
            // Get employee's current structure for preview form
            $currentStructure = $this->db->fetch("
                SELECT * FROM salary_structures 
                WHERE employee_id=? AND status='active' 
                ORDER BY effective_date DESC LIMIT 1
            ", [$employeeId]);

            $employee = $this->db->fetch("SELECT id, name FROM users WHERE id=?", [$employeeId]);
            
            // If preview POST, calculate arrears
            $arrears = null;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newStructure = [
                    'basic_salary' => (float)($_POST['basic_salary'] ?? 0),
                    'hra' => (float)($_POST['hra'] ?? 0),
                    'conveyance' => (float)($_POST['conveyance'] ?? 0),
                    'medical_allowance' => (float)($_POST['medical_allowance'] ?? 0),
                    'special_allowance' => (float)($_POST['special_allowance'] ?? 0),
                    'other_allowances' => (float)($_POST['other_allowances'] ?? 0),
                    'pf_employee' => (float)($_POST['pf_employee'] ?? 0),
                    'tds' => (float)($_POST['tds'] ?? 0),
                ];
                
                $arrearsEngine = new \App\Services\ArrearsEngine();
                $arrears = $arrearsEngine->calculateArrears($employeeId, $month, $year, $newStructure);
            }

        } catch (\Exception $e) {
            $arrears = ['success' => false, 'message' => $e->getMessage()];
        }

        return $this->render('admin/salary/arrears_preview', [
            'page_title' => 'Arrears Preview - Salary Revision',
            'employee' => $employee,
            'current_structure' => $currentStructure,
            'arrears' => $arrears,
            'preview_month' => $month,
            'preview_year' => $year,
        ]);
    }

    public function arrearsProcess()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/salary/arrears/preview');
            exit;
        }

        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $month = (int)($_POST['month'] ?? date('n'));
        $year = (int)($_POST['year'] ?? date('Y'));
        $adminId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

        if (!$employeeId) {
            $_SESSION['error'] = 'Employee ID required';
            $this->redirect('/admin/salary/structures');
            exit;
        }

        $newStructure = [
            'basic_salary' => (float)($_POST['basic_salary'] ?? 0),
            'hra' => (float)($_POST['hra'] ?? 0),
            'conveyance' => (float)($_POST['conveyance'] ?? 0),
            'medical_allowance' => (float)($_POST['medical_allowance'] ?? 0),
            'special_allowance' => (float)($_POST['special_allowance'] ?? 0),
            'other_allowances' => (float)($_POST['other_allowances'] ?? 0),
            'pf_employee' => (float)($_POST['pf_employee'] ?? 0),
            'tds' => (float)($_POST['tds'] ?? 0),
        ];

        try {
            $arrearsEngine = new \App\Services\ArrearsEngine();
            $result = $arrearsEngine->processArrears($employeeId, $month, $year, $newStructure, $adminId);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'] . ". Total Arrears: ₹" . number_format($result['total_arrears'], 2);
            } else {
                $_SESSION['error'] = $result['message'];
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        $this->redirect('/admin/salary/structures');
        exit;
    }

    public function arrearsHistory()
    {
        $this->requireAdmin();
        
        try {
            $arrears = $this->db->fetchAll("
                SELECT sp.*, u.name as employee_name
                FROM salary_payments sp
                LEFT JOIN users u ON sp.employee_id = u.id
                WHERE sp.payment_type = 'arrears' OR sp.remarks LIKE '%Arrears%'
                ORDER BY sp.created_at DESC
            ") ?? [];
        } catch (\Exception $e) {
            $arrears = [];
        }

        return $this->render('admin/salary/arrears_history', [
            'page_title' => 'Arrears History',
            'arrears' => $arrears,
        ]);
    }
}
