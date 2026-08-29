<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Traits\ServiceTenantTrait;

/**
 * Report Builder Service - Custom Reports & Charts
 * Dynamic report generation with filters and visualizations
 */
class ReportBuilderService
{
    use ServiceTenantTrait;
    private $database;
    private $chartsSupported = ['bar', 'line', 'pie', 'doughnut', 'table'];
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure report tables exist
     */
    private function ensureTablesExist(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS saved_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_name VARCHAR(100) NOT NULL,
            report_type ENUM('sales', 'leads', 'properties', 'commission', 'analytics', 'custom') NOT NULL,
            description TEXT NULL,
            data_source VARCHAR(50) NOT NULL,
            filters JSON NULL,
            columns JSON NULL,
            chart_type VARCHAR(20) NULL,
            schedule_frequency ENUM('none', 'daily', 'weekly', 'monthly') DEFAULT 'none',
            schedule_day INT NULL,
            schedule_time TIME NULL,
            last_run_at TIMESTAMP NULL,
            created_by INT NOT NULL,
            is_public TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            INDEX idx_type (report_type),
            INDEX idx_created_by (created_by),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->database->getConnection()->exec($sql);
    }
    
    /**
     * Generate Sales Report
     */
    public function generateSalesReport(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? date('Y-m-01');
        $dateTo = $filters['date_to'] ?? date('Y-m-d');
        $groupBy = $filters['group_by'] ?? 'daily'; // daily, weekly, monthly
        
        $data = [];
        
        // Sales by date
        $sql = "SELECT 
            DATE(created_at) as date,
            COUNT(*) as bookings,
            SUM(amount) as revenue,
            COUNT(DISTINCT customer_id) as users
            FROM bookings 
            WHERE DATE(created_at) BETWEEN ? AND ?
            AND status = 'confirmed'" . $this->tenantSql() . "
            GROUP BY DATE(created_at)
            ORDER BY date";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        $data['daily_sales'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Sales by property type
        $sql2 = "SELECT 
            p.type as property_type,
            COUNT(b.id) as bookings,
            SUM(b.amount) as revenue
            FROM bookings b
            JOIN properties p ON b.property_id = p.id
            WHERE DATE(b.created_at) BETWEEN ? AND ?
            AND b.status = 'confirmed'" . $this->tenantSql() . "
            GROUP BY p.type";
        
        $stmt2 = $this->database->prepare($sql2);
        $stmt2->execute([$dateFrom, $dateTo]);
        $data['by_property_type'] = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        
        // Sales by location
        $sql3 = "SELECT 
            p.location,
            COUNT(b.id) as bookings,
            SUM(b.amount) as revenue
            FROM bookings b
            JOIN properties p ON b.property_id = p.id
            WHERE DATE(b.created_at) BETWEEN ? AND ?
            AND b.status = 'confirmed'" . $this->tenantSql() . "
            GROUP BY p.location
            ORDER BY revenue DESC
            LIMIT 10";
        
        $stmt3 = $this->database->prepare($sql3);
        $stmt3->execute([$dateFrom, $dateTo]);
        $data['top_locations'] = $stmt3->fetchAll(\PDO::FETCH_ASSOC);
        
        // Summary
        $data['summary'] = [
            'total_bookings' => array_sum(array_column($data['daily_sales'], 'bookings')),
            'total_revenue' => array_sum(array_column($data['daily_sales'], 'revenue')),
            'avg_booking_value' => 0,
            'date_range' => [$dateFrom, $dateTo]
        ];
        
        if ($data['summary']['total_bookings'] > 0) {
            $data['summary']['avg_booking_value'] = 
                $data['summary']['total_revenue'] / $data['summary']['total_bookings'];
        }
        
        return $data;
    }
    
    /**
     * Generate Leads Report
     */
    public function generateLeadsReport(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? date('Y-m-01');
        $dateTo = $filters['date_to'] ?? date('Y-m-d');
        
        $data = [];
        
        // Leads by date
        $sql = "SELECT 
            DATE(created_at) as date,
            COUNT(*) as new_leads,
            SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted
            FROM leads 
            WHERE DATE(created_at) BETWEEN ? AND ?" . $this->tenantSql() . "
            GROUP BY DATE(created_at)
            ORDER BY date";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$dateFrom, $dateTo]);
        $data['daily_leads'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Leads by source
        $sql2 = "SELECT 
            source,
            COUNT(*) as count,
            SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted,
            ROUND((SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as conversion_rate
            FROM leads 
            WHERE DATE(created_at) BETWEEN ? AND ?" . $this->tenantSql() . "
            GROUP BY source
            ORDER BY count DESC";
        
        $stmt2 = $this->database->prepare($sql2);
        $stmt2->execute([$dateFrom, $dateTo]);
        $data['by_source'] = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        
        // Leads by status
        $sql3 = "SELECT 
            status,
            COUNT(*) as count
            FROM leads 
            WHERE DATE(created_at) BETWEEN ? AND ?" . $this->tenantSql() . "
            GROUP BY status";
        
        $stmt3 = $this->database->prepare($sql3);
        $stmt3->execute([$dateFrom, $dateTo]);
        $data['by_status'] = $stmt3->fetchAll(\PDO::FETCH_ASSOC);
        
        // Summary
        $data['summary'] = [
            'total_leads' => array_sum(array_column($data['daily_leads'], 'new_leads')),
            'total_converted' => array_sum(array_column($data['daily_leads'], 'converted')),
            'conversion_rate' => 0,
            'date_range' => [$dateFrom, $dateTo]
        ];
        
        if ($data['summary']['total_leads'] > 0) {
            $data['summary']['conversion_rate'] = 
                round(($data['summary']['total_converted'] / $data['summary']['total_leads']) * 100, 2);
        }
        
        return $data;
    }
    
    /**
     * Generate Commission Report
     */
    public function generateCommissionReport(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? date('Y-m-01');
        $dateTo = $filters['date_to'] ?? date('Y-m-d');
        $associateId = $filters['associate_id'] ?? null;
        
        $data = [];
        
        $where = "DATE(c.created_at) BETWEEN ? AND ?";
        $params = [$dateFrom, $dateTo];
        
        if ($associateId) {
            $where .= " AND c.associate_id = ?";
            $params[] = $associateId;
        }
        $where .= $this->tenantSql();
        
        // Commission summary
        $sql = "SELECT 
            a.name as associate_name,
            a.email as associate_code,
            COUNT(c.id) as total_commissions,
            SUM(c.amount) as total_amount,
            COALESCE(SUM(CASE WHEN c.status = 'paid' THEN c.amount ELSE 0 END), 0) as paid_amount,
            COALESCE(SUM(CASE WHEN c.status != 'paid' THEN c.amount ELSE 0 END), 0) as pending_amount
            FROM mlm_commission_ledger c
            JOIN users a ON c.beneficiary_user_id = a.id
            WHERE {$where}
            GROUP BY c.beneficiary_user_id
            ORDER BY total_amount DESC";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $data['by_associate'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Commission by month
        $sql2 = "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count,
            SUM(amount) as total
            FROM mlm_commission_ledger 
            WHERE DATE(created_at) BETWEEN ? AND ?" . $this->tenantSql() . "
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month";
        
        $stmt2 = $this->database->prepare($sql2);
        $stmt2->execute([$dateFrom, $dateTo]);
        $data['monthly'] = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        
        // Summary
        $data['summary'] = [
            'total_commission' => array_sum(array_column($data['by_associate'], 'total_amount')),
            'total_paid' => array_sum(array_column($data['by_associate'], 'paid_amount')),
            'total_pending' => array_sum(array_column($data['by_associate'], 'pending_amount')),
            'active_associates' => count($data['by_associate']),
            'date_range' => [$dateFrom, $dateTo]
        ];
        
        return $data;
    }
    
    /**
     * Export report to various formats
     */
    public function exportReport(string $reportType, array $filters, string $format = 'csv'): array
    {
        $data = match($reportType) {
            'sales' => $this->generateSalesReport($filters),
            'leads' => $this->generateLeadsReport($filters),
            'commission' => $this->generateCommissionReport($filters),
            default => []
        };
        
        $filename = $reportType . '_report_' . date('Y-m-d_H-i-s') . '.' . $format;
        $filepath = STORAGE_PATH . '/reports/' . $filename;
        
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        switch ($format) {
            case 'csv':
                $this->exportToCsv($data, $filepath);
                break;
            case 'excel':
                $this->exportToExcel($data, $filepath);
                break;
            case 'pdf':
                $this->exportToPdf($data, $filepath);
                break;
            case 'json':
                file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
                break;
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'size' => filesize($filepath),
            'records' => count($data['daily_sales'] ?? $data['by_associate'] ?? [])
        ];
    }
    
    /**
     * Export to CSV
     */
    private function exportToCsv(array $data, string $filepath): void
    {
        $handle = fopen($filepath, 'w');
        
        // Headers from first data set
        if (!empty($data['daily_sales'])) {
            fputcsv($handle, array_keys($data['daily_sales'][0]));
            foreach ($data['daily_sales'] as $row) {
                fputcsv($handle, $row);
            }
        }
        
        fclose($handle);
    }
    
    /**
     * Export to Excel (simplified as HTML table)
     */
    private function exportToExcel(array $data, string $filepath): void
    {
        $html = '<table border="1">';
        
        if (!empty($data['daily_sales'])) {
            // Header
            $html .= '<tr>';
            foreach (array_keys($data['daily_sales'][0]) as $header) {
                $html .= '<th>' . ucfirst(str_replace('_', ' ', $header)) . '</th>';
            }
            $html .= '</tr>';
            
            // Data
            foreach ($data['daily_sales'] as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . $value . '</td>';
                }
                $html .= '</tr>';
            }
        }
        
        $html .= '</table>';
        
        file_put_contents($filepath, $html);
    }
    
    /**
     * Export to PDF
     */
    private function exportToPdf(array $data, string $filepath): void
    {
        // Simplified: save HTML for now
        // In production, use a PDF library like dompdf or TCPDF
        $this->exportToExcel($data, $filepath);
    }
    
    /**
     * Save custom report
     */
    public function saveReport(string $name, string $type, array $config, int $userId): int
    {
        try {
            $insertData = $this->tenantInsertData();
        $extraCols = $insertData ? ', ' . implode(', ', array_keys($insertData)) : '';
        $extraVals = $insertData ? ', ' . implode(', ', array_fill(0, count($insertData), '?')) : '';
        $sql = "INSERT INTO saved_reports 
                    (report_name, report_type, data_source, filters, columns, chart_type, created_by{$extraCols}) 
                    VALUES (?, ?, ?, ?, ?, ?, ?{$extraVals})";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute(array_merge([
            $name,
            $type,
            $config['data_source'] ?? 'database',
            json_encode($config['filters'] ?? []),
            json_encode($config['columns'] ?? []),
            $config['chart_type'] ?? null,
            $userId
        ], array_values($insertData)));
        
        return $this->database->lastInsertId();
    }
    
    /**
     * Get saved reports
     */
    public function getSavedReports(int $userId): array
    {
        try {
            $sql = "SELECT * FROM saved_reports 
                    WHERE (created_by = ? OR is_public = 1) AND is_active = 1 " . $this->tenantSql() . "
                    ORDER BY created_at DESC";
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId]);
        
        $reports = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($reports as &$report) {
            $report['filters'] = json_decode($report['filters'], true);
            $report['columns'] = json_decode($report['columns'], true);
        }
        
        return $reports;
    }
    
    /**
     * Schedule report
     */
    public function scheduleReport(int $reportId, string $frequency, 
                                  ?int $day = null, ?string $time = null): bool
    {
        try {
            $sql = "UPDATE saved_reports 
                    SET schedule_frequency = ?, schedule_day = ?, schedule_time = ? 
                    WHERE id = ?" . $this->tenantSql();
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        
        $stmt = $this->database->prepare($sql);
        return $stmt->execute([$frequency, $day, $time, $reportId]);
    }
}
