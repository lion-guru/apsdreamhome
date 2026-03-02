<?php

namespace App\Services;

use Exception;
use PDO;
use App\Models\Model;

class RecommendationService
{
    private $db;
    private $mlWeights = [
        'location_match' => 0.25,
        'price_match' => 0.20,
        'property_type_match' => 0.15,
        'amenities_match' => 0.10,
        'size_match' => 0.10,
        'user_behavior' => 0.15,
        'market_trends' => 0.05
    ];

    public function __construct()
    {
        $this->db = \App\Core\App::database()->getConnection();
    }

    /**
     * Generate personalized recommendations for a user
     */
    public function getRecommendations(int $userId, int $limit = 10): array
    {
        try {
            // Check cache first (logic from PropertyRecommendationEngine)
            $cached = $this->getCachedRecommendations($userId);
            if ($cached) {
                return $cached;
            }

            // Analyze behavior and preferences
            $userProfile = $this->analyzeUserBehavior($userId);
            $preferences = $this->extractPreferences($userProfile);

            // Get property candidates
            $candidates = $this->getPropertyCandidates($limit * 3);

            // Score candidates using AI logic (from AIRecommendationEngine)
            $scoredProperties = $this->scoreProperties($candidates, $userId, $preferences);

            // Sort by score
            usort($scoredProperties, function ($a, $b) {
                return ($b['ai_score'] ?? 0) <=> ($a['ai_score'] ?? 0);
            });

            $recommendations = array_slice($scoredProperties, 0, $limit);

            // Cache results
            $this->cacheRecommendations($userId, $recommendations);

            return $recommendations;
        } catch (Exception $e) {
            error_log("Recommendation Service Error: " . $e->getMessage());
            return $this->getFallbackRecommendations($userId, $limit);
        }
    }

    /**
     * Analyze user behavior (views, clicks, searches)
     */
    private function analyzeUserBehavior(int $userId): array
    {
        // Logic from PropertyRecommendationEngine::analyzeUserBehavior
        $stmt = $this->db->prepare("
            SELECT action_type, property_id, category_id, price_range 
            FROM user_interactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 100
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Score properties based on multiple factors
     */
    private function scoreProperties(array $candidates, int $userId, array $preferences): array
    {
        $scored = [];
        foreach ($candidates as $property) {
            $score = 0;
            
            // Location match (25%)
            if (isset($preferences['locations']) && in_array($property['location'], $preferences['locations'])) {
                $score += $this->mlWeights['location_match'];
            }

            // Price match (20%)
            if (isset($preferences['price_min']) && isset($preferences['price_max'])) {
                if ($property['price'] >= $preferences['price_min'] && $property['price'] <= $preferences['price_max']) {
                    $score += $this->mlWeights['price_match'];
                }
            }

            // Property type match (15%)
            if (isset($preferences['types']) && in_array($property['type'], $preferences['types'])) {
                $score += $this->mlWeights['property_type_match'];
            }

            // Market trends (5%)
            $score += ($property['popularity_index'] ?? 0) * $this->mlWeights['market_trends'];

            $property['ai_score'] = $score;
            $scored[] = $property;
        }
        return $scored;
    }

    private function getPropertyCandidates(int $limit): array
    {
        $stmt = $this->db->prepare("SELECT * FROM properties WHERE status = 'active' LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function extractPreferences(array $userProfile): array
    {
        // Simplified preference extraction logic
        return [
            'locations' => array_unique(array_column($userProfile, 'location')),
            'types' => array_unique(array_column($userProfile, 'category_id')),
            'price_min' => min(array_column($userProfile, 'price_range')) ?: 0,
            'price_max' => max(array_column($userProfile, 'price_range')) ?: 1000000,
        ];
    }

    private function getCachedRecommendations(int $userId): ?array
    {
        // Placeholder for cache logic
        return null;
    }

    private function cacheRecommendations(int $userId, array $recommendations): void
    {
        // Placeholder for cache storage logic
    }

    private function getFallbackRecommendations(int $userId, int $limit): array
    {
        return $this->getPropertyCandidates($limit);
    }

    /**
     * Get collaborative filtering recommendations
     */
    public function getCollaborativeRecommendations(int $userId, int $limit = 5): array
    {
        // Logic from AIRecommendationEngine::getCollaborativeRecommendations
        // Find similar users and their liked properties
        return [];
    }
}
