<?php

namespace App\Http\Controllers\Reports;

use App\Services\Reports\ReportService;
use App\Http\Controllers\BaseController;
use Exception;

/**
 * Report Controller - APS Dream Home
 * Report generation and management
 * Custom MVC implementation without Laravel dependencies
 */
class ReportController extends BaseController
{
    private $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    /**
     * Display report dashboard
     */
    public function dashboard()
    {
        try {
            $scheduledReports = $this->reportService->getScheduledReports();
            $availableReports = $this->reportService->getAvailableReports();
            $availableFormats = $this->reportService->getAvailableFormats();

            $data = [
                'page_title' => 'Report Dashboard - APS Dream Home',
                'scheduled_reports' => $scheduledReports,
                'available_reports' => $availableReports,
                'available_formats' => $availableFormats,
                'total_scheduled' => count($scheduledReports)
            ];

            $this->render('reports/dashboard', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Error loading report dashboard: ' . $e->getMessage());
            $this->redirect(BASE_URL . 'reports/dashboard');
        }
    }

    /**
     * Display report generation form
     */
    public function generate()
    {
        try {
            $availableReports = $this->reportService->getAvailableReports();
            $availableFormats = $this->reportService->getAvailableFormats();

            $data = [
                'page_title' => 'Generate Report - APS Dream Home',
                'available_reports' => $availableReports,
                'available_formats' => $availableFormats,
                'action' => '/reports/create'
            ];

            $this->render('reports/generate', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading report generation form', $e->getMessage());
        }
    }

    /**
     * Create and display report
     */
    public function create()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $reportType = $_POST['report_type'] ?? 'sales';
                $format = $_POST['format'] ?? 'array';
                $startDate = $_POST['start_date'] ?? date('Y-m-01');
                $endDate = $_POST['end_date'] ?? date('Y-m-t');
                $status = $_POST['status'] ?? null;

                $report = null;

                switch ($reportType) {
                    case 'sales':
                        $report = $this->reportService->generateSalesReport($startDate, $endDate, $format);
                        break;

                    case 'property':
                        $report = $this->reportService->generatePropertyReport($status, $format);
                        break;

                    case 'associate':
                        $report = $this->reportService->generateAssociateReport($startDate, $endDate, $format);
                        break;

                    case 'customer':
                        $report = $this->reportService->generateCustomerReport($startDate, $endDate, $format);
                        break;

                    case 'financial':
                        $report = $this->reportService->generateFinancialReport($startDate, $endDate, $format);
                        break;

                    default:
                        throw new Exception('Invalid report type');
                }

                if ($report) {
                    $data = [
                        'page_title' => ucfirst($reportType) . ' Report - APS Dream Home',
                        'report' => $report,
                        'report_type' => $reportType,
                        'format' => $format,
                        'parameters' => [
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'status' => $status
                        ]
                    ];

                    if ($format === 'json' || $format === 'csv' || $format === 'excel' || $format === 'pdf') {
                        // For downloadable formats, set appropriate headers
                        $this->downloadReport($report, $reportType, $format);
                    } else {
                        // For display formats, render view
                        $this->render('reports/view', $data);
                    }
                } else {
                    throw new Exception('Failed to generate report');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error generating report', $e->getMessage());
        }
    }

    /**
     * Download report in specified format
     */
    private function downloadReport($report, $reportType, $format)
    {
        $filename = $reportType . '_report_' . date('Y-m-d_H-i-s');

        switch ($format) {
            case 'json':
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="' . $filename . '.json"');
                break;

            case 'csv':
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
                break;

            case 'excel':
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
                break;

            case 'pdf':
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
                break;
        }

        echo $report;
        exit;
    }

    /**
     * Display scheduled reports
     */
    public function scheduled()
    {
        try {
            $scheduledReports = $this->reportService->getScheduledReports();

            $data = [
                'page_title' => 'Scheduled Reports - APS Dream Home',
                'scheduled_reports' => $scheduledReports,
                'total_reports' => count($scheduledReports)
            ];

            $this->render('reports/scheduled', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading scheduled reports', $e->getMessage());
        }
    }

    /**
     * Display schedule report form
     */
    public function schedule()
    {
        try {
            $availableReports = $this->reportService->getAvailableReports();

            $data = [
                'page_title' => 'Schedule Report - APS Dream Home',
                'available_reports' => $availableReports,
                'action' => '/reports/store-schedule'
            ];

            $this->render('reports/schedule', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading schedule form', $e->getMessage());
        }
    }

    /**
     * Store scheduled report
     */
    public function storeSchedule()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $reportType = $_POST['report_type'] ?? 'sales';
                $schedule = $_POST['schedule'] ?? 'daily';
                $recipients = $_POST['recipients'] ?? [];
                $parameters = [
                    'start_date' => $_POST['start_date'] ?? date('Y-m-01'),
                    'end_date' => $_POST['end_date'] ?? date('Y-m-t'),
                    'status' => $_POST['status'] ?? null,
                    'format' => $_POST['format'] ?? 'array'
                ];

                $result = $this->reportService->scheduleReport($reportType, $parameters, $schedule, $recipients);

                if ($result) {
                    header('Location: /reports/scheduled');
                    exit;
                } else {
                    throw new Exception('Failed to schedule report');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error scheduling report', $e->getMessage());
        }
    }

    /**
     * Display sales report
     */
    public function sales()
    {
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            $format = $_GET['format'] ?? 'array';

            $report = $this->reportService->generateSalesReport($startDate, $endDate, $format);

            if ($report) {
                $data = [
                    'page_title' => 'Sales Report - APS Dream Home',
                    'report' => $report,
                    'parameters' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ];

                $this->render('reports/sales', $data);
            } else {
                throw new Exception('Failed to generate sales report');
            }
        } catch (Exception $e) {
            $this->renderError('Error generating sales report', $e->getMessage());
        }
    }

    /**
     * Display property report
     */
    public function property()
    {
        try {
            $status = $_GET['status'] ?? null;
            $format = $_GET['format'] ?? 'array';

            $report = $this->reportService->generatePropertyReport($status, $format);

            if ($report) {
                $data = [
                    'page_title' => 'Property Report - APS Dream Home',
                    'report' => $report,
                    'parameters' => [
                        'status' => $status
                    ]
                ];

                $this->render('reports/property', $data);
            } else {
                throw new Exception('Failed to generate property report');
            }
        } catch (Exception $e) {
            $this->renderError('Error generating property report', $e->getMessage());
        }
    }

    /**
     * Display associate performance report
     */
    public function associate()
    {
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            $format = $_GET['format'] ?? 'array';

            $report = $this->reportService->generateAssociateReport($startDate, $endDate, $format);

            if ($report) {
                $data = [
                    'page_title' => 'Associate Performance Report - APS Dream Home',
                    'report' => $report,
                    'parameters' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ];

                $this->render('reports/associate', $data);
            } else {
                throw new Exception('Failed to generate associate report');
            }
        } catch (Exception $e) {
            $this->renderError('Error generating associate report', $e->getMessage());
        }
    }

    /**
     * Display customer report
     */
    public function customer()
    {
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            $format = $_GET['format'] ?? 'array';

            $report = $this->reportService->generateCustomerReport($startDate, $endDate, $format);

            if ($report) {
                $data = [
                    'page_title' => 'Customer Report - APS Dream Home',
                    'report' => $report,
                    'parameters' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ];

                $this->render('reports/customer', $data);
            } else {
                throw new Exception('Failed to generate customer report');
            }
        } catch (Exception $e) {
            $this->renderError('Error generating customer report', $e->getMessage());
        }
    }

    /**
     * Display financial summary report
     */
    public function financial()
    {
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            $format = $_GET['format'] ?? 'array';

            $report = $this->reportService->generateFinancialReport($startDate, $endDate, $format);

            if ($report) {
                $data = [
                    'page_title' => 'Financial Summary Report - APS Dream Home',
                    'report' => $report,
                    'parameters' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]
                ];

                $this->render('reports/financial', $data);
            } else {
                throw new Exception('Failed to generate financial report');
            }
        } catch (Exception $e) {
            $this->renderError('Error generating financial report', $e->getMessage());
        }
    }
}
