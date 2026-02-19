<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Admin\AdminController;
use App\Services\ReportService;

class ReportController extends AdminController
{
    private $reportService;

    public function __construct()
    {
        parent::__construct();

        $this->reportService = new ReportService();
    }

    /**
     * Display reports dashboard
     */
    public function index()
    {
        $this->render('reports/index', [
            'title' => 'Reports Dashboard'
        ]);
    }

    /**
     * Generate sales report
     */
    public function sales()
    {
        $filters = [
            'start_date' => $this->request->get('start_date', date('Y-m-01')),
            'end_date' => $this->request->get('end_date', date('Y-m-t'))
        ];

        $report = $this->reportService->generateSalesReport($filters);

        // Export to CSV if requested
        if ($this->request->get('export') === 'csv') {
            $filename = 'sales-report-' . date('Y-m-d') . '.csv';
            $this->reportService->exportToCsv($report, $filename);
            return;
        }

        $this->render('reports/sales', [
            'title' => 'Sales Report',
            'report' => $report,
            'filters' => $filters
        ]);
    }

    /**
     * Generate property report
     */
    public function properties()
    {
        $filters = [
            'location' => $this->request->get('location'),
            'min_price' => $this->request->get('min_price') ? (float)$this->request->get('min_price') : null,
            'max_price' => $this->request->get('max_price') ? (float)$this->request->get('max_price') : null
        ];

        $report = $this->reportService->generatePropertyReport($filters);

        // Export to CSV if requested
        if ($this->request->get('export') === 'csv') {
            $filename = 'property-report-' . date('Y-m-d') . '.csv';
            $this->reportService->exportToCsv($report, $filename);
            return;
        }

        $this->render('reports/properties', [
            'title' => 'Property Report',
            'report' => $report,
            'filters' => $filters
        ]);
    }

    /**
     * Generate user activity report
     */
    public function userActivity()
    {
        $filters = [
            'start_date' => $this->request->get('start_date', date('Y-m-01')),
            'end_date' => $this->request->get('end_date', date('Y-m-d')),
            'sort' => $this->request->get('sort', 'last_activity'),
            'order' => $this->request->get('order', 'DESC'),
            'page' => (int)$this->request->get('page', 1)
        ];

        $report = $this->reportService->generateUserActivityReport($filters);

        // Get total count for pagination
        $totalUsers = $this->getTotalUsers($filters);
        $perPage = 20;
        $totalPages = ceil($totalUsers / $perPage);

        // Export to CSV if requested
        if ($this->request->get('export') === 'csv') {
            $filename = 'user-activity-report-' . date('Y-m-d') . '.csv';
            $this->reportService->exportToCsv($report, $filename);
            return;
        }

        $this->render('reports/user_activity', [
            'title' => 'User Activity Report',
            'report' => $report,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $filters['page'],
                'total_pages' => $totalPages,
                'base_url' => '/reports/user-activity?' . http_build_query(array_diff_key($filters, ['page' => '']))
            ]
        ]);
    }

    /**
     * Get total number of users for pagination
     */
    private function getTotalUsers(array $filters)
    {
        $query = "SELECT COUNT(DISTINCT u.id) as total 
                 FROM users u
                 LEFT JOIN property_views v ON u.id = v.user_id
                 LEFT JOIN contacts c ON u.id = c.user_id
                 WHERE 1=1";

        $params = [];

        if (!empty($filters['start_date'])) {
            $query .= " AND (v.visited_at >= ? OR c.created_at >= ?)";
            $params[] = $filters['start_date'];
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $query .= " AND (v.visited_at <= ? OR c.created_at <= ?)";
            $params[] = $filters['end_date'] . ' 23:59:59';
            $params[] = $filters['end_date'] . ' 23:59:59';
        }

        $stmt = $this->db->query($query, $params);
        $result = $stmt->fetch();

        return $result ? (int)$result['total'] : 0;
    }
}
