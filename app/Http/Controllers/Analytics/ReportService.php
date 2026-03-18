<?php

namespace App\Http\Controllers\Analytics;

use App\Core\Database;
use PDO;
use Exception;

/**
 * Report Service
 * Handles analytics report generation
 */
class ReportService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate sales report
     */
    public function generateSalesReport($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $query = "SELECT 
                        DATE(v.created_at) as report_date,
                        COUNT(*) as total_sales,
                        SUM(v.amount) as total_revenue,
                        COUNT(DISTINCT v.user_id) as unique_customers
                        FROM payments v
                        WHERE v.created_at BETWEEN ? AND ?";

            $stmt = $db->prepare($query);
            $stmt->execute([$filters['start_date'], $filters['end_date']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Generate property report
     */
    public function generatePropertyReport($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $query = "SELECT 
                        DATE(p.created_at) as report_date,
                        COUNT(*) as total_properties,
                        AVG(p.price) as avg_price,
                        MIN(p.price) as min_price,
                        MAX(p.price) as max_price
                        FROM properties p
                        WHERE p.created_at BETWEEN ? AND ?";

            $stmt = $db->prepare($query);
            $stmt->execute([$filters['start_date'], $filters['end_date']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Generate user activity report
     */
    public function generateUserActivityReport($filters)
    {
        try {
            $db = \App\Core\Database::getInstance();
            $query = "SELECT 
                        DATE(ua.created_at) as report_date,
                        COUNT(*) as total_activities,
                        COUNT(DISTINCT ua.user_id) as active_users,
                        AVG(ua.duration) as avg_session_duration
                        FROM user_activity_log ua
                        WHERE ua.created_at BETWEEN ? AND ?";

            $stmt = $db->prepare($query);
            $stmt->execute([$filters['start_date'], $filters['end_date']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Export data to CSV
     */
    public function exportToCsv($data, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Header
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0] ?? []));
        }

        // Data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
