<?php
/**
 * EmployeeSelfServiceController
 * Handles all employee self-service portal routes
 */

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;

class EmployeeSelfServiceController extends BaseController
{
    use TenantAwareTrait;

    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/employee';
        $this->service = new \App\Services\EmployeeSelfServiceService();
    }

    /**
     * Dashboard - Main self-service landing page
     */
    public function dashboard()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        // Get summary data
        $taxRegime = $this->service->getTaxRegime($employeeId, date('Y'));
        $leaveBalances = $this->service->getLeaveBalances($employeeId);
        $payslips = $this->service->getPayslipHistory($employeeId, 6);
        $reimbursements = $this->service->getReimbursementHistory($employeeId, 5);
        $attendanceStats = $this->service->getAttendanceStats($employeeId);

        return $this->render('employee/self_service/dashboard', [
            'page_title' => 'Employee Self-Service Portal',
            'tax_regime' => $taxRegime,
            'leave_balances' => $leaveBalances,
            'payslips' => $payslips,
            'reimbursements' => $reimbursements,
            'attendance_stats' => $attendanceStats,
            'current_fy' => date('Y') . '-' . (date('Y') + 1),
        ]);
    }

    /* ═════════════════════════════════════════════════════════════
       TAX REGIME
       ═════════════════════════════════════════════════════════════ */

    public function taxRegime()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];
        $financialYear = (int)($_GET['fy'] ?? date('Y'));

        $currentRegime = $this->service->getTaxRegime($employeeId, $financialYear);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $regime = $_POST['regime'] ?? 'new';
            $result = $this->service->setTaxRegime($employeeId, $financialYear, $regime);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/tax-regime?fy=' . $financialYear);
            exit;
        }

        return $this->render('employee/self_service/tax_regime', [
            'page_title' => 'Tax Regime Selection',
            'current_regime' => $currentRegime,
            'financial_year' => $financialYear,
            'fy_label' => $financialYear . '-' . ($financialYear + 1),
        ]);
    }

    /* ═════════════════════════════════════════════════════════════
       INVESTMENT DECLARATION
       ═════════════════════════════════════════════════════════════ */

    public function investmentDeclaration()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];
        $financialYear = (int)($_GET['fy'] ?? date('Y'));

        $declarations = $this->service->getInvestmentDeclaration($employeeId, $financialYear);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = $_POST['declarations'] ?? [];
            $result = $this->service->saveInvestmentDeclaration($employeeId, $financialYear, $formData);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/investment-declaration?fy=' . $financialYear);
            exit;
        }

        // Define section limits (as per IT Act)
        $sectionLimits = [
            '80C' => 150000,
            '80CCC' => 150000, // Combined with 80C
            '80CCD(1)' => 150000, // Combined with 80C
            '80CCD(1B)' => 50000, // NPS additional
            '80D' => 25000, // Self/family, 50000 for senior citizens
            '80DD' => 75000, // Disabled dependent
            '80DDB' => 40000, // Medical treatment
            '80E' => 0, // Education loan interest - no limit
            '80G' => 0, // Donations - varies
            '80TTA' => 10000, // Savings account interest
            '80TTB' => 50000, // Senior citizen deposit interest
            'HRA' => 0, // Based on salary/rent
            '24b' => 200000, // Home loan interest
        ];

        return $this->render('employee/self_service/investment_declaration', [
            'page_title' => 'Investment Declaration',
            'declarations' => $declarations,
            'financial_year' => $financialYear,
            'fy_label' => $financialYear . '-' . ($financialYear + 1),
            'section_limits' => $sectionLimits,
        ]);
    }

    public function uploadInvestmentProof()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];
        $financialYear = (int)($_POST['financial_year'] ?? date('Y'));
        $section = $_POST['section'] ?? '';

        if (!$section || empty($_FILES['proof_file']['tmp_name'])) {
            $_SESSION['error'] = 'Section and file required';
            $this->redirect(BASE_URL . '/employee/self-service/investment-declaration?fy=' . $financialYear);
            exit;
        }

        $result = $this->service->uploadInvestmentProof($employeeId, $financialYear, $section, $_FILES['proof_file']);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/employee/self-service/investment-declaration?fy=' . $financialYear);
        exit;
    }

    /* ═════════════════════════════════════════════════════════════
       FORM 16
       ═════════════════════════════════════════════════════════════ */

    public function form16()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $form16List = $this->service->getForm16List($employeeId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $financialYear = (int)($_POST['financial_year'] ?? 0);
            if ($financialYear) {
                $result = $this->service->generateForm16($employeeId, $financialYear);
                $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            }
            $this->redirect(BASE_URL . '/employee/self-service/form16');
            exit;
        }

        return $this->render('employee/self_service/form16', [
            'page_title' => 'Form 16 Download',
            'form16_list' => $form16List,
        ]);
    }

    public function downloadForm16($financialYear)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $form16List = $this->service->getForm16List($employeeId);
        $found = null;
        foreach ($form16List as $f) {
            if ($f['financial_year'] == $financialYear) {
                $found = $f;
                break;
            }
        }

        // PdfService stores ABSOLUTE storage paths; older rows may hold APP-relative ones.
        $diskPath = $found['file_path'] ?? '';
        if ($diskPath !== '' && !is_file($diskPath)) {
            $diskPath = APP_PATH . '/' . ltrim($diskPath, '/');
        }
        if (!$found || !$diskPath || !is_file($diskPath)) {
            $_SESSION['error'] = 'Form 16 not found for this year';
            $this->redirect(BASE_URL . '/employee/self-service/form16');
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Form16_' . $financialYear . '_' . $employeeId . '.pdf"');
        header('Content-Length: ' . filesize($diskPath));
        readfile($diskPath);
        exit;
    }

    /* ═════════════════════════════════════════════════════════════
       PAYSLIPS
       ═════════════════════════════════════════════════════════════ */

    public function payslips()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $payslips = $this->service->getPayslipHistory($employeeId);

        return $this->render('employee/self_service/payslips', [
            'page_title' => 'Payslip History',
            'payslips' => $payslips,
        ]);
    }

    public function downloadPayslip($payslipId)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];
        // Ownership gate: employees must only download their own payslips.
        $emp = $this->db->fetchOne("SELECT id FROM employees WHERE user_id=?", [$employeeId]);
        $mine = $emp ? $this->db->fetchOne("SELECT id FROM employee_payslips WHERE id=? AND employee_id=?", [(int)$payslipId, (int)$emp['id']]) : null;
        if (!$mine) {
            $_SESSION['error'] = 'Payslip not found';
            $this->redirect(BASE_URL . '/employee/self-service/payslips');
            exit;
        }
        $pdfPath = $this->service->getPayslipPdf((int)$payslipId);

        // PdfService returns ABSOLUTE storage paths; older rows may be APP-relative.
        $diskPath = $pdfPath ?? '';
        if ($diskPath !== '' && !is_file($diskPath)) {
            $diskPath = APP_PATH . '/' . ltrim($diskPath, '/');
        }
        if (!$diskPath || !is_file($diskPath)) {
            $_SESSION['error'] = 'Payslip PDF not found';
            $this->redirect(BASE_URL . '/employee/self-service/payslips');
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Payslip_' . $payslipId . '.pdf"');
        header('Content-Length: ' . filesize($diskPath));
        readfile($diskPath);
        exit;
    }

    /* ═════════════════════════════════════════════════════════════
       LEAVE MANAGEMENT
       ═════════════════════════════════════════════════════════════ */

    public function leave()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $leaveBalances = $this->service->getLeaveBalances($employeeId);
        $leaveHistory = $this->service->getLeaveHistory($employeeId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->service->applyLeave($employeeId, $_POST);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/leave');
            exit;
        }

        // Get leave types for dropdown
        $leaveTypes = $this->db->fetchAll("SELECT id, name, code FROM leave_types WHERE status='active' ORDER BY name");

        return $this->render('employee/self_service/leave', [
            'page_title' => 'Leave Management',
            'leave_balances' => $leaveBalances,
            'leave_history' => $leaveHistory,
            'leave_types' => $leaveTypes,
        ]);
    }

    /* ═════════════════════════════════════════════════════════════
       REIMBURSEMENT
       ═════════════════════════════════════════════════════════════ */

    public function reimbursement()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $reimbursements = $this->service->getReimbursementHistory($employeeId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->service->submitReimbursement($employeeId, $_POST + ['receipt_file' => $_FILES['receipt_file'] ?? []]);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/reimbursement');
            exit;
        }

        return $this->render('employee/self_service/reimbursement', [
            'page_title' => 'Reimbursement Claims',
            'reimbursements' => $reimbursements,
            'claim_types' => [
                'medical' => 'Medical Reimbursement',
                'lta' => 'Leave Travel Allowance (LTA)',
                'fuel' => 'Fuel / Conveyance',
                'phone' => 'Phone / Mobile',
                'internet' => 'Internet / Broadband',
                'books' => 'Books & Periodicals',
                'training' => 'Training & Development',
                'other' => 'Other',
            ],
        ]);
    }

    /* ═════════════════════════════════════════════════════════════
       PROFILE
       ═════════════════════════════════════════════════════════════ */

    public function profile()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        [$tidSql, $tidParams] = $this->tenantWhere();
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id=?{$tidSql}", array_merge([$employeeId], $tidParams));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->service->updateProfile($employeeId, $_POST);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/profile');
            exit;
        }

        return $this->render('employee/self_service/profile', [
            'page_title' => 'My Profile',
            'user' => $user,
        ]);
    }

    public function changePassword()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($_POST['new_password'] ?? '') !== ($_POST['confirm_password'] ?? '')) {
                $_SESSION['error'] = 'New passwords do not match';
                $this->redirect(BASE_URL . '/employee/self-service/change-password');
                exit;
            }
            $result = $this->service->changePassword($employeeId, $_POST['current_password'] ?? '', $_POST['new_password'] ?? '');
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/profile');
            exit;
        }

        return $this->render('employee/self_service/change_password', [
            'page_title' => 'Change Password',
        ]);
    }

    /* ═════════════════════════════════════════════════════════════
       ATTENDANCE
       ═════════════════════════════════════════════════════════════ */

    public function attendance()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];
        $month = $_GET['month'] ?? date('Y-m');

        $attendance = $this->service->getAttendance($employeeId, $month);
        $stats = $this->service->getAttendanceStats($employeeId, $month);

        return $this->render('employee/self_service/attendance', [
            'page_title' => 'My Attendance',
            'attendance' => $attendance,
            'stats' => $stats,
            'month' => $month,
            'prev_month' => date('Y-m', strtotime($month . ' -1 month')),
            'next_month' => date('Y-m', strtotime($month . ' +1 month')),
        ]);
    }

    /**
     * Helper to require employee login
     */
    protected function requireEmployeeLogin()
    {
        if (!isset($_SESSION['employee_id'])) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '/employee/login');
            exit;
        }
    }
}