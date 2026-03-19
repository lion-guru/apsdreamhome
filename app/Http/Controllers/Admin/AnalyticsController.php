<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\CoreFunctionsServiceCustom;
use App\Services\LoggingService;
use App\Core\Database;
use Exception;

/**
 * Analytics Controller - Custom MVC Implementation
 * Handles commission analytics and business intelligence
 */
class AnalyticsController extends AdminController
{
    private $loggingService;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Commission Analytics Dashboard
     */
    public function index()
    {
        try {
            $data = [
                'page_title' => 'Commission Analytics - APS Dream Home',
                'active_page' => 'analytics',
                'commission_stats' => $this->getCommissionStats(),
                'performance_data' => $this->getPerformanceData(),
                'trends' => $this->getCommissionTrends()
            ];

            return $this->render('admin/analytics/index', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Analytics Index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load analytics');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Associate Performance Analytics
     */
    public function associatePerformance()
    {
        try {
            $data = [
                'page_title' => 'Associate Performance - APS Dream Home',
                'active_page' => 'associate_performance',
                'top_performers' => $this->getTopPerformers(),
                'performance_metrics' => $this->getPerformanceMetrics(),
                'rank_distribution' => $this->getRankDistribution()
            ];

            return $this->render('admin/analytics/associate_performance', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Associate Performance error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load associate performance data');
            return $this->redirect('admin/analytics');
        }
    }

    /**
     * Sales Analytics
     */
    public function sales()
    {
        try {
            $data = [
                'page_title' => 'Sales Analytics - APS Dream Home',
                'active_page' => 'sales_analytics',
                'sales_data' => $this->getSalesData(),
                'revenue_data' => $this->getRevenueData(),
                'conversion_rates' => $this->getConversionRates()
            ];

            return $this->render('admin/analytics/sales', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Sales Analytics error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load sales analytics');
            return $this->redirect('admin/analytics');
        }
    }

    /**
     * Property Analytics
     */
    public function property()
    {
        try {
            $data = [
                'page_title' => 'Property Analytics - APS Dream Home',
                'active_page' => 'property_analytics',
                'property_stats' => $this->getPropertyStats(),
                'popular_properties' => $this->getPopularProperties(),
                'location_analytics' => $this->getLocationAnalytics()
            ];

            return $this->render('admin/analytics/property', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Property Analytics error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load property analytics');
            return $this->redirect('admin/analytics');
        }
    }

    /**
     * Financial Analytics
     */
    public function financial()
    {
        try {
            $data = [
                'page_title' => 'Financial Analytics - APS Dream Home',
                'active_page' => 'financial_analytics',
                'financial_summary' => $this->getFinancialSummary(),
                'payment_analytics' => $this->getPaymentAnalytics(),
                'payout_analytics' => $this->getPayoutAnalytics()
            ];

            return $this->render('admin/analytics/financial', $data);
        } catch (Exception $e) {
            $this->loggingService->error("Financial Analytics error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load financial analytics');
            return $this->redirect('admin/analytics');
        }
    }

    /**
     * Export Analytics Data
     */
    public function export()
    {
        try {
            $type = $_GET['type'] ?? 'commission';
            $format = $_GET['format'] ?? 'csv';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');

            $data = $this->getExportData($type, $startDate, $endDate);

            if ($format === 'csv') {
                return $this->exportCSV($data, $type, $startDate, $endDate);
            } elseif ($format === 'json') {
                return $this->exportJSON($data, $type, $startDate, $endDate);
            }

            $this->setFlash('error', 'Invalid export format');
            return $this->redirect('admin/analytics');
        } catch (Exception $e) {
            $this->loggingService->error("Export Analytics error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to export data');
            return $this->redirect('admin/analytics');
        }
    }

    /**
     * Get commission statistics
     */
    private function getCommissionStats(): array
    {
        try {
            $stats = [];

            // Total commissions paid
            $sql = "SELECT COALESCE(SUM(amount), 0) as total_paid,
                           COUNT(*) as total_transactions
                    FROM mlm_commission_ledger
                    WHERE status = 'paid'";
            $result = $this->db->fetchOne($sql);
            $stats['total_paid'] = (float)($result['total_paid'] ?? 0);
            $stats['total_transactions'] = (int)($result['total_transactions'] ?? 0);

            // Pending commissions
            $sql = "SELECT COALESCE(SUM(amount), 0) as pending_amount,
                           COUNT(*) as pending_count
                    FROM mlm_commission_ledger
                    WHERE status = 'pending'";
            $result = $this->db->fetchOne($sql);
            $stats['pending_amount'] = (float)($result['pending_amount'] ?? 0);
            $stats['pending_count'] = (int)($result['pending_count'] ?? 0);

            // This month's commissions
            $sql = "SELECT COALESCE(SUM(amount), 0) as this_month
                    FROM mlm_commission_ledger
                    WHERE status = 'paid' AND MONTH(created_at) = MONTH(CURRENT_DATE)
                    AND YEAR(created_at) = YEAR(CURRENT_DATE)";
            $result = $this->db->fetchOne($sql);
            $stats['this_month'] = (float)($result['this_month'] ?? 0);

            return $stats;
        } catch (Exception $e) {
            $this->loggingService->error("Get Commission Stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get performance data
     */
    private function getPerformanceData(): array
    {
        try {
            $sql = "SELECT u.name, u.email,
                           COALESCE(SUM(mcl.amount), 0) as total_commission,
                           COUNT(mcl.id) as commission_count,
                           AVG(mcl.amount) as avg_commission
                    FROM users u
                    LEFT JOIN mlm_commission_ledger mcl ON u.id = mcl.associate_id AND mcl.status = 'paid'
                    WHERE u.role = 'associate'
                    GROUP BY u.id, u.name, u.email
                    ORDER BY total_commission DESC
                    LIMIT 10";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Performance Data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get commission trends
     */
    private function getCommissionTrends(): array
    {
        try {
            $sql = "SELECT DATE(created_at) as date, SUM(amount) as daily_total
                    FROM mlm_commission_ledger
                    WHERE status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date DESC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Commission Trends error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get top performers
     */
    private function getTopPerformers(): array
    {
        try {
            $sql = "SELECT u.name, u.email, u.mlm_rank,
                           COALESCE(SUM(mcl.amount), 0) as total_commission,
                           COUNT(DISTINCT b.id) as total_bookings
                    FROM users u
                    LEFT JOIN mlm_commission_ledger mcl ON u.id = mcl.associate_id AND mcl.status = 'paid'
                    LEFT JOIN bookings b ON u.id = b.associate_id
                    WHERE u.role = 'associate' AND u.status = 'active'
                    GROUP BY u.id, u.name, u.email, u.mlm_rank
                    ORDER BY total_commission DESC
                    LIMIT 20";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Top Performers error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        try {
            $metrics = [];

            // Average commission per associate
            $sql = "SELECT AVG(commission_avg) as avg_commission
                    FROM (
                        SELECT AVG(mcl.amount) as commission_avg
                        FROM users u
                        LEFT JOIN mlm_commission_ledger mcl ON u.id = mcl.associate_id AND mcl.status = 'paid'
                        WHERE u.role = 'associate'
                        GROUP BY u.id
                    ) as avg_table";
            $result = $this->db->fetchOne($sql);
            $metrics['avg_commission_per_associate'] = round((float)($result['avg_commission'] ?? 0), 2);

            // Active associates
            $sql = "SELECT COUNT(*) as active_associates
                    FROM users
                    WHERE role = 'associate' AND status = 'active'";
            $result = $this->db->fetchOne($sql);
            $metrics['active_associates'] = (int)($result['active_associates'] ?? 0);

            // Commission growth rate
            $sql = "SELECT 
                        (SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger 
                         WHERE status = 'paid' AND MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)) as current_month,
                        (SELECT COALESCE(SUM(amount), 0) FROM mlm_commission_ledger 
                         WHERE status = 'paid' AND MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)) 
                         AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))) as last_month";
            $result = $this->db->fetchOne($sql);
            $current = (float)($result['current_month'] ?? 0);
            $last = (float)($result['last_month'] ?? 0);
            $metrics['growth_rate'] = $last > 0 ? round((($current - $last) / $last) * 100, 2) : 0;

            return $metrics;
        } catch (Exception $e) {
            $this->loggingService->error("Get Performance Metrics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get rank distribution
     */
    private function getRankDistribution(): array
    {
        try {
            $sql = "SELECT mlm_rank, COUNT(*) as count
                    FROM users
                    WHERE role = 'associate' AND status = 'active'
                    GROUP BY mlm_rank
                    ORDER BY count DESC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Rank Distribution error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get sales data
     */
    private function getSalesData(): array
    {
        try {
            $sql = "SELECT DATE(created_at) as date, COUNT(*) as sales_count, COALESCE(SUM(total_amount), 0) as total_revenue
                    FROM bookings
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date DESC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Sales Data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get revenue data
     */
    private function getRevenueData(): array
    {
        try {
            $data = [];

            // Revenue by property type
            $sql = "SELECT p.property_type, COALESCE(SUM(b.total_amount), 0) as revenue
                    FROM bookings b
                    JOIN properties p ON b.property_id = p.id
                    WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY p.property_type
                    ORDER BY revenue DESC";
            $data['by_property_type'] = $this->db->fetchAll($sql) ?: [];

            // Revenue by location
            $sql = "SELECT p.location, COALESCE(SUM(b.total_amount), 0) as revenue
                    FROM bookings b
                    JOIN properties p ON b.property_id = p.id
                    WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY p.location
                    ORDER BY revenue DESC
                    LIMIT 10";
            $data['by_location'] = $this->db->fetchAll($sql) ?: [];

            return $data;
        } catch (Exception $e) {
            $this->loggingService->error("Get Revenue Data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get conversion rates
     */
    private function getConversionRates(): array
    {
        try {
            $sql = "SELECT 
                        (SELECT COUNT(*) FROM leads WHERE status = 'converted' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as converted_leads,
                        (SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as total_leads";
            $result = $this->db->fetchOne($sql);
            
            $converted = (int)($result['converted_leads'] ?? 0);
            $total = (int)($result['total_leads'] ?? 0);
            
            return [
                'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 2) : 0,
                'converted_leads' => $converted,
                'total_leads' => $total
            ];
        } catch (Exception $e) {
            $this->loggingService->error("Get Conversion Rates error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property statistics
     */
    private function getPropertyStats(): array
    {
        try {
            $stats = [];

            // Total properties
            $sql = "SELECT COUNT(*) as total, 
                           SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                           SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                           SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked
                    FROM properties";
            $result = $this->db->fetchOne($sql);
            $stats['total'] = (int)($result['total'] ?? 0);
            $stats['available'] = (int)($result['available'] ?? 0);
            $stats['sold'] = (int)($result['sold'] ?? 0);
            $stats['booked'] = (int)($result['booked'] ?? 0);

            // Average property price
            $sql = "SELECT AVG(price) as avg_price FROM properties WHERE status IN ('available', 'booked')";
            $result = $this->db->fetchOne($sql);
            $stats['avg_price'] = round((float)($result['avg_price'] ?? 0), 2);

            return $stats;
        } catch (Exception $e) {
            $this->loggingService->error("Get Property Stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get popular properties
     */
    private function getPopularProperties(): array
    {
        try {
            $sql = "SELECT p.*, COUNT(b.id) as booking_count,
                           COALESCE(SUM(b.total_amount), 0) as total_revenue
                    FROM properties p
                    LEFT JOIN bookings b ON p.id = b.property_id
                    WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY p.id
                    ORDER BY booking_count DESC, total_revenue DESC
                    LIMIT 10";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Popular Properties error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get location analytics
     */
    private function getLocationAnalytics(): array
    {
        try {
            $sql = "SELECT p.location, COUNT(b.id) as booking_count,
                           COALESCE(SUM(b.total_amount), 0) as total_revenue,
                           AVG(p.price) as avg_price
                    FROM properties p
                    LEFT JOIN bookings b ON p.id = b.property_id
                    WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY p.location
                    ORDER BY total_revenue DESC
                    LIMIT 15";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Location Analytics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get financial summary
     */
    private function getFinancialSummary(): array
    {
        try {
            $summary = [];

            // Total revenue
            $sql = "SELECT COALESCE(SUM(total_amount), 0) as total_revenue
                    FROM bookings
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $result = $this->db->fetchOne($sql);
            $summary['total_revenue'] = (float)($result['total_revenue'] ?? 0);

            // Total commissions
            $sql = "SELECT COALESCE(SUM(amount), 0) as total_commissions
                    FROM mlm_commission_ledger
                    WHERE status = 'paid' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $result = $this->db->fetchOne($sql);
            $summary['total_commissions'] = (float)($result['total_commissions'] ?? 0);

            // Net profit
            $summary['net_profit'] = $summary['total_revenue'] - $summary['total_commissions'];

            // Profit margin
            $summary['profit_margin'] = $summary['total_revenue'] > 0 ? 
                round(($summary['net_profit'] / $summary['total_revenue']) * 100, 2) : 0;

            return $summary;
        } catch (Exception $e) {
            $this->loggingService->error("Get Financial Summary error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payment analytics
     */
    private function getPaymentAnalytics(): array
    {
        try {
            $sql = "SELECT payment_method, COUNT(*) as transaction_count,
                           COALESCE(SUM(amount), 0) as total_amount
                    FROM booking_payments
                    WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY payment_method
                    ORDER BY total_amount DESC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Payment Analytics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get payout analytics
     */
    private function getPayoutAnalytics(): array
    {
        try {
            $sql = "SELECT status, COUNT(*) as count,
                           COALESCE(SUM(amount), 0) as total_amount
                    FROM mlm_commission_ledger
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY status
                    ORDER BY total_amount DESC";
            return $this->db->fetchAll($sql) ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Payout Analytics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get export data
     */
    private function getExportData(string $type, string $startDate, string $endDate): array
    {
        try {
            switch ($type) {
                case 'commission':
                    $sql = "SELECT mcl.*, u.name as associate_name, u.email as associate_email
                            FROM mlm_commission_ledger mcl
                            JOIN users u ON mcl.associate_id = u.id
                            WHERE mcl.created_at BETWEEN ? AND ?
                            ORDER BY mcl.created_at DESC";
                    break;
                case 'sales':
                    $sql = "SELECT b.*, p.title as property_title, c.name as customer_name
                            FROM bookings b
                            JOIN properties p ON b.property_id = p.id
                            JOIN users c ON b.customer_id = c.id
                            WHERE b.created_at BETWEEN ? AND ?
                            ORDER BY b.created_at DESC";
                    break;
                case 'associates':
                    $sql = "SELECT u.*, COALESCE(SUM(mcl.amount), 0) as total_commission
                            FROM users u
                            LEFT JOIN mlm_commission_ledger mcl ON u.id = mcl.associate_id AND mcl.status = 'paid'
                            WHERE u.role = 'associate'
                            GROUP BY u.id
                            ORDER BY total_commission DESC";
                    break;
                default:
                    return [];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$startDate, $endDate]);
            return $stmt->fetchAll() ?: [];
        } catch (Exception $e) {
            $this->loggingService->error("Get Export Data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export data as CSV
     */
    private function exportCSV(array $data, string $type, string $startDate, string $endDate): void
    {
        $filename = "{$type}_analytics_{$startDate}_to_{$endDate}.csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Header row
            fputcsv($output, array_keys($data[0]));
            
            // Data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export data as JSON
     */
    private function exportJSON(array $data, string $type, string $startDate, string $endDate): void
    {
        $filename = "{$type}_analytics_{$startDate}_to_{$endDate}.json";
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo json_encode([
            'type' => $type,
            'period' => ['start' => $startDate, 'end' => $endDate],
            'data' => $data,
            'exported_at' => date('Y-m-d H:i:s')
        ]);
        
        exit;
    }
}