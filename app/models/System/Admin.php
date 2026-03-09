<?php

namespace App\Models\System;

use App\Models\Model;
use PDO;

/**
 * Admin Model - System Administration
 * Handles admin-related database operations and analytics
 */
class Admin extends Model
{
    protected static $table = 'users';
    protected static $primaryKey = 'id';

    /**
     * Get admin dashboard statistics
     */
    public function getDashboardStats()
    {
        try {
            $stats = [];
            
            // Total users
            $stmt = $this->db->prepare("SELECT COUNT(*) as total_users FROM users");
            $stmt->execute();
            $stats['total_users'] = $stmt->fetchColumn();
            
            // Total properties
            $stmt = $this->db->prepare("SELECT COUNT(*) as total_properties FROM properties");
            $stmt->execute();
            $stats['total_properties'] = $stmt->fetchColumn();
            
            // Total bookings
            $stmt = $this->db->prepare("SELECT COUNT(*) as total_bookings FROM bookings");
            $stmt->execute();
            $stats['total_bookings'] = $stmt->fetchColumn();
            
            // Total leads
            $stmt = $this->db->prepare("SELECT COUNT(*) as total_leads FROM leads");
            $stmt->execute();
            $stats['total_leads'] = $stmt->fetchColumn();
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Admin dashboard stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 10)
    {
        try {
            $activities = [];
            
            // Recent bookings
            $stmt = $this->db->prepare("
                SELECT b.*, u.name as customer_name, p.title as property_title 
                FROM bookings b 
                JOIN users u ON b.user_id = u.id 
                JOIN properties p ON b.property_id = p.id 
                ORDER BY b.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($bookings as $booking) {
                $activities[] = [
                    'type' => 'booking',
                    'message' => "New booking from {$booking['customer_name']} for {$booking['property_title']}",
                    'timestamp' => $booking['created_at'],
                    'data' => $booking
                ];
            }
            
            // Recent property additions
            $stmt = $this->db->prepare("
                SELECT p.*, u.name as agent_name 
                FROM properties p 
                JOIN users u ON p.agent_id = u.id 
                ORDER BY p.created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($properties as $property) {
                $activities[] = [
                    'type' => 'property',
                    'message' => "New property added: {$property['title']} by {$property['agent_name']}",
                    'timestamp' => $property['created_at'],
                    'data' => $property
                ];
            }
            
            // Sort by timestamp
            usort($activities, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
            
            return array_slice($activities, 0, $limit);
            
        } catch (PDOException $e) {
            error_log("Recent activities error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get property analytics
     */
    public function getPropertyAnalytics()
    {
        try {
            $analytics = [];
            
            // Properties by status
            $stmt = $this->db->prepare("
                SELECT status, COUNT(*) as count 
                FROM properties 
                GROUP BY status
            ");
            $stmt->execute();
            $analytics['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Properties by type
            $stmt = $this->db->prepare("
                SELECT type, COUNT(*) as count 
                FROM properties 
                GROUP BY type
            ");
            $stmt->execute();
            $analytics['by_type'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Properties by price range
            $stmt = $this->db->prepare("
                SELECT 
                    CASE 
                        WHEN price < 1000000 THEN 'Under 10L'
                        WHEN price < 5000000 THEN '10L - 50L'
                        WHEN price < 10000000 THEN '50L - 1Cr'
                        ELSE 'Above 1Cr'
                    END as price_range,
                    COUNT(*) as count
                FROM properties
                GROUP BY price_range
            ");
            $stmt->execute();
            $analytics['by_price_range'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $analytics;
            
        } catch (PDOException $e) {
            error_log("Property analytics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user management data
     */
    public function getUserManagementData()
    {
        try {
            $data = [];
            
            // Users by role
            $stmt = $this->db->prepare("
                SELECT role, COUNT(*) as count 
                FROM users 
                GROUP BY role
            ");
            $stmt->execute();
            $data['by_role'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Recent registrations
            $stmt = $this->db->prepare("
                SELECT id, name, email, role, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $stmt->execute();
            $data['recent_registrations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Active users this month
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as active_users 
                FROM users 
                WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $data['active_this_month'] = $stmt->fetchColumn();
            
            return $data;
            
        } catch (PDOException $e) {
            error_log("User management data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get lead management data
     */
    public function getLeadManagementData()
    {
        try {
            $data = [];
            
            // Leads by status
            $stmt = $this->db->prepare("
                SELECT status, COUNT(*) as count 
                FROM leads 
                GROUP BY status
            ");
            $stmt->execute();
            $data['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total leads
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM leads");
            $stmt->execute();
            $total_leads = $stmt->fetchColumn();
            
            // Converted leads
            $stmt = $this->db->prepare("SELECT COUNT(*) as converted FROM leads WHERE status = 'converted'");
            $stmt->execute();
            $converted_leads = $stmt->fetchColumn();
            
            $data['conversion_rate'] = $total_leads > 0 ? ($converted_leads / $total_leads) * 100 : 0;
            
            return $data;
            
        } catch (PDOException $e) {
            error_log("Lead management data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get booking management data
     */
    public function getBookingManagementData()
    {
        try {
            $data = [];
            
            // Bookings by status
            $stmt = $this->db->prepare("
                SELECT status, COUNT(*) as count 
                FROM bookings 
                GROUP BY status
            ");
            $stmt->execute();
            $data['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Recent bookings
            $stmt = $this->db->prepare("
                SELECT b.*, u.name as customer_name, p.title as property_title 
                FROM bookings b 
                JOIN users u ON b.user_id = u.id 
                JOIN properties p ON b.property_id = p.id 
                ORDER BY b.created_at DESC 
                LIMIT 10
            ");
            $stmt->execute();
            $data['recent_bookings'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Revenue this month
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(amount), 0) as revenue 
                FROM payments 
                WHERE status = 'completed' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            $data['revenue_this_month'] = $stmt->fetchColumn();
            
            return $data;
            
        } catch (PDOException $e) {
            error_log("Booking management data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system health status
     */
    public function getSystemHealthStatus()
    {
        try {
            $health = [];
            
            // Database connection
            $health['database'] = $this->db ? 'healthy' : 'error';
            
            // Disk space
            $free_space = disk_free_space(__DIR__);
            $total_space = disk_total_space(__DIR__);
            $health['disk_space'] = [
                'free' => $this->formatBytes($free_space),
                'total' => $this->formatBytes($total_space),
                'percentage' => round(($free_space / $total_space) * 100, 2)
            ];
            
            // Memory usage
            $health['memory'] = [
                'used' => $this->formatBytes(memory_get_usage(true)),
                'peak' => $this->formatBytes(memory_get_peak_usage(true))
            ];
            
            // Last backup (placeholder)
            $health['last_backup'] = date('Y-m-d H:i:s', strtotime('-1 day'));
            
            $health['overall'] = 'healthy';
            
            return $health;
            
        } catch (Exception $e) {
            error_log("System health status error: " . $e->getMessage());
            return ['overall' => 'error'];
        }
    }

    /**
     * Get comprehensive analytics
     */
    public function getAnalytics()
    {
        try {
            $analytics = [];
            
            // User growth over time
            $stmt = $this->db->prepare("
                SELECT DATE(created_at) as date, COUNT(*) as users 
                FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC
            ");
            $stmt->execute();
            $analytics['user_growth'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Revenue trends
            $stmt = $this->db->prepare("
                SELECT DATE(created_at) as date, SUM(amount) as revenue 
                FROM payments 
                WHERE status = 'completed' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC
            ");
            $stmt->execute();
            $analytics['revenue_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Top performing agents
            $stmt = $this->db->prepare("
                SELECT u.name, u.email, COUNT(p.id) as properties_sold, COALESCE(SUM(p.price), 0) as total_value
                FROM users u
                LEFT JOIN properties p ON u.id = p.agent_id AND p.status = 'sold'
                WHERE u.role = 'agent'
                GROUP BY u.id, u.name, u.email
                ORDER BY properties_sold DESC, total_value DESC
                LIMIT 10
            ");
            $stmt->execute();
            $analytics['top_agents'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Monthly trends
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as new_users,
                    COUNT(CASE WHEN role = 'associate' THEN 1 END) as new_associates,
                    COUNT(properties.id) as new_properties,
                    COALESCE(SUM(CASE WHEN payments.status = 'completed' THEN payments.amount ELSE 0 END), 0) as monthly_revenue
                FROM users
                LEFT JOIN associates ON users.id = associates.user_id
                LEFT JOIN properties ON DATE_FORMAT(properties.created_at, '%Y-%m') = DATE_FORMAT(users.created_at, '%Y-%m')
                LEFT JOIN payments ON DATE_FORMAT(payments.created_at, '%Y-%m') = DATE_FORMAT(users.created_at, '%Y-%m')
                WHERE users.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
                LIMIT 12
            ");
            $stmt->execute();
            $analytics['monthly_trends'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $analytics;
        } catch (PDOException $e) {
            error_log("Analytics error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

/**
 * Legacy Admin class - redirects calls to the modern Model.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Models\Admin as ModernAdmin;

class LegacyAdmin extends ModernAdmin
{
    // This class now inherits from the modern Model
    // and can be used as a drop-in replacement.
}
