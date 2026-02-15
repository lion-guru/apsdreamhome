<?php

namespace App\Services\AI;
/**
 * APS Dream Home - Phase 2: AI Integration Implementation
 * AI-Powered Property Recommendations System
 */

// AI Recommendation Engine Class
class AIPropertyRecommendationEngine {
    private $db;
    private $userPreferences;
    private $propertyData;

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        $this->loadPropertyData();
    }

    /**
     * Load all property data for AI analysis
     */
    private function loadPropertyData() {
        $sql = "
            SELECT p.*, u.uname as agent_name, c.name as city_name,
                   pt.name as property_type_name
            FROM properties p
            LEFT JOIN user u ON p.agent_id = u.uid
            LEFT JOIN cities c ON p.city_id = c.id
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            WHERE p.status = 'active'
            ORDER BY p.created_at DESC
        ";
        $this->propertyData = $this->db->fetchAll($sql);
    }

    /**
     * Generate personalized property recommendations for user
     */
    public function generateRecommendations($userId, $limit = 10) {
        // Get user preferences and behavior
        $this->userPreferences = $this->getUserPreferences($userId);

        // Calculate recommendation scores
        $recommendations = [];
        foreach ($this->propertyData as $property) {
            $score = $this->calculateRecommendationScore($property, $userId);
            if ($score > 0) {
                $recommendations[] = [
                    'property' => $property,
                    'score' => $score,
                    'reasons' => $this->getRecommendationReasons($property, $score)
                ];
            }
        }

        // Sort by score and return top recommendations
        usort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($recommendations, 0, $limit);
    }

    /**
     * Calculate recommendation score for a property
     */
    private function calculateRecommendationScore($property, $userId) {
        $score = 0;

        // Base score for active properties
        $score += 10;

        // Price preference matching
        if (!empty($this->userPreferences['price_range'])) {
            $priceRange = $this->userPreferences['price_range'];
            if ($property['price'] >= $priceRange['min'] && $property['price'] <= $priceRange['max']) {
                $score += 25;
            } elseif ($property['price'] < $priceRange['min']) {
                $score += 15; // Under budget is good
            }
        }

        // Location preference
        if (!empty($this->userPreferences['preferred_cities'])) {
            if (in_array($property['city_id'], $this->userPreferences['preferred_cities'])) {
                $score += 20;
            }
        }

        // Property type preference
        if (!empty($this->userPreferences['property_types'])) {
            if (in_array($property['property_type_id'], $this->userPreferences['property_types'])) {
                $score += 15;
            }
        }

        // User behavior analysis
        $behaviorScore = $this->analyzeUserBehavior($property, $userId);
        $score += $behaviorScore;

        // Property quality factors
        $score += $this->getPropertyQualityScore($property);

        // Market trends
        $score += $this->getMarketTrendScore($property);

        return min(100, $score); // Cap at 100
    }

    /**
     * Get user preferences from database and behavior
     */
    private function getUserPreferences($userId) {
        $preferences = [];

        // Get explicit preferences
        $sql = "SELECT * FROM user_preferences WHERE user_id = ?";
        $explicitPrefs = $this->db->fetch($sql, [$userId]);

        if ($explicitPrefs) {
            $preferences = [
                'price_range' => [
                    'min' => $explicitPrefs['min_price'],
                    'max' => $explicitPrefs['max_price']
                ],
                'preferred_cities' => json_decode($explicitPrefs['preferred_cities'] ?: '[]'),
                'property_types' => json_decode($explicitPrefs['property_types'] ?: '[]'),
                'bedrooms' => $explicitPrefs['preferred_bedrooms'],
                'bathrooms' => $explicitPrefs['preferred_bathrooms']
            ];
        }

        // Analyze implicit preferences from behavior
        $behaviorPrefs = $this->analyzeUserBehaviorPattern($userId);
        $preferences = array_merge($preferences, $behaviorPrefs);

        return $preferences;
    }

    /**
     * Analyze user behavior pattern
     */
    private function analyzeUserBehaviorPattern($userId) {
        $patterns = [];

        // Get viewed properties
        $sql = "
            SELECT p.*, COUNT(*) as view_count
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            WHERE pv.user_id = ?
            GROUP BY p.id
            ORDER BY view_count DESC
            LIMIT 10
        ";
        $viewedProperties = $this->db->fetchAll($sql, [$userId]);

        if (!empty($viewedProperties)) {
            // Analyze price patterns
            $prices = array_column($viewedProperties, 'price');
            $patterns['avg_viewed_price'] = array_sum($prices) / count($prices);

            // Analyze location patterns
            $cities = array_count_values(array_column($viewedProperties, 'city_id'));
            arsort($cities);
            $patterns['most_viewed_cities'] = array_keys($cities, 3, true); // Top 3 cities

            // Analyze property types
            $types = array_count_values(array_column($viewedProperties, 'property_type_id'));
            arsort($types);
            $patterns['most_viewed_types'] = array_keys($types, 2, true); // Top 2 types
        }

        // AI Enhancement: Analyze chat behavior if lead exists
        $chatPatterns = $this->analyzeChatBehavior($userId);
        if (!empty($chatPatterns)) {
            $patterns['chat_intent'] = $chatPatterns['intent'];
            $patterns['chat_entities'] = $chatPatterns['entities'];
            if (isset($chatPatterns['entities']['location'])) {
                $patterns['most_viewed_cities'] = array_unique(array_merge($patterns['most_viewed_cities'] ?? [], $chatPatterns['entities']['location']));
            }
        }

        return $patterns;
    }

    private function analyzeChatBehavior($userId) {
        // Try to find lead info for this user
        $sql = "
            SELECT l.ai_analysis
            FROM leads l
            JOIN users u ON l.phone = u.phone OR l.phone = u.mobile
            WHERE u.id = ? AND l.ai_analysis IS NOT NULL
            ORDER BY l.updated_at DESC LIMIT 1
        ";

        try {
            $analysis = $this->db->fetchColumn($sql, [$userId]);
            if ($analysis) {
                return json_decode($analysis, true);
            }
        } catch (Exception $e) {
            // Table might not exist or schema mismatch
        }
        return null;
    }

    /**
     * Analyze user behavior for specific property
     */
    private function analyzeUserBehavior($property, $userId) {
        $score = 0;

        // Check if user viewed similar properties
        $sql = "
            SELECT COUNT(*) as count
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            WHERE pv.user_id = ?
            AND p.city_id = ?
            AND p.property_type_id = ?
        ";
        $similarViews = $this->db->fetchColumn($sql, [$userId, $property['city_id'], $property['property_type_id']]);

        if ($similarViews > 0) {
            $score += min(10, $similarViews * 2);
        }

        // Check saved properties in same area
        $sql = "
            SELECT COUNT(*) as count
            FROM saved_properties sp
            JOIN properties p ON sp.property_id = p.id
            WHERE sp.user_id = ?
            AND p.city_id = ?
        ";
        $savedInArea = $this->db->fetchColumn($sql, [$userId, $property['city_id']]);

        if ($savedInArea > 0) {
            $score += min(15, $savedInArea * 3);
        }

        return $score;
    }

    /**
     * Calculate property quality score
     */
    private function getPropertyQualityScore($property) {
        $score = 0;

        // Image quality (number of images)
        if (!empty($property['images'])) {
            $imageCount = count(json_decode($property['images'] ?: '[]'));
            $score += min(10, $imageCount * 2);
        }

        // Description quality
        if (!empty($property['description'])) {
            $descLength = strlen($property['description']);
            if ($descLength > 500) {
                $score += 10;
            } elseif ($descLength > 200) {
                $score += 5;
            }
        }

        // Amenities
        if (!empty($property['amenities'])) {
            $amenityCount = count(json_decode($property['amenities'] ?: '[]'));
            $score += min(15, $amenityCount);
        }

        // Agent reputation
        if (!empty($property['agent_id'])) {
            $sql = "
                SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
                FROM agent_reviews
                WHERE agent_id = ?
            ";
            $agentStats = $this->db->fetch($sql, [$property['agent_id']]);

            if ($agentStats && $agentStats['review_count'] > 0) {
                $score += min(10, $agentStats['avg_rating'] * 2);
            }
        }

        return $score;
    }

    /**
     * Get market trend score
     */
    private function getMarketTrendScore($property) {
        $score = 0;

        // Recent views in the area
        $sql = "
            SELECT COUNT(*) as recent_views
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            WHERE p.city_id = ?
            AND pv.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ";
        $recentViews = $this->db->fetchColumn($sql, [$property['city_id']]);

        if ($recentViews > 10) {
            $score += 10; // Hot area
        }

        // Price trends (compare to similar properties)
        $sql = "
            SELECT AVG(price) as avg_price
            FROM properties
            WHERE city_id = ?
            AND property_type_id = ?
            AND status = 'active'
            AND id != ?
        ";
        $avgPrice = $this->db->fetchColumn($sql, [$property['city_id'], $property['property_type_id'], $property['id']]);

        if ($avgPrice > 0) {
            $priceDiff = ($property['price'] - $avgPrice) / $avgPrice;
            if ($priceDiff < -0.1) { // 10% below average
                $score += 15; // Good deal
            } elseif ($priceDiff < 0) { // Below average
                $score += 8;
            }
        }

        return $score;
    }

    /**
     * Get recommendation reasons
     */
    private function getRecommendationReasons($property, $score) {
        $reasons = [];

        if ($score >= 80) {
            $reasons[] = "Highly recommended based on your preferences";
        }

        // Price-based reasons
        if (!empty($this->userPreferences['price_range'])) {
            $priceRange = $this->userPreferences['price_range'];
            if ($property['price'] <= $priceRange['max']) {
                $reasons[] = "Within your budget";
            }
        }

        // Location-based reasons
        if (!empty($this->userPreferences['preferred_cities']) &&
            in_array($property['city_id'], $this->userPreferences['preferred_cities'])) {
            $reasons[] = "In your preferred location";
        }

        // Quality-based reasons
        if (!empty($property['images']) && count(json_decode($property['images'])) > 5) {
            $reasons[] = "Extensive photo gallery";
        }

        if (!empty($property['amenities']) && count(json_decode($property['amenities'])) > 3) {
            $reasons[] = "Great amenities";
        }

        return $reasons;
    }
}

// AI Chatbot Support Class
class AIPropertyChatbot {
    private $db;
    private $recommendationEngine;

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        $this->recommendationEngine = new AIPropertyRecommendationEngine($this->db);
    }

    /**
     * Process user chat message and generate response
     */
    public function processMessage($userId, $message) {
        $message = strtolower(trim($message));

        // Intent recognition
        $intent = $this->recognizeIntent($message);

        switch ($intent) {
            case 'property_search':
                return $this->handlePropertySearch($userId, $message);

            case 'recommendation_request':
                return $this->handleRecommendationRequest($userId);

            case 'price_inquiry':
                return $this->handlePriceInquiry($message);

            case 'location_inquiry':
                return $this->handleLocationInquiry($message);

            case 'general_help':
                return $this->handleGeneralHelp();

            default:
                return $this->handleUnknownIntent($message);
        }
    }

    /**
     * Recognize user intent from message
     */
    private function recognizeIntent($message) {
        $patterns = [
            'property_search' => ['/search/', '/find/', '/looking for/', '/show me/', '/properties/'],
            'recommendation_request' => ['/recommend/', '/suggest/', '/what do you think/', '/show me best/'],
            'price_inquiry' => ['/price/', '/cost/', '/budget/', '/affordable/', '/cheap/', '/expensive/'],
            'location_inquiry' => ['/location/', '/area/', '/city/', '/neighborhood/', '/where/'],
            'general_help' => ['/help/', '/how to/', '/what can you do/', '/assist/']
        ];

        foreach ($patterns as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $intent;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Handle property search requests
     */
    private function handlePropertySearch($userId, $message) {
        // Extract search parameters
        $params = $this->extractSearchParameters($message);

        // Build query
        $sql = "SELECT * FROM properties WHERE status = 'active'";
        $bindParams = [];

        if (!empty($params['price_min'])) {
            $sql .= " AND price >= ?";
            $bindParams[] = $params['price_min'];
        }

        if (!empty($params['price_max'])) {
            $sql .= " AND price <= ?";
            $bindParams[] = $params['price_max'];
        }

        if (!empty($params['location'])) {
            $sql .= " AND (city_name LIKE ? OR address LIKE ?)";
            $bindParams[] = "%{$params['location']}%";
            $bindParams[] = "%{$params['location']}%";
        }

        if (!empty($params['property_type'])) {
            $sql .= " AND property_type_name LIKE ?";
            $bindParams[] = "%{$params['property_type']}%";
        }

        $sql .= " LIMIT 5";

        $properties = $this->db->fetchAll($sql, $bindParams);

        if (empty($properties)) {
            return [
                'type' => 'search_response',
                'message' => "I couldn't find any properties matching your criteria. Would you like me to adjust the search parameters or show you some recommendations instead?",
                'properties' => []
            ];
        }

        return [
            'type' => 'search_response',
            'message' => "I found " . count($properties) . " properties matching your criteria:",
            'properties' => $properties
        ];
    }

    /**
     * Handle recommendation requests
     */
    private function handleRecommendationRequest($userId) {
        $recommendations = $this->recommendationEngine->generateRecommendations($userId, 5);

        if (empty($recommendations)) {
            return [
                'type' => 'recommendation_response',
                'message' => "I don't have enough information about your preferences yet. Let me show you some popular properties instead:",
                'properties' => $this->getPopularProperties(5)
            ];
        }

        $properties = array_column($recommendations, 'property');

        return [
            'type' => 'recommendation_response',
            'message' => "Based on your preferences and browsing history, here are my top recommendations:",
            'properties' => $properties,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Handle price inquiries
     */
    private function handlePriceInquiry($message) {
        // Extract price range from message
        if (preg_match('/(\d+)\s*(?:to|-)\s*(\d+)/', $message, $matches)) {
            $minPrice = (int)$matches[1];
            $maxPrice = (int)$matches[2];

            $sql = "
                SELECT COUNT(*) as count, MIN(price) as min_price, MAX(price) as max_price, AVG(price) as avg_price
                FROM properties
                WHERE status = 'active'
                AND price BETWEEN ? AND ?
            ";
            $stats = $this->db->fetch($sql, [$minPrice, $maxPrice]);

            return [
                'type' => 'price_response',
                'message' => "I found {$stats['count']} properties in your price range of $" . number_format($minPrice) . " to $" . number_format($maxPrice) . ". The average price is $" . number_format($stats['avg_price']) . ".",
                'stats' => $stats
            ];
        }

        return [
            'type' => 'price_response',
            'message' => "I can help you find properties in any price range. Please specify your budget (e.g., 'between $200,000 and $300,000')."
        ];
    }

    /**
     * Handle location inquiries
     */
    private function handleLocationInquiry($message) {
        // Extract location from message
        if (preg_match('/(?:in|at|near)\s+([a-zA-Z\s]+)/', $message, $matches)) {
            $location = trim($matches[1]);

            $sql = "
                SELECT COUNT(*) as count, AVG(price) as avg_price
                FROM properties p
                JOIN cities c ON p.city_id = c.id
                WHERE p.status = 'active'
                AND (c.name LIKE ? OR p.address LIKE ?)
            ";
            $stats = $this->db->fetch($sql, ["%$location%", "%$location%"]);

            if ($stats && $stats['count'] > 0) {
                return [
                    'type' => 'location_response',
                    'message' => "I found {$stats['count']} properties in $location. The average price is $" . number_format($stats['avg_price']) . ".",
                    'stats' => $stats
                ];
            }
        }

        return [
            'type' => 'location_response',
            'message' => "I can help you find properties in any location. Please specify the city or area you're interested in."
        ];
    }

    /**
     * Handle general help requests
     */
    private function handleGeneralHelp() {
        return [
            'type' => 'help_response',
            'message' => "I'm your AI property assistant! I can help you:\n\n• Find properties based on your criteria\n• Get personalized recommendations\n• Check prices in different areas\n• Learn about available locations\n• Answer questions about properties\n\nTry asking me things like:\n• 'Show me 3-bedroom houses under $300,000'\n• 'What do you recommend for me?'\n• 'Find properties in downtown'\n• 'What's the average price in this area?'"
        ];
    }

    /**
     * Handle unknown intents
     */
    private function handleUnknownIntent($message) {
        return [
            'type' => 'clarification_response',
            'message' => "I'm not sure I understand. Could you please rephrase your question? You can ask me about property searches, recommendations, prices, or locations. Type 'help' to see what I can do!"
        ];
    }

    /**
     * Extract search parameters from message
     */
    private function extractSearchParameters($message) {
        $params = [];

        // Extract price range
        if (preg_match('/(\d+)\s*(?:to|-)\s*(\d+)/', $message, $matches)) {
            $params['price_min'] = (int)$matches[1];
            $params['price_max'] = (int)$matches[2];
        } elseif (preg_match('/under\s+(\d+)/', $message, $matches)) {
            $params['price_max'] = (int)$matches[1];
        } elseif (preg_match('/over\s+(\d+)/', $message, $matches)) {
            $params['price_min'] = (int)$matches[1];
        }

        // Extract location
        if (preg_match('/(?:in|at|near)\s+([a-zA-Z\s]+)/', $message, $matches)) {
            $params['location'] = trim($matches[1]);
        }

        // Extract property type
        $types = ['house', 'apartment', 'condo', 'villa', 'townhouse', 'studio'];
        foreach ($types as $type) {
            if (strpos($message, $type) !== false) {
                $params['property_type'] = $type;
                break;
            }
        }

        // Extract bedrooms
        if (preg_match('/(\d+)\s*(?:bed|bedroom|br)/', $message, $matches)) {
            $params['bedrooms'] = (int)$matches[1];
        }

        return $params;
    }

    /**
     * Get popular properties
     */
    private function getPopularProperties($limit = 5) {
        $sql = "
            SELECT p.*, COUNT(pv.id) as view_count
            FROM properties p
            LEFT JOIN property_views pv ON p.id = pv.property_id
            WHERE p.status = 'active'
            GROUP BY p.id
            ORDER BY view_count DESC, p.created_at DESC
            LIMIT ?
        ";
        return $this->db->fetchAll($sql, [$limit]);
    }
}

// AI Property Valuation Class
class AIPropertyValuation {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
    }

    /**
     * Generate AI-powered property valuation
     */
    public function generateValuation($propertyId) {
        // Get property details
        $sql = "
            SELECT p.*, c.name as city_name, pt.name as property_type_name
            FROM properties p
            JOIN cities c ON p.city_id = c.id
            JOIN property_types pt ON p.property_type_id = pt.id
            WHERE p.id = ?
        ";
        $property = $this->db->fetch($sql, [$propertyId]);

        if (!$property) {
            return ['error' => 'Property not found'];
        }

        // Get comparable properties
        $comparables = $this->getComparableProperties($property);

        // Calculate base valuation
        $baseValuation = $this->calculateBaseValuation($property, $comparables);

        // Apply market adjustments
        $marketAdjustedValuation = $this->applyMarketAdjustments($baseValuation, $property);

        // Generate confidence score
        $confidenceScore = $this->calculateConfidenceScore($property, $comparables);

        // Generate valuation report
        return [
            'property_id' => $propertyId,
            'current_listed_price' => $property['price'],
            'estimated_market_value' => $marketAdjustedValuation,
            'price_difference' => $marketAdjustedValuation - $property['price'],
            'price_difference_percentage' => (($marketAdjustedValuation - $property['price']) / $property['price']) * 100,
            'confidence_score' => $confidenceScore,
            'comparable_properties' => $comparables,
            'valuation_factors' => $this->getValuationFactors($property),
            'market_analysis' => $this->getMarketAnalysis($property),
            'recommendation' => $this->getValuationRecommendation($property, $marketAdjustedValuation)
        ];
    }

    /**
     * Get comparable properties
     */
    private function getComparableProperties($property) {
        $sql = "
            SELECT *,
                   ABS(price - ?) as price_diff,
                   ABS(bedrooms - ?) as bedroom_diff,
                   ABS(bathrooms - ?) as bathroom_diff,
                   ABS(area_sqft - ?) as area_diff
            FROM properties
            WHERE city_id = ?
            AND property_type_id = ?
            AND status = 'active'
            AND id != ?
            HAVING price_diff < ? * 0.3
            AND bedroom_diff <= 2
            AND bathroom_diff <= 2
            ORDER BY (price_diff + bedroom_diff * 10000 + bathroom_diff * 5000 + area_diff * 10)
            LIMIT 10
        ";

        return $this->db->fetchAll($sql, [
            $property['price'],
            $property['bedrooms'],
            $property['bathrooms'],
            $property['area_sqft'],
            $property['city_id'],
            $property['property_type_id'],
            $property['id'],
            $property['price']
        ]);
    }

    /**
     * Calculate base valuation from comparables
     */
    private function calculateBaseValuation($property, $comparables) {
        if (empty($comparables)) {
            return $property['price']; // Fallback to listed price
        }

        $totalPrice = 0;
        $totalWeight = 0;

        foreach ($comparables as $comparable) {
            // Calculate similarity score
            $similarityScore = $this->calculateSimilarityScore($property, $comparable);

            // Weight by similarity
            $weight = $similarityScore;
            $totalPrice += $comparable['price'] * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? $totalPrice / $totalWeight : $property['price'];
    }

    /**
     * Calculate similarity score between properties
     */
    private function calculateSimilarityScore($property1, $property2) {
        $score = 1.0;

        // Price similarity
        $priceDiff = abs($property1['price'] - $property2['price']) / $property1['price'];
        $score *= (1 - min($priceDiff, 0.5)); // Cap at 50% difference

        // Bedroom similarity
        $bedroomDiff = abs($property1['bedrooms'] - $property2['bedrooms']);
        $score *= (1 - min($bedroomDiff / 4, 0.5));

        // Bathroom similarity
        $bathroomDiff = abs($property1['bathrooms'] - $property2['bathrooms']);
        $score *= (1 - min($bathroomDiff / 3, 0.5));

        // Area similarity
        if ($property1['area_sqft'] > 0) {
            $areaDiff = abs($property1['area_sqft'] - $property2['area_sqft']) / $property1['area_sqft'];
            $score *= (1 - min($areaDiff, 0.5));
        }

        return $score;
    }

    /**
     * Apply market adjustments
     */
    private function applyMarketAdjustments($baseValuation, $property) {
        $adjustment = 0;

        // Market trend adjustment
        $marketTrend = $this->getMarketTrend($property['city_id']);
        $adjustment += $marketTrend;

        // Seasonal adjustment
        $seasonalAdjustment = $this->getSeasonalAdjustment();
        $adjustment += $seasonalAdjustment;

        // Property condition adjustment (if available)
        if (!empty($property['condition_score'])) {
            $conditionAdjustment = ($property['condition_score'] - 5) * 0.02; // 1-10 scale, center at 5
            $adjustment += $conditionAdjustment;
        }

        // Apply adjustment
        return $baseValuation * (1 + $adjustment);
    }

    /**
     * Get market trend for city
     */
    private function getMarketTrend($cityId) {
        $sql = "
            SELECT
                AVG(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN price END) as recent_avg,
                AVG(CASE WHEN created_at < DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN price END) as older_avg
            FROM properties
            WHERE city_id = ?
            AND status = 'active'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ";
        $trend = $this->db->fetch($sql, [$cityId]);

        if ($trend && $trend['recent_avg'] && $trend['older_avg'] && $trend['older_avg'] > 0) {
            return ($trend['recent_avg'] - $trend['older_avg']) / $trend['older_avg'];
        }

        return 0; // No trend data
    }

    /**
     * Get seasonal adjustment
     */
    private function getSeasonalAdjustment() {
        $month = (int)date('n');

        // Simple seasonal model (can be enhanced with historical data)
        $seasonalFactors = [
            1 => -0.02,  // January - slight dip
            2 => -0.01,  // February
            3 => 0.00,   // March
            4 => 0.01,   // April - spring boost
            5 => 0.02,   // May
            6 => 0.02,   // June - summer peak
            7 => 0.01,   // July
            8 => 0.00,   // August
            9 => 0.01,   // September - fall market
            10 => 0.02,  // October
            11 => 0.00,  // November
            12 => -0.01  // December - holiday slowdown
        ];

        return $seasonalFactors[$month] ?? 0;
    }

    /**
     * Calculate confidence score
     */
    private function calculateConfidenceScore($property, $comparables) {
        $score = 50; // Base score

        // More comparables = higher confidence
        $score += min(30, count($comparables) * 3);

        // Recent comparable data = higher confidence
        $recentComparables = array_filter($comparables, function($comp) {
            return strtotime($comp['created_at']) > strtotime('-6 months');
        });
        $score += min(20, count($recentComparables) * 4);

        return min(100, $score);
    }

    /**
     * Get valuation factors
     */
    private function getValuationFactors($property) {
        return [
            'location_quality' => $this->getLocationQuality($property['city_id']),
            'property_type_demand' => $this->getPropertyTypeDemand($property['property_type_id']),
            'size_appropriateness' => $this->getSizeAppropriateness($property),
            'amenity_value' => $this->getAmenityValue($property),
            'market_activity' => $this->getMarketActivity($property['city_id'])
        ];
    }

    /**
     * Get market analysis
     */
    private function getMarketAnalysis($property) {
        $sql = "
            SELECT
                COUNT(*) as total_properties,
                AVG(price) as avg_price,
                MIN(price) as min_price,
                MAX(price) as max_price,
                AVG(DAYOFYEAR(created_at)) as avg_listing_day
            FROM properties
            WHERE city_id = ?
            AND status = 'active'
            AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ";
        return $this->db->fetch($sql, [$property['city_id']]);
    }

    /**
     * Get valuation recommendation
     */
    private function getValuationRecommendation($property, $estimatedValue) {
        $priceDifference = $estimatedValue - $property['price'];
        $percentageDiff = ($priceDifference / $property['price']) * 100;

        if (abs($percentageDiff) < 5) {
            return "The property is priced appropriately for the current market.";
        } elseif ($percentageDiff > 5) {
            return "The property may be underpriced. Consider increasing the price by approximately " . round($percentageDiff) . "% to match market value.";
        } else {
            return "The property may be overpriced. Consider reducing the price by approximately " . round(abs($percentageDiff)) . "% to align with market value.";
        }
    }

    // Helper methods for valuation factors
    private function getLocationQuality($cityId) {
        // This could be enhanced with actual location quality data
        return 0.8; // Placeholder
    }

    private function getPropertyTypeDemand($propertyTypeId) {
        // This could be enhanced with actual demand data
        return 0.7; // Placeholder
    }

    private function getSizeAppropriateness($property) {
        // This could analyze if the property size is appropriate for the market
        return 0.75; // Placeholder
    }

    private function getAmenityValue($property) {
        if (!empty($property['amenities'])) {
            $amenityCount = count(json_decode($property['amenities'] ?: '[]'));
            return min(1.0, $amenityCount / 10);
        }
        return 0.3;
    }

    private function getMarketActivity($cityId) {
        $sql = "
            SELECT COUNT(*) as recent_views
            FROM property_views pv
            JOIN properties p ON pv.property_id = p.id
            WHERE p.city_id = ?
            AND pv.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ";
        $views = $this->db->fetchColumn($sql, [$cityId]);

        return min(1.0, $views / 100); // Normalize to 0-1 scale
    }
}

// AI API Endpoints
if (isset($_GET['action'])) {
    $db = \App\Core\App::database();

    switch ($_GET['action']) {
        case 'recommendations':
            // Get AI recommendations for user
            $userId = $_GET['user_id'] ?? $_SESSION['user_id'] ?? 1;
            $engine = new AIPropertyRecommendationEngine($db);
            $recommendations = $engine->generateRecommendations($userId);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'recommendations' => $recommendations
            ]);
            break;

        case 'chatbot':
            // Process chatbot message
            $userId = $_POST['user_id'] ?? $_SESSION['user_id'] ?? 1;
            $message = $_POST['message'] ?? '';

            $chatbot = new AIPropertyChatbot($db);
            $response = $chatbot->processMessage($userId, $message);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'response' => $response
            ]);
            break;

        case 'valuation':
            // Generate AI property valuation
            $propertyId = $_GET['property_id'] ?? $_POST['property_id'] ?? 0;

            $valuation = new AIPropertyValuation($db);
            $result = $valuation->generateValuation($propertyId);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'valuation' => $result
            ]);
            break;

        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}
?>
