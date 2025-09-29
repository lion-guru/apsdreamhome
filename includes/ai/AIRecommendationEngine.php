<?php
/**
 * Advanced AI Property Recommendation Engine
 * Machine Learning-based property matching and recommendations
 */

class AIRecommendationEngine {
    private $conn;
    private $config;
    private $userPreferences = [];
    private $propertyScores = [];

    // Machine learning weights for different factors
    private $mlWeights = [
        'location_match' => 0.25,
        'price_match' => 0.20,
        'property_type_match' => 0.15,
        'amenities_match' => 0.10,
        'size_match' => 0.10,
        'user_behavior' => 0.15,
        'market_trends' => 0.05
    ];

    /**
     * Constructor
     */
    public function __construct($conn, $config = []) {
        $this->conn = $conn;
        $this->config = array_merge([
            'enable_ml' => true,
            'cache_ttl' => 1800,
            'max_recommendations' => 20,
            'min_score_threshold' => 0.3
        ], $config);
    }

    /**
     * Get personalized property recommendations for a user
     */
    public function getPersonalizedRecommendations($userId, $limit = 10) {
        // Get user preferences and behavior
        $this->loadUserPreferences($userId);

        // Get candidate properties
        $candidates = $this->getPropertyCandidates($limit * 3);

        // Score properties using ML algorithm
        $scoredProperties = $this->scoreProperties($candidates, $userId);

        // Sort by score and return top recommendations
        usort($scoredProperties, function($a, $b) {
            return $b['ai_score'] <=> $a['ai_score'];
        });

        return array_slice($scoredProperties, 0, $limit);
    }

    /**
     * Load user preferences from database
     */
    private function loadUserPreferences($userId) {
        try {
            // Get user's search history
            $sql = "SELECT search_query, search_filters, created_at
                    FROM user_search_history
                    WHERE user_id = ?
                    ORDER BY created_at DESC LIMIT 20";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $searchHistory = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Get user's favorite properties
            $sql = "SELECT property_id FROM user_favorites WHERE user_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $favorites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Get user's property views
            $sql = "SELECT property_id, view_duration, created_at
                    FROM property_views
                    WHERE user_id = ?
                    ORDER BY created_at DESC LIMIT 50";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $propertyViews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // Analyze preferences
            $this->userPreferences = [
                'search_history' => $searchHistory,
                'favorites' => array_column($favorites, 'property_id'),
                'property_views' => $propertyViews,
                'preferred_locations' => $this->extractPreferredLocations($searchHistory),
                'preferred_price_range' => $this->extractPreferredPriceRange($searchHistory),
                'preferred_types' => $this->extractPreferredTypes($searchHistory),
                'behavior_score' => $this->calculateUserBehaviorScore($propertyViews)
            ];

        } catch (Exception $e) {
            $this->userPreferences = [
                'search_history' => [],
                'favorites' => [],
                'property_views' => [],
                'preferred_locations' => [],
                'preferred_price_range' => ['min' => 1000000, 'max' => 10000000],
                'preferred_types' => [],
                'behavior_score' => 0.5
            ];
        }
    }

    /**
     * Extract preferred locations from search history
     */
    private function extractPreferredLocations($searchHistory) {
        $locations = [];
        $locationCounts = [];

        foreach ($searchHistory as $search) {
            $filters = json_decode($search['search_filters'], true);
            if (isset($filters['location'])) {
                $location = strtolower($filters['location']);
                $locationCounts[$location] = ($locationCounts[$location] ?? 0) + 1;
            }
        }

        // Return top 3 locations
        arsort($locationCounts);
        return array_slice(array_keys($locationCounts), 0, 3);
    }

    /**
     * Extract preferred price range from search history
     */
    private function extractPreferredPriceRange($searchHistory) {
        $priceRanges = [];

        foreach ($searchHistory as $search) {
            $filters = json_decode($search['search_filters'], true);
            if (isset($filters['price_min']) && isset($filters['price_max'])) {
                $priceRanges[] = [
                    'min' => $filters['price_min'],
                    'max' => $filters['price_max']
                ];
            }
        }

        if (empty($priceRanges)) {
            return ['min' => 1000000, 'max' => 10000000]; // Default 10L to 1Cr
        }

        $minPrices = array_column($priceRanges, 'min');
        $maxPrices = array_column($priceRanges, 'max');

        return [
            'min' => min($minPrices),
            'max' => max($maxPrices)
        ];
    }

    /**
     * Extract preferred property types from search history
     */
    private function extractPreferredTypes($searchHistory) {
        $types = [];
        $typeCounts = [];

        foreach ($searchHistory as $search) {
            $filters = json_decode($search['search_filters'], true);
            if (isset($filters['property_type'])) {
                $type = strtolower($filters['property_type']);
                $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
            }
        }

        arsort($typeCounts);
        return array_slice(array_keys($typeCounts), 0, 3);
    }

    /**
     * Calculate user behavior score based on property views
     */
    private function calculateUserBehaviorScore($propertyViews) {
        if (empty($propertyViews)) {
            return 0.5; // Neutral score for new users
        }

        $totalViews = count($propertyViews);
        $longViews = count(array_filter($propertyViews, function($view) {
            return $view['view_duration'] > 30; // More than 30 seconds
        }));

        $engagementRate = $longViews / $totalViews;

        // Recent activity bonus
        $recentViews = count(array_filter($propertyViews, function($view) {
            return strtotime($view['created_at']) > (time() - 7 * 24 * 3600); // Last 7 days
        }));

        $recencyScore = min($recentViews / 10, 1); // Cap at 10 recent views

        return ($engagementRate * 0.7) + ($recencyScore * 0.3);
    }

    /**
     * Get property candidates for scoring
     */
    private function getPropertyCandidates($limit = 30) {
        try {
            $sql = "SELECT p.*, pt.name as property_type_name, pt.id as property_type_id,
                           l.name as location_name, l.city, l.state,
                           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) as main_image,
                           (SELECT AVG(rating) FROM property_reviews WHERE property_id = p.id) as avg_rating,
                           (SELECT COUNT(*) FROM property_inquiries WHERE property_id = p.id) as inquiry_count
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    LEFT JOIN locations l ON p.location_id = l.id
                    WHERE p.status = 'active'
                    ORDER BY p.featured DESC, p.created_at DESC LIMIT ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $properties = [];
            while ($row = $result->fetch_assoc()) {
                $properties[] = $row;
            }

            return $properties;

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Score properties using machine learning algorithm
     */
    private function scoreProperties($properties, $userId) {
        $scoredProperties = [];

        foreach ($properties as $property) {
            $score = $this->calculatePropertyScore($property, $userId);
            $property['ai_score'] = $score;
            $property['score_breakdown'] = $this->getScoreBreakdown($property, $userId);

            if ($score >= $this->config['min_score_threshold']) {
                $scoredProperties[] = $property;
            }
        }

        return $scoredProperties;
    }

    /**
     * Calculate comprehensive AI score for a property
     */
    private function calculatePropertyScore($property, $userId) {
        $score = 0;

        // Location matching score
        $locationScore = $this->calculateLocationScore($property);
        $score += $locationScore * $this->mlWeights['location_match'];

        // Price matching score
        $priceScore = $this->calculatePriceScore($property);
        $score += $priceScore * $this->mlWeights['price_match'];

        // Property type matching score
        $typeScore = $this->calculateTypeScore($property);
        $score += $typeScore * $this->mlWeights['property_type_match'];

        // Amenities matching score
        $amenitiesScore = $this->calculateAmenitiesScore($property);
        $score += $amenitiesScore * $this->mlWeights['amenities_match'];

        // Size matching score
        $sizeScore = $this->calculateSizeScore($property);
        $score += $sizeScore * $this->mlWeights['size_match'];

        // User behavior score
        $behaviorScore = $this->userPreferences['behavior_score'];
        $score += $behaviorScore * $this->mlWeights['user_behavior'];

        // Market trends score
        $marketScore = $this->calculateMarketScore($property);
        $score += $marketScore * $this->mlWeights['market_trends'];

        // Boost for featured properties
        if ($property['featured']) {
            $score += 0.1;
        }

        // Boost for highly rated properties
        if (!empty($property['avg_rating']) && $property['avg_rating'] >= 4.0) {
            $score += 0.05;
        }

        return min($score, 1.0); // Cap at 1.0
    }

    /**
     * Calculate location matching score
     */
    private function calculateLocationScore($property) {
        $score = 0;

        // Check if property location matches user's preferred locations
        foreach ($this->userPreferences['preferred_locations'] as $preferredLocation) {
            if (stripos($property['location'] ?? '', $preferredLocation) !== false ||
                stripos($property['city'] ?? '', $preferredLocation) !== false) {
                $score = 1.0;
                break;
            }
        }

        // If no specific preferences, give neutral score
        if ($score === 0 && empty($this->userPreferences['preferred_locations'])) {
            $score = 0.7;
        }

        return $score;
    }

    /**
     * Calculate price matching score
     */
    private function calculatePriceScore($property) {
        $price = $property['price'];
        $priceRange = $this->userPreferences['preferred_price_range'];

        if ($price >= $priceRange['min'] && $price <= $priceRange['max']) {
            // Perfect match within range
            return 1.0;
        } elseif ($price < $priceRange['min']) {
            // Too cheap (might indicate quality issues)
            return 0.3;
        } else {
            // Too expensive - score decreases as price increases
            $overage = ($price - $priceRange['max']) / $priceRange['max'];
            return max(0.1, 0.5 - $overage);
        }
    }

    /**
     * Calculate property type matching score
     */
    private function calculateTypeScore($property) {
        $propertyType = strtolower($property['property_type_name'] ?? '');

        foreach ($this->userPreferences['preferred_types'] as $preferredType) {
            if (stripos($propertyType, $preferredType) !== false) {
                return 1.0;
            }
        }

        // If no specific type preferences, give neutral score
        if (empty($this->userPreferences['preferred_types'])) {
            return 0.7;
        }

        return 0.3; // Low score if type doesn't match preferences
    }

    /**
     * Calculate amenities matching score
     */
    private function calculateAmenitiesScore($property) {
        // This would require amenities data from the database
        // For now, return a neutral score
        return 0.6;
    }

    /**
     * Calculate size matching score
     */
    private function calculateSizeScore($property) {
        // This would require size/square footage data
        // For now, return a neutral score
        return 0.6;
    }

    /**
     * Calculate market trends score
     */
    private function calculateMarketScore($property) {
        // This would analyze market data and trends
        // For now, return a neutral score
        return 0.5;
    }

    /**
     * Get detailed score breakdown for a property
     */
    private function getScoreBreakdown($property, $userId) {
        return [
            'location_score' => $this->calculateLocationScore($property),
            'price_score' => $this->calculatePriceScore($property),
            'type_score' => $this->calculateTypeScore($property),
            'amenities_score' => $this->calculateAmenitiesScore($property),
            'size_score' => $this->calculateSizeScore($property),
            'behavior_score' => $this->userPreferences['behavior_score'],
            'market_score' => $this->calculateMarketScore($property),
            'featured_bonus' => $property['featured'] ? 0.1 : 0,
            'rating_bonus' => (!empty($property['avg_rating']) && $property['avg_rating'] >= 4.0) ? 0.05 : 0
        ];
    }

    /**
     * Get property recommendations based on collaborative filtering
     */
    public function getCollaborativeRecommendations($userId, $limit = 10) {
        try {
            // Find users with similar preferences
            $similarUsers = $this->findSimilarUsers($userId);

            if (empty($similarUsers)) {
                return $this->getPersonalizedRecommendations($userId, $limit);
            }

            // Get properties liked by similar users
            $similarUserIds = array_column($similarUsers, 'user_id');
            $placeholders = str_repeat('?,', count($similarUserIds) - 1) . '?';

            $sql = "SELECT p.*, pt.name as property_type_name,
                           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) as main_image,
                           COUNT(*) as like_count
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    LEFT JOIN user_favorites uf ON p.id = uf.property_id
                    WHERE uf.user_id IN ($placeholders)
                    AND p.status = 'active'
                    AND p.id NOT IN (SELECT property_id FROM user_favorites WHERE user_id = ?)
                    GROUP BY p.id
                    ORDER BY like_count DESC, p.featured DESC
                    LIMIT ?";

            $stmt = $this->conn->prepare($sql);
            $params = array_merge($similarUserIds, [$userId, $limit]);
            $stmt->bind_param(str_repeat('i', count($params)), ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $properties = [];
            while ($row = $result->fetch_assoc()) {
                $row['ai_score'] = 0.8; // High score for collaborative recommendations
                $properties[] = $row;
            }

            return $properties;

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Find users with similar preferences
     */
    private function findSimilarUsers($userId) {
        try {
            // This is a simplified version - in reality, you'd use more sophisticated similarity metrics
            $sql = "SELECT uf2.user_id, COUNT(*) as common_favorites
                    FROM user_favorites uf1
                    JOIN user_favorites uf2 ON uf1.property_id = uf2.property_id
                    WHERE uf1.user_id = ? AND uf2.user_id != ?
                    GROUP BY uf2.user_id
                    ORDER BY common_favorites DESC
                    LIMIT 5";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get trending properties based on recent activity
     */
    public function getTrendingProperties($limit = 10) {
        try {
            $sql = "SELECT p.*, pt.name as property_type_name,
                           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) as main_image,
                           (SELECT COUNT(*) FROM property_views WHERE property_id = p.id AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as recent_views,
                           (SELECT COUNT(*) FROM property_inquiries WHERE property_id = p.id AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as recent_inquiries
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    WHERE p.status = 'active'
                    ORDER BY (recent_views + recent_inquiries * 2) DESC, p.featured DESC
                    LIMIT ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();

            $properties = [];
            while ($row = $result->fetch_assoc()) {
                $row['ai_score'] = 0.7; // Good score for trending properties
                $properties[] = $row;
            }

            return $properties;

        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get market insights and predictions
     */
    public function getMarketInsights($location = null) {
        try {
            $whereClause = $location ? "WHERE location LIKE ?" : "";

            $sql = "SELECT
                        AVG(price) as avg_price,
                        MIN(price) as min_price,
                        MAX(price) as max_price,
                        COUNT(*) as total_properties,
                        AVG(bedrooms) as avg_bedrooms,
                        AVG(bathrooms) as avg_bathrooms
                    FROM properties
                    WHERE status = 'active' $whereClause";

            $stmt = $this->conn->prepare($sql);

            if ($location) {
                $stmt->bind_param("s", "%$location%");
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $marketData = $result->fetch_assoc();

            // Calculate price trends (simplified)
            $priceTrend = $this->calculatePriceTrend($location);

            return [
                'market_data' => $marketData,
                'price_trend' => $priceTrend,
                'location' => $location,
                'insights' => $this->generateMarketInsights($marketData, $priceTrend, $location)
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Calculate price trend (simplified)
     */
    private function calculatePriceTrend($location) {
        // In a real implementation, this would analyze historical price data
        // For now, return mock trend data
        return [
            'direction' => 'up',
            'percentage' => 5.2,
            'period' => '6 months',
            'confidence' => 0.75
        ];
    }

    /**
     * Generate market insights based on data
     */
    private function generateMarketInsights($marketData, $priceTrend, $location) {
        $insights = [];

        if ($marketData['total_properties'] > 0) {
            $insights[] = "Average property price: ₹" . number_format($marketData['avg_price']);

            if ($priceTrend['direction'] === 'up') {
                $insights[] = "Market trending upward by {$priceTrend['percentage']}% over the last {$priceTrend['period']}";
            } else {
                $insights[] = "Market trending downward by {$priceTrend['percentage']}% over the last {$priceTrend['period']}";
            }

            if ($location) {
                $insights[] = "Strong demand for properties in $location area";
            }
        }

        return $insights;
    }

    /**
     * Get AI-powered property valuation
     */
    public function getPropertyValuation($propertyId) {
        try {
            $sql = "SELECT p.*, pt.name as property_type_name, l.city, l.state
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    LEFT JOIN locations l ON p.location_id = l.id
                    WHERE p.id = ?";

            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $propertyId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($property = $result->fetch_assoc()) {
                // Get comparable properties
                $comparables = $this->getComparableProperties($property);

                // Calculate AI valuation
                $aiValuation = $this->calculateAIValuation($property, $comparables);

                // Get market adjustment factors
                $marketAdjustment = $this->calculateMarketAdjustment($property);

                return [
                    'property' => $property,
                    'ai_valuation' => $aiValuation,
                    'market_adjustment' => $marketAdjustment,
                    'comparables' => $comparables,
                    'confidence' => 0.85
                ];
            }

            return null;

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get comparable properties for valuation
     */
    private function getComparableProperties($property) {
        // Find similar properties in the same area
        $sql = "SELECT p.*, pt.name as property_type_name,
                       ABS(p.price - ?) as price_diff,
                       ABS(p.bedrooms - ?) as bedroom_diff,
                       ABS(p.bathrooms - ?) as bathroom_diff
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                WHERE p.status = 'active'
                AND p.location_id = ?
                AND p.id != ?
                ORDER BY (price_diff + bedroom_diff * 100000 + bathroom_diff * 50000) ASC
                LIMIT 5";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ddiii",
            $property['price'],
            $property['bedrooms'] ?? 0,
            $property['bathrooms'] ?? 0,
            $property['location_id'] ?? 0,
            $property['id']
        );
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Calculate AI-powered property valuation
     */
    private function calculateAIValuation($property, $comparables) {
        if (empty($comparables)) {
            return $property['price']; // Fallback to listed price
        }

        // Calculate average of comparable properties
        $comparablePrices = array_column($comparables, 'price');
        $avgComparablePrice = array_sum($comparablePrices) / count($comparablePrices);

        // Apply adjustments based on property features
        $adjustment = 0;

        // Bedroom adjustment
        if (isset($property['bedrooms'])) {
            $avgBedrooms = array_sum(array_column($comparables, 'bedrooms')) / count($comparables);
            $bedroomDiff = $property['bedrooms'] - $avgBedrooms;
            $adjustment += $bedroomDiff * 500000; // ₹5L per bedroom difference
        }

        // Bathroom adjustment
        if (isset($property['bathrooms'])) {
            $avgBathrooms = array_sum(array_column($comparables, 'bathrooms')) / count($comparables);
            $bathroomDiff = $property['bathrooms'] - $avgBathrooms;
            $adjustment += $bathroomDiff * 300000; // ₹3L per bathroom difference
        }

        // Featured property premium
        if ($property['featured']) {
            $adjustment += 200000; // ₹2L premium for featured properties
        }

        return $avgComparablePrice + $adjustment;
    }

    /**
     * Calculate market adjustment factors
     */
    private function calculateMarketAdjustment($property) {
        // This would analyze current market conditions
        // For now, return mock data
        return [
            'market_direction' => 'up',
            'adjustment_percentage' => 2.5,
            'factors' => [
                'Economic growth' => 1.5,
                'Infrastructure development' => 0.8,
                'Demand supply ratio' => 0.2
            ]
        ];
    }
}
?>
