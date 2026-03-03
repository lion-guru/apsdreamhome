<?php
/**
 * Admin Controller
 * 
 * Handles all admin-related functionality including dashboard,
 * user management, property management, and system settings.
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database\Database;
use App\Core\Security;
use Exception;
use PDO;

class AdminController extends Controller {
    private $security;
    
    public function __construct() {
        parent::__construct();
        $this->security = new Security();
        
        // Check if user is admin
        if (!$this->isAdmin()) {
            $this->redirect('/login');
            exit;
        }
    }
    
    /**
     * Admin dashboard
     */
    public function dashboard() {
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities();
        $securityAlerts = $this->getSecurityAlerts();
        
        return $this->view('admin.dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'securityAlerts' => $securityAlerts
        ]);
    }
    
    /**
     * User management page
     */
    public function users() {
        $page = $this->request()->get('page', 1);
        $limit = $this->request()->get('limit', 10);
        $filters = $this->request()->all();
        
        $users = $this->getUsers($page, $limit, $filters);
        $stats = $this->getUserStats();
        
        return $this->view('admin.users', [
            'users' => $users,
            'stats' => $stats,
            'currentPage' => $page
        ]);
    }
    
    /**
     * Property management page
     */
    public function properties() {
        $page = $this->request()->get('page', 1);
        $limit = $this->request()->get('limit', 10);
        $filters = $this->request()->all();
        
        $properties = $this->getProperties($page, $limit, $filters);
        $stats = $this->getPropertyStats();
        
        return $this->view('admin.properties', [
            'properties' => $properties,
            'stats' => $stats,
            'currentPage' => $page
        ]);
    }
    
    /**
     * Key management page
     */
    public function keys() {
        $keys = $this->getApiKeys();
        
        return $this->view('admin.keys', [
            'keys' => $keys
        ]);
    }
    
    /**
     * System settings page
     */
    public function settings() {
        $settings = $this->getSystemSettings();
        
        return $this->view('admin.settings', [
            'settings' => $settings
        ]);
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats() {
        $stats = [];
        
        try {
            // User statistics
            $sql = "SELECT COUNT(*) as total FROM users";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['users']['total'] = $stmt->fetchColumn();
            
            $sql = "SELECT COUNT(*) as active FROM users WHERE status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['users']['active'] = $stmt->fetchColumn();
            
            $sql = "SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['users']['new_users'] = $stmt->fetchColumn();
            
            // Property statistics
            $sql = "SELECT COUNT(*) as total FROM properties";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['properties']['total'] = $stmt->fetchColumn();
            
            $sql = "SELECT COUNT(*) as available FROM properties WHERE status = 'available'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['properties']['available'] = $stmt->fetchColumn();
            
            $sql = "SELECT COUNT(*) as sold FROM properties WHERE status = 'sold'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['properties']['sold'] = $stmt->fetchColumn();
            
            // System statistics
            $sql = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS db_size 
                    FROM information_schema.tables 
                    WHERE table_schema = DATABASE()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['system']['database_size'] = $stmt->fetchColumn() . ' MB';
            
            $stats['system']['php_version'] = PHP_VERSION;
            $stats['system']['memory_usage'] = round(memory_get_usage() / 1024 / 1024, 2) . ' MB';
            
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities() {
        $activities = [];
        
        try {
            // Recent user registrations
            $sql = "SELECT 'User Registration' as activity, username, created_at 
                    FROM users 
                    ORDER BY created_at DESC 
                    LIMIT 5";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Recent property additions
            $sql = "SELECT 'Property Added' as activity, title as username, created_at 
                    FROM properties 
                    ORDER BY created_at DESC 
                    LIMIT 5";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $activities = array_merge($activities, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Sort by date
            usort($activities, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
        } catch (Exception $e) {
            error_log("Recent activities error: " . $e->getMessage());
        }
        
        return array_slice($activities, 0, 10);
    }
    
    /**
     * Get security alerts
     */
    private function getSecurityAlerts() {
        $alerts = [];
        
        try {
            // Failed login attempts
            $sql = "SELECT COUNT(*) as failed_logins 
                    FROM login_attempts 
                    WHERE success = 0 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $failedLogins = $stmt->fetchColumn();
            
            if ($failedLogins > 10) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "High number of failed login attempts: $failedLogins in last 24 hours",
                    'icon' => 'fas fa-exclamation-triangle'
                ];
            }
            
            // Suspicious activities
            $sql = "SELECT COUNT(*) as suspicious 
                    FROM security_logs 
                    WHERE level = 'suspicious' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $suspicious = $stmt->fetchColumn();
            
            if ($suspicious > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'message' => "$suspicious suspicious activities detected in last 24 hours",
                    'icon' => 'fas fa-shield-alt'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Security alerts error: " . $e->getMessage());
        }
        
        return $alerts;
    }
    
    /**
     * Get users with pagination
     */
    private function getUsers($page = 1, $limit = 10, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            $where = [];
            $params = [];
            
            // Build WHERE clause from filters
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['role'])) {
                $where[] = "role = ?";
                $params[] = $filters['role'];
            }
            
            if (!empty($filters['search'])) {
                $where[] = "(username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total count
            $countSql = "SELECT COUNT(*) FROM users $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            // Get users
            $sql = "SELECT id, username, email, full_name, phone, role, status, 
                    created_at, updated_at, last_login 
                    FROM users $whereClause 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge($params, [$limit, $offset]));
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            return ['users' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get user statistics
     */
    private function getUserStats() {
        $stats = [];
        
        try {
            // Total users
            $sql = "SELECT COUNT(*) as total FROM users";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total'] = $stmt->fetchColumn();
            
            // Users by status
            $sql = "SELECT status, COUNT(*) as count FROM users GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Users by role
            $sql = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_role'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Active users (last 30 days)
            $sql = "SELECT COUNT(*) as active FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['active'] = $stmt->fetchColumn();
            
            // New users (last 7 days)
            $sql = "SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['new_users'] = $stmt->fetchColumn();
            
        } catch (Exception $e) {
            error_log("User stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get properties with pagination
     */
    private function getProperties($page = 1, $limit = 10, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            $where = [];
            $params = [];
            
            // Build WHERE clause from filters
            if (!empty($filters['status'])) {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['property_type'])) {
                $where[] = "property_type = ?";
                $params[] = $filters['property_type'];
            }
            
            if (!empty($filters['location'])) {
                $where[] = "location LIKE ?";
                $params[] = '%' . $filters['location'] . '%';
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total count
            $countSql = "SELECT COUNT(*) FROM properties $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();
            
            // Get properties
            $sql = "SELECT * FROM properties $whereClause 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge($params, [$limit, $offset]));
            $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode images JSON
            foreach ($properties as &$property) {
                $property['images'] = json_decode($property['images'], true) ?? [];
            }
            
            return [
                'properties' => $properties,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            return ['properties' => [], 'total' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get property statistics
     */
    private function getPropertyStats() {
        $stats = [];
        
        try {
            // Total properties
            $sql = "SELECT COUNT(*) as total FROM properties";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['total'] = $stmt->fetchColumn();
            
            // Properties by status
            $sql = "SELECT status, COUNT(*) as count FROM properties GROUP BY status";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Properties by type
            $sql = "SELECT property_type, COUNT(*) as count FROM properties GROUP BY property_type";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['by_type'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Average price
            $sql = "SELECT AVG(price) as avg_price FROM properties WHERE status = 'available'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $stats['avg_price'] = round($stmt->fetchColumn(), 2);
            
        } catch (Exception $e) {
            error_log("Property stats error: " . $e->getMessage());
        }
        
        return $stats;
    }
    
    /**
     * Get API keys
     */
    private function getApiKeys() {
        try {
            $sql = "SELECT id, key_name, key_type, description, created_at, updated_at 
                    FROM api_keys 
                    ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Get system settings
     */
    private function getSystemSettings() {
        return [
            'site_name' => 'APS Dream Home',
            'site_email' => 'info@apsdreamhome.com',
            'maintenance_mode' => false,
            'debug_mode' => false,
            'max_upload_size' => '10MB',
            'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
            'session_timeout' => 3600,
            'password_min_length' => 8,
            'enable_2fa' => false,
            'backup_frequency' => 'daily',
            'log_retention_days' => 30
        ];
    }
    
    /**
     * Check if current user is admin
     */
    private function isAdmin() {
        // Check session
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
            return true;
        }
        
        // Check database (if user is logged in)
        if (isset($_SESSION['user_id'])) {
            try {
                $sql = "SELECT role FROM users WHERE id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && $user['role'] === 'admin') {
                    $_SESSION['user_role'] = 'admin';
                    return true;
                }
            } catch (Exception $e) {
                error_log("Admin check error: " . $e->getMessage());
            }
        }
        
        return false;
    }
    
    /**
     * API endpoint for AJAX requests
     */
    public function api($action = null) {
        header('Content-Type: application/json');
        
        if (!$this->isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }
        
        switch ($action) {
            case 'get_stats':
                echo json_encode($this->getDashboardStats());
                break;
                
            case 'get_users':
                $page = $this->request()->get('page', 1);
                $limit = $this->request()->get('limit', 10);
                $filters = $this->request()->all();
                unset($filters['action'], $filters['page'], $filters['limit']);
                echo json_encode($this->getUsers($page, $limit, $filters));
                break;
                
            case 'get_properties':
                $page = $this->request()->get('page', 1);
                $limit = $this->request()->get('limit', 10);
                $filters = $this->request()->all();
                unset($filters['action'], $filters['page'], $filters['limit']);
                echo json_encode($this->getProperties($page, $limit, $filters));
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    }
}
