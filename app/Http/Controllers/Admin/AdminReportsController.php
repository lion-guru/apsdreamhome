<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

/**
 * Admin Reports Controller
 * Handles report generation for admin panel
 */
class AdminReportsController extends AdminController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Show reports index page
     */
    public function index()
    {
        $this->render('admin/reports', [
            'page_title' => 'Reports',
            'page_description' => 'Generate and view various reports'
        ]);
    }

    /**
     * Generate daily report
     */
    public function dailyReport()
    {
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $reportData = [
            'date' => $date,
            'leads_count' => $this->getLeadsCount($date),
            'inquiries_count' => $this->getInquiriesCount($date),
            'sales_count' => $this->getSalesCount($date),
            'revenue' => $this->getRevenue($date),
            'new_customers' => $this->getNewCustomersCount($date)
        ];

        $this->render('admin/reports/daily', [
            'page_title' => 'Daily Report',
            'page_description' => "Report for {$date}",
            'report_data' => $reportData
        ]);
    }

    /**
     * Generate weekly report
     */
    public function weeklyReport()
    {
        $weekStart = $_GET['week_start'] ?? date('Y-m-d', strtotime('monday this week'));
        $weekEnd = $_GET['week_end'] ?? date('Y-m-d', strtotime('sunday this week'));

        $reportData = [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'leads_count' => $this->getLeadsCount($weekStart, $weekEnd),
            'inquiries_count' => $this->getInquiriesCount($weekStart, $weekEnd),
            'sales_count' => $this->getSalesCount($weekStart, $weekEnd),
            'revenue' => $this->getRevenue($weekStart, $weekEnd),
            'conversion_rate' => $this->getConversionRate($weekStart, $weekEnd)
        ];

        $this->render('admin/reports/weekly', [
            'page_title' => 'Weekly Report',
            'page_description' => "Report from {$weekStart} to {$weekEnd}",
            'report_data' => $reportData
        ]);
    }

    /**
     * Generate monthly report
     */
    public function monthlyReport()
    {
        $month = $_GET['month'] ?? date('Y-m');
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $reportData = [
            'month' => $month,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'leads_count' => $this->getLeadsCount($startDate, $endDate),
            'inquiries_count' => $this->getInquiriesCount($startDate, $endDate),
            'sales_count' => $this->getSalesCount($startDate, $endDate),
            'revenue' => $this->getRevenue($startDate, $endDate),
            'conversion_rate' => $this->getConversionRate($startDate, $endDate),
            'top_performers' => $this->getTopPerformers($startDate, $endDate)
        ];

        $this->render('admin/reports/monthly', [
            'page_title' => 'Monthly Report',
            'page_description' => "Report for {$month}",
            'report_data' => $reportData
        ]);
    }

    /**
     * Sales report
     */
    public function salesReport()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $salesData = $this->getSalesData($startDate, $endDate);

        $this->render('admin/reports/sales', [
            'page_title' => 'Sales Report',
            'page_description' => "Sales from {$startDate} to {$endDate}",
            'sales_data' => $salesData,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Lead report
     */
    public function leadReport()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $leadData = $this->getLeadData($startDate, $endDate);

        $this->render('admin/reports/lead', [
            'page_title' => 'Lead Report',
            'page_description' => "Leads from {$startDate} to {$endDate}",
            'lead_data' => $leadData,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Get leads count for date range
     */
    private function getLeadsCount($startDate, $endDate = null)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM leads WHERE DATE(created_at) >= ?";
            $params = [$startDate];
            
            if ($endDate) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $endDate;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get inquiries count for date range
     */
    private function getInquiriesCount($startDate, $endDate = null)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM inquiries WHERE DATE(created_at) >= ?";
            $params = [$startDate];
            
            if ($endDate) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $endDate;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get sales count for date range
     */
    private function getSalesCount($startDate, $endDate = null)
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM sales WHERE DATE(created_at) >= ?";
            $params = [$startDate];
            
            if ($endDate) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $endDate;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get revenue for date range
     */
    private function getRevenue($startDate, $endDate = null)
    {
        try {
            $sql = "SELECT COALESCE(SUM(amount), 0) as revenue FROM sales WHERE DATE(created_at) >= ?";
            $params = [$startDate];
            
            if ($endDate) {
                $sql .= " AND DATE(created_at) <= ?";
                $params[] = $endDate;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result['revenue'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get new users count for date
     */
    private function getNewCustomersCount($date)
    {
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $sql = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?{$tidSql}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge([$date], $tidParams));
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get conversion rate for date range
     */
    private function getConversionRate($startDate, $endDate)
    {
        $leads = $this->getLeadsCount($startDate, $endDate);
        $sales = $this->getSalesCount($startDate, $endDate);
        
        if ($leads == 0) return 0;
        return round(($sales / $leads) * 100, 2);
    }

    /**
     * Get top performers for date range
     */
    private function getTopPerformers($startDate, $endDate)
    {
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $sql = "SELECT 
                        u.name, 
                        u.email,
                        COUNT(s.id) as sales_count,
                        COALESCE(SUM(s.amount), 0) as total_revenue
                    FROM users u
                    LEFT JOIN sales s ON u.id = s.created_by 
                        AND DATE(s.created_at) >= ? 
                        AND DATE(s.created_at) <= ?
                    WHERE 1=1{$tidSql}
                    GROUP BY u.id, u.name, u.email
                    ORDER BY sales_count DESC
                    LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge([$startDate, $endDate], $tidParams));
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get sales data for date range
     */
    private function getSalesData($startDate, $endDate)
    {
        try {
            $sql = "SELECT 
                        s.*,
                        u.name as customer_name,
                        u.email as customer_email
                    FROM sales s
                    LEFT JOIN users u ON s.customer_id = u.id
                    WHERE DATE(s.created_at) >= ? 
                    AND DATE(s.created_at) <= ?
                    ORDER BY s.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get lead data for date range
     */
    private function getLeadData($startDate, $endDate)
    {
        try {
            $sql = "SELECT 
                        l.*,
                        (SELECT COUNT(*) FROM lead_activities WHERE lead_id = l.id) as activity_count
                    FROM leads l
                    WHERE DATE(l.created_at) >= ? 
                    AND DATE(l.created_at) <= ?
                    ORDER BY l.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Export report to CSV
     */
    public function export()
    {
        $reportType = $_GET['type'] ?? 'sales';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $data = [];
        $filename = '';

        switch ($reportType) {
            case 'sales':
                $data = $this->getSalesData($startDate, $endDate);
                $filename = "sales_report_{$startDate}_to_{$endDate}.csv";
                break;
            case 'leads':
                $data = $this->getLeadData($startDate, $endDate);
                $filename = "lead_report_{$startDate}_to_{$endDate}.csv";
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid report type']);
                exit;
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}
