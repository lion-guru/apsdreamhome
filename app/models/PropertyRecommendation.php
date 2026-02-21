<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * AI Property Recommendation Engine Model
 * Implements collaborative filtering, content-based filtering, and hybrid recommendation algorithms
 */
class PropertyRecommendation extends Model
{
    protected $table = 'user_property_preferences';

    /**
     * Get personalized property recommendations for a user
     */
    public function getPersonalizedRecommendations(int $userId, int $limit = 10): array
    {
        // Check cache first
        $cached = $this->getCachedRecommendations($userId, 'personalized');
        if ($cached) {
            return array_slice($cached, 0, $limit);
        }

        // Generate new recommendations
        $recommendations = $this->generateHybridRecommendations($userId, $limit);

        // Cache the results
        $this->cacheRecommendations($userId, 'personalized', $recommendations);

        return $recommendations;
    }

    /**
     * Generate hybrid recommendations using multiple algorithms
     */
    private function generateHybridRecommendations(int $userId, int $limit): array
    {
        $algorithms = [
            'collaborative' => $this->getCollaborativeRecommendations($userId, $limit * 2),
            'content_based' => $this->getContentBasedRecommendations($userId, $limit * 2),
            'popularity' => $this->getPopularityBasedRecommendations($limit * 2)
        ];

        // Get algorithm weights from settings
        $weights = $this->getAlgorithmWeights();

        // Combine and score recommendations
        $combinedScores = [];
        foreach ($algorithms as $algorithm => $recommendations) {
            $weight = $weights[$algorithm . '_weight'] ?? 0.33;
            foreach ($recommendations as $rec) {
                $propertyId = $rec['property_id'];
                if (!isset($combinedScores[$propertyId])) {
                    $combinedScores[$propertyId] = [
                        'property_id' => $propertyId,
                        'score' => 0,
                        'algorithms' => [],
                        'property_data' => $rec
                    ];
                }
                $combinedScores[$propertyId]['score'] += ($rec['score'] ?? 1) * $weight;
                $combinedScores[$propertyId]['algorithms'][] = $algorithm;
            }
        }

        // Sort by combined score and return top recommendations
        usort($combinedScores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Filter out properties user has already viewed/contacted
        $filtered = $this->filterViewedProperties($userId, $combinedScores);

        return array_slice($filtered, 0, $limit);
    }

    /**
     * Get collaborative filtering recommendations (users who liked similar properties)
     */
    private function getCollaborativeRecommendations(int $userId, int $limit): array
    {
        $db = Database::getInstance();

        // Find similar users based on ratings and preferences
        $similarUsers = $db->query(
            "SELECT us.user_id_2 as similar_user_id, us.similarity_score
             FROM user_similarity us
             WHERE us.user_id_1 = ? AND us.similarity_score > 0.3
             ORDER BY us.similarity_score DESC
             LIMIT 10",
            [$userId]
        )->fetchAll();

        if (empty($similarUsers)) {
            return $this->getPopularityBasedRecommendations($limit);
        }

        $similarUserIds = array_column($similarUsers, 'similar_user_id');

        // Get highly rated properties by similar users that current user hasn't rated
        $placeholders = str_repeat('?,', count($similarUserIds) - 1) . '?';

        $recommendations = $db->query(
            "SELECT p.id as property_id, p.title, p.price, p.location, p.city,
                    AVG(pr.rating) as avg_rating, COUNT(pr.rating) as rating_count,
                    GROUP_CONCAT(DISTINCT pr.rating) as ratings
             FROM properties p
             LEFT JOIN property_ratings pr ON p.id = pr.property_id
             WHERE pr.user_id IN ($placeholders)
             AND pr.rating >= 4.0
             AND p.id NOT IN (
                 SELECT property_id FROM property_ratings WHERE user_id = ?
             )
             AND p.status = 'available'
             GROUP BY p.id
             ORDER BY avg_rating DESC, rating_count DESC
             LIMIT ?",
            array_merge($similarUserIds, [$userId, $limit])
        )->fetchAll();

        // Calculate collaborative scores
        foreach ($recommendations as &$rec) {
            $rec['score'] = ($rec['avg_rating'] / 5.0) * 0.7 + (min($rec['rating_count'], 10) / 10.0) * 0.3;
        }

        return $recommendations;
    }

    /**
     * Get content-based recommendations (similar properties to user's preferences/ratings)
     */
    private function getContentBasedRecommendations(int $userId, int $limit): array
    {
        $db = Database::getInstance();

        // Get user's preferences
        $preferences = $this->getUserPreferences($userId);

        // Get user's highly rated properties
        $likedProperties = $db->query(
            "SELECT property_id FROM property_ratings
             WHERE user_id = ? AND rating >= 4.0
             ORDER BY rating DESC LIMIT 5",
            [$userId]
        )->fetchAll();

        if (empty($likedProperties) && empty($preferences)) {
            return $this->getPopularityBasedRecommendations($limit);
        }

        // Build similarity query based on preferences
        $whereConditions = ["p.status = 'available'"];
        $params = [];

        if (!empty($preferences['property_type'])) {
            $whereConditions[] = "p.property_type_id = ?";
            $params[] = $preferences['property_type'];
        }

        if (!empty($preferences['min_price']) && !empty($preferences['max_price'])) {
            $whereConditions[] = "p.price BETWEEN ? AND ?";
            $params[] = $preferences['min_price'];
            $params[] = $preferences['max_price'];
        } elseif (!empty($preferences['max_price'])) {
            $whereConditions[] = "p.price <= ?";
            $params[] = $preferences['max_price'];
        }

        if (!empty($preferences['preferred_locations'])) {
            $locations = json_decode($preferences['preferred_locations'], true);
            if (!empty($locations)) {
                $locationPlaceholders = str_repeat('?,', count($locations) - 1) . '?';
                $whereConditions[] = "p.city IN ($locationPlaceholders)";
                $params = array_merge($params, $locations);
            }
        }

        if (!empty($preferences['bedrooms'])) {
            $whereConditions[] = "p.bedrooms = ?";
            $params[] = $preferences['bedrooms'];
        }

        // Exclude already viewed properties
        $viewedProperties = $db->query(
            "SELECT DISTINCT property_id FROM user_browsing_history WHERE user_id = ?",
            [$userId]
        )->fetchAll();

        if (!empty($viewedProperties)) {
            $viewedIds = array_column($viewedProperties, 'property_id');
            $placeholders = str_repeat('?,', count($viewedIds) - 1) . '?';
            $whereConditions[] = "p.id NOT IN ($placeholders)";
            $params = array_merge($params, $viewedIds);
        }

        $whereClause = implode(' AND ', $whereConditions);

        $recommendations = $db->query(
            "SELECT p.id as property_id, p.title, p.price, p.location, p.city,
                    p.bedrooms, p.bathrooms, p.area,
                    p.property_type_id, pt.type as property_type,
                    (SELECT AVG(rating) FROM property_ratings WHERE property_id = p.id) as avg_rating
             FROM properties p
             LEFT JOIN property_types pt ON p.property_type_id = pt.id
             WHERE $whereClause
             ORDER BY p.created_at DESC
             LIMIT ?",
            array_merge($params, [$limit])
        )->fetchAll();

        // Calculate content-based scores
        foreach ($recommendations as &$rec) {
            $score = 0;
            $factors = 0;

            // Price match score
            if (!empty($preferences['min_price']) && !empty($preferences['max_price'])) {
                $priceRange = $preferences['max_price'] - $preferences['min_price'];
                $priceCenter = ($preferences['max_price'] + $preferences['min_price']) / 2;
                $priceDistance = abs($rec['price'] - $priceCenter);
                $priceScore = max(0, 1 - ($priceDistance / $priceRange));
                $score += $priceScore;
                $factors++;
            }

            // Bedroom match
            if (!empty($preferences['bedrooms']) && $rec['bedrooms'] == $preferences['bedrooms']) {
                $score += 1;
                $factors++;
            }

            // Rating bonus
            if ($rec['avg_rating']) {
                $score += ($rec['avg_rating'] / 5.0) * 0.5;
                $factors++;
            }

            $rec['score'] = $factors > 0 ? $score / $factors : 0.5;
        }

        usort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $recommendations;
    }

    /**
     * Get popularity-based recommendations (most viewed/rated properties)
     */
    private function getPopularityBasedRecommendations(int $limit): array
    {
        $db = Database::getInstance();

        $recommendations = $db->query(
            "SELECT p.id as property_id, p.title, p.price, p.location, p.city,
                    COALESCE(AVG(pr.rating), 0) as avg_rating,
                    COUNT(DISTINCT pr.id) as rating_count,
                    COUNT(DISTINCT ubh.id) as view_count
             FROM properties p
             LEFT JOIN property_ratings pr ON p.id = pr.property_id
             LEFT JOIN user_browsing_history ubh ON p.id = ubh.property_id
             WHERE p.status = 'available'
             GROUP BY p.id
             ORDER BY (avg_rating * 0.4 + LEAST(view_count, 100) * 0.6) DESC
             LIMIT ?",
            [$limit]
        )->fetchAll();

        // Calculate popularity scores
        foreach ($recommendations as &$rec) {
            $ratingScore = ($rec['avg_rating'] / 5.0) * 0.6;
            $viewScore = min($rec['view_count'] / 100, 1) * 0.4;
            $rec['score'] = $ratingScore + $viewScore;
        }

        return $recommendations;
    }

    /**
     * Get similar properties to a given property
     */
    public function getSimilarProperties(int $propertyId, int $limit = 5): array
    {
        $db = Database::getInstance();

        // Check if similarity scores exist
        $similar = $db->query(
            "SELECT ps.property_id_2 as property_id, ps.similarity_score,
                    p.title, p.price, p.location, p.city, p.bedrooms, p.bathrooms, p.area
             FROM property_similarity ps
             LEFT JOIN properties p ON ps.property_id_2 = p.id
             WHERE ps.property_id_1 = ? AND p.status = 'available'
             ORDER BY ps.similarity_score DESC
             LIMIT ?",
            [$propertyId, $limit]
        )->fetchAll();

        if (!empty($similar)) {
            return $similar;
        }

        // Fallback to content-based similarity
        return $this->calculateContentSimilarity($propertyId, $limit);
    }

    /**
     * Calculate content-based similarity between properties
     */
    private function calculateContentSimilarity(int $propertyId, int $limit): array
    {
        $db = Database::getInstance();

        // Get source property details
        $sourceProperty = $db->query(
            "SELECT * FROM properties WHERE id = ?",
            [$propertyId]
        )->fetch();

        if (!$sourceProperty) return [];

        // Find similar properties based on multiple criteria
        $similar = $db->query(
            "SELECT p.id as property_id, p.title, p.price, p.location, p.city,
                    p.bedrooms, p.bathrooms, p.area, p.property_type_id,
                    1 - (ABS(p.price - ?) / GREATEST(p.price, ?)) as price_similarity,
                    CASE WHEN p.bedrooms = ? THEN 1 ELSE 0.5 END as bedroom_similarity,
                    CASE WHEN p.city = ? THEN 1 ELSE 0 END as location_similarity
             FROM properties p
             WHERE p.id != ? AND p.status = 'available'
             AND p.property_type_id = ?
             ORDER BY (price_similarity + bedroom_similarity + location_similarity) DESC
             LIMIT ?",
            [
                $sourceProperty['price'], $sourceProperty['price'],
                $sourceProperty['bedrooms'], $sourceProperty['city'],
                $propertyId, $sourceProperty['property_type_id'], $limit
            ]
        )->fetchAll();

        // Calculate overall similarity score
        foreach ($similar as &$rec) {
            $rec['similarity_score'] = (
                $rec['price_similarity'] * 0.4 +
                $rec['bedroom_similarity'] * 0.3 +
                $rec['location_similarity'] * 0.3
            );
        }

        usort($similar, function($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });

        return $similar;
    }

    /**
     * Update user preferences
     */
    public function updateUserPreferences(int $userId, array $preferences): array
    {
        $existing = $this->query(
            "SELECT id FROM user_property_preferences WHERE user_id = ?",
            [$userId]
        )->fetch();

        $data = array_merge($preferences, [
            'user_id' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($existing) {
            $this->update($existing['id'], $data);
        } else {
            $this->insert($data);
        }

        return ['success' => true, 'message' => 'Preferences updated successfully'];
    }

    /**
     * Add property rating
     */
    public function addPropertyRating(int $userId, int $propertyId, float $rating, string $review = null, array $criteria = null): array
    {
        $db = Database::getInstance();

        $ratingData = [
            'user_id' => $userId,
            'property_id' => $propertyId,
            'rating' => $rating,
            'review_text' => $review,
            'rating_criteria' => $criteria ? json_encode($criteria) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->query(
            "INSERT INTO property_ratings (user_id, property_id, rating, review_text, rating_criteria, created_at)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE rating = ?, review_text = ?, rating_criteria = ?, updated_at = ?",
            [
                $ratingData['user_id'], $ratingData['property_id'], $ratingData['rating'],
                $ratingData['review_text'], $ratingData['rating_criteria'], $ratingData['created_at'],
                $rating, $review, json_encode($criteria), date('Y-m-d H:i:s')
            ]
        );

        // Log browsing history
        $this->logUserAction($userId, $propertyId, 'rating');

        return ['success' => true, 'message' => 'Rating submitted successfully'];
    }

    /**
     * Log user browsing/action history
     */
    public function logUserAction(int $userId, int $propertyId, string $action, array $metadata = []): void
    {
        $db = Database::getInstance();

        $logData = [
            'user_id' => $userId,
            'property_id' => $propertyId,
            'session_id' => session_id(),
            'action_type' => $action,
            'duration_seconds' => $metadata['duration'] ?? null,
            'device_type' => $this->detectDeviceType(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'referrer_url' => $_SERVER['HTTP_REFERER'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db->query(
            "INSERT INTO user_browsing_history
             (user_id, property_id, session_id, action_type, duration_seconds, device_type, ip_address, user_agent, referrer_url, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($logData)
        );
    }

    /**
     * Get user preferences
     */
    private function getUserPreferences(int $userId): ?array
    {
        return $this->query(
            "SELECT * FROM user_property_preferences WHERE user_id = ?",
            [$userId]
        )->fetch();
    }

    /**
     * Get algorithm weights from settings
     */
    private function getAlgorithmWeights(): array
    {
        $db = Database::getInstance();

        $settings = $db->query(
            "SELECT setting_key, setting_value FROM recommendation_settings WHERE is_active = 1"
        )->fetchAll();

        $weights = [];
        foreach ($settings as $setting) {
            $weights[$setting['setting_key']] = (float)$setting['setting_value'];
        }

        return $weights;
    }

    /**
     * Filter out already viewed properties
     */
    private function filterViewedProperties(int $userId, array $recommendations): array
    {
        if (empty($recommendations)) return [];

        $db = Database::getInstance();

        $propertyIds = array_column($recommendations, 'property_id');
        $placeholders = str_repeat('?,', count($propertyIds) - 1) . '?';

        $viewed = $db->query(
            "SELECT DISTINCT property_id FROM user_browsing_history
             WHERE user_id = ? AND property_id IN ($placeholders)
             AND action_type IN ('view', 'contact', 'inquiry')",
            array_merge([$userId], $propertyIds)
        )->fetchAll();

        $viewedIds = array_column($viewed, 'property_id');

        return array_filter($recommendations, function($rec) use ($viewedIds) {
            return !in_array($rec['property_id'], $viewedIds);
        });
    }

    /**
     * Get cached recommendations
     */
    private function getCachedRecommendations(int $userId, string $type): ?array
    {
        $db = Database::getInstance();

        $cache = $db->query(
            "SELECT property_ids, scores FROM recommendation_cache
             WHERE user_id = ? AND recommendation_type = ? AND cache_expires_at > NOW()
             ORDER BY created_at DESC LIMIT 1",
            [$userId, $type]
        )->fetch();

        if ($cache) {
            $propertyIds = json_decode($cache['property_ids'], true);
            $scores = json_decode($cache['scores'], true);

            // Get property details
            $placeholders = str_repeat('?,', count($propertyIds) - 1) . '?';
            $properties = $db->query(
                "SELECT id as property_id, title, price, location, city, bedrooms, bathrooms, area
                 FROM properties WHERE id IN ($placeholders) AND status = 'available'",
                $propertyIds
            )->fetchAll();

            // Merge scores
            foreach ($properties as &$property) {
                $property['score'] = $scores[$property['property_id']] ?? 0.5;
            }

            usort($properties, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            return $properties;
        }

        return null;
    }

    /**
     * Cache recommendations
     */
    private function cacheRecommendations(int $userId, string $type, array $recommendations): void
    {
        $db = Database::getInstance();

        $cacheHours = $this->getAlgorithmWeights()['cache_duration_hours'] ?? 24;
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$cacheHours} hours"));

        $propertyIds = array_column($recommendations, 'property_id');
        $scores = [];
        foreach ($recommendations as $rec) {
            $scores[$rec['property_id']] = $rec['score'] ?? 0.5;
        }

        $db->query(
            "INSERT INTO recommendation_cache
             (user_id, recommendation_type, property_ids, scores, cache_expires_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $userId, $type,
                json_encode($propertyIds),
                json_encode($scores),
                $expiresAt,
                date('Y-m-d H:i:s')
            ]
        );
    }

    /**
     * Detect device type
     */
    private function detectDeviceType(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if (preg_match('/mobile/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Get trending properties
     */
    public function getTrendingProperties(int $limit = 10, int $days = 7): array
    {
        $db = Database::getInstance();

        $startDate = date('Y-m-d', strtotime("-{$days} days"));

        $trending = $db->query(
            "SELECT p.id as property_id, p.title, p.price, p.location, p.city,
                    COUNT(DISTINCT ubh.id) as view_count,
                    COUNT(DISTINCT CASE WHEN ubh.action_type = 'contact' THEN ubh.id END) as contact_count,
                    COUNT(DISTINCT CASE WHEN ubh.action_type = 'inquiry' THEN ubh.id END) as inquiry_count
             FROM properties p
             LEFT JOIN user_browsing_history ubh ON p.id = ubh.property_id
             WHERE p.status = 'available'
             AND ubh.created_at >= ?
             GROUP BY p.id
             ORDER BY (view_count * 1 + contact_count * 3 + inquiry_count * 5) DESC
             LIMIT ?",
            [$startDate, $limit]
        )->fetchAll();

        foreach ($trending as &$property) {
            $property['trend_score'] = $property['view_count'] + ($property['contact_count'] * 3) + ($property['inquiry_count'] * 5);
        }

        return $trending;
    }

    /**
     * Get location-based recommendations
     */
    public function getLocationBasedRecommendations(int $userId, int $limit = 10): array
    {
        $db = Database::getInstance();

        // Get user's preferred locations from history
        $preferredLocations = $db->query(
            "SELECT p.city, COUNT(*) as visit_count
             FROM user_browsing_history ubh
             LEFT JOIN properties p ON ubh.property_id = p.id
             WHERE ubh.user_id = ? AND ubh.action_type IN ('view', 'contact', 'inquiry')
             GROUP BY p.city
             ORDER BY visit_count DESC
             LIMIT 3",
            [$userId]
        )->fetchAll();

        if (empty($preferredLocations)) {
            return $this->getTrendingProperties($limit);
        }

        $locations = array_column($preferredLocations, 'city');
        $placeholders = str_repeat('?,', count($locations) - 1) . '?';

        $recommendations = $db->query(
            "SELECT p.id as property_id, p.title, p.price, p.location, p.city,
                    p.bedrooms, p.bathrooms, p.area
             FROM properties p
             WHERE p.city IN ($placeholders)
             AND p.status = 'available'
             AND p.id NOT IN (
                 SELECT property_id FROM user_browsing_history WHERE user_id = ?
             )
             ORDER BY p.created_at DESC
             LIMIT ?",
            array_merge($locations, [$userId, $limit])
        )->fetchAll();

        // Add location preference score
        $locationScores = array_column($preferredLocations, 'visit_count', 'city');

        foreach ($recommendations as &$rec) {
            $rec['score'] = ($locationScores[$rec['city']] ?? 1) / 10; // Normalize score
        }

        usort($recommendations, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $recommendations;
    }
}
