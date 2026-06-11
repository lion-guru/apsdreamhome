<?php

namespace App\Services\Search;

use App\Core\Database\Database;
use App\Core\Cache\CacheManager;

/**
 * Advanced Property Search Service
 * Multi-criteria search with filters, sorting, and recommendations
 */
class AdvancedSearchService
{
    private $database;
    private $cache;
    private $cachePrefix = 'search_';
    private $cacheTtl = 300; // 5 minutes
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->cache = CacheManager::getInstance();
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure search tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Search indices table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Saved searches table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Search suggestions table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Search logs table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Advanced property search
     */
    public function searchProperties(array $filters = [], array $options = []): array
    {
        $startTime = microtime(true);
        $page = $options['page'] ?? 1;
        $limit = $options['limit'] ?? 20;
        $offset = ($page - 1) * $limit;
        
        $cacheKey = $this->cachePrefix . md5(json_encode($filters) . $page . $limit);
        
        // Check cache
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        // Build query
        $where = ['p.status = ?'];
        $params = ['available'];
        
        // Text search
        if (!empty($filters['query'])) {
            $where[] = "(p.title LIKE ? OR p.description LIKE ? OR p.address LIKE ? OR p.location LIKE ?)";
            $searchTerm = '%' . $filters['query'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            
            // Log search query
            $this->logSearch($filters['query'], $filters);
        }
        
        // Property type filter
        if (!empty($filters['type'])) {
            if (is_array($filters['type'])) {
                $placeholders = implode(',', array_fill(0, count($filters['type']), '?'));
                $where[] = "p.type IN ({$placeholders})";
                $params = array_merge($params, $filters['type']);
            } else {
                $where[] = "p.type = ?";
                $params[] = $filters['type'];
            }
        }
        
        // Price range filter
        if (!empty($filters['min_price'])) {
            $where[] = "p.price >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $where[] = "p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        // Area range filter
        if (!empty($filters['min_area'])) {
            $where[] = "p.area >= ?";
            $params[] = $filters['min_area'];
        }
        if (!empty($filters['max_area'])) {
            $where[] = "p.area <= ?";
            $params[] = $filters['max_area'];
        }
        
        // Location filters
        if (!empty($filters['city'])) {
            $where[] = "p.city = ?";
            $params[] = $filters['city'];
        }
        if (!empty($filters['state'])) {
            $where[] = "p.state = ?";
            $params[] = $filters['state'];
        }
        if (!empty($filters['locality'])) {
            $where[] = "p.locality LIKE ?";
            $params[] = '%' . $filters['locality'] . '%';
        }
        
        // Bedroom/Bathroom filters
        if (!empty($filters['bedrooms'])) {
            if ($filters['bedrooms'] == '3+') {
                $where[] = "p.bedrooms >= 3";
            } else {
                $where[] = "p.bedrooms = ?";
                $params[] = (int)$filters['bedrooms'];
            }
        }
        
        // Furnishing status
        if (!empty($filters['furnishing'])) {
            $where[] = "p.furnishing_status = ?";
            $params[] = $filters['furnishing'];
        }
        
        // Amenities filter
        if (!empty($filters['amenities']) && is_array($filters['amenities'])) {
            foreach ($filters['amenities'] as $amenity) {
                $where[] = "JSON_CONTAINS(p.amenities, ?)";
                $params[] = json_encode($amenity);
            }
        }
        
        // Possession date
        if (!empty($filters['possession'])) {
            switch ($filters['possession']) {
                case 'ready':
                    $where[] = "p.possession_date <= CURDATE()";
                    break;
                case '1year':
                    $where[] = "p.possession_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 YEAR)";
                    break;
                case '2year':
                    $where[] = "p.possession_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 1 YEAR) AND DATE_ADD(CURDATE(), INTERVAL 2 YEAR)";
                    break;
            }
        }
        
        // Posted by
        if (!empty($filters['posted_by'])) {
            $where[] = "p.posted_by = ?";
            $params[] = $filters['posted_by'];
        }
        
        // Property age
        if (!empty($filters['property_age'])) {
            $currentYear = date('Y');
            switch ($filters['property_age']) {
                case 'new':
                    $where[] = "p.year_built IS NULL OR p.year_built >= ?";
                    $params[] = $currentYear - 1;
                    break;
                case '0-5':
                    $where[] = "p.year_built BETWEEN ? AND ?";
                    $params[] = $currentYear - 5;
                    $params[] = $currentYear;
                    break;
                case '5-10':
                    $where[] = "p.year_built BETWEEN ? AND ?";
                    $params[] = $currentYear - 10;
                    $params[] = $currentYear - 5;
                    break;
                case '10+':
                    $where[] = "p.year_built <= ?";
                    $params[] = $currentYear - 10;
                    break;
            }
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM properties p WHERE {$whereClause}";
        $countStmt = $this->database->prepare($countSql);
        $countStmt->execute($params);
        $totalCount = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];
        
        // Sort order
        $orderBy = match($options['sort'] ?? 'relevance') {
            'price_low' => 'p.price ASC',
            'price_high' => 'p.price DESC',
            'area' => 'p.area DESC',
            'newest' => 'p.created_at DESC',
            'popular' => 'p.views DESC',
            default => 'p.created_at DESC'
        };
        
        // Main query
        $sql = "SELECT 
            p.*,
            pi.image_path as primary_image,
            (SELECT COUNT(*) FROM property_images WHERE property_id = p.id) as total_images,
            (SELECT AVG(rating) FROM property_reviews WHERE property_id = p.id) as avg_rating,
            (SELECT COUNT(*) FROM property_reviews WHERE property_id = p.id) as review_count
            FROM properties p
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT {$limit} OFFSET {$offset}";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Calculate search duration
        $searchDuration = round((microtime(true) - $startTime) * 1000);
        
        $result = [
            'properties' => $properties,
            'total' => (int)$totalCount,
            'page' => $page,
            'per_page' => $limit,
            'total_pages' => ceil($totalCount / $limit),
            'search_duration_ms' => $searchDuration,
            'filters_applied' => count(array_filter($filters)),
            'facets' => $this->getFacets($filters)
        ];
        
        // Cache results
        $this->cache->set($cacheKey, $result, $this->cacheTtl);
        
        return $result;
    }
    
    /**
     * Get search facets (aggregated filter options)
     */
    private function getFacets(array $currentFilters): array
    {
        $facets = [];
        
        // Property type counts
        $typeSql = "SELECT type, COUNT(*) as count FROM properties WHERE status = 'available' GROUP BY type";
        $facets['types'] = $this->database->query($typeSql)->fetchAll(\PDO::FETCH_ASSOC);
        
        // Price ranges
        $priceSql = "SELECT 
            SUM(CASE WHEN price <= 500000 THEN 1 ELSE 0 END) as 'under_5l',
            SUM(CASE WHEN price BETWEEN 500000 AND 1000000 THEN 1 ELSE 0 END) as '5l_to_10l',
            SUM(CASE WHEN price BETWEEN 1000000 AND 2500000 THEN 1 ELSE 0 END) as '10l_to_25l',
            SUM(CASE WHEN price BETWEEN 2500000 AND 5000000 THEN 1 ELSE 0 END) as '25l_to_50l',
            SUM(CASE WHEN price > 5000000 THEN 1 ELSE 0 END) as 'above_50l'
            FROM properties WHERE status = 'available'";
        $facets['price_ranges'] = $this->database->query($priceSql)->fetch(\PDO::FETCH_ASSOC);
        
        // Cities
        $citySql = "SELECT city, COUNT(*) as count FROM properties WHERE status = 'available' AND city IS NOT NULL GROUP BY city ORDER BY count DESC LIMIT 20";
        $facets['cities'] = $this->database->query($citySql)->fetchAll(\PDO::FETCH_ASSOC);
        
        // Furnishing options
        $furnishSql = "SELECT furnishing_status, COUNT(*) as count FROM properties WHERE status = 'available' AND furnishing_status IS NOT NULL GROUP BY furnishing_status";
        $facets['furnishing'] = $this->database->query($furnishSql)->fetchAll(\PDO::FETCH_ASSOC);
        
        return $facets;
    }
    
    /**
     * Get autocomplete suggestions
     */
    public function getSuggestions(string $query, int $limit = 10): array
    {
        $suggestions = [];
        
        // Search locations
        $locationSql = "SELECT DISTINCT location, city, 'location' as type 
            FROM properties 
            WHERE (location LIKE ? OR city LIKE ?) AND status = 'available'
            LIMIT 5";
        $stmt = $this->database->prepare($locationSql);
        $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
        $locations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Search from suggestions table
        $suggestSql = "SELECT query, suggestion_type, result_count 
            FROM search_suggestions 
            WHERE query LIKE ? 
            ORDER BY search_count DESC, result_count DESC 
            LIMIT 5";
        $stmt = $this->database->prepare($suggestSql);
        $stmt->execute(['%' . $query . '%']);
        $popular = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'locations' => $locations,
            'popular_searches' => $popular,
            'query' => $query
        ];
    }
    
    /**
     * Save search for user
     */
    public function saveSearch(int $userId, string $name, array $criteria): int
    {
        // Count current results
        $result = $this->searchProperties($criteria, ['limit' => 1]);
        $resultCount = $result['total'];
        
        $sql = "INSERT INTO saved_searches 
                (user_id, name, search_criteria, result_count) 
                VALUES (?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId, $name, json_encode($criteria), $resultCount]);
        
        return $this->database->lastInsertId();
    }
    
    /**
     * Get user's saved searches
     */
    public function getSavedSearches(int $userId): array
    {
        $sql = "SELECT * FROM saved_searches WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId]);
        
        $searches = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($searches as &$search) {
            $search['search_criteria'] = json_decode($search['search_criteria'], true);
        }
        
        return $searches;
    }
    
    /**
     * Get similar properties
     */
    public function getSimilarProperties(int $propertyId, int $limit = 6): array
    {
        // Get property details
        $sql = "SELECT * FROM properties WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$propertyId]);
        $property = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$property) {
            return [];
        }
        
        // Find similar properties
        $similarSql = "SELECT p.*, pi.image_path as primary_image,
            (SELECT AVG(rating) FROM property_reviews WHERE property_id = p.id) as avg_rating
            FROM properties p
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            WHERE p.id != ? 
            AND p.status = 'available'
            AND (p.type = ? OR p.city = ? OR p.locality = ?)
            AND ABS(p.price - ?) / ? < 0.3
            ORDER BY 
                (CASE WHEN p.type = ? THEN 100 ELSE 0 END) +
                (CASE WHEN p.city = ? THEN 50 ELSE 0 END) +
                (CASE WHEN p.locality = ? THEN 25 ELSE 0 END) DESC
            LIMIT ?";
        
        $stmt = $this->database->prepare($similarSql);
        $stmt->execute([
            $propertyId,
            $property['type'],
            $property['city'],
            $property['locality'],
            $property['price'],
            $property['price'],
            $property['type'],
            $property['city'],
            $property['locality'],
            $limit
        ]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get trending searches
     */
    public function getTrendingSearches(int $limit = 10): array
    {
        $sql = "SELECT query, suggestion_type, search_count 
            FROM search_suggestions 
            ORDER BY search_count DESC, last_searched DESC 
            LIMIT ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Index property for search
     */
    public function indexProperty(int $propertyId): void
    {
        $sql = "SELECT * FROM properties WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$propertyId]);
        $property = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$property) {
            return;
        }
        
        // Build search text
        $searchText = implode(' ', [
            $property['title'],
            $property['description'],
            $property['location'],
            $property['city'],
            $property['state'],
            $property['address'],
            $property['type']
        ]);
        
        // Extract keywords
        $keywords = $this->extractKeywords($searchText);
        
        // Insert or update index
        $indexSql = "INSERT INTO search_indices 
            (entity_type, entity_id, search_text, search_keywords, price_range_min, price_range_max, 
             area_range_min, area_range_max, location_data, attributes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            search_text = VALUES(search_text),
            search_keywords = VALUES(search_keywords),
            price_range_min = VALUES(price_range_min),
            price_range_max = VALUES(price_range_max),
            last_updated = NOW()";
        
        $stmt = $this->database->prepare($indexSql);
        $stmt->execute([
            'property',
            $propertyId,
            $searchText,
            json_encode($keywords),
            $property['price'],
            $property['price'],
            $property['area'],
            $property['area'],
            json_encode([
                'city' => $property['city'],
                'locality' => $property['locality'],
                'state' => $property['state']
            ]),
            json_encode([
                'type' => $property['type'],
                'bedrooms' => $property['bedrooms'],
                'furnishing' => $property['furnishing_status']
            ])
        ]);
    }
    
    /**
     * Extract keywords from text
     */
    private function extractKeywords(string $text): array
    {
        // Remove special characters and convert to lowercase
        $text = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', ' ', $text));
        
        // Split into words
        $words = explode(' ', $text);
        
        // Filter out common stop words
        $stopWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'a', 'an'];
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });
        
        // Return unique keywords with frequency
        $keywordFreq = array_count_values($keywords);
        arsort($keywordFreq);
        
        return array_slice($keywordFreq, 0, 50, true);
    }
    
    /**
     * Log search query
     */
    private function logSearch(string $query, array $filters): void
    {
        $sql = "INSERT INTO search_logs 
                (user_id, session_id, search_query, filters_applied, ip_address) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $_SESSION['user_id'] ?? null,
            session_id(),
            $query,
            json_encode($filters),
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
        
        // Update suggestions table
        $suggestSql = "INSERT INTO search_suggestions (query, suggestion_type) 
            VALUES (?, 'location') 
            ON DUPLICATE KEY UPDATE search_count = search_count + 1, last_searched = NOW()";
        $suggestStmt = $this->database->prepare($suggestSql);
        $suggestStmt->execute([$query]);
    }
    
    /**
     * Get search analytics
     */
    public function getSearchAnalytics(string $period = '7days'): array
    {
        $dateCondition = match($period) {
            'today' => 'DATE(created_at) = CURDATE()',
            'yesterday' => 'DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)',
            '7days' => 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
            '30days' => 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
            default => 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        };
        
        // Total searches
        $totalSql = "SELECT COUNT(*) as total FROM search_logs WHERE {$dateCondition}";
        $total = $this->database->query($totalSql)->fetch(\PDO::FETCH_ASSOC)['total'];
        
        // Unique users
        $uniqueSql = "SELECT COUNT(DISTINCT user_id) as unique_users FROM search_logs WHERE {$dateCondition}";
        $uniqueUsers = $this->database->query($uniqueSql)->fetch(\PDO::FETCH_ASSOC)['unique_users'];
        
        // Top searches
        $topSql = "SELECT search_query, COUNT(*) as count 
            FROM search_logs 
            WHERE {$dateCondition} AND search_query IS NOT NULL
            GROUP BY search_query 
            ORDER BY count DESC 
            LIMIT 10";
        $topSearches = $this->database->query($topSql)->fetchAll(\PDO::FETCH_ASSOC);
        
        // Searches with no results
        $noResultSql = "SELECT COUNT(*) as count FROM search_logs WHERE {$dateCondition} AND results_count = 0";
        $noResults = $this->database->query($noResultSql)->fetch(\PDO::FETCH_ASSOC)['count'];
        
        return [
            'period' => $period,
            'total_searches' => (int)$total,
            'unique_users' => (int)$uniqueUsers,
            'avg_searches_per_user' => $uniqueUsers > 0 ? round($total / $uniqueUsers, 2) : 0,
            'top_searches' => $topSearches,
            'no_result_searches' => (int)$noResults,
            'success_rate' => $total > 0 ? round((($total - $noResults) / $total) * 100, 2) : 100
        ];
    }
}
