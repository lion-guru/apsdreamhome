<?php

namespace App\Services\Reports;

use App\Core\Database\Database;
use App\Services\LoggingService;

/**
 * Report Service - APS Dream Home
 * Dynamic report generation system
 * Custom MVC implementation without Laravel dependencies
 */
class ReportService
{
    private $database;
    private $logger;

    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new LoggingService();
    }

    /**
     * Generate sales report
     */
    public function generateSalesReport($startDate, $endDate, $format = 'array')
    {
        try {
            $sql = "SELECT 
                        s.id,
                        s.sale_amount,
                        s.sale_date,
                        s.property_id,
                        p.title as property_title,
                        p.location,
                        a.name as associate_name,
                        c.name as customer_name,
                        c.email as customer_email,
                        s.commission_amount,
                        s.status
                    FROM sales s
                    LEFT JOIN properties p ON s.property_id = p.id
                    LEFT JOIN associates a ON s.associate_id = a.id
                    LEFT JOIN customers c ON s.customer_id = c.id
                    WHERE s.sale_date BETWEEN :start_date AND :end_date
                    ORDER BY s.sale_date DESC";
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $data = $stmt->fetchAll();
            
            // Calculate summary statistics
            $summary = [
                'total_sales' => count($data),
                'total_revenue' => array_sum(array_column($data, 'sale_amount')),
                'total_commission' => array_sum(array_column($data, 'commission_amount')),
                'average_sale' => count($data) > 0 ? array_sum(array_column($data, 'sale_amount')) / count($data) : 0,
                'date_range' => ['start' => $startDate, 'end' => $endDate]
            ];
            
            $report = [
                'title' => 'Sales Report',
                'generated_at' => date('Y-m-d H:i:s'),
                'summary' => $summary,
                'data' => $data
            ];
            
            return $this->formatReport($report, $format);
        } catch (Exception $e) {
            $this->logger->error("Error generating sales report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate property report
     */
    public function generatePropertyReport($status = null, $format = 'array')
    {
        try {
            $sql = "SELECT 
                        p.id,
                        p.title,
                        p.type,
                        p.location,
                        p.price,
                        p.bedrooms,
                        p.bathrooms,
                        p.area,
                        p.status,
                        p.created_at,
                        COUNT(s.id) as sales_count,
                        COALESCE(SUM(s.sale_amount), 0) as total_sales
                    FROM properties p
                    LEFT JOIN sales s ON p.id = s.property_id";
            
            if ($status) {
                $sql .= " WHERE p.status = :status";
            }
            
            $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
            
            $stmt = $this->database->prepare($sql);
            
            if ($status) {
                $stmt->bindParam(':status', $status);
            }
            
            $stmt->execute();
            $data = $stmt->fetchAll();
            
            // Calculate summary statistics
            $summary = [
                'total_properties' => count($data),
                'total_value' => array_sum(array_column($data, 'price')),
                'total_sales' => array_sum(array_column($data, 'sales_count')),
                'total_revenue' => array_sum(array_column($data, 'total_sales')),
                'average_price' => count($data) > 0 ? array_sum(array_column($data, 'price')) / count($data) : 0,
                'status_filter' => $status
            ];
            
            $report = [
                'title' => 'Property Report',
                'generated_at' => date('Y-m-d H:i:s'),
                'summary' => $summary,
                'data' => $data
            ];
            
            return $this->formatReport($report, $format);
        } catch (Exception $e) {
            $this->logger->error("Error generating property report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate associate performance report
     */
    public function generateAssociateReport($startDate, $endDate, $format = 'array')
    {
        try {
            $sql = "SELECT 
                        a.id,
                        a.name,
                        a.email,
                        a.phone,
                        a.commission_rate,
                        a.status,
                        COUNT(s.id) as total_sales,
                        COALESCE(SUM(s.sale_amount), 0) as total_revenue,
                        COALESCE(SUM(s.commission_amount), 0) as total_commission,
                        AVG(s.sale_amount) as average_sale,
                        MAX(s.sale_date) as last_sale_date
                    FROM associates a
                    LEFT JOIN sales s ON a.id = s.associate_id 
                        AND s.sale_date BETWEEN :start_date AND :end_date
                    GROUP BY a.id
                    ORDER BY total_revenue DESC";
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $data = $stmt->fetchAll();
            
            // Calculate summary statistics
            $summary = [
                'total_associates' => count($data),
                'active_associates' => count(array_filter($data, fn($a) => $a['status'] === 'active')),
                'total_sales' => array_sum(array_column($data, 'total_sales')),
                'total_revenue' => array_sum(array_column($data, 'total_revenue')),
                'total_commission' => array_sum(array_column($data, 'total_commission')),
                'top_performer' => !empty($data) ? $data[0]['name'] : 'N/A',
                'date_range' => ['start' => $startDate, 'end' => $endDate]
            ];
            
            $report = [
                'title' => 'Associate Performance Report',
                'generated_at' => date('Y-m-d H:i:s'),
                'summary' => $summary,
                'data' => $data
            ];
            
            return $this->formatReport($report, $format);
        } catch (Exception $e) {
            $this->logger->error("Error generating associate report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate customer report
     */
    public function generateCustomerReport($startDate, $endDate, $format = 'array')
    {
        try {
            $sql = "SELECT 
                        c.id,
                        c.name,
                        c.email,
                        c.phone,
                        COUNT(s.id) as total_purchases,
                        COALESCE(SUM(s.sale_amount), 0) as total_spent,
                        AVG(s.sale_amount) as average_purchase,
                        MAX(s.sale_date) as last_purchase_date,
                        c.created_at as registration_date
                    FROM customers c
                    LEFT JOIN sales s ON c.id = s.customer_id 
                        AND s.sale_date BETWEEN :start_date AND :end_date
                    GROUP BY c.id
                    ORDER BY total_spent DESC";
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            
            $data = $stmt->fetchAll();
            
            // Calculate summary statistics
            $summary = [
                'total_customers' => count($data),
                'active_customers' => count(array_filter($data, fn($c) => $c['total_purchases'] > 0)),
                'total_purchases' => array_sum(array_column($data, 'total_purchases')),
                'total_revenue' => array_sum(array_column($data, 'total_spent')),
                'average_purchase' => count(array_filter($data, fn($c) => $c['total_purchases'] > 0)) > 0 ? 
                    array_sum(array_column(array_filter($data, fn($c) => $c['total_purchases'] > 0), 'total_spent')) / 
                    count(array_filter($data, fn($c) => $c['total_purchases'] > 0)) : 0,
                'date_range' => ['start' => $startDate, 'end' => $endDate]
            ];
            
            $report = [
                'title' => 'Customer Report',
                'generated_at' => date('Y-m-d H:i:s'),
                'summary' => $summary,
                'data' => $data
            ];
            
            return $this->formatReport($report, $format);
        } catch (Exception $e) {
            $this->logger->error("Error generating customer report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate financial summary report
     */
    public function generateFinancialReport($startDate, $endDate, $format = 'array')
    {
        try {
            // Sales data
            $salesSql = "SELECT 
                            COUNT(*) as total_sales,
                            SUM(sale_amount) as total_revenue,
                            SUM(commission_amount) as total_commission,
                            AVG(sale_amount) as average_sale,
                            MAX(sale_amount) as highest_sale,
                            MIN(sale_amount) as lowest_sale
                        FROM sales 
                        WHERE sale_date BETWEEN :start_date AND :end_date";
            
            $stmt = $this->database->prepare($salesSql);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $salesData = $stmt->fetch();
            
            // Property data
            $propertySql = "SELECT 
                              COUNT(*) as total_properties,
                              AVG(price) as average_property_price,
                              SUM(price) as total_property_value
                          FROM properties 
                          WHERE created_at BETWEEN :start_date AND :end_date";
            
            $stmt = $this->database->prepare($propertySql);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $propertyData = $stmt->fetch();
            
            // Customer data
            $customerSql = "SELECT 
                              COUNT(*) as new_customers
                          FROM customers 
                          WHERE created_at BETWEEN :start_date AND :end_date";
            
            $stmt = $this->database->prepare($customerSql);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $customerData = $stmt->fetch();
            
            // Monthly trends
            $trendsSql = "SELECT 
                            DATE_FORMAT(sale_date, '%Y-%m') as month,
                            COUNT(*) as sales_count,
                            SUM(sale_amount) as revenue
                        FROM sales 
                        WHERE sale_date BETWEEN :start_date AND :end_date
                        GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
                        ORDER BY month";
            
            $stmt = $this->database->prepare($trendsSql);
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
            $stmt->execute();
            $trendsData = $stmt->fetchAll();
            
            $summary = [
                'sales_summary' => $salesData,
                'property_summary' => $propertyData,
                'customer_summary' => $customerData,
                'monthly_trends' => $trendsData,
                'date_range' => ['start' => $startDate, 'end' => $endDate]
            ];
            
            $report = [
                'title' => 'Financial Summary Report',
                'generated_at' => date('Y-m-d H:i:s'),
                'summary' => $summary,
                'data' => [
                    'sales' => $salesData,
                    'properties' => $propertyData,
                    'customers' => $customerData,
                    'trends' => $trendsData
                ]
            ];
            
            return $this->formatReport($report, $format);
        } catch (Exception $e) {
            $this->logger->error("Error generating financial report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format report based on requested format
     */
    private function formatReport($report, $format)
    {
        switch ($format) {
            case 'json':
                return json_encode($report, JSON_PRETTY_PRINT);
            
            case 'csv':
                return $this->convertToCSV($report);
            
            case 'excel':
                return $this->convertToExcel($report);
            
            case 'pdf':
                return $this->convertToPDF($report);
            
            default:
                return $report;
        }
    }

    /**
     * Convert report to CSV format
     */
    private function convertToCSV($report)
    {
        $csv = '';
        $csv .= $report['title'] . "\n";
        $csv .= "Generated: " . $report['generated_at'] . "\n\n";
        
        if (!empty($report['data'])) {
            // Headers
            $headers = array_keys($report['data'][0]);
            $csv .= implode(',', $headers) . "\n";
            
            // Data rows
            foreach ($report['data'] as $row) {
                $csvRow = [];
                foreach ($headers as $header) {
                    $csvRow[] = '"' . str_replace('"', '""', $row[$header]) . '"';
                }
                $csv .= implode(',', $csvRow) . "\n";
            }
        }
        
        return $csv;
    }

    /**
     * Convert report to Excel format (simplified)
     */
    private function convertToExcel($report)
    {
        // This would typically use a library like PHPExcel
        // For now, return CSV format as placeholder
        return $this->convertToCSV($report);
    }

    /**
     * Convert report to PDF format (simplified)
     */
    private function convertToPDF($report)
    {
        // This would typically use a library like TCPDF or FPDF
        // For now, return formatted text as placeholder
        $pdf = $report['title'] . "\n";
        $pdf .= "Generated: " . $report['generated_at'] . "\n\n";
        
        if (!empty($report['data'])) {
            foreach ($report['data'] as $row) {
                $pdf .= implode(' | ', $row) . "\n";
            }
        }
        
        return $pdf;
    }

    /**
     * Get available report types
     */
    public function getAvailableReports()
    {
        return [
            'sales' => 'Sales Report',
            'property' => 'Property Report',
            'associate' => 'Associate Performance Report',
            'customer' => 'Customer Report',
            'financial' => 'Financial Summary Report'
        ];
    }

    /**
     * Get available formats
     */
    public function getAvailableFormats()
    {
        return [
            'array' => 'Array Format',
            'json' => 'JSON Format',
            'csv' => 'CSV Format',
            'excel' => 'Excel Format',
            'pdf' => 'PDF Format'
        ];
    }

    /**
     * Schedule report generation
     */
    public function scheduleReport($reportType, $parameters, $schedule, $recipients)
    {
        try {
            $sql = "INSERT INTO scheduled_reports 
                    (report_type, parameters, schedule, recipients, created_at) 
                    VALUES (:report_type, :parameters, :schedule, :recipients, NOW())";
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':report_type', $reportType);
            $stmt->bindParam(':parameters', json_encode($parameters));
            $stmt->bindParam(':schedule', $schedule);
            $stmt->bindParam(':recipients', json_encode($recipients));
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->logger->info("Report scheduled successfully: {$reportType}");
                return $this->database->lastInsertId();
            }
            
            return false;
        } catch (Exception $e) {
            $this->logger->error("Error scheduling report: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get scheduled reports
     */
    public function getScheduledReports()
    {
        try {
            $sql = "SELECT * FROM scheduled_reports ORDER BY created_at DESC";
            $stmt = $this->database->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            $this->logger->error("Error getting scheduled reports: " . $e->getMessage());
            return [];
        }
    }
}
