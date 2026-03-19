<?php

namespace App\Services\AI;

use App\Core\Database;
use App\Core\Config;
use Exception;

/**
 * Property Recommendation Engine - APS Dream Home
 * AI-powered property recommendation system
 */
class PropertyRecommendationEngine
{
    private $db;
    private $weights = [
        'location_match' => 0.3,
        'price_range' => 0.25,
        'property_type' => 0.2,
        'user_behavior' => 0.15,
        'similarity_score' => 0.1
    ];
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Get personalized property recommendations
     */
    public function getRecommendations(int $userId, int $limit = 10): array
    {
        try {
            // Get user preferences and behavior
            $userData = $this->getUserData($userId);
            $candidates = $this->getCandidateProperties($userId, $userData);
            
            // Score and rank properties
            $recommendations = [];
            foreach ($candidates as $property) {
                $score = $this->calculatePropertyScore($property, $userData, $userId);
                if ($score > 0.3) { // Minimum threshold
                    $recommendations[] = [
                        'property' => $property,
                        'score' => $score,
                        'reasons' => $this->getMatchReasons($property, $userData)
                    ];
                }
            }
            
            // Sort by score and limit results
            usort($recommendations, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            return array_slice($recommendations, 0, $limit);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Get user data for recommendations
     */
    private function getUserData(int $userId): array
    {
        $preferences = $this->getUserPreferences($userId);
        $behavior = $this->getUserBehavior($userId);
        
        return array_merge($this->getDefaultPreferences(), $preferences, $behavior);
    }
    
    /**
     * Get user preferences
     */
    private function getUserPreferences(int $userId): array
    {
        $sql = "SELECT * FROM user_preferences WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$result) {
            return [];
        }
        
        return [
            'preferred_locations' => json_decode($result['preferred_locations'] ?? '[]', true),
            'price_min' => (float)($result['price_min'] ?? 0),
            'price_max' => (float)($result['price_max'] ?? PHP_INT_MAX),
            'property_types' => json_decode($result['property_types'] ?? '[]', true),
            'bedrooms_min' => (int)($result['bedrooms_min'] ?? 0),
            'bathrooms_min' => (int)($result['bathrooms_min'] ?? 0),
            'amenities' => json_decode($result['amenities'] ?? '[]', true),
            'purpose' => $result['purpose'] ?? 'buy'
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
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Get recently interacted property features
        $sql = "SELECT p.location, p.property_type, p.price, p.amenities
                FROM property_favorites pf
                JOIN properties p ON pf.property_id = p.id
                WHERE pf.user_id = ?
                ORDER BY pf.created_at DESC
                LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $favorites = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
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
    private function getCandidateProperties(int $userId, array $userData): array
    {
        $sql = "SELECT p.* FROM properties p
                WHERE p.status = 'available'
                AND p.id NOT IN (
                    SELECT property_id FROM property_views WHERE user_id = ?
                )
                ORDER BY p.created_at DESC
                LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $userId]);
        $similarUsers = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        if (empty($similarUsers)) {
            return 0;
        }
        
        // Check if similar users interacted with this property
        $placeholders = implode(',', array_fill(0, count($similarUsers), '?'));
        $sql = "SELECT COUNT(*) FROM property_favorites 
                WHERE property_id = ? AND user_id IN ($placeholders)";
        
        $params = array_merge([$propertyId], $similarUsers);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $count = $stmt->fetchColumn();
        
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
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
