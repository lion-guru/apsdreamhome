<?php

namespace App\Services\AI;

use App\Core\Database;
use App\Models\Property;
use App\Models\User;

/**
 * AI Property Recommendation Engine
 * Uses collaborative filtering and content-based filtering
 */
class PropertyRecommendationEngine
{
    private $db;
    private $weights = [
        'location_match' => 0.25,
        'price_range' => 0.20,
        'property_type' => 0.15,
        'amenities_match' => 0.15,
        'user_behavior' => 0.15,
        'similarity_score' => 0.10
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get personalized property recommendations for user
     */
    public function getRecommendations(int $userId, int $limit = 10): array
    {
        $user = (new User())->find($userId);
        if (!$user) {
            return [];
        }

        // Get user preferences
        $preferences = $this->getUserPreferences($userId);
        
        // Get user behavior data
        $behavior = $this->getUserBehavior($userId);

        // Combine for scoring
        $scoringData = array_merge($preferences, $behavior);

        // Get candidate properties
        $candidates = $this->getCandidateProperties($userId, $scoringData);

        // Score each property
        $scored = [];
        foreach ($candidates as $property) {
            $score = $this->calculatePropertyScore($property, $scoringData, $userId);
            $scored[] = [
                'property' => $property,
                'score' => $score,
                'reasons' => $this->getMatchReasons($property, $scoringData)
            ];
        }

        // Sort by score
        usort($scored, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($scored, 0, $limit);
    }

    /**
     * Get similar properties
     */
    public function getSimilarProperties(int $propertyId, int $limit = 5): array
    {
        $property = (new Property())->find($propertyId);
        if (!$property) {
            return [];
        }

        $sql = "SELECT p.*, 
                       (CASE WHEN p.location = ? THEN 1 ELSE 0 END) as location_score,
                       (CASE WHEN p.property_type = ? THEN 1 ELSE 0 END) as type_score,
                       (1 - ABS(p.price - ?) / GREATEST(p.price, ?)) as price_score,
                       (CASE WHEN p.bedrooms = ? THEN 1 ELSE 0 END) as bedroom_score
                FROM properties p
                WHERE p.id != ? AND p.status = 'available'
                ORDER BY (location_score + type_score + price_score + bedroom_score) DESC
                LIMIT ?";

        return $this->db->query($sql, [
            $property['location'],
            $property['property_type'],
            $property['price'],
            $property['price'],
            $property['bedrooms'],
            $propertyId,
            $limit
        ])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get trending properties
     */
    public function getTrendingProperties(int $limit = 10): array
    {
        $sql = "SELECT p.*, 
                       COUNT(DISTINCT pv.id) as view_count,
                       COUNT(DISTINCT pi.id) as inquiry_count,
                       COUNT(DISTINCT pf.id) as favorite_count,
                       (COUNT(DISTINCT pv.id) * 0.3 + 
                        COUNT(DISTINCT pi.id) * 0.5 + 
                        COUNT(DISTINCT pf.id) * 0.2) as trend_score
                FROM properties p
                LEFT JOIN property_views pv ON p.id = pv.property_id 
                    AND pv.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                LEFT JOIN property_inquiries pi ON p.id = pi.property_id 
                    AND pi.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                LEFT JOIN property_favorites pf ON p.id = pf.property_id 
                    AND pf.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                WHERE p.status = 'available'
                GROUP BY p.id
                ORDER BY trend_score DESC
                LIMIT ?";

        return $this->db->query($sql, [$limit])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get user preferences from profile and history
     */
    private function getUserPreferences(int $userId): array
    {
        $sql = "SELECT * FROM user_preferences WHERE user_id = ?";
        $prefs = $this->db->query($sql, [$userId])->fetch(\PDO::FETCH_ASSOC);

        if (!$prefs) {
            // Infer from behavior
            return $this->inferPreferencesFromBehavior($userId);
        }

        return [
            'preferred_locations' => json_decode($prefs['preferred_locations'] ?? '[]', true),
            'price_min' => $prefs['price_min'] ?? 0,
            'price_max' => $prefs['price_max'] ?? PHP_INT_MAX,
            'property_types' => json_decode($prefs['property_types'] ?? '[]', true),
            'bedrooms_min' => $prefs['bedrooms_min'] ?? 0,
            'bathrooms_min' => $prefs['bathrooms_min'] ?? 0,
            'amenities' => json_decode($prefs['amenities'] ?? '[]', true),
            'purpose' => $prefs['purpose'] ?? 'buy'
        ];
    }

    /**
     * Infer preferences from user behavior
     */
    private function inferPreferencesFromBehavior(int $userId): array
    {
        // Analyze viewed properties
        $sql = "SELECT p.location, p.property_type, p.price, p.bedrooms, p.bathrooms, p.amenities
                FROM property_views pv
                JOIN properties p ON pv.property_id = p.id
                WHERE pv.user_id = ?
                ORDER BY pv.created_at DESC
                LIMIT 20";

        $viewed = $this->db->query($sql, [$userId])->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($viewed)) {
            return $this->getDefaultPreferences();
        }

        // Calculate most common values
        $locations = array_column($viewed, 'location');
        $types = array_column($viewed, 'property_type');
        $prices = array_column($viewed, 'price');

        return [
            'preferred_locations' => $this->getMostFrequent($locations, 3),
            'price_min' => min($prices) * 0.8,
            'price_max' => max($prices) * 1.2,
            'property_types' => $this->getMostFrequent($types, 2),
            'bedrooms_min' => min(array_column($viewed, 'bedrooms')),
            'bathrooms_min' => min(array_column($viewed, 'bathrooms')),
            'amenities' => [],
            'purpose' => 'buy'
        ];
    }

    /**
     * Get user behavior data
     */
    private function getUserBehavior(int $userId): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT pv.property_id) as total_views,
                    COUNT(DISTINCT pf.property_id) as total_favorites,
                    COUNT(DISTINCT pi.property_id) as total_inquiries
                FROM users u
                LEFT JOIN property_views pv ON u.id = pv.user_id
                LEFT JOIN property_favorites pf ON u.id = pf.user_id
                LEFT JOIN property_inquiries pi ON u.id = pi.user_id
                WHERE u.id = ?";

        $stats = $this->db->query($sql, [$userId])->fetch(\PDO::FETCH_ASSOC);

        // Get recently interacted property features
        $sql = "SELECT p.location, p.property_type, p.price, p.amenities
                FROM property_favorites pf
                JOIN properties p ON pf.property_id = p.id
                WHERE pf.user_id = ?
                ORDER BY pf.created_at DESC
                LIMIT 10";

        $favorites = $this->db->query($sql, [$userId])->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'total_views' => $stats['total_views'] ?? 0,
            'total_favorites' => $stats['total_favorites'] ?? 0,
            'total_inquiries' => $stats['total_inquiries'] ?? 0,
            'favorite_locations' => array_unique(array_column($favorites, 'location')),
            'favorite_types' => array_unique(array_column($favorites, 'property_type')),
            'engagement_level' => $this->calculateEngagementLevel($stats)
        ];
    }

    /**
     * Get candidate properties for recommendation
     */
    private function getCandidateProperties(int $userId, array $scoringData): array
    {
        $sql = "SELECT p.* FROM properties p
                WHERE p.status = 'available'
                AND p.id NOT IN (
                    SELECT property_id FROM property_views WHERE user_id = ?
                )
                ORDER BY p.created_at DESC
                LIMIT 100";

        return $this->db->query($sql, [$userId])->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Calculate property match score
     */
    private function calculatePropertyScore(array $property, array $userData, int $userId): float
    {
        $score = 0;

        // Location match
        if (in_array($property['location'], $userData['preferred_locations'] ?? [])) {
            $score += $this->weights['location_match'];
        }

        // Price range match
        $price = $property['price'];
        if ($price >= ($userData['price_min'] ?? 0) && $price <= ($userData['price_max'] ?? PHP_INT_MAX)) {
            $score += $this->weights['price_range'];
        }

        // Property type match
        if (in_array($property['property_type'], $userData['property_types'] ?? [])) {
            $score += $this->weights['property_type'];
        }

        // Bedrooms match
        if ($property['bedrooms'] >= ($userData['bedrooms_min'] ?? 0)) {
            $score += 0.05;
        }

        // User behavior bonus
        if (in_array($property['location'], $userData['favorite_locations'] ?? [])) {
            $score += $this->weights['user_behavior'] * 0.5;
        }
        if (in_array($property['property_type'], $userData['favorite_types'] ?? [])) {
            $score += $this->weights['user_behavior'] * 0.5;
        }

        // Collaborative filtering boost
        $collaborativeScore = $this->getCollaborativeScore($property['id'], $userId);
        $score += $collaborativeScore * $this->weights['similarity_score'];

        return min($score, 1.0);
    }

    /**
     * Get collaborative filtering score
     */
    private function getCollaborativeScore(int $propertyId, int $userId): float
    {
        // Find users with similar behavior
        $sql = "SELECT DISTINCT pv2.user_id
                FROM property_views pv1
                JOIN property_views pv2 ON pv1.property_id = pv2.property_id 
                    AND pv1.user_id = ? AND pv2.user_id != ?
                LIMIT 50";

        $similarUsers = $this->db->query($sql, [$userId, $userId])->fetchAll(\PDO::FETCH_COLUMN);

        if (empty($similarUsers)) {
            return 0;
        }

        // Check if similar users interacted with this property
        $placeholders = implode(',', array_fill(0, count($similarUsers), '?'));
        $sql = "SELECT COUNT(*) FROM property_favorites 
                WHERE property_id = ? AND user_id IN ($placeholders)";

        $params = array_merge([$propertyId], $similarUsers);
        $count = $this->db->query($sql, $params)->fetchColumn();

        return min($count / 10, 1.0);
    }

    /**
     * Get match reasons for display
     */
    private function getMatchReasons(array $property, array $userData): array
    {
        $reasons = [];

        if (in_array($property['location'], $userData['preferred_locations'] ?? [])) {
            $reasons[] = 'Located in your preferred area';
        }

        if ($property['price'] >= ($userData['price_min'] ?? 0) && 
            $property['price'] <= ($userData['price_max'] ?? PHP_INT_MAX)) {
            $reasons[] = 'Within your budget';
        }

        if (in_array($property['property_type'], $userData['property_types'] ?? [])) {
            $reasons[] = 'Matches your preferred property type';
        }

        if ($property['bedrooms'] >= ($userData['bedrooms_min'] ?? 0)) {
            $reasons[] = 'Has enough bedrooms';
        }

        return $reasons;
    }

    /**
     * Calculate user engagement level
     */
    private function calculateEngagementLevel(array $stats): string
    {
        $total = ($stats['total_views'] ?? 0) + 
                 ($stats['total_favorites'] ?? 0) * 2 + 
                 ($stats['total_inquiries'] ?? 0) * 3;

        if ($total >= 20) return 'high';
        if ($total >= 10) return 'medium';
        return 'low';
    }

    /**
     * Get most frequent values from array
     */
    private function getMostFrequent(array $values, int $limit): array
    {
        $counts = array_count_values($values);
        arsort($counts);
        return array_slice(array_keys($counts), 0, $limit);
    }

    /**
     * Get default preferences
     */
    private function getDefaultPreferences(): array
    {
        return [
            'preferred_locations' => [],
            'price_min' => 0,
            'price_max' => PHP_INT_MAX,
            'property_types' => [],
            'bedrooms_min' => 0,
            'bathrooms_min' => 0,
            'amenities' => [],
            'purpose' => 'buy'
        ];
    }

    /**
     * Update user preferences
     */
    public function updateUserPreferences(int $userId, array $preferences): bool
    {
        $sql = "INSERT INTO user_preferences (user_id, preferred_locations, price_min, price_max, 
                                              property_types, bedrooms_min, bathrooms_min, amenities, purpose, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    preferred_locations = VALUES(preferred_locations),
                    price_min = VALUES(price_min),
                    price_max = VALUES(price_max),
                    property_types = VALUES(property_types),
                    bedrooms_min = VALUES(bedrooms_min),
                    bathrooms_min = VALUES(bathrooms_min),
                    amenities = VALUES(amenities),
                    purpose = VALUES(purpose),
                    updated_at = NOW()";

        $params = [
            $userId,
            json_encode($preferences['preferred_locations'] ?? []),
            $preferences['price_min'] ?? 0,
            $preferences['price_max'] ?? PHP_INT_MAX,
            json_encode($preferences['property_types'] ?? []),
            $preferences['bedrooms_min'] ?? 0,
            $preferences['bathrooms_min'] ?? 0,
            json_encode($preferences['amenities'] ?? []),
            $preferences['purpose'] ?? 'buy'
        ];

        return $this->db->query($sql, $params)->rowCount() > 0;
    }
}
