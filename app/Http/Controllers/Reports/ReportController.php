<?php

namespace App\Http\Controllers\Reports;

use App\Services\Reports\ReportService;
use App\Http\Controllers\Admin\AdminController;
use Exception;

/**
 * Report Controller - APS Dream Home
 * Report generation and management
 * Custom MVC implementation without Laravel dependencies
 */
class ReportController extends AdminController
{
    private $reportService;

    public function __construct()
    {
        parent::__construct();
        $this->reportService = new ReportService();
    }

    /**
     * Display report dashboard
     */
    public function dashboard()
    {
        $this->requireAdmin();
        $scheduledReports = [];
        $availableReports = [];
        $availableFormats = [];
        $errorMsg = null;

        try {
            $scheduledReports = $this->reportService->getScheduledReports();
        } catch (Exception $e) {
            $errorMsg = 'Could not load scheduled reports: ' . $e->getMessage();
        }

        try {
            $availableReports = $this->reportService->getAvailableReports();
        } catch (Exception $e) {
            $availableReports = [];
        }

        try {
            $availableFormats = $this->reportService->getAvailableFormats();
        } catch (Exception $e) {
            $availableFormats = ['array' => 'Array Format'];
        }

        $data = [
            'page_title' => 'Report Dashboard - APS Dream Home',
            'scheduled_reports' => $scheduledReports,
            'available_reports' => $availableReports,
            'available_formats' => $availableFormats,
            'total_scheduled' => count($scheduledReports),
            'error_message' => $errorMsg
        ];

        $this->render('reports/dashboard', $data);
    }

    /**
     * Display report generation form
     */
    public function generate()
    {
        $this->requireAdmin();
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
            $this->setFlash('error', 'Error loading report generation form: ' . $e->getMessage());
        }
    }

    /**
     * Create and display report
     */
    public function create()
    {
        $this->requireAdmin();
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
            $this->setFlash('error', 'Error generating report: ' . $e->getMessage());
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
        $this->requireAdmin();
        try {
            $scheduledReports = $this->reportService->getScheduledReports();

            $data = [
                'page_title' => 'Scheduled Reports - APS Dream Home',
                'scheduled_reports' => $scheduledReports,
                'total_reports' => count($scheduledReports)
            ];

            $this->render('reports/scheduled', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Error loading scheduled reports: ' . $e->getMessage());
        }
    }

    /**
     * Display schedule report form
     */
    public function schedule()
    {
        $this->requireAdmin();
        try {
            $availableReports = $this->reportService->getAvailableReports();

            $data = [
                'page_title' => 'Schedule Report - APS Dream Home',
                'available_reports' => $availableReports,
                'action' => '/reports/store-schedule'
            ];

            $this->render('reports/schedule', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Error loading schedule form: ' . $e->getMessage());
        }
    }

    /**
     * Store scheduled report
     */
    public function storeSchedule()
    {
        $this->requireAdmin();
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
            $this->setFlash('error', 'Error scheduling report: ' . $e->getMessage());
        }
    }

    /**
     * Display sales report
     */
    public function sales()
    {
        $this->requireAdmin();
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
            $this->setFlash('error', 'Error generating sales report: ' . $e->getMessage());
        }
    }

    /**
     * Display property report
     */
    public function property()
    {
        $this->requireAdmin();
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
            $this->setFlash('error', 'Error generating property report: ' . $e->getMessage());
        }
    }

    /**
     * Display associate performance report
     */
    public function associate()
    {
        $this->requireAdmin();
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
            $this->setFlash('error', 'Error generating associate report: ' . $e->getMessage());
        }
    }

    /**
     * Display customer report
     */
    public function customer()
    {
        $this->requireAdmin();
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
            $this->setFlash('error', 'Error generating customer report: ' . $e->getMessage());
        }
    }

    /**
     * Display user activity report
     */
    public function userActivity()
    {
        $this->requireAdmin();
        try {
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');

            $users = [];
            try {
                $stmt = $this->db->query(
                    "SELECT u.id, u.name, u.email, u.role, u.created_at,
                            (SELECT COUNT(*) FROM leads WHERE assigned_to = u.id) as lead_count,
                            (SELECT COUNT(*) FROM user_properties WHERE user_id = u.id) as property_count,
                            (SELECT COUNT(*) FROM inquiries WHERE email = u.email) as inquiry_count
                     FROM users u
                     WHERE u.created_at BETWEEN ? AND ?
                     ORDER BY u.created_at DESC"
                );
                $stmt->execute([$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
                $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $users = [];
            }

            $data = [
                'page_title' => 'User Activity Report - APS Dream Home',
                'users' => $users,
                'parameters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ],
                'total_users' => count($users)
            ];

            $this->render('reports/user_activity', $data);
        } catch (Exception $e) {
            $this->setFlash('error', 'Error generating user activity report: ' . $e->getMessage());
        }
    }

    /**
     * Display financial summary report
     */
    public function financial()
    {
        $this->requireAdmin();
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
            $this->setFlash('error', 'Error generating financial report: ' . $e->getMessage());
        }
    }
}
