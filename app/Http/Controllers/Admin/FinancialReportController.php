<?php
/**
 * Financial Reports Controller
 */

namespace App\Http\Controllers\Admin;

use App\Services\FinancialReportService;
use App\Helpers\Export;

class FinancialReportController extends AdminController
{
    private $reportService;

    public function __construct()
    {
        parent::__construct();
        $this->reportService = new FinancialReportService();
    }

    /**
     * Reports Dashboard
     */
    public function index()
    {
        $this->requireAdmin();
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $profitLoss = $this->reportService->getProfitLoss($startDate, $endDate);
        $balanceSheet = $this->reportService->getBalanceSheet($endDate);
        $cashFlow = $this->reportService->getCashFlow($startDate, $endDate);
        $monthlyRevenue = $this->reportService->getMonthlyRevenue(12);

        return $this->render('admin/reports/financial/index', [
            'page_title' => 'Financial Reports',
            'profit_loss' => $profitLoss,
            'balance_sheet' => $balanceSheet,
            'cash_flow' => $cashFlow,
            'monthly_revenue' => $monthlyRevenue,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Profit & Loss Statement
     */
    public function profitLoss()
    {
        $this->requireAdmin();
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $data = $this->reportService->getProfitLoss($startDate, $endDate);

        return $this->render('admin/reports/financial/profit_loss', [
            'page_title' => 'Profit & Loss Statement',
            'data' => $data,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Balance Sheet
     */
    public function balanceSheet()
    {
        $this->requireAdmin();
        $asOfDate = $_GET['as_of_date'] ?? date('Y-m-d');

        $data = $this->reportService->getBalanceSheet($asOfDate);

        return $this->render('admin/reports/financial/balance_sheet', [
            'page_title' => 'Balance Sheet',
            'data' => $data,
            'as_of_date' => $asOfDate,
        ]);
    }

    /**
     * Cash Flow Statement
     */
    public function cashFlow()
    {
        $this->requireAdmin();
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $data = $this->reportService->getCashFlow($startDate, $endDate);

        return $this->render('admin/reports/financial/cash_flow', [
            'page_title' => 'Cash Flow Statement',
            'data' => $data,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Export report
     */
    public function export()
    {
        $this->requireAdmin();
        $type = $_GET['type'] ?? 'profit_loss';
        $format = $_GET['format'] ?? 'csv';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $data = [];
        $filename = 'report';

        switch ($type) {
            case 'profit_loss':
                $report = $this->reportService->getProfitLoss($startDate, $endDate);
                $filename = 'profit_loss';
                $data = [
                    ['Category' => 'Revenue', 'Amount' => $report['revenue']['total']],
                    ['Category' => 'Expenses', 'Amount' => $report['expenses']['total']],
                    ['Category' => 'Net Profit', 'Amount' => $report['net_profit']],
                ];
                break;
            case 'balance_sheet':
                $report = $this->reportService->getBalanceSheet($endDate);
                $filename = 'balance_sheet';
                $data = [
                    ['Category' => 'Total Assets', 'Amount' => $report['assets']['total']],
                    ['Category' => 'Total Liabilities', 'Amount' => $report['liabilities']['total']],
                    ['Category' => 'Equity', 'Amount' => $report['equity']],
                ];
                break;
            case 'cash_flow':
                $report = $this->reportService->getCashFlow($startDate, $endDate);
                $filename = 'cash_flow';
                $data = [
                    ['Category' => 'Total Inflows', 'Amount' => $report['inflows']['total']],
                    ['Category' => 'Total Outflows', 'Amount' => $report['outflows']['total']],
                    ['Category' => 'Net Cash Flow', 'Amount' => $report['net_cash_flow']],
                ];
                break;
        }

        if ($format === 'excel') {
            Export::excel($data, $filename);
        } else {
            Export::csv($data, $filename);
        }
    }

    // ──────────────────────────────────────────────
    // SECTION 194H TDS QUARTERLY REPORT (associate/MLM brokerage)
    // Gross per deductee from mlm_commission_ledger; TDS math delegated
    // to TdsConfigService (5% valid PAN / 20% 206AA, service threshold).
    // ──────────────────────────────────────────────

    public function tdsReport()
    {
        $this->requireAdmin();
        $fy = trim($_GET['fy'] ?? '');
        $quarter = strtoupper(trim($_GET['quarter'] ?? ''));
        if (!preg_match('/^(\d{4})-(\d{4})$/', $fy, $m) || ((int)$m[2] !== (int)$m[1] + 1)) {
            $fy = $this->currentFyLabel();
        }
        if (!in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'], true)) $quarter = '';
        $data = $this->buildTdsData($fy, $quarter);
        return $this->render('admin/reports/tds_194h', array_merge([
            'page_title' => 'Section 194H TDS Report',
            'fy' => $fy,
            'quarter' => $quarter,
            'fy_options' => $this->fyOptions(),
        ], $data));
    }

    public function exportTdsCsv()
    {
        $this->requireAdmin();
        $fy = trim($_GET['fy'] ?? '');
        $quarter = strtoupper(trim($_GET['quarter'] ?? ''));
        if (!preg_match('/^(\d{4})-(\d{4})$/', $fy, $m) || ((int)$m[2] !== (int)$m[1] + 1)) {
            $fy = $this->currentFyLabel();
        }
        if (!in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'], true)) $quarter = '';
        $data = $this->buildTdsData($fy, $quarter);
        $suffix = $quarter !== '' ? "_{$quarter}" : '';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="tds_194h_' . str_replace('-', '', $fy) . $suffix . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Associate Name', 'Code', 'PAN', 'PAN Valid (5% else 20% u/s 206AA)', 'Section', 'FY', 'Quarter', 'Gross Brokerage', 'Below Threshold (' . $data['threshold_label'] . ')', 'TDS Rate %', 'TDS Deducted', 'Net Paid (payouts)', 'UTR / Payout Ref']);
        foreach ($data['rows'] as $r) {
            fputcsv($out, [$r['name'], $r['code'], $r['pan'], $r['pan_valid'] ? 'YES' : 'NO', '194H', $fy, $quarter !== '' ? $quarter : 'ALL', $r['gross'], $r['below_threshold'] ? 'YES' : 'NO', $r['rate'], $r['tds'], $r['net_paid'], $r['utr']]);
        }
        fputcsv($out, ['TOTAL', '', '', '', '', '', '', $data['summary']['gross'], '', '', $data['summary']['tds'], $data['summary']['net_paid'], '']);
        fclose($out);
        exit;
    }

    /**
     * Executive P&L: FinancialReportService base + real-table lines
     * (land, development, salaries both systems). Each extra line is
     * individually guarded so a missing table can never 500 the page.
     */
    public function profitAndLossStatement()
    {
        $this->requireAdmin();
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) $startDate = date('Y') . '-04-01';
        if ((int)date('m') < 4) $startDate = (date('Y') - 1) . '-04-01';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) $endDate = date('Y-m-d');
        if ($endDate < $startDate) { $tmp = $startDate; $startDate = $endDate; $endDate = $tmp; }

        try {
            $base = $this->reportService->getProfitLoss($startDate, $endDate);
        } catch (\Exception $e) {
            error_log('FinancialReportController::profitAndLossStatement base: ' . $e->getMessage());
            $base = ['revenue' => ['total' => 0], 'expenses' => ['total' => 0], 'net_profit' => 0, 'profit_margin' => 0];
        }
        [$tSql, $tParams] = $this->tenantWhere();
        $sum = function (string $sql, array $params) {
            try {
                $row = $this->db->fetch($sql, $params);
                return (float)($row['total'] ?? 0);
            } catch (\Exception $e) {
                error_log('FinancialReportController::profitAndLossStatement line: ' . $e->getMessage());
                return 0.0;
            }
        };
        $range = [$startDate, $endDate];
        $bookingValue = $sum("SELECT COALESCE(SUM(total_amount),0) AS total FROM bookings WHERE (status IS NULL OR status NOT IN ('cancelled')) AND DATE(created_at) BETWEEN ? AND ?{$tSql}", array_merge($range, $tParams));
        $emiCollections = $sum("SELECT COALESCE(SUM(amount),0) AS total FROM booking_payment_receipts WHERE status='cleared' AND DATE(receipt_date) BETWEEN ? AND ?{$tSql}", array_merge($range, $tParams));
        $landCost = $sum("SELECT COALESCE(SUM(amount),0) AS total FROM land_purchases WHERE DATE(purchase_date) BETWEEN ? AND ?{$tSql}", array_merge($range, $tParams));
        $devCost = $sum("SELECT COALESCE(SUM(amount),0) AS total FROM colony_development_costs WHERE DATE(COALESCE(invoice_date, created_at)) BETWEEN ? AND ?{$tSql}", array_merge($range, $tParams));
        $commissions = $sum("SELECT COALESCE(SUM(amount),0) AS total FROM mlm_commission_ledger WHERE status='paid' AND DATE(created_at) BETWEEN ? AND ?{$tSql}", array_merge($range, $tParams));
        $salPayments = $sum("SELECT COALESCE(SUM(net_amount),0) AS total FROM salary_payments WHERE payment_status='paid' AND DATE(payment_date) BETWEEN ? AND ?{$tSql}", array_merge($range, $tParams));
        $salPayslips = $sum("SELECT COALESCE(SUM(net_salary),0) AS total FROM employee_payslips WHERE status='paid' AND DATE(COALESCE(paid_date, created_at)) BETWEEN ? AND ?{$tSql}", array_merge($range, $tParams));
        $overheads = $sum("SELECT COALESCE(SUM(amount),0) AS total FROM expenses WHERE DATE(expense_date) BETWEEN ? AND ?{$tSql}", array_merge($range, $tParams));

        $incomeTotal = $bookingValue + $emiCollections;
        $salariesTotal = $salPayments + $salPayslips;
        $expenseTotal = $landCost + $devCost + $commissions + $salariesTotal + $overheads;
        $ebitda = $incomeTotal - $expenseTotal;
        $statement = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'income' => [
                'booking_value' => $bookingValue,
                'emi_collections' => $emiCollections,
                'transfer_fees' => 0.0,
                'interest' => 0.0,
                'total' => $incomeTotal,
            ],
            'expenses' => [
                'land' => $landCost,
                'development' => $devCost,
                'commissions' => $commissions,
                'salaries_payments' => $salPayments,
                'salaries_payslips' => $salPayslips,
                'salaries_total' => $salariesTotal,
                'overheads' => $overheads,
                'total' => $expenseTotal,
            ],
            'ebitda' => $ebitda,
            'margin' => $incomeTotal > 0 ? round(($ebitda / $incomeTotal) * 100, 2) : 0,
            'service' => $base,
        ];
        return $this->render('admin/reports/profit_loss', [
            'page_title' => 'Profit & Loss Statement',
            'statement' => $statement,
        ]);
    }

    /**
     * Shared TDS builder: FY/quarter bounds, ledger aggregation, PAN/TDS
     * per deductee via TdsConfigService, payout refs from mlm_payouts.
     */
    private function buildTdsData(string $fy, string $quarter): array
    {
        [$y1, $y2] = array_map('intval', explode('-', $fy));
        $start = sprintf('%04d-04-01', $y1);
        $end = sprintf('%04d-03-31', $y2);
        $qlabel = '';
        if ($quarter === 'Q1') { $start = sprintf('%04d-04-01', $y1); $end = sprintf('%04d-06-30', $y1); $qlabel = 'Q1 (Apr–Jun)'; }
        elseif ($quarter === 'Q2') { $start = sprintf('%04d-07-01', $y1); $end = sprintf('%04d-09-30', $y1); $qlabel = 'Q2 (Jul–Sep)'; }
        elseif ($quarter === 'Q3') { $start = sprintf('%04d-10-01', $y1); $end = sprintf('%04d-12-31', $y1); $qlabel = 'Q3 (Oct–Dec)'; }
        elseif ($quarter === 'Q4') { $start = sprintf('%04d-01-01', $y2); $end = sprintf('%04d-03-31', $y2); $qlabel = 'Q4 (Jan–Mar)'; }

        try {
            $tdsSvc = new \App\Services\MLM\TdsConfigService();
        } catch (\Exception $e) {
            error_log('FinancialReportController TdsConfigService: ' . $e->getMessage());
            $tdsSvc = null;
        }
        $threshold = 30000;
        try {
            $sections = $tdsSvc ? $tdsSvc->getSections() : [];
            if (isset($sections['194H']['threshold'])) $threshold = (float)$sections['194H']['threshold'];
        } catch (\Exception $e) { $threshold = 30000; }
        $thresholdLabel = '₹' . number_format($threshold, 0);

        [$tSql, $tParams] = $this->tenantWhere();
        try {
            $groups = $this->db->fetchAll("
                SELECT beneficiary_user_id, COUNT(*) AS entries,
                       COALESCE(SUM(amount), 0) AS gross
                FROM mlm_commission_ledger
                WHERE DATE(created_at) BETWEEN ? AND ?{$tSql}
                GROUP BY beneficiary_user_id
                ORDER BY gross DESC
            ", array_merge([$start, $end], $tParams)) ?: [];
        } catch (\Exception $e) {
            error_log('FinancialReportController::buildTdsData ledger: ' . $e->getMessage());
            $groups = [];
        }

        $rows = [];
        $sumGross = 0.0; $sumTds = 0.0; $sumNet = 0.0;
        foreach ($groups as $g) {
            $uid = (int)($g['beneficiary_user_id'] ?? 0);
            if ($uid <= 0) continue;
            try {
                $who = $this->db->fetch("SELECT u.name, u.referral_code, e.pan_number FROM users u LEFT JOIN employees e ON e.user_id = u.id WHERE u.id = ?", [$uid]);
            } catch (\Exception $e) { $who = null; }
            $pan = strtoupper(trim((string)($who['pan_number'] ?? '')));
            $gross = (float)($g['gross'] ?? 0);
            $calc = null;
            try {
                if ($tdsSvc) $calc = $tdsSvc->calculateForCommission($gross, $pan !== '' ? $pan : null);
            } catch (\Exception $e) {
                error_log('FinancialReportController::buildTdsData calc: ' . $e->getMessage());
                $calc = null;
            }
            $tds = (float)($calc['tds_amount'] ?? 0);
            $rate = (float)($calc['rate_used'] ?? 0);
            $below = !empty($calc['below_threshold']);
            $panValid = ($calc['pan_status'] ?? '') === 'valid';
            $refs = [];
            $netPaid = 0.0;
            try {
                $pays = $this->db->fetchAll("SELECT transaction_ref, cheque_number, net_amount, status FROM mlm_payouts WHERE associate_user_id = ?{$tSql} ORDER BY id DESC LIMIT 5", array_merge([$uid], $tParams)) ?: [];
                foreach ($pays as $p) {
                    $ref = $p['transaction_ref'] ?: $p['cheque_number'] ?: '';
                    if ($ref !== '') $refs[] = $ref;
                    if (($p['status'] ?? '') === 'paid') $netPaid += (float)($p['net_amount'] ?? 0);
                }
            } catch (\Exception $e) {
                error_log('FinancialReportController::buildTdsData payouts: ' . $e->getMessage());
            }
            $rows[] = [
                'user_id' => $uid,
                'name' => $who['name'] ?? ('User #' . $uid),
                'code' => $who['referral_code'] ?? '',
                'pan' => $pan !== '' ? $pan : '—',
                'pan_valid' => $panValid,
                'gross' => $gross,
                'entries' => (int)($g['entries'] ?? 0),
                'below_threshold' => $below,
                'rate' => $rate,
                'tds' => $tds,
                'net_paid' => $netPaid,
                'utr' => implode(', ', array_slice(array_unique($refs), 0, 3)),
            ];
            $sumGross += $gross; $sumTds += $tds; $sumNet += $netPaid;
        }
        return [
            'rows' => $rows,
            'summary' => ['deductees' => count($rows), 'gross' => $sumGross, 'tds' => $sumTds, 'net_paid' => $sumNet],
            'range' => ['start' => $start, 'end' => $end, 'qlabel' => $qlabel],
            'threshold' => $threshold,
            'threshold_label' => $thresholdLabel,
        ];
    }

    private function currentFyLabel(): string
    {
        $m = (int)date('m');
        $y = (int)date('Y');
        return $m >= 4 ? "{$y}-" . ($y + 1) : ($y - 1) . "-{$y}";
    }

    private function fyOptions(): array
    {
        $cur = $this->currentFyLabel();
        [$cy] = array_map('intval', explode('-', $cur));
        $opts = [];
        for ($y = $cy; $y >= $cy - 3; $y--) $opts[] = "{$y}-" . ($y + 1);
        return $opts;
    }
}
