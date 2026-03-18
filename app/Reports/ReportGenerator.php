<?php

namespace App\Reports;

use App\Core\Database;
use App\Core\App;
use Exception;

/**
 * Report Generator Class
 * Handles all report generation operations
 */
class ReportGenerator
{
    private $db;
    private $config;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->config = App::getInstance();
    }

    /**
     * Generate sales report
     * @param string $period Period (day, week, month, year)
     * @param array $filters Additional filters
     * @return array Sales report data
     */
    public function generateSalesReport($period = 'month', $filters = [])
    {
        try {
            $sql = "SELECT 
                        DATE(o.created_at) as order_date,
                        COUNT(o.id) as total_orders,
                        SUM(o.amount) as total_revenue,
                        AVG(o.amount) as avg_order_value,
                        COUNT(CASE WHEN o.status = 'completed' THEN 1 END) as completed_orders,
                        COUNT(CASE WHEN o.status = 'pending' THEN 1 END) as pending_orders,
                        COUNT(CASE WHEN o.status = 'cancelled' THEN 1 END) as cancelled_orders
                    FROM orders o 
                    WHERE o.status IN ('completed', 'pending', 'cancelled')";

            // Apply period filter
            switch ($period) {
                case 'day':
                    $sql .= " AND DATE(o.created_at) = CURDATE()";
                    break;
                case 'week':
                    $sql .= " AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $sql .= " AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                    break;
                case 'year':
                    $sql .= " AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
                    break;
            }

            // Apply additional filters
            $params = [];
            if (!empty($filters['user_id'])) {
                $sql .= " AND o.user_id = ?";
                $params[] = $filters['user_id'];
            }

            if (!empty($filters['min_amount'])) {
                $sql .= " AND o.amount >= ?";
                $params[] = $filters['min_amount'];
            }

            if (!empty($filters['max_amount'])) {
                $sql .= " AND o.amount <= ?";
                $params[] = $filters['max_amount'];
            }

            $sql .= " GROUP BY DATE(o.created_at) ORDER BY order_date DESC";

            $report = $this->db->fetchAll($sql, $params);

            // Calculate totals
            $totalOrders = array_sum(array_column($report, 'total_orders'));
            $totalRevenue = array_sum(array_column($report, 'total_revenue'));
            $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

            return [
                'success' => true,
                'report' => [
                    'type' => 'sales',
                    'period' => $period,
                    'summary' => [
                        'total_orders' => $totalOrders,
                        'total_revenue' => $totalRevenue,
                        'avg_order_value' => $avgOrderValue,
                        'completed_orders' => array_sum(array_column($report, 'completed_orders')),
                        'pending_orders' => array_sum(array_column($report, 'pending_orders')),
                        'cancelled_orders' => array_sum(array_column($report, 'cancelled_orders'))
                    ],
                    'daily_data' => $report
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to generate sales report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate property report
     * @param array $filters Property filters
     * @return array Property report data
     */
    public function generatePropertyReport($filters = [])
    {
        try {
            $sql = "SELECT 
                        p.type,
                        p.status,
                        COUNT(p.id) as total_properties,
                        AVG(p.price) as avg_price,
                        MIN(p.price) as min_price,
                        MAX(p.price) as max_price,
                        SUM(p.price) as total_value,
                        COUNT(CASE WHEN p.featured = 1 THEN 1 END) as featured_properties
                    FROM properties p
                    WHERE 1=1";

            $params = [];

            // Apply filters
            if (!empty($filters['type'])) {
                $sql .= " AND p.type = ?";
                $params[] = $filters['type'];
            }

            if (!empty($filters['status'])) {
                $sql .= " AND p.status = ?";
                $params[] = $filters['status'];
            }

            if (!empty($filters['min_price'])) {
                $sql .= " AND p.price >= ?";
                $params[] = $filters['min_price'];
            }

            if (!empty($filters['max_price'])) {
                $sql .= " AND p.price <= ?";
                $params[] = $filters['max_price'];
            }

            $sql .= " GROUP BY p.type, p.status ORDER BY p.type, p.status";

            $report = $this->db->fetchAll($sql, $params);

            // Calculate totals
            $totalProperties = array_sum(array_column($report, 'total_properties'));
            $totalValue = array_sum(array_column($report, 'total_value'));
            $featuredProperties = array_sum(array_column($report, 'featured_properties'));

            return [
                'success' => true,
                'report' => [
                    'type' => 'property',
                    'summary' => [
                        'total_properties' => $totalProperties,
                        'total_value' => $totalValue,
                        'featured_properties' => $featuredProperties,
                        'avg_price' => $totalProperties > 0 ? $totalValue / $totalProperties : 0
                    ],
                    'breakdown' => $report
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to generate property report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate user activity report
     * @param string $period Period (day, week, month)
     * @param array $filters User filters
     * @return array User activity report
     */
    public function generateUserActivityReport($period = 'month', $filters = [])
    {
        try {
            $sql = "SELECT 
                        u.name,
                        u.email,
                        COUNT(al.id) as total_activities,
                        MAX(al.created_at) as last_activity,
                        COUNT(CASE WHEN al.action = 'login' THEN 1 END) as logins,
                        COUNT(CASE WHEN al.action = 'logout' THEN 1 END) as logouts,
                        COUNT(CASE WHEN al.action = 'property_view' THEN 1 END) as property_views
                    FROM users u
                    LEFT JOIN activity_log al ON u.id = al.user_id
                    WHERE 1=1";

            // Apply period filter
            switch ($period) {
                case 'day':
                    $sql .= " AND al.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
                    break;
                case 'week':
                    $sql .= " AND al.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                    break;
                case 'month':
                    $sql .= " AND al.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                    break;
            }

            // Apply additional filters
            $params = [];
            if (!empty($filters['user_id'])) {
                $sql .= " AND u.id = ?";
                $params[] = $filters['user_id'];
            }

            if (!empty($filters['role'])) {
                $sql .= " AND u.role = ?";
                $params[] = $filters['role'];
            }

            $sql .= " GROUP BY u.id, u.name, u.email ORDER BY total_activities DESC";

            $report = $this->db->fetchAll($sql, $params);

            // Calculate totals
            $totalActivities = array_sum(array_column($report, 'total_activities'));
            $totalLogins = array_sum(array_column($report, 'logins'));
            $totalPropertyViews = array_sum(array_column($report, 'property_views'));

            return [
                'success' => true,
                'report' => [
                    'type' => 'user_activity',
                    'period' => $period,
                    'summary' => [
                        'total_users' => count($report),
                        'total_activities' => $totalActivities,
                        'total_logins' => $totalLogins,
                        'total_property_views' => $totalPropertyViews
                    ],
                    'user_breakdown' => $report
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to generate user activity report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate financial report
     * @param string $period Period
     * @return array Financial report data
     */
    public function generateFinancialReport($period = 'month')
    {
        try {
            $sql = "SELECT 
                        DATE(o.created_at) as transaction_date,
                        SUM(CASE WHEN o.status = 'completed' THEN o.amount ELSE 0 END) as income,
                        SUM(CASE WHEN o.status = 'refunded' THEN o.amount ELSE 0 END) as refunds,
                        COUNT(CASE WHEN o.status = 'completed' THEN 1 END) as successful_transactions,
                        COUNT(CASE WHEN o.status = 'refunded' THEN 1 END) as refunded_transactions
                    FROM orders o 
                    WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

            // Adjust for different periods
            switch ($period) {
                case 'day':
                    $sql = str_replace("DATE_SUB(NOW(), INTERVAL 30 DAY)", "DATE(NOW())", $sql);
                    break;
                case 'week':
                    $sql = str_replace("DATE_SUB(NOW(), INTERVAL 30 DAY)", "DATE_SUB(NOW(), INTERVAL 7 DAY)", $sql);
                    break;
                case 'year':
                    $sql = str_replace("DATE_SUB(NOW(), INTERVAL 30 DAY)", "DATE_SUB(NOW(), INTERVAL 365 DAY)", $sql);
                    break;
            }

            $sql .= " GROUP BY DATE(o.created_at) ORDER BY transaction_date DESC";

            $report = $this->db->fetchAll($sql);

            // Calculate totals
            $totalIncome = array_sum(array_column($report, 'income'));
            $totalRefunds = array_sum(array_column($report, 'refunds'));
            $netIncome = $totalIncome - $totalRefunds;

            return [
                'success' => true,
                'report' => [
                    'type' => 'financial',
                    'period' => $period,
                    'summary' => [
                        'total_income' => $totalIncome,
                        'total_refunds' => $totalRefunds,
                        'net_income' => $netIncome,
                        'successful_transactions' => array_sum(array_column($report, 'successful_transactions')),
                        'refunded_transactions' => array_sum(array_column($report, 'refunded_transactions'))
                    ],
                    'daily_breakdown' => $report
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to generate financial report: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Export report to CSV
     * @param array $data Report data
     * @param string $filename Output filename
     * @return bool Export success
     */
    public function exportToCSV($data, $filename = 'report.csv')
    {
        try {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: 0');

            $output = fopen('php://output', 'w');

            // Get headers from first row
            if (!empty($data)) {
                $headers = array_keys($data[0]);
                fputcsv($output, $headers);

                // Write data rows
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }

            fclose($output);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get available report types
     * @return array Report types
     */
    public function getReportTypes()
    {
        return [
            'sales' => 'Sales Report',
            'property' => 'Property Report',
            'user_activity' => 'User Activity Report',
            'financial' => 'Financial Report'
        ];
    }
}
