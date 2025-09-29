<?php

namespace App\Services;

class ReportService {
    private $db;

    public function __construct() {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Generate sales report
     * 
     * @param array $filters
     * @return array
     */
    public function generateSalesReport(array $filters = []) {
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_sales,
                    SUM(amount) as total_amount,
                    AVG(amount) as average_sale
                  FROM sales 
                  WHERE 1=1";
        
        $params = [];
        
        // Apply date filters
        if (!empty($filters['start_date'])) {
            $query .= " AND DATE(created_at) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND DATE(created_at) <= ?";
            $params[] = $filters['end_date'];
        }
        
        // Group by date
        $query .= " GROUP BY DATE(created_at) ORDER BY date";
        
        $stmt = $this->db->query($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate property report
     * 
     * @param array $filters
     * @return array
     */
    public function generatePropertyReport(array $filters = []) {
        $query = "SELECT 
                    p.type,
                    COUNT(*) as total_properties,
                    AVG(p.price) as avg_price,
                    SUM(CASE WHEN s.id IS NOT NULL THEN 1 ELSE 0 END) as sold_count
                  FROM properties p
                  LEFT JOIN sales s ON p.id = s.property_id
                  WHERE p.status = 'active'";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['location'])) {
            $query .= " AND p.location LIKE ?";
            $params[] = "%{$filters['location']}%";
        }
        
        if (!empty($filters['min_price'])) {
            $query .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $query .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        // Group by property type
        $query .= " GROUP BY p.type";
        
        $stmt = $this->db->query($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate user activity report
     * 
     * @param array $filters
     * @return array
     */
    public function generateUserActivityReport(array $filters = []) {
        $query = "SELECT 
                    u.id,
                    u.username,
                    u.email,
                    u.role,
                    COUNT(DISTINCT v.id) as property_views,
                    COUNT(DISTINCT c.id) as contacts_made,
                    MAX(v.visited_at) as last_activity
                  FROM users u
                  LEFT JOIN property_views v ON u.id = v.user_id
                  LEFT JOIN contacts c ON u.id = c.user_id
                  WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['start_date'])) {
            $query .= " AND (v.visited_at >= ? OR c.created_at >= ?)";
            $params[] = $filters['start_date'];
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND (v.visited_at <= ? OR c.created_at <= ?)";
            $params[] = $filters['end_date'];
            $params[] = $filters['end_date'];
        }
        
        // Group by user
        $query .= " GROUP BY u.id, u.username, u.email, u.role";
        
        // Add sorting
        $sort = $filters['sort'] ?? 'last_activity';
        $order = isset($filters['order']) && strtoupper($filters['order']) === 'ASC' ? 'ASC' : 'DESC';
        $query .= " ORDER BY $sort $order";
        
        // Add pagination
        $page = max(1, $filters['page'] ?? 1);
        $perPage = min(50, max(1, $filters['per_page'] ?? 20));
        $offset = ($page - 1) * $perPage;
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->query($query, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate report
     */
    public function generateReport($reportType, $dateRange) {
        try {
            switch ($reportType) {
                case 'overview':
                    return $this->getOverviewReport($dateRange);
                case 'sales':
                    return $this->generateSalesReport($dateRange);
                case 'leads':
                    return $this->generateLeadsReport($dateRange);
                case 'properties':
                    return $this->generatePropertyReport($dateRange);
                default:
                    return [];
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get available reports
     */
    public function getAvailableReports() {
        return [
            'overview' => 'Overview Dashboard',
            'sales' => 'Sales Report',
            'leads' => 'Lead Analytics',
            'properties' => 'Property Performance'
        ];
    }

    /**
     * Get overview report
     */
    private function getOverviewReport($dateRange) {
        try {
            $data = [];

            // Total revenue
            $stmt = $this->db->query("
                SELECT COALESCE(SUM(amount), 0) as total
                FROM payments
                WHERE status = 'completed'
                AND created_at BETWEEN ? AND ?
            ", [$dateRange['start'], $dateRange['end']]);
            $data['total_revenue'] = $stmt->fetch()['total'];

            // Total properties
            $stmt = $this->db->query("
                SELECT COUNT(*) as total
                FROM properties
                WHERE created_at BETWEEN ? AND ?
            ", [$dateRange['start'], $dateRange['end']]);
            $data['total_properties'] = $stmt->fetch()['total'];

            // Total leads
            $stmt = $this->db->query("
                SELECT COUNT(*) as total
                FROM leads
                WHERE created_at BETWEEN ? AND ?
            ", [$dateRange['start'], $dateRange['end']]);
            $data['total_leads'] = $stmt->fetch()['total'];

            // Total users
            $stmt = $this->db->query("
                SELECT COUNT(*) as total
                FROM users
                WHERE created_at BETWEEN ? AND ?
            ", [$dateRange['start'], $dateRange['end']]);
            $data['total_users'] = $stmt->fetch()['total'];

            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate leads report
     */
    public function generateLeadsReport($dateRange = []) {
        try {
            $query = "SELECT
                        DATE(created_at) as date,
                        COUNT(*) as total_leads,
                        source,
                        status,
                        COUNT(CASE WHEN status = 'new' THEN 1 END) as new_leads,
                        COUNT(CASE WHEN status = 'contacted' THEN 1 END) as contacted_leads,
                        COUNT(CASE WHEN status = 'qualified' THEN 1 END) as qualified_leads,
                        COUNT(CASE WHEN status = 'converted' THEN 1 END) as converted_leads
                      FROM leads
                      WHERE 1=1";

            $params = [];

            if (!empty($dateRange['start'])) {
                $query .= " AND DATE(created_at) >= ?";
                $params[] = $dateRange['start'];
            }

            if (!empty($dateRange['end'])) {
                $query .= " AND DATE(created_at) <= ?";
                $params[] = $dateRange['end'];
            }

            $query .= " GROUP BY DATE(created_at), source, status ORDER BY date DESC";

            $stmt = $this->db->query($query, $params);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }
    }
}
