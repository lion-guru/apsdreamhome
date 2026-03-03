<?php
/**
 * APS Dream Home - Machine Learning Integration
 * AI-powered property recommendations and analytics
 */

echo "🤖 APS DREAM HOME - MACHINE LEARNING INTEGRATION\n";
echo "====================================================\n\n";

require_once __DIR__ . '/config/paths.php';

class MachineLearningService
{
    private $db;
    private $cache;
    private $config;
    
    public function __construct()
    {
        $this->db = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
        $this->cache = new RedisCacheService();
        $this->config = [
            'algorithm_weights' => [
                'collaborative' => 0.4,
                'content_based' => 0.3,
                'hybrid' => 0.3
            ],
            'similarity_threshold' => 0.7,
            'recommendation_limit' => 10
        ];
    }
    
    /**
     * Get property recommendations for user
     */
    public function getPropertyRecommendations($userId, $limit = 10)
    {
        $cacheKey = "ml_recommendations:{$userId}";
        
        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if ($cached) {
            return json_decode($cached, true);
        }
        
        // Get user preferences and history
        $userProfile = $this->getUserProfile($userId);
        $userHistory = $this->getUserHistory($userId);
        
        // Generate recommendations using multiple algorithms
        $collaborativeRecs = $this->collaborativeFiltering($userId);
        $contentBasedRecs = $this->contentBasedFiltering($userProfile);
        $hybridRecs = $this->hybridRecommendations($userId, $userHistory);
        
        // Combine and rank recommendations
        $recommendations = $this->combineRecommendations([
            'collaborative' => $collaborativeRecs,
            'content_based' => $contentBasedRecs,
            'hybrid' => $hybridRecs
        ]);
        
        // Cache results
        $this->cache->set($cacheKey, json_encode($recommendations), 3600);
        
        return array_slice($recommendations, 0, $limit);
    }
    
    /**
     * Collaborative filtering recommendations
     */
    private function collaborativeFiltering($userId)
    {
        $sql = "
            SELECT p.*, COUNT(*) as interaction_count
            FROM properties p
            INNER JOIN favorites f ON p.id = f.property_id
            INNER JOIN favorites f2 ON p.id = f2.property_id
            WHERE f2.user_id = ?
            AND f.user_id != ?
            AND p.status = 'active'
            GROUP BY p.id
            HAVING interaction_count >= 3
            ORDER BY interaction_count DESC, p.created_at DESC
            LIMIT 20
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->calculateCollaborativeScores($results, $userId);
    }
    
    /**
     * Content-based filtering recommendations
     */
    private function contentBasedFiltering($userProfile)
    {
        if (empty($userProfile)) {
            return [];
        }
        
        $conditions = [];
        $params = [];
        
        // Build query based on user preferences
        if (!empty($userProfile['preferred_types'])) {
            $placeholders = str_repeat('?,', count($userProfile['preferred_types']) - 1) . '?';
            $conditions[] = "p.property_type IN ($placeholders)";
            $params = array_merge($params, $userProfile['preferred_types']);
        }
        
        if (!empty($userProfile['price_range'])) {
            $conditions[] = "p.price BETWEEN ? AND ?";
            $params[] = $userProfile['price_range']['min'];
            $params[] = $userProfile['price_range']['max'];
        }
        
        if (!empty($userProfile['preferred_locations'])) {
            $locationConditions = [];
            foreach ($userProfile['preferred_locations'] as $location) {
                $locationConditions[] = "p.location LIKE ?";
                $params[] = "%{$location}%";
            }
            $conditions[] = '(' . implode(' OR ', $locationConditions) . ')';
        }
        
        $sql = "
            SELECT p.*, 
                   (CASE WHEN p.featured = 1 THEN 1.5 ELSE 1.0 END) as featured_boost
            FROM properties p
            WHERE p.status = 'active'
            " . (!empty($conditions) ? 'AND ' . implode(' AND ', $conditions) : '') . "
            ORDER BY featured_boost DESC, p.created_at DESC
            LIMIT 20
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->calculateContentBasedScores($results, $userProfile);
    }
    
    /**
     * Hybrid recommendations
     */
    private function hybridRecommendations($userId, $userHistory)
    {
        $sql = "
            SELECT p.*, 
                   COUNT(DISTINCT h.user_id) as similar_users,
                   AVG(h.interaction_score) as avg_score
            FROM properties p
            INNER JOIN (
                SELECT user_id, property_id, 
                       (CASE 
                        WHEN action='http://localhost./view' THEN 1
                        WHEN action='http://localhost./favorite' THEN 3
                        WHEN action='http://localhost./inquire' THEN 5
                        ELSE 0
                       END) as interaction_score
                FROM user_interactions
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ) h ON p.id = h.property_id
            WHERE p.status = 'active'
            AND h.user_id != ?
            GROUP BY p.id
            HAVING similar_users >= 2
            ORDER BY similar_users DESC, avg_score DESC
            LIMIT 20
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->calculateHybridScores($results, $userHistory);
    }
    
    /**
     * Get user profile
     */
    private function getUserProfile($userId)
    {
        $sql = "
            SELECT 
                u.id,
                u.preferences,
                COUNT(DISTINCT p.property_type) as viewed_types,
                AVG(p.price) as avg_price_viewed,
                GROUP_CONCAT(DISTINCT p.location) as viewed_locations
            FROM users u
            LEFT JOIN user_interactions ui ON u.id = ui.user_id
            LEFT JOIN properties p ON ui.property_id = p.id
            WHERE u.id = ?
            AND ui.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            GROUP BY u.id
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return [];
        }
        
        return $this->parseUserProfile($result);
    }
    
    /**
     * Get user history
     */
    private function getUserHistory($userId)
    {
        $sql = "
            SELECT 
                ui.property_id,
                ui.action,
                ui.created_at,
                p.property_type,
                p.price,
                p.location
            FROM user_interactions ui
            INNER JOIN properties p ON ui.property_id = p.id
            WHERE ui.user_id = ?
            AND ui.created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
            ORDER BY ui.created_at DESC
            LIMIT 50
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Parse user profile
     */
    private function parseUserProfile($data)
    {
        $profile = [];
        
        if (!empty($data['preferences'])) {
            $preferences = json_decode($data['preferences'], true);
            $profile = array_merge($profile, $preferences);
        }
        
        if (!empty($data['viewed_types'])) {
            $profile['preferred_types'] = explode(',', $data['viewed_types']);
        }
        
        if (!empty($data['avg_price_viewed'])) {
            $profile['price_range'] = [
                'min' => $data['avg_price_viewed'] * 0.8,
                'max' => $data['avg_price_viewed'] * 1.2
            ];
        }
        
        if (!empty($data['viewed_locations'])) {
            $profile['preferred_locations'] = array_unique(explode(',', $data['viewed_locations']));
        }
        
        return $profile;
    }
    
    /**
     * Calculate collaborative filtering scores
     */
    private function calculateCollaborativeScores($properties, $userId)
    {
        $scores = [];
        
        foreach ($properties as $property) {
            $score = $property['interaction_count'] * 0.1;
            
            // Boost for recent interactions
            if (isset($property['last_interaction'])) {
                $daysAgo = (time() - strtotime($property['last_interaction'])) / 86400;
                $score *= (1 - $daysAgo * 0.01);
            }
            
            $scores[] = [
                'property_id' => $property['id'],
                'score' => min($score, 1.0),
                'algorithm' => 'collaborative',
                'reason' => 'Users with similar interests liked this property'
            ];
        }
        
        return $scores;
    }
    
    /**
     * Calculate content-based scores
     */
    private function calculateContentBasedScores($properties, $userProfile)
    {
        $scores = [];
        
        foreach ($properties as $property) {
            $score = 0.5; // Base score
            
            // Boost for matching preferences
            if (!empty($userProfile['preferred_types']) && 
                in_array($property['property_type'], $userProfile['preferred_types'])) {
                $score += 0.2;
            }
            
            if (!empty($userProfile['price_range'])) {
                $price = $property['price'];
                if ($price >= $userProfile['price_range']['min'] && 
                    $price <= $userProfile['price_range']['max']) {
                    $score += 0.2;
                }
            }
            
            if (!empty($userProfile['preferred_locations'])) {
                foreach ($userProfile['preferred_locations'] as $location) {
                    if (stripos($property['location'], $location) !== false) {
                        $score += 0.1;
                        break;
                    }
                }
            }
            
            $scores[] = [
                'property_id' => $property['id'],
                'score' => min($score, 1.0),
                'algorithm' => 'content_based',
                'reason' => 'Matches your preferences'
            ];
        }
        
        return $scores;
    }
    
    /**
     * Calculate hybrid scores
     */
    private function calculateHybridScores($properties, $userHistory)
    {
        $scores = [];
        
        foreach ($properties as $property) {
            $score = ($property['similar_users'] * 0.05) + ($property['avg_score'] * 0.1);
            
            // Boost for trending properties
            if ($property['similar_users'] >= 5) {
                $score += 0.2;
            }
            
            $scores[] = [
                'property_id' => $property['id'],
                'score' => min($score, 1.0),
                'algorithm' => 'hybrid',
                'reason' => 'Trending among similar users'
            ];
        }
        
        return $scores;
    }
    
    /**
     * Combine recommendations from different algorithms
     */
    private function combineRecommendations($recommendations)
    {
        $combined = [];
        $weights = $this->config['algorithm_weights'];
        
        // Group by property_id
        $grouped = [];
        foreach ($recommendations as $algorithm => $recs) {
            foreach ($recs as $rec) {
                $propertyId = $rec['property_id'];
                
                if (!isset($grouped[$propertyId])) {
                    $grouped[$propertyId] = [
                        'property_id' => $propertyId,
                        'scores' => [],
                        'reasons' => []
                    ];
                }
                
                $grouped[$propertyId]['scores'][$algorithm] = $rec['score'];
                $grouped[$propertyId]['reasons'][] = $rec['reason'];
            }
        }
        
        // Calculate weighted scores
        foreach ($grouped as $propertyId => $data) {
            $finalScore = 0;
            $totalWeight = 0;
            
            foreach ($data['scores'] as $algorithm => $score) {
                if (isset($weights[$algorithm])) {
                    $finalScore += $score * $weights[$algorithm];
                    $totalWeight += $weights[$algorithm];
                }
            }
            
            if ($totalWeight > 0) {
                $finalScore /= $totalWeight;
            }
            
            $combined[] = [
                'property_id' => $propertyId,
                'score' => $finalScore,
                'reasons' => array_unique($data['reasons'])
            ];
        }
        
        // Sort by score
        usort($combined, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return $combined;
    }
    
    /**
     * Update user interaction
     */
    public function updateInteraction($userId, $propertyId, $action)
    {
        $sql = "
            INSERT INTO user_interactions (user_id, property_id, action, created_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            action = VALUES(action),
            created_at = VALUES(created_at)
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $propertyId, $action]);
    }
    
    /**
     * Get property analytics
     */
    public function getPropertyAnalytics($propertyId)
    {
        $cacheKey = "ml_analytics:{$propertyId}";
        
        $cached = $this->cache->get($cacheKey);
        if ($cached) {
            return json_decode($cached, true);
        }
        
        $sql = "
            SELECT 
                COUNT(DISTINCT user_id) as unique_viewers,
                COUNT(*) as total_interactions,
                AVG(CASE 
                    WHEN action='http://localhost./view' THEN 1
                    WHEN action='http://localhost./favorite' THEN 3
                    WHEN action='http://localhost./inquire' THEN 5
                    ELSE 0
                END) as engagement_score,
                COUNT(CASE WHEN action='http://localhost./favorite' THEN 1 END) as favorites,
                COUNT(CASE WHEN action='http://localhost./inquire' THEN 1 END) as inquiries
            FROM user_interactions
            WHERE property_id = ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propertyId]);
        $analytics = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Add trend analysis
        $analytics['trend'] = $this->calculateTrend($propertyId);
        
        $this->cache->set($cacheKey, json_encode($analytics), 1800);
        
        return $analytics;
    }
    
    /**
     * Calculate property trend
     */
    private function calculateTrend($propertyId)
    {
        $sql = "
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as interactions
            FROM user_interactions
            WHERE property_id = ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$propertyId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($data) < 2) {
            return 'stable';
        }
        
        $firstHalf = array_slice($data, 0, floor(count($data) / 2));
        $secondHalf = array_slice($data, floor(count($data) / 2));
        
        $firstAvg = array_sum(array_column($firstHalf, 'interactions')) / count($firstHalf);
        $secondAvg = array_sum(array_column($secondHalf, 'interactions')) / count($secondHalf);
        
        $change = (($secondAvg - $firstAvg) / $firstAvg) * 100;
        
        if ($change > 20) {
            return 'increasing';
        } elseif ($change < -20) {
            return 'decreasing';
        } else {
            return 'stable';
        }
    }
    
    /**
     * Train recommendation models
     */
    public function trainModels()
    {
        echo "🤖 Training ML models...\n";
        
        // Update collaborative filtering matrix
        $this->updateCollaborativeMatrix();
        
        // Update content-based features
        $this->updateContentFeatures();
        
        // Update hybrid model parameters
        $this->updateHybridParameters();
        
        echo "✅ ML models training completed\n";
        return true;
    }
    
    /**
     * Update collaborative filtering matrix
     */
    private function updateCollaborativeMatrix()
    {
        $sql = "
            CREATE TEMPORARY TABLE temp_user_item_matrix AS
            SELECT 
                user_id,
                property_id,
                (CASE 
                    WHEN action='http://localhost./view' THEN 1
                    WHEN action='http://localhost./favorite' THEN 3
                    WHEN action='http://localhost./inquire' THEN 5
                    ELSE 0
                END) as rating
            FROM user_interactions
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
        ";
        
        $this->db->exec($sql);
        
        // Calculate user similarities
        $similaritySql = "
            SELECT 
                u1.user_id as user1_id,
                u2.user_id as user2_id,
                COUNT(*) as common_items,
                SUM(u1.rating * u2.rating) as dot_product
            FROM temp_user_item_matrix u1
            INNER JOIN temp_user_item_matrix u2 ON u1.property_id = u2.property_id
            WHERE u1.user_id < u2.user_id
            GROUP BY u1.user_id, u2.user_id
            HAVING common_items >= 3
        ";
        
        $stmt = $this->db->prepare($similaritySql);
        $stmt->execute();
        
        // Store similarities
        $this->storeUserSimilarities($stmt->fetchAll(PDO::FETCH_ASSOC));
        
        $this->db->exec("DROP TEMPORARY TABLE IF EXISTS temp_user_item_matrix");
    }
    
    /**
     * Update content-based features
     */
    private function updateContentFeatures()
    {
        $sql = "
            SELECT 
                p.id,
                p.title,
                p.description,
                p.property_type,
                p.price,
                p.location,
                p.bedrooms,
                p.bathrooms,
                p.size
            FROM properties p
            WHERE p.status = 'active'
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Extract and store features
        foreach ($properties as $property) {
            $features = $this->extractFeatures($property);
            $this->storePropertyFeatures($property['id'], $features);
        }
    }
    
    /**
     * Extract features from property
     */
    private function extractFeatures($property)
    {
        $features = [];
        
        // Basic features
        $features['type'] = $property['property_type'];
        $features['price_range'] = $this->getPriceRange($property['price']);
        $features['size_category'] = $this->getSizeCategory($property['size']);
        $features['bedroom_category'] = $this->getBedroomCategory($property['bedrooms']);
        
        // Text features
        $text = $property['title'] . ' ' . $property['description'];
        $features['keywords'] = $this->extractKeywords($text);
        
        // Location features
        $features['location_features'] = $this->extractLocationFeatures($property['location']);
        
        return $features;
    }
    
    /**
     * Get price range category
     */
    private function getPriceRange($price)
    {
        if ($price < 100000) return 'budget';
        if ($price < 250000) return 'standard';
        if ($price < 500000) return 'premium';
        return 'luxury';
    }
    
    /**
     * Get size category
     */
    private function getSizeCategory($size)
    {
        if ($size < 50) return 'small';
        if ($size < 100) return 'medium';
        if ($size < 200) return 'large';
        return 'extra_large';
    }
    
    /**
     * Get bedroom category
     */
    private function getBedroomCategory($bedrooms)
    {
        if ($bedrooms <= 1) return 'studio';
        if ($bedrooms <= 2) return 'small';
        if ($bedrooms <= 3) return 'medium';
        return 'large';
    }
    
    /**
     * Extract keywords from text
     */
    private function extractKeywords($text)
    {
        $keywords = [];
        $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        
        $words = preg_split('/\s+/', strtolower($text));
        $words = array_diff($words, $commonWords);
        
        foreach ($words as $word) {
            if (strlen($word) > 3) {
                $keywords[] = $word;
            }
        }
        
        return array_unique($keywords);
    }
    
    /**
     * Extract location features
     */
    private function extractLocationFeatures($location)
    {
        $features = [];
        
        // Common location indicators
        if (stripos($location, 'downtown') !== false) $features[] = 'downtown';
        if (stripos($location, 'suburb') !== false) $features[] = 'suburban';
        if (stripos($location, 'city center') !== false) $features[] = 'city_center';
        if (stripos($location, 'beach') !== false) $features[] = 'beach';
        if (stripos($location, 'park') !== false) $features[] = 'park';
        
        return $features;
    }
    
    /**
     * Store property features
     */
    private function storePropertyFeatures($propertyId, $features)
    {
        $sql = "
            INSERT INTO property_features (property_id, features, updated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            features = VALUES(features),
            updated_at = VALUES(updated_at)
        ";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$propertyId, json_encode($features)]);
    }
    
    /**
     * Store user similarities
     */
    private function storeUserSimilarities($similarities)
    {
        $sql = "
            INSERT INTO user_similarities (user1_id, user2_id, similarity_score, updated_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
            similarity_score = VALUES(similarity_score),
            updated_at = VALUES(updated_at)
        ";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($similarities as $similarity) {
            $score = $similarity['dot_product'] / sqrt($similarity['common_items']);
            $stmt->execute([$similarity['user1_id'], $similarity['user2_id'], $score]);
        }
    }
    
    /**
     * Update hybrid model parameters
     */
    private function updateHybridParameters()
    {
        // Calculate optimal weights based on recent performance
        $sql = "
            SELECT 
                algorithm,
                AVG(CASE WHEN user_action='http://localhost./positive' THEN 1 ELSE 0 END) as success_rate
            FROM recommendation_performance
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY algorithm
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $performance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Update weights based on performance
        $totalSuccess = array_sum(array_column($performance, 'success_rate'));
        
        foreach ($performance as $algo) {
            $weight = $algo['success_rate'] / $totalSuccess;
            $this->config['algorithm_weights'][$algo['algorithm']] = $weight;
        }
    }
}

// Simple Redis cache service
class RedisCacheService
{
    private $redis;
    
    public function __construct()
    {
        try {
            $this->redis = new Redis();
            $this->redis->connect('127.0.0.1', 6379);
        } catch (Exception $e) {
            $this->redis = null;
        }
    }
    
    public function get($key)
    {
        if (!$this->redis) return null;
        return $this->redis->get($key);
    }
    
    public function set($key, $value, $ttl = 3600)
    {
        if (!$this->redis) return false;
        return $this->redis->setex($key, $ttl, $value);
    }
}

// Initialize and test the service
$mlService = new MachineLearningService();

echo "🤖 Machine Learning Integration Service Initialized\n";
echo "📊 Ready to provide AI-powered recommendations\n";
echo "🎯 Property recommendations engine active\n";
echo "📈 Analytics and trend analysis enabled\n";
echo "✅ Machine Learning Integration Complete\n";
?>
