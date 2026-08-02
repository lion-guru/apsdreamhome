<?php
/**
 * Advanced Search Service for APS Dream Home
 * Provides powerful search functionality with filtering, suggestions, and history
 */

use App\Core\Database\Database;
use App\Core\Cache;
use \App\Traits\ServiceTenantTrait;

class AdvancedSearchService {
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $cachePrefix = 'search_';
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Advanced property search with multiple filters
     */
    public function searchProperties($params = []) {
        $cacheKey = $this->cachePrefix . 'properties_' . md5(serialize($params));
        
        return Cache::remember($cacheKey, function() use ($params) {
            $query = "SELECT p.*, u.name as user_name, u.phone as user_phone,
                      d.name as district_name, s.name as state_name
                      FROM user_properties p 
                      LEFT JOIN users u ON p.user_id = u.id 
                      LEFT JOIN districts d ON p.district_id = d.id
                      LEFT JOIN states s ON p.state_id = s.id
                      WHERE p.status = 'approved'";
            
            $bindings = [];
            
            // Text search
            if (!empty($params['search'])) {
                $query .= " AND (p.name LIKE :search1 OR p.description LIKE :search2 OR p.address LIKE :search3 OR p.location LIKE :search4 OR p.city_name LIKE :search5)";
                $searchTerm = '%' . $params['search'] . '%';
                $bindings['search1'] = $searchTerm;
                $bindings['search2'] = $searchTerm;
                $bindings['search3'] = $searchTerm;
                $bindings['search4'] = $searchTerm;
                $bindings['search5'] = $searchTerm;
            }
            
            // Property type filter
            if (!empty($params['property_type'])) {
                if (is_array($params['property_type'])) {
                    $placeholders = [];
                    foreach ($params['property_type'] as $i => $type) {
                        $placeholders[] = ":property_type_$i";
                        $bindings["property_type_$i"] = $type;
                    }
                    $query .= " AND p.property_type IN (" . implode(',', $placeholders) . ")";
                } else {
                    $query .= " AND p.property_type = :property_type";
                    $bindings['property_type'] = $params['property_type'];
                }
            }
            
            // Listing type filter
            if (!empty($params['listing_type'])) {
                $query .= " AND p.listing_type = :listing_type";
                $bindings['listing_type'] = $params['listing_type'];
            }
            
            // Price range
            if (!empty($params['min_price'])) {
                $query .= " AND p.price >= :min_price";
                $bindings['min_price'] = $params['min_price'];
            }
            
            if (!empty($params['max_price'])) {
                $query .= " AND p.price <= :max_price";
                $bindings['max_price'] = $params['max_price'];
            }
            
            // Area range
            if (!empty($params['min_area'])) {
                $query .= " AND p.area_sqft >= :min_area";
                $bindings['min_area'] = $params['min_area'];
            }
            
            if (!empty($params['max_area'])) {
                $query .= " AND p.area_sqft <= :max_area";
                $bindings['max_area'] = $params['max_area'];
            }
            
            // Location filters
            if (!empty($params['state_id'])) {
                $query .= " AND p.state_id = :state_id";
                $bindings['state_id'] = $params['state_id'];
            }
            
            if (!empty($params['district_id'])) {
                $query .= " AND p.district_id = :district_id";
                $bindings['district_id'] = $params['district_id'];
            }
            
            // Location text search
            if (!empty($params['location'])) {
                $query .= " AND (p.address LIKE :location1 OR d.name LIKE :location2 OR s.name LIKE :location3)";
                $locationTerm = '%' . $params['location'] . '%';
                $bindings['location1'] = $locationTerm;
                $bindings['location2'] = $locationTerm;
                $bindings['location3'] = $locationTerm;
            }
            
            // BHK/Bedrooms filter
            if (!empty($params['bedrooms'])) {
                $query .= " AND p.bedrooms >= :bedrooms";
                $bindings['bedrooms'] = $params['bedrooms'];
            }
            
            // Amenities filter (user_properties stores extra attributes in metadata JSON)
            if (!empty($params['amenities'])) {
                foreach ($params['amenities'] as $i => $amenity) {
                    $query .= " AND p.metadata LIKE :amenity_$i";
                    $bindings["amenity_$i"] = "%$amenity%";
                }
            }
            
            // Posted date range
            if (!empty($params['posted_after'])) {
                $query .= " AND p.created_at >= :posted_after";
                $bindings['posted_after'] = $params['posted_after'];
            }
            
            if (!empty($params['posted_before'])) {
                $query .= " AND p.created_at <= :posted_before";
                $bindings['posted_before'] = $params['posted_before'];
            }
            
            // Sorting
            $orderBy = 'p.created_at DESC';
            if (!empty($params['sort'])) {
                switch ($params['sort']) {
                    case 'price_asc':
                        $orderBy = 'p.price ASC';
                        break;
                    case 'price_desc':
                        $orderBy = 'p.price DESC';
                        break;
                    case 'area_asc':
                        $orderBy = 'p.area_sqft ASC';
                        break;
                    case 'area_desc':
                        $orderBy = 'p.area_sqft DESC';
                        break;
                    case 'date_asc':
                        $orderBy = 'p.created_at ASC';
                        break;
                    case 'date_desc':
                        $orderBy = 'p.created_at DESC';
                        break;
                    case 'relevance':
                        $orderBy = 'p.views DESC'; // Use views as relevance proxy
                        break;
                }
            }
            $query .= " ORDER BY $orderBy";
            
            // Pagination
            $page = $params['page'] ?? 1;
            $perPage = $params['per_page'] ?? 20;
            $offset = ($page - 1) * $perPage;
            
            $query .= " LIMIT $offset, $perPage";
            
            // Execute query
            if (!empty($bindings)) {
                $stmt = $this->db->prepare($query);
                $stmt->execute($bindings);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $results = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Get total count for pagination
            $countQuery = str_replace("SELECT p.*, u.name as user_name, u.phone as user_phone,
                      d.name as district_name, s.name as state_name", "SELECT COUNT(*) as total", $query);
            $countQuery = preg_replace('/ORDER BY.*$/s', '', $countQuery);
            $countQuery = preg_replace('/LIMIT.*$/s', '', $countQuery);
            
            if (!empty($bindings)) {
                $stmt = $this->db->prepare($countQuery);
                $stmt->execute($bindings);
                $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            } else {
                $total = $this->db->query($countQuery)->fetch(PDO::FETCH_ASSOC)['total'];
            }
            
            return [
                'results' => $results,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ];
        }, 300); // Cache for 5 minutes
    }
    
    /**
     * Get search suggestions based on partial input
     */
    public function getSearchSuggestions($query, $limit = 10) {
        $cacheKey = $this->cachePrefix . 'suggestions_' . md5($query);
        
        return Cache::remember($cacheKey, function() use ($query, $limit) {
            $suggestions = [];
            
            // Suggest locations
            $locationQuery = "SELECT DISTINCT name as suggestion, 'location' as type 
                              FROM districts 
                              WHERE name LIKE :query1 
                              UNION 
                              SELECT DISTINCT name as suggestion, 'location' as type 
                              FROM states 
                              WHERE name LIKE :query2
                              LIMIT $limit";
            
            $stmt = $this->db->prepare($locationQuery);
            $stmt->execute(['query1' => '%' . $query . '%', 'query2' => '%' . $query . '%']);
            $suggestions = array_merge($suggestions, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Suggest property types
            $typeQuery = "SELECT DISTINCT property_type as suggestion, 'property_type' as type 
                          FROM user_properties 
                          WHERE property_type LIKE :query 
                          LIMIT 5";
            
            $stmt = $this->db->prepare($typeQuery);
            $stmt->execute(['query' => '%' . $query . '%']);
            $suggestions = array_merge($suggestions, $stmt->fetchAll(PDO::FETCH_ASSOC));
            
            // Suggest from recent searches
            $recentQuery = "SELECT DISTINCT search_term as suggestion, 'recent' as type 
                            FROM search_history 
                            WHERE search_term LIKE :query 
                            ORDER BY created_at DESC 
                            LIMIT 5";
            
            try {
                $stmt = $this->db->prepare($recentQuery);
                $stmt->execute(['query' => '%' . $query . '%']);
                $suggestions = array_merge($suggestions, $stmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (\Exception $e) {
                // Table might not exist yet
                        error_log("AdvancedSearchService.php: " . $e->getMessage());
            }
            
            return array_slice($suggestions, 0, $limit);
        }, 600); // Cache for 10 minutes
    }
    
    /**
     * Save search to history
     */
    public function saveSearchHistory($userId, $searchTerm, $filters = []) {
        try {
            // Create search_history table if it doesn't exist
            $this->db->exec("");
            
            $stmt = $this->db->prepare("INSERT INTO search_history (user_id, search_term, filters" . implode(',', array_keys($this->tenantInsertData())) . ") VALUES (?, ?, ?" . implode(',', array_fill(0, count($this->tenantInsertData()), '?')) . ")");
            $params = [$userId, $searchTerm, json_encode($filters)];
            if (!empty($insertData = $this->tenantInsertData())) $params = array_merge($params, array_values($insertData));
            $stmt->execute($params);
            
            return true;
        } catch (\Exception $e) {
            error_log("Failed to save search history: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's recent searches
     */
    public function getRecentSearches($userId, $limit = 10) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM search_history WHERE user_id = ?" . $this->tenantSql() . " ORDER BY created_at DESC LIMIT $limit");
            $params = [$userId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Parse filters JSON
            foreach ($results as &$result) {
                $result['filters'] = json_decode($result['filters'], true) ?? [];
            }
            
            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get popular searches (trending)
     */
    public function getPopularSearches($limit = 10) {
        try {
            $query = "SELECT search_term, COUNT(*) as search_count 
                      FROM search_history 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)" . $this->tenantSql() . "
                      GROUP BY search_term 
                      ORDER BY search_count DESC 
                      LIMIT $limit";
            
            $results = $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get search facets for filtering
     */
    public function getSearchFacets() {
        $cacheKey = $this->cachePrefix . 'facets';
        
        return Cache::remember($cacheKey, function() {
            $facets = [];
            
            // Property types
            $propertyTypes = $this->db->query("SELECT DISTINCT property_type FROM user_properties WHERE status = 'approved'" . $this->tenantSql() . " ORDER BY property_type")->fetchAll(PDO::FETCH_COLUMN);
            $facets['property_types'] = $propertyTypes;
            
            // Listing types
            $listingTypes = $this->db->query("SELECT DISTINCT listing_type FROM user_properties WHERE status = 'approved'" . $this->tenantSql() . " ORDER BY listing_type")->fetchAll(PDO::FETCH_COLUMN);
            $facets['listing_types'] = $listingTypes;
            
            // Price ranges
            $priceRanges = $this->db->query("SELECT MIN(price) as min_price, MAX(price) as max_price FROM user_properties WHERE status = 'approved'" . $this->tenantSql())->fetch(PDO::FETCH_ASSOC);
            $facets['price_ranges'] = [
                'min' => (int)($priceRanges['min_price'] ?? 0),
                'max' => (int)($priceRanges['max_price'] ?? 10000000)
            ];
            
            // States
            $states = $this->db->query("SELECT DISTINCT id, name FROM states ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
            $facets['states'] = $states;
            
            // Districts (grouped by state)
            $districts = $this->db->query("SELECT DISTINCT d.id, d.name, d.state_id, s.name as state_name 
                                          FROM districts d 
                                          JOIN states s ON d.state_id = s.id 
                                          ORDER BY s.name, d.name")->fetchAll(PDO::FETCH_ASSOC);
            
            $groupedDistricts = [];
            foreach ($districts as $district) {
                $stateName = $district['state_name'];
                if (!isset($groupedDistricts[$stateName])) {
                    $groupedDistricts[$stateName] = [];
                }
                $groupedDistricts[$stateName][] = $district;
            }
            $facets['districts'] = $groupedDistricts;
            
            return $facets;
        }, 3600); // Cache for 1 hour
    }
    
    /**
     * Clear search cache
     */
    public function clearCache() {
        $cacheDir = __DIR__ . '/../../storage/cache';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/' . $this->cachePrefix . '*.cache');
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }
}

// This class is declared in the global namespace but is referenced as
// App\Services\AdvancedSearchService (e.g. by Api\SearchController). The
// autoloader loads this file for that namespaced name, so alias it here.
if (!class_exists('App\\Services\\AdvancedSearchService', false)) {
    class_alias('AdvancedSearchService', 'App\\Services\\AdvancedSearchService');
}