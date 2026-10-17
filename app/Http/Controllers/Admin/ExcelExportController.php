<?php
/**
 * ExcelExportController — .xlsx export for any report.
 *
 * Reuses ExcelExportService so every report gets Excel output without
 * reinventing CSV-only export. Endpoints:
 *   GET  /admin/export/excel/{report}   — stream .xlsx for a named report
 *   POST /admin/export/excel             — generic export (headers + rows JSON)
 *
 * Reports supported: receivables-aging, payables-aging, fund-flow, leads,
 * bookings, payments, customers. Add more by extending the exportReport() switch.
 */
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\ExcelExportService;
use App\Services\ReceivablesAgingService;
use App\Services\PayablesAgingService;
use App\Services\FundFlowService;
use Exception;

class ExcelExportController extends AdminController
{
    protected $excel;
    protected $receivablesService;
    protected $payablesService;
    protected $fundFlowService;

    public function __construct()
    {
        parent::__construct();
        $this->excel = new ExcelExportService();
        $this->receivablesService = new ReceivablesAgingService();
        $this->payablesService = new PayablesAgingService();
        $this->fundFlowService = new FundFlowService();
    }

    /**
     * Stream a named report as .xlsx.
     */
    public function export(string $report): void
    {
        $this->requireAdmin();
        $asOfDate = $_GET['as_of_date'] ?? date('Y-m-d');

        try {
            switch ($report) {
                case 'receivables-aging':
                    $colonyId = (int)($_GET['colony_id'] ?? 0);
                    $result = $this->receivablesService->getAging($asOfDate, $colonyId);
                    $this->exportReceivablesAging($result, $asOfDate);
                    break;

                case 'payables-aging':
                    $vendorId = (int)($_GET['vendor_id'] ?? 0);
                    $result = $this->payablesService->getAging($asOfDate, $vendorId);
                    $this->exportPayablesAging($result, $asOfDate);
                    break;

                case 'fund-flow':
                    $result = $this->fundFlowService->getFundFlow($asOfDate);
                    $this->exportFundFlow($result, $asOfDate);
                    break;

                default:
                    $this->setFlash('error', "Unknown export report: {$report}");
                    $this->redirect('/admin/reports');
                    return;
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Excel export failed: ' . $e->getMessage());
            $this->redirect('/admin/reports');
        }
    }

    /**
     * Generic export: accepts JSON body {headers, rows, filename, sheet_name}.
     */
    public function exportGeneric(): void
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'POST required']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input) || empty($input['headers']) || empty($input['rows'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'headers and rows required']);
            exit;
        }

        $filename = $input['filename'] ?? 'export_' . date('Y-m-d') . '.xlsx';
        $sheetName = $input['sheet_name'] ?? 'Export';

        $this->excel->export($input['headers'], $input['rows'], $filename, $sheetName);
    }

    /* ---------------------------------------------------------------- */

    private function exportReceivablesAging(array $result, string $asOfDate): void
    {
        $rows = $result['rows'] ?? [];
        $headers = [
            'Schedule ID', 'Booking No', 'Customer', 'Phone', 'Colony',
            'Installment', 'Due Date', 'Amount', 'Paid', 'Outstanding',
            'Age (days)', 'Bucket',
        ];

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                $r['schedule_id'] ?? '',
                $r['booking_number'] ?? '',
                $r['customer_name'] ?? '',
                $r['customer_phone'] ?? '',
                $r['colony_name'] ?? '',
                $r['installment_no'] ?? '',
                $r['due_date'] ?? '',
                (float)($r['amount'] ?? 0),
                (float)($r['paid_amount'] ?? 0),
                (float)($r['outstanding'] ?? 0),
                (int)($r['age_days'] ?? 0),
                ucfirst(str_replace('_', ' ', $r['bucket'] ?? '')),
            ];
        }

        $filename = 'receivables_aging_' . $asOfDate . '.xlsx';
        $this->excel->export($headers, $data, $filename, 'Receivables Aging');
    }

    private function exportPayablesAging(array $result, string $asOfDate): void
    {
        $rows = $result['rows'] ?? [];
        $headers = [
            'Vendor', 'Invoice No', 'Due Date', 'Amount', 'Paid',
            'Outstanding', 'Age (days)', 'Bucket',
        ];

        $data = [];
        foreach ($rows as $r) {
            $data[] = [
                $r['vendor_name'] ?? $r['vendor'] ?? '',
                $r['invoice_no'] ?? $r['reference'] ?? '',
                $r['due_date'] ?? '',
                (float)($r['amount'] ?? 0),
                (float)($r['paid_amount'] ?? 0),
                (float)($r['outstanding'] ?? 0),
                (int)($r['age_days'] ?? 0),
                ucfirst(str_replace('_', ' ', $r['bucket'] ?? '')),
            ];
        }

        $filename = 'payables_aging_' . $asOfDate . '.xlsx';
        $this->excel->export($headers, $data, $filename, 'Payables Aging');
    }

    private function exportFundFlow(array $result, string $asOfDate): void
    {
        $rows = $result['rows'] ?? [];
        $headers = array_keys(reset($rows) ?: []);
        if (empty($headers)) {
            $headers = ['Date', 'Description', 'Source', 'Amount', 'Type'];
        }

        $data = [];
        foreach ($rows as $r) {
            $row = [];
            foreach ($headers as $h) {
                $row[] = $r[$h] ?? '';
            }
            $data[] = $row;
        }

        $filename = 'fund_flow_' . $asOfDate . '.xlsx';
        $this->excel->export($headers, $data, $filename, 'Fund Flow');
    }
}
