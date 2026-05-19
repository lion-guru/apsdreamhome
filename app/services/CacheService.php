<?php
/**
 * Cache Service for APS Dream Home
 * Provides high-level caching for frequently accessed data
 */

class CacheService {
    private static $cachePrefix = 'apsdreamhome_';
    
    /**
     * Cache projects data
     */
    public static function getProjects($forceRefresh = false) {
        $key = self::$cachePrefix . 'projects';
        
        if ($forceRefresh) {
            Cache::delete($key);
        }
        
        return Cache::remember($key, function() {
            $db = Database::getInstance();
            
            // Get projects with location data
            $query = "SELECT p.*, d.name as district_name, s.name as state_name 
                      FROM projects p 
                      LEFT JOIN districts d ON p.district_id = d.id 
                      LEFT JOIN states s ON p.state_id = s.id 
                      WHERE p.status = 'active' 
                      ORDER BY p.created_at DESC";
            
            $result = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
            
            // Group by location for efficient display
            $grouped = [];
            foreach ($result as $project) {
                $location = $project['district_name'] ?? 'Other';
                if (!isset($grouped[$location])) {
                    $grouped[$location] = [];
                }
                $grouped[$location][] = $project;
            }
            
            return [
                'projects' => $result,
                'grouped_by_location' => $grouped,
                'total' => count($result)
            ];
        }, 3600); // Cache for 1 hour
    }
    
    /**
     * Cache locations/states data
     */
    public static function getLocations($forceRefresh = false) {
        $key = self::$cachePrefix . 'locations';
        
        if ($forceRefresh) {
            Cache::delete($key);
        }
        
        return Cache::remember($key, function() {
            $db = Database::getInstance();
            
            $states = $db->query("SELECT * FROM states ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            $districts = $db->query("SELECT * FROM districts ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'states' => $states,
                'districts' => $districts,
                'total_states' => count($states),
                'total_districts' => count($districts)
            ];
        }, 86400); // Cache for 24 hours
    }
    
    /**
     * Cache properties data
     */
    public static function getProperties($filters = [], $forceRefresh = false) {
        // Create cache key based on filters
        $filterKey = md5(serialize($filters));
        $key = self::$cachePrefix . 'properties_' . $filterKey;
        
        if ($forceRefresh) {
            Cache::delete($key);
        }
        
        return Cache::remember($key, function() use ($filters) {
            $db = Database::getInstance();
            
            $query = "SELECT p.*, u.name as user_name, u.phone as user_phone 
                      FROM user_properties p 
                      LEFT JOIN users u ON p.user_id = u.id 
                      WHERE p.status = 'approved'";
            
            $params = [];
            
            // Apply filters
            if (!empty($filters['property_type'])) {
                $query .= " AND p.property_type = :property_type";
                $params['property_type'] = $filters['property_type'];
            }
            
            if (!empty($filters['listing_type'])) {
                $query .= " AND p.listing_type = :listing_type";
                $params['listing_type'] = $filters['listing_type'];
            }
            
            if (!empty($filters['min_price'])) {
                $query .= " AND p.price >= :min_price";
                $params['min_price'] = $filters['min_price'];
            }
            
            if (!empty($filters['max_price'])) {
                $query .= " AND p.price <= :max_price";
                $params['max_price'] = $filters['max_price'];
            }
            
            if (!empty($filters['location'])) {
                $query .= " AND p.address LIKE :location";
                $params['location'] = '%' . $filters['location'] . '%';
            }
            
            $query .= " ORDER BY p.created_at DESC";
            
            if (!empty($params)) {
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $result = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return [
                'properties' => $result,
                'total' => count($result),
                'filters' => $filters
            ];
        }, 1800); // Cache for 30 minutes
    }
    
    /**
     * Cache user dashboard data
     */
    public static function getUserDashboard($userId, $forceRefresh = false) {
        $key = self::$cachePrefix . 'user_dashboard_' . $userId;
        
        if ($forceRefresh) {
            Cache::delete($key);
        }
        
        return Cache::remember($key, function() use ($userId) {
            $db = Database::getInstance();
            
            // Get user's properties count
            $propertyCount = $db->prepare("SELECT COUNT(*) as count FROM user_properties WHERE user_id = ?");
            $propertyCount->execute([$userId]);
            $propertyCount = $propertyCount->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Get user's inquiries count
            $inquiryCount = $db->prepare("SELECT COUNT(*) as count FROM inquiries WHERE user_id = ?");
            $inquiryCount->execute([$userId]);
            $inquiryCount = $inquiryCount->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Get recent properties
            $recentProperties = $db->prepare("SELECT * FROM user_properties WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
            $recentProperties->execute([$userId]);
            $recentProperties = $recentProperties->fetchAll(PDO::FETCH_ASSOC);
            
            // Get recent inquiries
            $recentInquiries = $db->prepare("SELECT * FROM inquiries WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
            $recentInquiries->execute([$userId]);
            $recentInquiries = $recentInquiries->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'property_count' => $propertyCount,
                'inquiry_count' => $inquiryCount,
                'recent_properties' => $recentProperties,
                'recent_inquiries' => $recentInquiries
            ];
        }, 900); // Cache for 15 minutes
    }
    
    /**
     * Cache admin dashboard stats
     */
    public static function getAdminDashboardStats($forceRefresh = false) {
        $key = self::$cachePrefix . 'admin_dashboard_stats';
        
        if ($forceRefresh) {
            Cache::delete($key);
        }
        
        return Cache::remember($key, function() {
            $db = Database::getInstance();
            
            $stats = [];
            
            // Count pending properties
            $stats['pending_properties'] = $db->query("SELECT COUNT(*) as count FROM user_properties WHERE status = 'pending'")
                ->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Count approved properties
            $stats['approved_properties'] = $db->query("SELECT COUNT(*) as count FROM user_properties WHERE status = 'approved'")
                ->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Count total users
            $stats['total_users'] = $db->query("SELECT COUNT(*) as count FROM users")
                ->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Count pending inquiries
            $stats['pending_inquiries'] = $db->query("SELECT COUNT(*) as count FROM inquiries WHERE status = 'pending'")
                ->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Count total projects
            $stats['total_projects'] = $db->query("SELECT COUNT(*) as count FROM projects")
                ->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Count active projects
            $stats['active_projects'] = $db->query("SELECT COUNT(*) as count FROM projects WHERE status = 'active'")
                ->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stats['generated_at'] = date('Y-m-d H:i:s');
            
            return $stats;
        }, 600); // Cache for 10 minutes
    }
    
    /**
     * Cache menu structure for navigation
     */
    public static function getMenuStructure($role = 'customer', $forceRefresh = false) {
        $key = self::$cachePrefix . 'menu_' . $role;
        
        if ($forceRefresh) {
            Cache::delete($key);
        }
        
        return Cache::remember($key, function() use ($role) {
            $db = Database::getInstance();
            
            // Get menu items for role
            $query = "SELECT mi.*, rmp.status as permission_status 
                      FROM admin_menu_items mi 
                      LEFT JOIN admin_role_menu_permissions rmp ON mi.id = rmp.menu_item_id 
                      LEFT JOIN admin_roles r ON rmp.role_id = r.id 
                      WHERE r.name = :role OR rmp.role_id IS NULL
                      ORDER BY mi.sort_order ASC";
            
            $stmt = $db->prepare($query);
            $stmt->execute(['role' => $role]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Organize into menu structure
            $menu = [];
            foreach ($items as $item) {
                if ($item['parent_id'] == 0) {
                    $menu[$item['id']] = [
                        'item' => $item,
                        'children' => []
                    ];
                } else {
                    if (isset($menu[$item['parent_id']])) {
                        $menu[$item['parent_id']]['children'][] = $item;
                    }
                }
            }
            
            return $menu;
        }, 3600); // Cache for 1 hour
    }
    
    /**
     * Clear all application cache
     */
    public static function clearAll() {
        $prefix = self::$cachePrefix;
        $cacheDir = __DIR__ . '/../../storage/cache';
        
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*.cache');
            $cleared = 0;
            
            foreach ($files as $file) {
                $content = file_get_contents($file);
                $data = json_decode($content, true);
                
                // Clear only APS Dream Home cache
                if (isset($data['key']) && strpos($data['key'], $prefix) === 0) {
                    unlink($file);
                    $cleared++;
                }
            }
            
            return $cleared;
        }
        
        return 0;
    }
    
    /**
     * Clear specific cache type
     */
    public static function clearType($type) {
        $key = self::$cachePrefix . $type;
        return Cache::delete($key);
    }
    
    /**
     * Get cache statistics
     */
    public static function getStats() {
        $cacheStats = Cache::getStats();
        
        // Add application-specific stats
        $appStats = [
            'prefix' => self::$cachePrefix,
            'cache_types' => [
                'projects' => Cache::get(self::$cachePrefix . 'projects') !== null,
                'locations' => Cache::get(self::$cachePrefix . 'locations') !== null,
                'admin_dashboard' => Cache::get(self::$cachePrefix . 'admin_dashboard_stats') !== null
            ]
        ];
        
        return array_merge($cacheStats, $appStats);
    }
}