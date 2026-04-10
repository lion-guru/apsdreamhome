<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * DashboardAnalyticsService
 * 
 * Provides common methods to fetch dashboard analytics data
 * Eliminates repetitive code for dashboard statistics across multiple controllers
 */
class DashboardAnalyticsService
{
    /**
     * Get booking statistics
     * 
     * @param array $filters Optional filters (date_from, date_to, status, etc.)
     * @return array Booking statistics
     */
    public static function getBookingStats($filters = [])
    {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $where = [];
            $params = [];
            
            // Apply date filters
            if (!empty($filters['date_from'])) {
                $where[] = "booking_date >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = "booking_date <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total bookings
            $sql = "SELECT COUNT(*) as count FROM bookings {$whereClause}";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $total = $stmt->fetch()['count'];
            
            // Get bookings by status
            $sql = "SELECT status, COUNT(*) as count FROM bookings {$whereClause} GROUP BY status";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $byStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get total revenue
            $sql = "SELECT SUM(total_amount) as revenue FROM bookings {$whereClause}";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $revenue = $stmt->fetch()['revenue'] ?? 0;
            
            return [
                'total' => $total,
                'by_status' => $byStatus,
                'confirmed' => $byStatus['confirmed'] ?? 0,
                'pending' => $byStatus['pending'] ?? 0,
                'cancelled' => $byStatus['cancelled'] ?? 0,
                'completed' => $byStatus['completed'] ?? 0,
                'total_revenue' => $revenue
            ];
        } catch (\Exception $e) {
            error_log('Error in DashboardAnalyticsService::getBookingStats: ' . $e->getMessage());
            return [
                'total' => 0,
                'by_status' => [],
                'confirmed' => 0,
                'pending' => 0,
                'cancelled' => 0,
                'completed' => 0,
                'total_revenue' => 0
            ];
        }
    }
    
    /**
     * Get property statistics
     * 
     * @param array $filters Optional filters (date_from, date_to, status, type, etc.)
     * @return array Property statistics
     */
    public static function getPropertyStats($filters = [])
    {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $where = [];
            $params = [];
            
            // Apply date filters
            if (!empty($filters['date_from'])) {
                $where[] = "created_at >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = "created_at <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            // Apply status filter
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }
            
            // Apply type filter
            if (!empty($filters['type']) && $filters['type'] !== 'all') {
                $where[] = "type = :type";
                $params['type'] = $filters['type'];
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total properties
            $sql = "SELECT COUNT(*) as count FROM properties {$whereClause}";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $total = $stmt->fetch()['count'];
            
            // Get properties by status
            $sql = "SELECT status, COUNT(*) as count FROM properties {$whereClause} GROUP BY status";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $byStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get properties by type
            $sql = "SELECT type, COUNT(*) as count FROM properties {$whereClause} GROUP BY type";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $byType = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get featured count
            $featuredWhere = $where;
            $featuredWhere[] = "featured = 1";
            $featuredWhereClause = 'WHERE ' . implode(' AND ', $featuredWhere);
            $sql = "SELECT COUNT(*) as count FROM properties {$featuredWhereClause}";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $featured = $stmt->fetch()['count'];
            
            return [
                'total' => $total,
                'by_status' => $byStatus,
                'active' => $byStatus['active'] ?? 0,
                'sold' => $byStatus['sold'] ?? 0,
                'pending' => $byStatus['pending'] ?? 0,
                'by_type' => $byType,
                'featured' => $featured
            ];
        } catch (\Exception $e) {
            error_log('Error in DashboardAnalyticsService::getPropertyStats: ' . $e->getMessage());
            return [
                'total' => 0,
                'by_status' => [],
                'active' => 0,
                'sold' => 0,
                'pending' => 0,
                'by_type' => [],
                'featured' => 0
            ];
        }
    }
    
    /**
     * Get lead statistics
     * 
     * @param array $filters Optional filters (date_from, date_to, status, source, etc.)
     * @return array Lead statistics
     */
    public static function getLeadStats($filters = [])
    {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $where = [];
            $params = [];
            
            // Apply date filters
            if (!empty($filters['date_from'])) {
                $where[] = "created_at >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = "created_at <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            // Apply status filter
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }
            
            // Apply source filter
            if (!empty($filters['source']) && $filters['source'] !== 'all') {
                $where[] = "source = :source";
                $params['source'] = $filters['source'];
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total leads
            $sql = "SELECT COUNT(*) as count FROM leads {$whereClause}";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $total = $stmt->fetch()['count'];
            
            // Get leads by status
            $sql = "SELECT status, COUNT(*) as count FROM leads {$whereClause} GROUP BY status";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $byStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get leads by source
            $sql = "SELECT source, COUNT(*) as count FROM leads {$whereClause} GROUP BY source";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $bySource = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get today's leads
            $todayWhere = $where;
            $todayWhere[] = "DATE(created_at) = CURDATE()";
            $todayWhereClause = 'WHERE ' . implode(' AND ', $todayWhere);
            $sql = "SELECT COUNT(*) as count FROM leads {$todayWhereClause}";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $today = $stmt->fetch()['count'];
            
            return [
                'total' => $total,
                'by_status' => $byStatus,
                'new' => $byStatus['new'] ?? 0,
                'contacted' => $byStatus['contacted'] ?? 0,
                'qualified' => $byStatus['qualified'] ?? 0,
                'converted' => $byStatus['converted'] ?? 0,
                'by_source' => $bySource,
                'today' => $today
            ];
        } catch (\Exception $e) {
            error_log('Error in DashboardAnalyticsService::getLeadStats: ' . $e->getMessage());
            return [
                'total' => 0,
                'by_status' => [],
                'new' => 0,
                'contacted' => 0,
                'qualified' => 0,
                'converted' => 0,
                'by_source' => [],
                'today' => 0
            ];
        }
    }
    
    /**
     * Get revenue statistics
     * 
     * @param array $filters Optional filters (date_from, date_to, etc.)
     * @return array Revenue statistics
     */
    public static function getRevenueStats($filters = [])
    {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $where = [];
            $params = [];
            
            // Apply date filters
            if (!empty($filters['date_from'])) {
                $where[] = "created_at >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = "created_at <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total revenue from bookings
            $sql = "SELECT SUM(total_amount) as revenue FROM bookings {$whereClause} WHERE payment_status = 'paid'";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $totalRevenue = $stmt->fetch()['revenue'] ?? 0;
            
            // Get pending revenue
            $sql = "SELECT SUM(total_amount) as revenue FROM bookings {$whereClause} WHERE payment_status IN ('pending', 'partial')";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $pendingRevenue = $stmt->fetch()['revenue'] ?? 0;
            
            // Get revenue by month (for chart)
            $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid,
                        SUM(CASE WHEN payment_status IN ('pending', 'partial') THEN total_amount ELSE 0 END) as pending
                    FROM bookings 
                    {$whereClause}
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month DESC
                    LIMIT 12";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $byMonth = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total' => $totalRevenue,
                'pending' => $pendingRevenue,
                'by_month' => $byMonth
            ];
        } catch (\Exception $e) {
            error_log('Error in DashboardAnalyticsService::getRevenueStats: ' . $e->getMessage());
            return [
                'total' => 0,
                'pending' => 0,
                'by_month' => []
            ];
        }
    }
    
    /**
     * Get team performance statistics
     * 
     * @param array $filters Optional filters (date_from, date_to, role, etc.)
     * @return array Team performance statistics
     */
    public static function getTeamPerformanceStats($filters = [])
    {
        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();
            
            $where = [];
            $params = [];
            
            // Apply date filters
            if (!empty($filters['date_from'])) {
                $where[] = "created_at >= :date_from";
                $params['date_from'] = $filters['date_from'];
            }
            if (!empty($filters['date_to'])) {
                $where[] = "created_at <= :date_to";
                $params['date_to'] = $filters['date_to'];
            }
            
            // Apply role filter
            if (!empty($filters['role']) && $filters['role'] !== 'all') {
                $where[] = "role = :role";
                $params['role'] = $filters['role'];
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get team members count
            $sql = "SELECT COUNT(*) as count FROM users {$whereClause}";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $total = $stmt->fetch()['count'];
            
            // Get team members by role
            $sql = "SELECT role, COUNT(*) as count FROM users {$whereClause} GROUP BY role";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $byRole = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get top performers (by bookings/revenue)
            $sql = "SELECT 
                        u.id,
                        u.name,
                        u.role,
                        COUNT(b.id) as booking_count,
                        SUM(b.total_amount) as total_revenue
                    FROM users u
                    LEFT JOIN bookings b ON u.id = b.associate_id OR u.id = b.customer_id
                    {$whereClause}
                    GROUP BY u.id
                    ORDER BY total_revenue DESC
                    LIMIT 10";
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            $topPerformers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total' => $total,
                'by_role' => $byRole,
                'admin' => $byRole['admin'] ?? 0,
                'associate' => $byRole['associate'] ?? 0,
                'employee' => $byRole['employee'] ?? 0,
                'customer' => $byRole['customer'] ?? 0,
                'top_performers' => $topPerformers
            ];
        } catch (\Exception $e) {
            error_log('Error in DashboardAnalyticsService::getTeamPerformanceStats: ' . $e->getMessage());
            return [
                'total' => 0,
                'by_role' => [],
                'admin' => 0,
                'associate' => 0,
                'employee' => 0,
                'customer' => 0,
                'top_performers' => []
            ];
        }
    }
    
    /**
     * Get all dashboard statistics in one call
     * 
     * @param array $filters Optional filters for all stats
     * @return array All dashboard statistics
     */
    public static function getAllDashboardStats($filters = [])
    {
        return [
            'bookings' => self::getBookingStats($filters),
            'properties' => self::getPropertyStats($filters),
            'leads' => self::getLeadStats($filters),
            'revenue' => self::getRevenueStats($filters),
            'team_performance' => self::getTeamPerformanceStats($filters)
        ];
    }
}
