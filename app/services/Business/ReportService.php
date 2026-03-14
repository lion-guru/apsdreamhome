<?php

/**
 * Report Service
 * Handles all report generation business logic
 */

namespace App\Services\Business;

use App\Core\Database;
use App\Core\Session\SessionManager;
use App\Core\Logger\Logger;

class ReportService
{
    private $db;
    private $sessionManager;
    private $logger;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->sessionManager = SessionManager::getInstance();
        $this->logger = new Logger();
    }

    /**
     * Generate property sales report
     */
    public function generatePropertySalesReport($filters = [])
    {
        try {
            $sql = "SELECT 
                p.id, p.title, p.type, p.price, p.area, p.location,
                p.status, p.sold_date, p.created_at,
                a.name as associate_name, a.commission_rate,
                u.name as buyer_name, u.email as buyer_email,
                (p.price * a.commission_rate / 100) as commission_amount
                FROM properties p
                LEFT JOIN associates a ON p.associate_id = a.id
                LEFT JOIN users u ON p.buyer_id = u.id
                WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['start_date'])) {
                $sql .= " AND p.sold_date >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $sql .= " AND p.sold_date <= ?";
                $params[] = $filters['end_date'];
            }
            
            if (!empty($filters['associate_id'])) {
                $sql .= " AND p.associate_id = ?";
                $params[] = $filters['associate_id'];
            }
            
            if (!empty($filters['property_type'])) {
                $sql .= " AND p.type = ?";
                $params[] = $filters['property_type'];
            }
            
            if (!empty($filters['location'])) {
                $sql .= " AND p.location LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }
            
            $sql .= " ORDER BY p.sold_date DESC";
            
            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("ReportService::generatePropertySalesReport - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate user activity report
     */
    public function generateUserActivityReport($filters = [])
    {
        try {
            $sql = "SELECT 
                u.id, u.name, u.email, u.phone, u.role, u.status,
                u.registered_date, u.last_login,
                COUNT(pv.id) as properties_viewed,
                COUNT(e.id) as enquiries_sent,
                COUNT(f.id) as favorites_added,
                MAX(al.created_at) as last_activity
                FROM users u
                LEFT JOIN property_views pv ON u.id = pv.user_id
                LEFT JOIN enquiries e ON u.id = e.user_id
                LEFT JOIN favorites f ON u.id = f.user_id
                LEFT JOIN activity_log al ON u.id = al.user_id
                WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['start_date'])) {
                $sql .= " AND u.registered_date >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $sql .= " AND u.registered_date <= ?";
                $params[] = $filters['end_date'];
            }
            
            if (!empty($filters['role'])) {
                $sql .= " AND u.role = ?";
                $params[] = $filters['role'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND u.status = ?";
                $params[] = $filters['status'];
            }
            
            $sql .= " GROUP BY u.id, u.name, u.email, u.phone, u.role, u.status,
                      u.registered_date, u.last_login
                      ORDER BY u.registered_date DESC";
            
            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("ReportService::generateUserActivityReport - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate financial summary report
     */
    public function generateFinancialSummaryReport($filters = [])
    {
        try {
            $sql = "SELECT 
                DATE_FORMAT(t.transaction_date, '%Y-%m') as month,
                DATE_FORMAT(t.transaction_date, '%M %Y') as month_name,
                COUNT(*) as total_transactions,
                SUM(CASE WHEN t.type = 'payment' THEN t.amount ELSE 0 END) as total_payments,
                SUM(CASE WHEN t.type = 'commission' THEN t.amount ELSE 0 END) as total_commissions,
                SUM(CASE WHEN t.type = 'refund' THEN t.amount ELSE 0 END) as total_refunds,
                SUM(t.amount) as total_amount,
                AVG(t.amount) as avg_amount
                FROM transactions t
                WHERE t.status = 'completed'";
            
            $params = [];
            
            if (!empty($filters['start_date'])) {
                $sql .= " AND t.transaction_date >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $sql .= " AND t.transaction_date <= ?";
                $params[] = $filters['end_date'];
            }
            
            if (!empty($filters['transaction_type'])) {
                $sql .= " AND t.type = ?";
                $params[] = $filters['transaction_type'];
            }
            
            $sql .= " GROUP BY DATE_FORMAT(t.transaction_date, '%Y-%m'), DATE_FORMAT(t.transaction_date, '%M %Y')
                      ORDER BY month DESC";
            
            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("ReportService::generateFinancialSummaryReport - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate associate performance report
     */
    public function generateAssociatePerformanceReport($filters = [])
    {
        try {
            $sql = "SELECT 
                a.id, a.name, a.email, a.phone, a.commission_rate,
                a.join_date, a.status, a.rating,
                COUNT(p.id) as total_properties,
                COUNT(CASE WHEN p.status = 'sold' THEN 1 END) as sold_properties,
                COALESCE(SUM(p.price), 0) as total_property_value,
                COALESCE(SUM(CASE WHEN p.status = 'sold' THEN p.price * a.commission_rate / 100 END), 0) as total_commissions,
                COALESCE(AVG(p.price), 0) as avg_property_price,
                MAX(p.sold_date) as last_sale_date,
                COALESCE(SUM(CASE WHEN p.sold_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END), 0) as sales_this_month
                FROM associates a
                LEFT JOIN properties p ON a.id = p.associate_id
                WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['start_date'])) {
                $sql .= " AND a.join_date >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $sql .= " AND a.join_date <= ?";
                $params[] = $filters['end_date'];
            }
            
            if (!empty($filters['status'])) {
                $sql .= " AND a.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['min_rating'])) {
                $sql .= " AND a.rating >= ?";
                $params[] = $filters['min_rating'];
            }
            
            $sql .= " GROUP BY a.id, a.name, a.email, a.phone, a.commission_rate,
                      a.join_date, a.status, a.rating
                      ORDER BY total_commissions DESC";
            
            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("ReportService::generateAssociatePerformanceReport - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate lead conversion report
     */
    public function generateLeadConversionReport($filters = [])
    {
        try {
            $sql = "SELECT 
                DATE_FORMAT(e.created_at, '%Y-%m') as month,
                DATE_FORMAT(e.created_at, '%M %Y') as month_name,
                COUNT(*) as total_enquiries,
                COUNT(CASE WHEN e.status = 'converted' THEN 1 END) as converted_enquiries,
                COUNT(CASE WHEN e.status = 'pending' THEN 1 END) as pending_enquiries,
                COUNT(CASE WHEN e.status = 'closed' THEN 1 END) as closed_enquiries,
                ROUND(COUNT(CASE WHEN e.status = 'converted' THEN 1 END) * 100.0 / COUNT(*), 2) as conversion_rate,
                COALESCE(AVG(CASE WHEN e.status = 'converted' THEN DATEDIFF(e.converted_date, e.created_at) END), 0) as avg_conversion_days
                FROM enquiries e
                WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['start_date'])) {
                $sql .= " AND e.created_at >= ?";
                $params[] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $sql .= " AND e.created_at <= ?";
                $params[] = $filters['end_date'];
            }
            
            if (!empty($filters['source'])) {
                $sql .= " AND e.source = ?";
                $params[] = $filters['source'];
            }
            
            $sql .= " GROUP BY DATE_FORMAT(e.created_at, '%Y-%m'), DATE_FORMAT(e.created_at, '%M %Y')
                      ORDER BY month DESC";
            
            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("ReportService::generateLeadConversionReport - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate property inventory report
     */
    public function generatePropertyInventoryReport($filters = [])
    {
        try {
            $sql = "SELECT 
                p.type,
                COUNT(*) as total_properties,
                COUNT(CASE WHEN p.status = 'active' THEN 1 END) as active_properties,
                COUNT(CASE WHEN p.status = 'sold' THEN 1 END) as sold_properties,
                COUNT(CASE WHEN p.status = 'pending' THEN 1 END) as pending_properties,
                COALESCE(AVG(p.price), 0) as avg_price,
                COALESCE(SUM(p.price), 0) as total_value,
                COALESCE(AVG(p.area), 0) as avg_area,
                COALESCE(SUM(p.area), 0) as total_area
                FROM properties p
                WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['property_type'])) {
                $sql .= " AND p.type = ?";
                $params[] = $filters['property_type'];
            }
            
            if (!empty($filters['location'])) {
                $sql .= " AND p.location LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }
            
            if (!empty($filters['min_price'])) {
                $sql .= " AND p.price >= ?";
                $params[] = $filters['min_price'];
            }
            
            if (!empty($filters['max_price'])) {
                $sql .= " AND p.price <= ?";
                $params[] = $filters['max_price'];
            }
            
            $sql .= " GROUP BY p.type
                      ORDER BY total_properties DESC";
            
            return $this->db->fetchAll($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("ReportService::generatePropertyInventoryReport - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Export report to CSV
     */
    public function exportToCSV($data, $filename)
    {
        try {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

            $output = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fwrite($output, "\xEF\xBB\xBF");

            if (!empty($data)) {
                // Get headers from keys of first row
                $headers = array_keys($data[0]);
                fputcsv($output, $headers);

                // Add data rows
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }

            fclose($output);
            exit;

        } catch (Exception $e) {
            $this->logger->error("ReportService::exportToCSV - Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStatistics()
    {
        try {
            $stats = [];

            // Property statistics
            $propertyStats = $this->db->fetch("
                SELECT 
                    COUNT(*) as total_properties,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_properties,
                    COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold_properties,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_properties,
                    COALESCE(AVG(price), 0) as avg_property_price,
                    COALESCE(SUM(price), 0) as total_property_value
                FROM properties
            ");

            // User statistics
            $userStats = $this->db->fetch("
                SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
                    COUNT(CASE WHEN role = 'client' THEN 1 END) as client_users,
                    COUNT(CASE WHEN role = 'associate' THEN 1 END) as associate_users,
                    COUNT(CASE WHEN registered_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_month
                FROM users
            ");

            // Associate statistics
            $associateStats = $this->db->fetch("
                SELECT 
                    COUNT(*) as total_associates,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_associates,
                    AVG(rating) as avg_rating,
                    SUM(total_commissions) as total_commissions,
                    SUM(properties_sold) as total_properties_sold
                FROM associates
            ");

            // Financial statistics
            $financialStats = $this->db->fetch("
                SELECT 
                    COUNT(*) as total_transactions,
                    SUM(CASE WHEN type = 'payment' AND status = 'completed' THEN amount ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN type = 'commission' AND status = 'completed' THEN amount ELSE 0 END) as total_commissions_paid,
                    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN amount ELSE 0 END) as revenue_this_month
                FROM transactions
                WHERE status = 'completed'
            ");

            // Enquiry statistics
            $enquiryStats = $this->db->fetch("
                SELECT 
                    COUNT(*) as total_enquiries,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_enquiries,
                    COUNT(CASE WHEN status = 'converted' THEN 1 END) as converted_enquiries,
                    COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as enquiries_this_month
                FROM enquiries
            ");

            return [
                'properties' => $propertyStats,
                'users' => $userStats,
                'associates' => $associateStats,
                'financial' => $financialStats,
                'enquiries' => $enquiryStats
            ];

        } catch (Exception $e) {
            $this->logger->error("ReportService::getDashboardStatistics - Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Log activity
     */
    private function logActivity($action, $details = '')
    {
        try {
            $sql = "INSERT INTO activity_log (user_id, action, details, ip_address, user_agent, created_at) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
            
            $params = [
                $this->sessionManager->get('admin_id'),
                $action,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ];
            
            $this->db->execute($sql, $params);

        } catch (Exception $e) {
            $this->logger->error("ReportService::logActivity - Error: " . $e->getMessage());
        }
    }
}
