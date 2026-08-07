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
}
