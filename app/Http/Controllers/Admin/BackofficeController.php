<?php

/**
 * Module 5: BackofficeController
 * Daily operations dashboard, attendance, leaves, payslips, leads, operations log, reports
 */

namespace App\Http\Controllers\Admin;

use App\Services\Backoffice\DailyOperationsService;

class BackofficeController extends AdminController
{
    protected $svc;

    public function __construct()
    {
        parent::__construct();
        $this->svc = new DailyOperationsService($this->db);
    }

    private function adminId()
    {
        return (int)($_SESSION['admin_id'] ?? 0);
    }

    /* ── DASHBOARD ─────────────────────────────────────── */

    public function index()
    {
        $this->requireAdmin();
        $stats = $this->svc->getDashboardStats();
        $this->render('admin/backoffice/dashboard', [
            'page_title' => 'Daily Operations Dashboard',
            'stats' => $stats
        ]);
    }

    /* ── ATTENDANCE ────────────────────────────────────── */

    public function attendance()
    {
        $this->requireAdmin();
        $today = date('Y-m-d');
        $records = $this->svc->getMonthlyAttendance(date('Y-m'));
        $employees = $this->svc->getEmployees();
        $this->render('admin/backoffice/attendance', [
            'page_title' => 'Employee Attendance',
            'records' => $records,
            'employees' => $employees,
            'today' => $today
        ]);
    }

    public function attendanceRecord()
    {
        $this->requireAdmin();
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success'=>false,'error'=>'Invalid CSRF'], 403);
            return;
        }
        $id = $this->svc->markAttendance($_POST);
        $this->json(['success'=>true,'id'=>$id]);
    }

    public function attendanceMonthly()
    {
        $this->requireAdmin();
        $month = $_GET['month'] ?? date('Y-m');
        $records = $this->svc->getMonthlyAttendance($month);
        $employees = $this->svc->getEmployees();
        $this->render('admin/backoffice/attendance-monthly', [
            'page_title' => 'Monthly Attendance Report',
            'records' => $records,
            'employees' => $employees,
            'month' => $month
        ]);
    }

    public function attendanceMonthlyReport()
    {
        $this->requireAdmin();
        $month = $_GET['month'] ?? date('Y-m');
        $records = $this->svc->getMonthlyAttendance($month);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance_' . $month . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Date','Employee','Status','Check In','Check Out','Hours','OT','Late(min)']);
        foreach ($records as $r) {
            fputcsv($out, [$r['attendance_date']??'',$r['employee_name']??'',$r['status']??'',$r['check_in_time']??'',$r['check_out_time']??'',$r['hours_worked']??'',$r['overtime_hours']??'',$r['late_minutes']??'']);
        }
        fclose($out);
        exit;
    }

    /* ── LEAVES ────────────────────────────────────────── */

    public function leaves()
    {
        $this->requireAdmin();
        $pending = $this->svc->getPendingLeaves();
        $this->render('admin/backoffice/leaves', [
            'page_title' => 'Pending Leave Requests',
            'leaves' => $pending
        ]);
    }

    public function leaveApprove($id)
    {
        $this->requireAdmin();
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success'=>false,'error'=>'Invalid CSRF'], 403);
            return;
        }
        $this->svc->approveLeave((int)$id, $this->adminId());
        $this->redirect(BASE_URL . '/admin/backoffice/leaves');
    }

    public function leaveReject($id)
    {
        $this->requireAdmin();
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success'=>false,'error'=>'Invalid CSRF'], 403);
            return;
        }
        $this->svc->rejectLeave((int)$id, $this->adminId(), $_POST['remarks'] ?? '');
        $this->redirect(BASE_URL . '/admin/backoffice/leaves');
    }

    public function leaveHistory()
    {
        $this->requireAdmin();
        $status = $_GET['status'] ?? '';
        $leaves = $this->svc->getAllLeaves($status);
        $this->render('admin/backoffice/leave-history', [
            'page_title' => 'Leave History',
            'leaves' => $leaves,
            'status_filter' => $status
        ]);
    }

    /* ── PAYSLIPS ──────────────────────────────────────── */

    public function payslips()
    {
        $this->requireAdmin();
        $month = $_GET['month'] ?? '';
        $year = $_GET['year'] ?? '';
        $payslips = $this->svc->getAllPayslips($month, $year);
        $employees = $this->svc->getEmployees();
        $this->render('admin/backoffice/payslips', [
            'page_title' => 'Employee Payslips',
            'payslips' => $payslips,
            'employees' => $employees,
            'filter_month' => $month,
            'filter_year' => $year
        ]);
    }

    public function payslipGenerate()
    {
        $this->requireAdmin();
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success'=>false,'error'=>'Invalid CSRF'], 403);
            return;
        }
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $month = (int)($_POST['period_month'] ?? date('n'));
        $year = (int)($_POST['period_year'] ?? date('Y'));
        $result = $this->svc->generatePayslip($employeeId, $month, $year);
        if (isset($result['error'])) {
            $this->redirect(BASE_URL . '/admin/backoffice/payslips?error=' . urlencode($result['error']));
            return;
        }
        $this->redirect(BASE_URL . '/admin/backoffice/payslips/' . $result['id']);
    }

    public function payslipView($id)
    {
        $this->requireAdmin();
        $payslip = $this->svc->getPayslipById((int)$id);
        if (!$payslip) {
            $this->redirect(BASE_URL . '/admin/backoffice/payslips');
            return;
        }

        $moneySvc = new \App\Services\Accounting\MoneyWorkflowService();
        $bankAccounts = $moneySvc->listBankAccounts(true);

        $this->render('admin/backoffice/payslip-view', [
            'page_title' => 'Payslip #' . $id,
            'payslip' => $payslip,
            'bank_accounts' => $bankAccounts
        ]);
    }

    public function payslipPay($id)
    {
        $this->requireAdmin();
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->redirect(BASE_URL . '/admin/backoffice/payslips/' . $id . '?error=' . urlencode('Invalid CSRF token'));
            return;
        }

        $paymentMode = $_POST['payment_mode'] ?? 'cash';
        $bankAccountId = !empty($_POST['bank_account_id']) ? (int)$_POST['bank_account_id'] : null;

        try {
            $this->svc->payPayslip((int)$id, $paymentMode, $bankAccountId);
            $this->redirect(BASE_URL . '/admin/backoffice/payslips/' . $id . '?success=' . urlencode('Salary paid successfully'));
        } catch (\Throwable $e) {
            $this->redirect(BASE_URL . '/admin/backoffice/payslips/' . $id . '?error=' . urlencode($e->getMessage()));
        }
    }

    /* ── LEADS ─────────────────────────────────────────── */

    public function leads()
    {
        $this->requireAdmin();
        $filters = [
            'status' => $_GET['status'] ?? '',
            'source' => $_GET['source'] ?? '',
            'type' => $_GET['type'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'search' => $_GET['search'] ?? '',
            'limit' => 50
        ];
        $leads = $this->svc->listLeads($filters);
        $total = $this->svc->countLeads($filters);
        $summary = $this->svc->getLeadPipelineSummary();
        $this->render('admin/backoffice/leads', [
            'page_title' => 'Lead Pipeline',
            'leads' => $leads,
            'total' => $total,
            'summary' => $summary,
            'filters' => $filters
        ]);
    }

    public function leadCreate()
    {
        $this->requireAdmin();
        $employees = $this->svc->getEmployees();
        $this->render('admin/backoffice/lead-create', [
            'page_title' => 'New Lead',
            'employees' => $employees
        ]);
    }

    public function leadStore()
    {
        $this->requireAdmin();
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success'=>false,'error'=>'Invalid CSRF'], 403);
            return;
        }
        $data = $_POST;
        $data['created_by'] = $this->adminId();
        $id = $this->svc->createLead($data);
        if ($id) {
            $this->redirect(BASE_URL . '/admin/backoffice/leads/' . $id);
        } else {
            $this->redirect(BASE_URL . '/admin/backoffice/leads/create');
        }
    }

    public function leadDetail($id)
    {
        $this->requireAdmin();
        $lead = $this->svc->getLeadById((int)$id);
        if (!$lead) {
            $this->redirect(BASE_URL . '/admin/backoffice/leads');
            return;
        }
        $timeline = $this->svc->getLeadTimeline((int)$id);
        $employees = $this->svc->getEmployees();
        $this->render('admin/backoffice/lead-detail', [
            'page_title' => 'Lead: ' . ($lead['lead_name'] ?? ''),
            'lead' => $lead,
            'timeline' => $timeline,
            'employees' => $employees
        ]);
    }

    public function leadEdit($id)
    {
        $this->requireAdmin();
        $lead = $this->svc->getLeadById((int)$id);
        if (!$lead) {
            $this->redirect(BASE_URL . '/admin/backoffice/leads');
            return;
        }
        $employees = $this->svc->getEmployees();
        $this->render('admin/backoffice/lead-edit', [
            'page_title' => 'Edit Lead',
            'lead' => $lead,
            'employees' => $employees
        ]);
    }

    public function leadUpdate($id)
    {
        $this->requireAdmin();
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success'=>false,'error'=>'Invalid CSRF'], 403);
            return;
        }
        $this->svc->updateLead((int)$id, $_POST);
        $this->redirect(BASE_URL . '/admin/backoffice/leads/' . $id);
    }

    public function leadAddActivity($id)
    {
        $this->requireAdmin();
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success'=>false,'error'=>'Invalid CSRF'], 403);
            return;
        }
        $data = $_POST;
        $data['created_by'] = $this->adminId();
        $this->svc->addLeadActivity((int)$id, $data);
        $this->redirect(BASE_URL . '/admin/backoffice/leads/' . $id);
    }

    public function leadAdvanceStage($id)
    {
        $this->requireAdmin();
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success'=>false,'error'=>'Invalid CSRF'], 403);
            return;
        }
        $newStage = $_POST['new_stage'] ?? '';
        $this->svc->advanceLeadStage((int)$id, $newStage);
        $this->redirect(BASE_URL . '/admin/backoffice/leads/' . $id);
    }

    /* ── OPERATIONS ────────────────────────────────────── */

    public function operations()
    {
        $this->requireAdmin();
        $date = $_GET['date'] ?? date('Y-m-d');
        $filters = [
            'log_type' => $_GET['log_type'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];
        $logs = $this->svc->getOperationsLog($date, $filters);
        $colonies = $this->svc->getColonies();
        $this->render('admin/backoffice/operations', [
            'page_title' => 'Daily Operations Log',
            'logs' => $logs,
            'colonies' => $colonies,
            'filter_date' => $date,
            'filters' => $filters
        ]);
    }

    public function operationsCreate()
    {
        $this->requireAdmin();
        $colonies = $this->svc->getColonies();
        $employees = $this->svc->getEmployees();
        $this->render('admin/backoffice/operations-create', [
            'page_title' => 'New Operations Entry',
            'colonies' => $colonies,
            'employees' => $employees
        ]);
    }

    public function operationsStore()
    {
        $this->requireAdmin();
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->json(['success'=>false,'error'=>'Invalid CSRF'], 403);
            return;
        }
        $data = $_POST;
        $data['created_by'] = $this->adminId();
        $this->svc->logOperation($data);
        $this->redirect(BASE_URL . '/admin/backoffice/operations');
    }

    /* ── REPORTS ───────────────────────────────────────── */

    public function reports()
    {
        $this->requireAdmin();
        $reports = $this->svc->getReportList();
        $this->render('admin/backoffice/reports', [
            'page_title' => 'Report Center',
            'reports' => $reports
        ]);
    }

    public function reportsRun($id)
    {
        $this->requireAdmin();
        $report = null;
        $result = null;
        $reports = $this->svc->getReportList();
        foreach ($reports as $r) {
            if ((int)$r['id'] === (int)$id) { $report = $r; break; }
        }
        if (!$report) {
            $this->redirect(BASE_URL . '/admin/backoffice/reports');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $params = [];
            foreach (json_decode($report['parameters'] ?? '{}', true) as $key => $label) {
                $params[$key] = $_POST[$key] ?? '';
            }
            $result = $this->svc->executeReport((int)$id, $params, $this->adminId());
        }

        $this->render('admin/backoffice/report-run', [
            'page_title' => 'Run: ' . $report['report_name'],
            'report' => $report,
            'result' => $result
        ]);
    }

    public function reportsHistory($id)
    {
        $this->requireAdmin();
        $history = $this->svc->getReportHistory((int)$id);
        $this->render('admin/backoffice/report-history', [
            'page_title' => 'Report History',
            'report_id' => $id,
            'history' => $history
        ]);
    }

    /* ── API ───────────────────────────────────────────── */

    public function apiLeadSummary()
    {
        $this->requireAdmin();
        $this->json($this->svc->getLeadSummary());
    }
}
