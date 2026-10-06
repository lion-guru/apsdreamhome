<?php
namespace App\Http\Controllers\Admin;

use App\Services\InvestmentAnalyticsService;

class InvestmentAnalyticsController extends AdminController
{
    private InvestmentAnalyticsService $analyticsService;

    public function __construct()
    {
        parent::__construct();
        $this->analyticsService = new InvestmentAnalyticsService();
    }

    public function index()
    {
        $this->requireAdmin();
        $stats = $this->analyticsService->getOverviewStats();
        $planPerformance = $this->analyticsService->getPlanPerformance();
        $recentActivity = $this->analyticsService->getRecentActivity(20);

        $this->render('admin/investment/analytics', [
            'page_title' => 'Investment Analytics Dashboard',
            'stats' => $stats,
            'planPerformance' => $planPerformance,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function planPerformance()
    {
        $this->requireAdmin();
        $planPerformance = $this->analyticsService->getPlanPerformance();

        $this->render('admin/investment/plan_performance', [
            'page_title' => 'Plan Performance',
            'planPerformance' => $planPerformance,
        ]);
    }

    public function installmentReport()
    {
        $this->requireAdmin();
        $startDate = $_GET['start_date'] ?? date('Y-m-01', strtotime('-1 month'));
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $report = [];
        if ($startDate && $endDate) {
            $report = $this->analyticsService->getInstallmentCollectionReport($startDate, $_GET['end_date'] ?? date('Y-m-t'));
        }

        $this->render('admin/investment/installment_report', [
            'page_title' => 'Installment Collection Report',
            'report' => $report,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function exportInstallmentReport()
    {
        $this->requireAdmin();
        $startDate = $_GET['start_date'] ?? date('Y-m-01', strtotime('-1 month'));
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $report = $this->analyticsService->getInstallmentCollectionReport($startDate, $endDate);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="investment_installment_report_' . $startDate . '_to_' . $endDate . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'Installments Processed', 'Total Amount', 'Paid Amount', 'Failed Amount', 'Paid Count', 'Failed Count']);
        
        foreach ($report as $row) {
            fputcsv($output, [
                $row['paid_date'],
                $row['count'],
                number_format($row['total_collected'], 2, '.', ''),
                number_format($row['paid_amount'], 2, '.', ''),
                number_format($row['failed_amount'], 2, '.', ''),
                $row['paid_count'],
                $row['failed_count'],
            ]);
        }
        
        fclose($output);
        exit;
    }
}