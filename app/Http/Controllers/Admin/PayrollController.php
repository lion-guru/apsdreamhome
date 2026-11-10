<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Services\PayrollService;

class PayrollController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PayrollService();
    }

    public function index()
    {
        $this->requireAdmin();
        $filters = [
            'status' => $_GET['status'] ?? null,
            'from_date' => $_GET['from_date'] ?? null,
            'to_date' => $_GET['to_date'] ?? null,
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $runs = $this->service->getRuns($filters);
        $stats = $this->service->getStats();

        return $this->render('admin/payroll/index', [
            'page_title' => 'Payroll Management',
            'runs' => $runs,
            'stats' => $stats,
            'filters' => $_GET,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/payroll/create', [
            'page_title' => 'Create Payroll Run',
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = [
            'period_start' => $_POST['period_start'],
            'period_end' => $_POST['period_end'],
            'pay_date' => $_POST['pay_date'],
            'notes' => $_POST['notes'] ?? null,
        ];
        $result = $this->service->createRun($data);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/payroll');
    }

    public function show($id)
    {
        $this->requireAdmin();
        $run = $this->service->getRun($id);
        if (!$run) {
            $this->setFlash('error', 'Payroll run not found');
            $this->redirect(BASE_URL . '/admin/payroll');
        }
        return $this->render('admin/payroll/show', [
            'page_title' => 'Payroll Run: ' . $run['run_number'],
            'run' => $run,
        ]);
    }

    public function calculate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->calculateRun($id);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/payroll/' . $id);
    }

    public function approve($id)
    {
        $this->requireAdmin();
        $result = $this->service->approveRun($id);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/payroll/' . $id);
    }

    public function process($id)
    {
        $this->requireAdmin();
        $result = $this->service->processRun($id);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/payroll/' . $id);
    }

    public function stats()
    {
        $this->requireAdmin();
        return $this->jsonResponse($this->service->getStats());
    }

    /* ============================================================
       MISSING ACTIONS (routes existed, methods did not)
       ============================================================ */

    /**
     * Generate payroll for a batch of employees.
     * POST /admin/payroll/generate-batch
     */
    public function generateBatch()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $employeeIds = $_POST['employee_ids'] ?? [];
        if (is_string($employeeIds)) {
            $employeeIds = array_filter(array_map('trim', explode(',', $employeeIds)));
        }
        $employeeIds = array_values(array_filter(array_map('intval', (array)$employeeIds)));

        if (empty($employeeIds)) {
            $this->setFlash('error', 'Select at least one employee for the batch');
            $this->redirect(BASE_URL . '/admin/payroll');
            return;
        }

        $data = [
            'period_start' => $_POST['period_start'] ?? date('Y-m-01'),
            'period_end'   => $_POST['period_end'] ?? date('Y-m-t'),
            'pay_date'     => $_POST['pay_date'] ?? date('Y-m-d'),
            'notes'        => $_POST['notes'] ?? null,
            'employee_ids' => $employeeIds,
        ];

        $result = $this->service->createRun($data);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/payroll');
    }

    /**
     * Show a single payslip (payroll item).
     * GET /admin/payroll/payslip/{id}
     */
    public function payslip($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $item = $this->db->fetchOne(
            "SELECT pi.*, e.name AS employee_name, e.employee_code, e.designation, e.department,
                    u.email AS employee_email, u.phone AS employee_phone
             FROM payroll_items pi
             LEFT JOIN employees e ON e.id = pi.employee_id
             LEFT JOIN users u ON u.id = e.user_id
             WHERE pi.id = ? LIMIT 1",
            [$id]
        );

        if (!$item) {
            $this->setFlash('error', 'Payslip not found');
            $this->redirect(BASE_URL . '/admin/payroll');
            return;
        }

        return $this->render('admin/payroll/payslip', [
            'page_title'   => 'Payslip',
            'payslip'      => $item,
            'pid'          => $id,
            'period_label' => ($item['pay_period_start'] ?? '') . ' - ' . ($item['pay_period_end'] ?? ''),
        ]);
    }

    /**
     * Download a payslip as PDF.
     * GET /admin/payroll/payslip/{id}/pdf
     */
    public function downloadPayslipPdf($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $item = $this->db->fetchOne(
            "SELECT pi.*, e.name AS employee_name, e.employee_code, e.designation, e.department
             FROM payroll_items pi
             LEFT JOIN employees e ON e.id = pi.employee_id
             WHERE pi.id = ? LIMIT 1",
            [$id]
        );

        if (!$item) {
            $this->setFlash('error', 'Payslip not found');
            $this->redirect(BASE_URL . '/admin/payroll');
            return;
        }

        $pdfService = new \App\Services\PDFService();
        $html = '<html><head><meta charset="utf-8"><style>'
              . 'body{font-family:DejaVu Sans,sans-serif;font-size:12px;margin:30px}'
              . 'h2{margin:0 0 4px}.muted{color:#555;font-size:11px}'
              . 'table{width:100%;border-collapse:collapse;margin-top:14px}'
              . 'td,th{border:1px solid #ccc;padding:6px;font-size:11px}'
              . 'th{background:#f3f4f6;text-align:left}.num{text-align:right}'
              . '</style></head><body>'
              . '<h2>APS Dream Home — Payslip</h2>'
              . '<div class="muted">Employee: ' . htmlspecialchars((string)($item['employee_name'] ?? ''))
              . ' (' . htmlspecialchars((string)($item['employee_code'] ?? '')) . ')'
              . ' — ' . htmlspecialchars((string)($item['designation'] ?? '')) . '</div>'
              . '<div class="muted">Period: ' . htmlspecialchars((string)($item['pay_period_start'] ?? ''))
              . ' to ' . htmlspecialchars((string)($item['pay_period_end'] ?? '')) . '</div>'
              . '<table><tr><th>Earnings</th><th class="num">Amount</th><th>Deductions</th><th class="num">Amount</th></tr>'
              . '<tr><td>Basic</td><td class="num">' . number_format((float)($item['basic_salary'] ?? 0), 2) . '</td>'
              . '<td>PF</td><td class="num">' . number_format((float)($item['pf_employee'] ?? 0), 2) . '</td></tr>'
              . '<tr><td>HRA</td><td class="num">' . number_format((float)($item['hra'] ?? 0), 2) . '</td>'
              . '<td>ESI</td><td class="num">' . number_format((float)($item['esi_employee'] ?? 0), 2) . '</td></tr>'
              . '<tr><td>Allowances</td><td class="num">'
              . number_format((float)($item['da'] ?? 0) + (float)($item['conveyance'] ?? 0)
                  + (float)($item['medical_allowance'] ?? 0) + (float)($item['special_allowance'] ?? 0), 2)
              . '</td><td>Professional Tax</td><td class="num">'
              . number_format((float)($item['professional_tax'] ?? 0), 2) . '</td></tr>'
              . '<tr><td>Overtime &amp; Bonus</td><td class="num">'
              . number_format((float)($item['overtime_pay'] ?? 0) + (float)($item['bonus'] ?? 0)
                  + (float)($item['incentives'] ?? 0), 2)
              . '</td><td>TDS</td><td class="num">' . number_format((float)($item['tds'] ?? 0), 2) . '</td></tr>'
              . '<tr><td><b>Gross</b></td><td class="num"><b>' . number_format((float)($item['gross_earnings'] ?? 0), 2) . '</b></td>'
              . '<td>Advance/Loan Recovery</td><td class="num">'
              . number_format((float)($item['advance_recovery'] ?? 0) + (float)($item['loan_recovery'] ?? 0), 2) . '</td></tr>'
              . '<tr><td colspan="2"></td><td><b>Total Deductions</b></td><td class="num"><b>'
              . number_format((float)($item['total_deductions'] ?? 0), 2) . '</b></td></tr>'
              . '<tr><td colspan="2"></td><td><b>Net Pay</b></td><td class="num"><b>'
              . number_format((float)($item['net_pay'] ?? 0), 2) . '</b></td></tr>'
              . '</table></body></html>';

        $filename = 'payslip-' . ($item['employee_code'] ?: $id) . '.pdf';

        // TCPDF is the PDF engine already used across this codebase
        // (vendor/tecnickcom/tcpdf). Fall back to the HTML service if the
        // class is somehow unavailable.
        if (class_exists('\TCPDF')) {
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('APS Dream Home');
            $pdf->SetTitle('Payslip - ' . (string)($item['employee_code'] ?: $id));
            $pdf->SetPrintHeader(false);
            $pdf->SetPrintFooter(false);
            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 9, 'APS DREAM HOME  -  PAYSLIP', 0, 1, 'C');
            $pdf->Ln(2);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 6, 'Employee: ' . (string)($item['employee_name'] ?? '')
                . '  (' . (string)($item['employee_code'] ?? '') . ')  -  '
                . (string)($item['designation'] ?? ''), 0, 1, 'L');
            $pdf->Cell(0, 6, 'Period: ' . (string)($item['pay_period_start'] ?? '')
                . ' to ' . (string)($item['pay_period_end'] ?? ''), 0, 1, 'L');
            $pdf->Ln(3);

            $allowances = (float)($item['da'] ?? 0) + (float)($item['conveyance'] ?? 0)
                + (float)($item['medical_allowance'] ?? 0) + (float)($item['special_allowance'] ?? 0);
            $extra = (float)($item['overtime_pay'] ?? 0) + (float)($item['bonus'] ?? 0)
                + (float)($item['incentives'] ?? 0);
            $recovery = (float)($item['advance_recovery'] ?? 0) + (float)($item['loan_recovery'] ?? 0);

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetFillColor(243, 244, 246);
            $pdf->Cell(45, 8, 'Earnings', 1, 0, 'L', 1);
            $pdf->Cell(30, 8, 'Amount', 1, 0, 'R', 1);
            $pdf->Cell(45, 8, 'Deductions', 1, 0, 'L', 1);
            $pdf->Cell(30, 8, 'Amount', 1, 1, 'R', 1);

            $pdf->SetFont('helvetica', '', 10);
            $rows = [
                ['Basic', (float)($item['basic_salary'] ?? 0), 'PF', (float)($item['pf_employee'] ?? 0)],
                ['HRA', (float)($item['hra'] ?? 0), 'ESI', (float)($item['esi_employee'] ?? 0)],
                ['Allowances', $allowances, 'Professional Tax', (float)($item['professional_tax'] ?? 0)],
                ['Overtime & Bonus', $extra, 'TDS', (float)($item['tds'] ?? 0)],
                ['', 0.0, 'Advance/Loan Recovery', $recovery],
            ];
            foreach ($rows as $r) {
                $pdf->Cell(45, 7, $r[0], 1, 0, 'L');
                $pdf->Cell(30, 7, number_format($r[1], 2), 1, 0, 'R');
                $pdf->Cell(45, 7, $r[2], 1, 0, 'L');
                $pdf->Cell(30, 7, number_format($r[3], 2), 1, 1, 'R');
            }

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(75, 8, 'Gross Earnings', 1, 0, 'L');
            $pdf->Cell(30, 8, number_format((float)($item['gross_earnings'] ?? 0), 2), 1, 0, 'R');
            $pdf->Cell(45, 8, 'Total Deductions', 1, 0, 'L');
            $pdf->Cell(30, 8, number_format((float)($item['total_deductions'] ?? 0), 2), 1, 1, 'R');
            $pdf->Cell(75, 9, 'NET PAY', 1, 0, 'L');
            $pdf->Cell(30, 9, number_format((float)($item['net_pay'] ?? 0), 2), 1, 0, 'R');
            $pdf->Cell(45, 9, 'Employer Cost', 1, 0, 'L');
            $pdf->Cell(30, 9, number_format((float)($item['total_employer_cost'] ?? 0), 2), 1, 1, 'R');

            $pdf->Output($filename, 'D');
            exit;
        }

        // Fallback: write via the generic PDF service and stream the file
        $outDir = rtrim(PUBLIC_PATH, '/') . '/downloads';
        if (!is_dir($outDir)) {
            @mkdir($outDir, 0775, true);
        }
        $outPath = $outDir . '/' . $filename;
        \App\Services\PDFService::generateFromHtml($html, $outPath);
        if (is_file($outPath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($outPath));
            readfile($outPath);
            @unlink($outPath);
            exit;
        }

        $this->setFlash('error', 'Could not generate the PDF');
        $this->redirect(BASE_URL . '/admin/payroll/payslip/' . $id);
    }

    /**
     * Edit a payroll run.
     * GET /admin/payroll/{id}/edit
     */
    public function edit($id)
    {
        $this->requireAdmin();
        $run = $this->service->getRun((int)$id);
        if (!$run) {
            $this->setFlash('error', 'Payroll run not found');
            $this->redirect(BASE_URL . '/admin/payroll');
            return;
        }

        $employees = $this->db->fetchAll(
            "SELECT e.id, e.name, e.employee_code, e.designation, e.department
             FROM employees e WHERE e.status = 'active' ORDER BY e.name LIMIT 500"
        ) ?: [];

        return $this->render('admin/payroll/edit', [
            'page_title' => 'Edit Payroll Run',
            'payroll'    => $run,
            'emp'        => $employees,
            'users'      => $employees,
        ]);
    }

    /**
     * Update a payroll run.
     * POST /admin/payroll/{id}/update
     */
    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $id = (int)$id;

        $data = [
            'run_name'        => $_POST['run_name'] ?? null,
            'pay_period_start' => $_POST['period_start'] ?? null,
            'pay_period_end'  => $_POST['period_end'] ?? null,
            'pay_date'        => $_POST['pay_date'] ?? null,
            'notes'           => $_POST['notes'] ?? null,
        ];

        $result = $this->service->updateRun($id, $data);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/payroll/' . $id);
    }

    /**
     * Salary advances list (admin view).
     * GET /admin/payroll/advances
     */
    public function advances()
    {
        $this->requireAdmin();
        $tid = (int) (\App\Core\Middleware\TenantContext::getId() ?: 1);

        $advances = $this->db->fetchAll(
            "SELECT sa.*, e.name AS employee_name, e.employee_code, e.designation
             FROM salary_advances sa
             LEFT JOIN employees e ON e.id = sa.employee_id
             WHERE sa.tenant_id = ?
             ORDER BY sa.id DESC LIMIT 200",
            [$tid]
        ) ?: [];

        $users = $this->db->fetchAll(
            "SELECT id, name, employee_code FROM employees WHERE status = 'active' ORDER BY name LIMIT 500"
        ) ?: [];

        return $this->render('admin/payroll/advances', [
            'page_title' => 'Salary Advances & Loans',
            'advances'   => $advances,
            'users'      => $users,
        ]);
    }

    /**
     * Record a new salary advance / loan (admin).
     * POST /admin/payroll/advances/add
     */
    public function addAdvance()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $tid = (int) (\App\Core\Middleware\TenantContext::getId() ?: 1);

        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $amount     = (float)($_POST['amount'] ?? 0);
        $type       = ($_POST['type'] ?? 'advance') === 'loan' ? 'loan' : 'advance';
        $installments = (int)($_POST['installments'] ?? 0);
        $interestRate = (float)($_POST['interest_rate'] ?? 0);
        $reason     = trim((string)($_POST['reason'] ?? ''));

        if ($employeeId <= 0 || $amount <= 0) {
            $this->setFlash('error', 'Employee and a positive amount are required');
            $this->redirect(BASE_URL . '/admin/payroll/advances');
            return;
        }

        $emi = 0.0;
        if ($installments > 0) {
            $emi = round($amount / $installments, 2);
        }

        try {
            $this->db->insert('salary_advances', [
                'tenant_id'        => $tid,
                'employee_id'      => $employeeId,
                'type'             => $type,
                'amount'           => $amount,
                'installments'     => $installments,
                'emi_amount'       => $emi,
                'interest_rate'    => $interestRate,
                'status'           => 'approved',
                'requested_at'     => date('Y-m-d H:i:s'),
                'approved_at'      => date('Y-m-d H:i:s'),
                'approved_by'      => (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0),
                'total_repaid'     => 0,
                'remaining_balance' => $amount,
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);
            $this->setFlash('success', 'Advance recorded');
        } catch (\Throwable $e) {
            error_log('[PayrollController::addAdvance] ' . $e->getMessage());
            $this->setFlash('error', 'Could not record the advance: ' . $e->getMessage());
        }

        $this->redirect(BASE_URL . '/admin/payroll/advances');
    }

    public function statutoryReturns()
    {
        $this->requireAdmin();
        $filters = [
            'return_type' => $_GET['return_type'] ?? null,
            'status' => $_GET['status'] ?? null,
            'period_year' => $_GET['period_year'] ?? null,
            'period_month' => $_GET['period_month'] ?? null,
        ];
        $returns = $this->service->getStatutoryReturns($filters);
        return $this->render('admin/payroll/statutory_returns', [
            'page_title' => 'Statutory Returns',
            'returns' => $returns,
            'filters' => $filters,
        ]);
    }

    public function fileStatutoryReturn($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->markReturnFiled($id, $_POST);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/statutory-returns');
    }
}