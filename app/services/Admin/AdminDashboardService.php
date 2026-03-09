<?php

namespace App\Services\Admin;

use App\Core\Database;
use App\Core\Logger;
use App\Core\Config;

/**
 * Admin Dashboard Service - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 */
class AdminDashboardService
{
    private $database;
    private $logger;
    private $config;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->logger = new Logger();
        $this->config = Config::getInstance();
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats()
    {
        try {
            $stats = [];
            
            // Total users
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as total_users FROM users"
            );
            $stats['total_users'] = $result['total_users'] ?? 0;
            
            // Active properties
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as active_properties FROM properties WHERE status = 'available'"
            );
            $stats['active_properties'] = $result['active_properties'] ?? 0;
            
            // Total properties
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as total_properties FROM properties"
            );
            $stats['total_properties'] = $result['total_properties'] ?? 0;
            
            // Total bookings
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as total_bookings FROM bookings"
            );
            $stats['total_bookings'] = $result['total_bookings'] ?? 0;
            
            // Recent bookings (last 30 days)
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as recent_bookings FROM bookings 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $stats['recent_bookings'] = $result['recent_bookings'] ?? 0;
            
            // Total leads
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as total_leads FROM leads"
            );
            $stats['total_leads'] = $result['total_leads'] ?? 0;
            
            // New leads (last 7 days)
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as new_leads FROM leads 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            );
            $stats['new_leads'] = $result['new_leads'] ?? 0;
            
            // Total revenue (from payments)
            $result = $this->database->selectOne(
                "SELECT SUM(amount) as total_revenue FROM payments WHERE status = 'completed'"
            );
            $stats['total_revenue'] = $result['total_revenue'] ?? 0;
            
            // Monthly revenue
            $result = $this->database->selectOne(
                "SELECT SUM(amount) as monthly_revenue FROM payments 
                 WHERE status = 'completed' AND payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            );
            $stats['monthly_revenue'] = $result['monthly_revenue'] ?? 0;
            
            // Total associates
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as total_associates FROM associates WHERE status = 'active'"
            );
            $stats['total_associates'] = $result['total_associates'] ?? 0;
            
            // Pending tasks
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as pending_tasks FROM tasks WHERE status = 'pending'"
            );
            $stats['pending_tasks'] = $result['pending_tasks'] ?? 0;
            
            $this->logger->info('Dashboard stats retrieved', ['stats_count' => count($stats)]);
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get dashboard stats', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve dashboard statistics'
            ];
        }
    }
    
    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 10)
    {
        try {
            $activities = [];
            
            // Recent user registrations
            $users = $this->database->select(
                "SELECT 'user_registered' as activity_type, name as description,
                        created_at as activity_time, id as reference_id, 'user' as entity_type
                 FROM users 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY created_at DESC LIMIT ?",
                [$limit]
            );
            
            // Recent property additions
            $properties = $this->database->select(
                "SELECT 'property_added' as activity_type, title as description,
                        created_at as activity_time, id as reference_id, 'property' as entity_type
                 FROM properties 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY created_at DESC LIMIT ?",
                [$limit]
            );
            
            // Recent bookings
            $bookings = $this->database->select(
                "SELECT 'booking_created' as activity_type,
                        CONCAT('New booking for property ID: ', property_id) as description,
                        created_at as activity_time, id as reference_id, 'booking' as entity_type
                 FROM bookings 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY created_at DESC LIMIT ?",
                [$limit]
            );
            
            // Recent leads
            $leads = $this->database->select(
                "SELECT 'lead_created' as activity_type, name as description,
                        created_at as activity_time, id as reference_id, 'lead' as entity_type
                 FROM leads 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY created_at DESC LIMIT ?",
                [$limit]
            );
            
            // Merge all activities
            $activities = array_merge($users, $properties, $bookings, $leads);
            
            // Sort by activity time
            usort($activities, function ($a, $b) {
                return strtotime($b['activity_time']) - strtotime($a['activity_time']);
            });
            
            return [
                'success' => true,
                'data' => array_slice($activities, 0, $limit)
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get recent activities', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve recent activities'
            ];
        }
    }
    
    /**
     * Get property analytics
     */
    public function getPropertyAnalytics()
    {
        try {
            $analytics = [];
            
            // Properties by type
            $analytics['properties_by_type'] = $this->database->select(
                "SELECT pt.name, COUNT(p.id) as count
                 FROM properties p
                 LEFT JOIN property_types pt ON p.property_type_id = pt.id
                 GROUP BY p.property_type_id, pt.name
                 ORDER BY count DESC"
            );
            
            // Properties by status
            $analytics['properties_by_status'] = $this->database->select(
                "SELECT status, COUNT(*) as count FROM properties GROUP BY status ORDER BY count DESC"
            );
            
            // Properties by location (top 10)
            $analytics['properties_by_location'] = $this->database->select(
                "SELECT city, COUNT(*) as count FROM properties 
                 WHERE city IS NOT NULL AND city != ''
                 GROUP BY city ORDER BY count DESC LIMIT 10"
            );
            
            // Price ranges
            $analytics['properties_by_price_range'] = $this->database->select(
                "SELECT CASE
                        WHEN price < 1000000 THEN 'Under 10L'
                        WHEN price < 5000000 THEN '10L-50L'
                        WHEN price < 10000000 THEN '50L-1Cr'
                        ELSE 'Above 1Cr'
                    END as price_range,
                    COUNT(*) as count
                 FROM properties
                 WHERE price IS NOT NULL
                 GROUP BY price_range
                 ORDER BY count DESC"
            );
            
            // Monthly property additions
            $analytics['monthly_property_additions'] = $this->database->select(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
                 FROM properties 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month DESC"
            );
            
            return [
                'success' => true,
                'data' => $analytics
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get property analytics', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve property analytics'
            ];
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
            $data['users_by_role'] = $this->database->select(
                "SELECT role, COUNT(*) as count FROM users GROUP BY role ORDER BY count DESC"
            );
            
            // Users by status
            $data['users_by_status'] = $this->database->select(
                "SELECT status, COUNT(*) as count FROM users GROUP BY status ORDER BY count DESC"
            );
            
            // Recent users
            $data['recent_users'] = $this->database->select(
                "SELECT id, name as username, name as full_name, role, status, created_at
                 FROM users ORDER BY created_at DESC LIMIT 10"
            );
            
            // User registration trends
            $data['user_registration_trends'] = $this->database->select(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
                 FROM users 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month DESC"
            );
            
            return [
                'success' => true,
                'data' => $data
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get user management data', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve user management data'
            ];
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
            $data['leads_by_status'] = $this->database->select(
                "SELECT status, COUNT(*) as count FROM leads GROUP BY status ORDER BY count DESC"
            );
            
            // Leads by source
            $data['leads_by_source'] = $this->database->select(
                "SELECT source, COUNT(*) as count FROM leads 
                 WHERE source IS NOT NULL AND source != ''
                 GROUP BY source ORDER BY count DESC"
            );
            
            // Recent leads
            $data['recent_leads'] = $this->database->select(
                "SELECT id, name, email, phone, source, status, created_at
                 FROM leads ORDER BY created_at DESC LIMIT 10"
            );
            
            // Lead conversion rates
            $data['lead_conversion_rates'] = $this->database->select(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as total_leads,
                        SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_leads
                 FROM leads 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month DESC"
            );
            
            return [
                'success' => true,
                'data' => $data
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get lead management data', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve lead management data'
            ];
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
            $data['bookings_by_status'] = $this->database->select(
                "SELECT status, COUNT(*) as count FROM bookings GROUP BY status ORDER BY count DESC"
            );
            
            // Bookings by type
            $data['bookings_by_type'] = $this->database->select(
                "SELECT booking_type, COUNT(*) as count FROM bookings 
                 WHERE booking_type IS NOT NULL AND booking_type != ''
                 GROUP BY booking_type ORDER BY count DESC"
            );
            
            // Recent bookings
            $data['recent_bookings'] = $this->database->select(
                "SELECT b.id, b.booking_type, b.status, b.created_at,
                        p.title as property_title, u.name as customer_name
                 FROM bookings b
                 LEFT JOIN properties p ON b.property_id = p.id
                 LEFT JOIN users u ON b.user_id = u.id
                 ORDER BY b.created_at DESC LIMIT 10"
            );
            
            // Booking trends
            $data['booking_trends'] = $this->database->select(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
                 FROM bookings 
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month DESC"
            );
            
            return [
                'success' => true,
                'data' => $data
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get booking management data', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve booking management data'
            ];
        }
    }
    
    /**
     * Get system health status
     */
    public function getSystemHealth()
    {
        try {
            $health = [
                'status' => 'healthy',
                'checks' => [],
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            // Database connection check
            try {
                $this->database->query("SELECT 1");
                $health['checks']['database'] = [
                    'status' => 'ok',
                    'message' => 'Database connection healthy'
                ];
            } catch (\Exception $e) {
                $health['checks']['database'] = [
                    'status' => 'error',
                    'message' => 'Database connection failed: ' . $e->getMessage()
                ];
                $health['status'] = 'unhealthy';
            }
            
            // Total properties check
            $result = $this->database->selectOne("SELECT COUNT(*) as count FROM properties");
            $totalProperties = $result['count'] ?? 0;
            $health['checks']['properties'] = [
                'status' => $totalProperties > 0 ? 'ok' : 'warning',
                'message' => "Total properties: $totalProperties"
            ];
            
            // Total users check
            $result = $this->database->selectOne("SELECT COUNT(*) as count FROM users");
            $totalUsers = $result['count'] ?? 0;
            $health['checks']['users'] = [
                'status' => $totalUsers > 0 ? 'ok' : 'warning',
                'message' => "Total users: $totalUsers"
            ];
            
            // Recent errors check
            try {
                $result = $this->database->selectOne(
                    "SELECT COUNT(*) as count FROM error_logs 
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)"
                );
                $recentErrors = $result['count'] ?? 0;
                $health['checks']['errors'] = [
                    'status' => $recentErrors == 0 ? 'ok' : 'warning',
                    'message' => "Recent errors: $recentErrors"
                ];
            } catch (\Exception $e) {
                $health['checks']['errors'] = [
                    'status' => 'ok',
                    'message' => 'Error log table not found'
                ];
            }
            
            // File permissions check
            $uploadPath = STORAGE_PATH . '/uploads/';
            $writable = is_writable($uploadPath);
            $health['checks']['uploads'] = [
                'status' => $writable ? 'ok' : 'error',
                'message' => "Upload directory: " . ($writable ? 'writable' : 'not writable')
            ];
            
            // Memory usage check
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = ini_get('memory_limit');
            $health['checks']['memory'] = [
                'status' => 'ok',
                'message' => "Memory usage: " . round($memoryUsage / 1024 / 1024, 2) . "MB / $memoryLimit"
            ];
            
            // Disk space check
            $freeSpace = disk_free_space(STORAGE_PATH);
            $totalSpace = disk_total_space(STORAGE_PATH);
            $usedPercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
            $health['checks']['disk_space'] = [
                'status' => $usedPercent < 90 ? 'ok' : 'warning',
                'message' => "Disk usage: $usedPercent%"
            ];
            
            return [
                'success' => true,
                'data' => $health
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get system health', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve system health status'
            ];
        }
    }
    
    /**
     * Get admin menu items based on user role
     */
    public function getAdminMenu($userRole)
    {
        $menu = [
            'dashboard' => [
                'title' => 'Dashboard',
                'icon' => 'dashboard',
                'url' => '/admin/dashboard',
                'active' => true
            ],
            'properties' => [
                'title' => 'Properties',
                'icon' => 'home',
                'url' => '/admin/properties',
                'submenu' => [
                    ['title' => 'All Properties', 'url' => '/admin/properties'],
                    ['title' => 'Add Property', 'url' => '/admin/properties/add'],
                    ['title' => 'Property Types', 'url' => '/admin/property-types'],
                    ['title' => 'Featured Properties', 'url' => '/admin/properties/featured']
                ]
            ],
            'users' => [
                'title' => 'Users',
                'icon' => 'users',
                'url' => '/admin/users',
                'submenu' => [
                    ['title' => 'All Users', 'url' => '/admin/users'],
                    ['title' => 'Add User', 'url' => '/admin/users/add'],
                    ['title' => 'User Roles', 'url' => '/admin/roles']
                ]
            ],
            'associates' => [
                'title' => 'Associates',
                'icon' => 'user-tie',
                'url' => '/admin/associates',
                'submenu' => [
                    ['title' => 'All Associates', 'url' => '/admin/associates'],
                    ['title' => 'Add Associate', 'url' => '/admin/associates/add'],
                    ['title' => 'Performance Report', 'url' => '/admin/associates/performance']
                ]
            ],
            'leads' => [
                'title' => 'Leads',
                'icon' => 'user-plus',
                'url' => '/admin/leads'
            ],
            'bookings' => [
                'title' => 'Bookings',
                'icon' => 'calendar-check',
                'url' => '/admin/bookings'
            ],
            'analytics' => [
                'title' => 'Analytics',
                'icon' => 'chart-bar',
                'url' => '/admin/analytics'
            ]
        ];
        
        // Add admin-only menu items
        if ($userRole === 'admin') {
            $menu['system'] = [
                'title' => 'System',
                'icon' => 'cogs',
                'url' => '/admin/system',
                'submenu' => [
                    ['title' => 'Settings', 'url' => '/admin/settings'],
                    ['title' => 'Email Templates', 'url' => '/admin/email-templates'],
                    ['title' => 'API Keys', 'url' => '/admin/api-keys'],
                    ['title' => 'System Health', 'url' => '/admin/health'],
                    ['title' => 'Backup', 'url' => '/admin/backup'],
                    ['title' => 'Logs', 'url' => '/admin/logs']
                ]
            ];
        }
        
        return [
            'success' => true,
            'data' => $menu
        ];
    }
    
    /**
     * Get quick stats for dashboard widgets
     */
    public function getQuickStats()
    {
        try {
            $stats = [];
            
            // Today's stats
            $today = date('Y-m-d');
            
            // New users today
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = ?",
                [$today]
            );
            $stats['new_users_today'] = $result['count'] ?? 0;
            
            // New properties today
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as count FROM properties WHERE DATE(created_at) = ?",
                [$today]
            );
            $stats['new_properties_today'] = $result['count'] ?? 0;
            
            // New leads today
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as count FROM leads WHERE DATE(created_at) = ?",
                [$today]
            );
            $stats['new_leads_today'] = $result['count'] ?? 0;
            
            // New bookings today
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = ?",
                [$today]
            );
            $stats['new_bookings_today'] = $result['count'] ?? 0;
            
            // Revenue today
            $result = $this->database->selectOne(
                "SELECT SUM(amount) as total FROM payments 
                 WHERE DATE(payment_date) = ? AND status = 'completed'",
                [$today]
            );
            $stats['revenue_today'] = $result['total'] ?? 0;
            
            // This week's stats
            $weekStart = date('Y-m-d', strtotime('this week'));
            
            $result = $this->database->selectOne(
                "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) >= ?",
                [$weekStart]
            );
            $stats['new_users_week'] = $result['count'] ?? 0;
            
            $result = $this->database->selectOne(
                "SELECT SUM(amount) as total FROM payments 
                 WHERE DATE(payment_date) >= ? AND status = 'completed'",
                [$weekStart]
            );
            $stats['revenue_week'] = $result['total'] ?? 0;
            
            return [
                'success' => true,
                'data' => $stats
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get quick stats', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to retrieve quick statistics'
            ];
        }
    }
}