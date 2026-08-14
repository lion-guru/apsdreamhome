<?php

namespace App\Services\AI;

use App\Core\Database\Database;

/**
 * AI Recommendation Service
 * Personalized property recommendations using ML algorithms
 */
class AIRecommendationService
{
    private $database;
    private $userProfiles = [];
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    private function ensureTablesExist(): void
    {
        // Table initialization handled by migration script scripts/create_ai_tables.php
        return;
    }
    
    /**
     * Get personalized recommendations for user
     */
    public function getRecommendations(int $userId, string $userType = 'customer', int $limit = 10): array
    {
        try {
            // Build user profile
            $userProfile = $this->buildUserProfile($userId, $userType);
            
            // Get candidate properties
            $candidates = $this->getCandidateProperties($userProfile);
            
            // Score each property
            $scoredProperties = [];
            foreach ($candidates as $property) {
                $score = $this->calculateRecommendationScore($property, $userProfile);
                $scoredProperties[] = [
                    'property' => $property,
                    'score' => $score,
                    'reasons' => $this->getRecommendationReasons($property, $userProfile)
                ];
            }
            
            // Sort by score
            usort($scoredProperties, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });
            
            // Take top N
            $recommendations = array_slice($scoredProperties, 0, $limit);
            
            // Log recommendations
            $this->logRecommendations($userId, $userType, $recommendations);
            
            return [
                'success' => true,
                'recommendations' => $recommendations,
                'user_profile' => $userProfile,
                'total_candidates' => count($candidates),
                'algorithm' => 'collaborative_filtering_hybrid'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Build user profile from behavior and preferences
     */
    private function buildUserProfile(int $userId, string $userType): array
    {
        $db = $this->database->getConnection();
        
        try {
            // Get explicit preferences
            $prefSql = "SELECT * FROM ai_user_preferences WHERE user_id = ? AND user_type = ?";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $prefStmt = $db->prepare($prefSql);
        $prefStmt->execute([$userId, $userType]);
        $preferences = $prefStmt->fetch(\PDO::FETCH_ASSOC);
        
        // Get behavior analysis
        $behavior = $this->analyzeUserBehavior($userId);
        
        // Get similar users' preferences (collaborative filtering)
        $similarUsers = $this->findSimilarUsers($userId, $userType);
        
        return [
            'user_id' => $userId,
            'explicit_preferences' => $preferences ?: [],
            'behavior_analysis' => $behavior,
            'similar_users' => $similarUsers,
            'inferred_preferences' => $this->inferPreferences($behavior, $preferences ?: [])
        ];
    }
    
    /**
     * Analyze user behavior
     */
    private function analyzeUserBehavior(int $userId): array
    {
        $db = $this->database->getConnection();
        
        // Views in last 30 days
        $viewSql = "SELECT 
            COUNT(DISTINCT property_id) as unique_properties_viewed,
            AVG(time_spent_seconds) as avg_time_spent,
            GROUP_CONCAT(DISTINCT action_type) as actions
            FROM ai_user_behavior
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $viewStmt = $db->prepare($viewSql);
        $viewStmt->execute([$userId]);
        $viewData = $viewStmt->fetch(\PDO::FETCH_ASSOC);
        
        // Property types viewed
        $typeSql = "SELECT p.property_type, COUNT(*) as count
            FROM ai_user_behavior ub
            JOIN properties p ON ub.property_id = p.id
            WHERE ub.user_id = ? AND ub.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY p.property_type
            ORDER BY count DESC";
        
        $typeStmt = $db->prepare($typeSql);
        $typeStmt->execute([$userId]);
        $preferredTypes = $typeStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Price range viewed
        $priceSql = "SELECT 
            MIN(p.price) as min_viewed_price,
            MAX(p.price) as max_viewed_price,
            AVG(p.price) as avg_viewed_price
            FROM ai_user_behavior ub
            JOIN properties p ON ub.property_id = p.id
            WHERE ub.user_id = ? AND ub.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $priceStmt = $db->prepare($priceSql);
        $priceStmt->execute([$userId]);
        $priceRange = $priceStmt->fetch(\PDO::FETCH_ASSOC);
        
        // Locations viewed
        $locSql = "SELECT p.location, COUNT(*) as count
            FROM ai_user_behavior ub
            JOIN properties p ON ub.property_id = p.id
            WHERE ub.user_id = ? AND ub.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY p.location
            ORDER BY count DESC
            LIMIT 5";
        
        $locStmt = $db->prepare($locSql);
        $locStmt->execute([$userId]);
        $preferredLocations = $locStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'engagement_level' => $viewData['unique_properties_viewed'] > 10 ? 'high' : 
                                 ($viewData['unique_properties_viewed'] > 5 ? 'medium' : 'low'),
            'unique_properties_viewed' => $viewData['unique_properties_viewed'] ?: 0,
            'avg_time_spent' => round($viewData['avg_time_spent'] ?: 0, 2),
            'preferred_property_types' => $preferredTypes,
            'preferred_locations' => $preferredLocations,
            'price_range_viewed' => $priceRange
        ];
    }
    
    /**
     * Find similar users
     */
    private function findSimilarUsers(int $userId, string $userType): array
    {
        $db = $this->database->getConnection();
        
        // Find users with similar viewing patterns
        $sql = "SELECT DISTINCT ub2.user_id
            FROM ai_user_behavior ub1
            JOIN ai_user_behavior ub2 ON ub1.property_id = ub2.property_id
            WHERE ub1.user_id = ? 
            AND ub2.user_id != ?
            AND ub1.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND ub2.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY ub2.user_id
            HAVING COUNT(DISTINCT ub1.property_id) >= 3
            LIMIT 10";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $userId]);
        
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
    
    /**
     * Infer preferences from behavior
     */
    private function inferPreferences(array $behavior, array $explicit): array
    {
        $inferred = [];
        
        // Inferred budget
        if (!empty($behavior['price_range_viewed']['avg_viewed_price'])) {
            $avg = $behavior['price_range_viewed']['avg_viewed_price'];
            $inferred['budget_min'] = $avg * 0.7;
            $inferred['budget_max'] = $avg * 1.3;
        }
        
        // Inferred property types
        if (!empty($behavior['preferred_property_types'])) {
            $inferred['property_types'] = array_column($behavior['preferred_property_types'], 'property_type');
        }
        
        // Inferred locations
        if (!empty($behavior['preferred_locations'])) {
            $inferred['locations'] = array_column($behavior['preferred_locations'], 'location');
        }
        
        // Urgency based on engagement
        if ($behavior['engagement_level'] === 'high') {
            $inferred['urgency'] = 'high';
        }
        
        return $inferred;
    }
    
    /**
     * Get candidate properties
     */
    private function getCandidateProperties(array $userProfile): array
    {
        $db = $this->database->getConnection();
        
        $inferred = $userProfile['inferred_preferences'];
        $explicit = $userProfile['explicit_preferences'];
        
        // Build query
        $where = ['p.status = ?'];
        $params = ['available'];
        
        // Location filter
        $locations = [];
        if (!empty($inferred['locations'])) {
            $locations = $inferred['locations'];
        } elseif (!empty($explicit['preferred_locations'])) {
            $locations = json_decode($explicit['preferred_locations'], true) ?: [];
        }
        
        if (!empty($locations)) {
            $placeholders = array_fill(0, count($locations), '?');
            $where[] = "(p.location IN (" . implode(',', $placeholders) . ") OR p.city IN (" . implode(',', $placeholders) . "))";
            $params = array_merge($params, $locations, $locations);
        }
        
        // Property type filter
        $types = [];
        if (!empty($inferred['property_types'])) {
            $types = $inferred['property_types'];
        } elseif (!empty($explicit['preferred_property_types'])) {
            $types = json_decode($explicit['preferred_property_types'], true) ?: [];
        }
        
        if (!empty($types)) {
            $placeholders = array_fill(0, count($types), '?');
            $where[] = "p.property_type IN (" . implode(',', $placeholders) . ")";
            $params = array_merge($params, $types);
        }
        
        // Budget filter
        $budgetMin = $explicit['budget_min'] ?? $inferred['budget_min'] ?? 0;
        $budgetMax = $explicit['budget_max'] ?? $inferred['budget_max'] ?? PHP_INT_MAX;
        
        if ($budgetMin > 0) {
            $where[] = "p.price >= ?";
            $params[] = $budgetMin * 0.8; // 20% below min
        }
        if ($budgetMax < PHP_INT_MAX) {
            $where[] = "p.price <= ?";
            $params[] = $budgetMax * 1.2; // 20% above max
        }
        
        // Exclude already viewed
        $where[] = "p.id NOT IN (SELECT property_id FROM ai_user_behavior WHERE user_id = ? AND action_type = 'view')";
        $params[] = $userProfile['user_id'];
        
        $sql = "SELECT p.*, 
            (SELECT AVG(rating) FROM property_reviews WHERE property_id = p.id) as avg_rating,
            (SELECT COUNT(*) FROM inquiries WHERE property_id = p.id) as inquiry_count
            FROM properties p
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.created_at DESC
            LIMIT 50";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Calculate recommendation score
     */
    private function calculateRecommendationScore(array $property, array $userProfile): float
    {
        $score = 50; // Base score
        $inferred = $userProfile['inferred_preferences'];
        $explicit = $userProfile['explicit_preferences'];
        
        // Location match
        if (!empty($inferred['locations'])) {
            foreach ($inferred['locations'] as $loc) {
                if (stripos($property['location'], $loc) !== false || 
                    stripos($property['city'], $loc) !== false) {
                    $score += 20;
                    break;
                }
            }
        }
        
        // Property type match
        if (!empty($inferred['property_types'])) {
            if (in_array($property['property_type'], $inferred['property_types'])) {
                $score += 15;
            }
        }
        
        // Budget fit
        $budgetMax = $explicit['budget_max'] ?? $inferred['budget_max'] ?? PHP_INT_MAX;
        if ($property['price'] <= $budgetMax) {
            $score += 10;
            if ($property['price'] <= $budgetMax * 0.9) {
                $score += 5; // Under budget bonus
            }
        }
        
        // Rating bonus
        if ($property['avg_rating'] >= 4) {
            $score += 10;
        }
        
        // Demand/popularity
        if ($property['inquiry_count'] > 5) {
            $score += 5;
        }
        
        // Freshness (new listings get boost)
        $daysListed = (new \DateTime())->diff(new \DateTime($property['created_at']))->days;
        if ($daysListed <= 7) {
            $score += 10;
        }
        
        // Collaborative filtering boost
        if ($this->isPopularWithSimilarUsers($property['id'], $userProfile['similar_users'])) {
            $score += 15;
        }
        
        return min($score, 100);
    }
    
    /**
     * Check if property is popular with similar users
     */
    private function isPopularWithSimilarUsers(int $propertyId, array $similarUsers): bool
    {
        if (empty($similarUsers)) {
            return false;
        }
        
        $db = $this->database->getConnection();
        
        $placeholders = array_fill(0, count($similarUsers), '?');
        $sql = "SELECT COUNT(*) FROM ai_user_behavior 
            WHERE property_id = ? AND user_id IN (" . implode(',', $placeholders) . ")
            AND action_type IN ('save', 'inquiry', 'booking')";
        
        $params = array_merge([$propertyId], $similarUsers);
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() >= 2;
    }
    
    /**
     * Get recommendation reasons
     */
    private function getRecommendationReasons(array $property, array $userProfile): array
    {
        $reasons = [];
        $inferred = $userProfile['inferred_preferences'];
        
        if (!empty($inferred['locations'])) {
            foreach ($inferred['locations'] as $loc) {
                if (stripos($property['location'], $loc) !== false) {
                    $reasons[] = "Popular in your preferred location: $loc";
                    break;
                }
            }
        }
        
        if (!empty($inferred['property_types']) && in_array($property['property_type'], $inferred['property_types'])) {
            $reasons[] = "Matches your preferred property type";
        }
        
        if ($property['avg_rating'] >= 4) {
            $reasons[] = "Highly rated by other users";
        }
        
        $daysListed = (new \DateTime())->diff(new \DateTime($property['created_at']))->days;
        if ($daysListed <= 7) {
            $reasons[] = "New listing - Be the first to inquire!";
        }
        
        if ($this->isPopularWithSimilarUsers($property['id'], $userProfile['similar_users'])) {
            $reasons[] = "Similar users showed interest";
        }
        
        return $reasons;
    }
    
    /**
     * Log recommendations
     */
    private function logRecommendations(int $userId, string $userType, array $recommendations): void
    {
        $db = $this->database->getConnection();
        
        try {
            $sql = "INSERT INTO ai_recommendations 
                (user_id, user_type, property_id, recommendation_score, recommendation_reason, algorithm_used)
                VALUES (?, ?, ?, ?, ?, ?)";
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        
        $stmt = $db->prepare($sql);
        
        foreach ($recommendations as $rec) {
            $stmt->execute([
                $userId,
                $userType,
                $rec['property']['id'],
                $rec['score'],
                json_encode($rec['reasons']),
                'collaborative_filtering_hybrid'
            ]);
        }
    }
    
    /**
     * Track user behavior
     */
    public function trackBehavior(int $userId, string $actionType, array $data): void
    {
        try {
            $db = $this->database->getConnection();
            
            $sql = "INSERT INTO ai_user_behavior 
                (user_id, action_type, property_id, search_keywords, filters_used, 
                 time_spent_seconds, session_id, device_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $userId,
                $actionType,
                $data['property_id'] ?? null,
                json_encode($data['search_keywords'] ?? []),
                json_encode($data['filters_used'] ?? []),
                $data['time_spent'] ?? null,
                $data['session_id'] ?? null,
                $data['device_type'] ?? null
            ]);
            
        } catch (\Exception $e) {
            // Silently fail for tracking
                    error_log("AIRecommendationService.php: " . $e->getMessage());
        }
    }
    
    /**
     * Save user preferences
     */
    public function savePreferences(int $userId, string $userType, array $preferences): array
    {
        try {
            $db = $this->database->getConnection();
            
            try {
                // Check if exists
                $checkSql = "SELECT id FROM ai_user_preferences WHERE user_id = ? AND user_type = ?";
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->execute([$userId, $userType]);
            $exists = $checkStmt->fetch();
            
            if ($exists) {
                // Update
                $sql = "UPDATE ai_user_preferences SET
                    preferred_locations = ?,
                    preferred_property_types = ?,
                    budget_min = ?,
                    budget_max = ?,
                    preferred_amenities = ?,
                    must_have_features = ?,
                    family_size = ?,
                    purpose = ?,
                    urgency_level = ?
                    WHERE user_id = ? AND user_type = ?";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    json_encode($preferences['locations'] ?? []),
                    json_encode($preferences['property_types'] ?? []),
                    $preferences['budget_min'] ?? null,
                    $preferences['budget_max'] ?? null,
                    json_encode($preferences['amenities'] ?? []),
                    json_encode($preferences['must_have'] ?? []),
                    $preferences['family_size'] ?? null,
                    $preferences['purpose'] ?? null,
                    $preferences['urgency'] ?? 'medium',
                    $userId,
                    $userType
                ]);
            } else {
                try {
                    // Insert
                    $sql = "INSERT INTO ai_user_preferences
                        (user_id, user_type, preferred_locations, preferred_property_types,
                         budget_min, budget_max, preferred_amenities, must_have_features,
                         family_size, purpose, urgency_level)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                } catch (\Throwable $e) {
                    // Gracefully handle dropped table ref
                }
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $userId,
                    $userType,
                    json_encode($preferences['locations'] ?? []),
                    json_encode($preferences['property_types'] ?? []),
                    $preferences['budget_min'] ?? null,
                    $preferences['budget_max'] ?? null,
                    json_encode($preferences['amenities'] ?? []),
                    json_encode($preferences['must_have'] ?? []),
                    $preferences['family_size'] ?? null,
                    $preferences['purpose'] ?? null,
                    $preferences['urgency'] ?? 'medium'
                ]);
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get trending properties
     */
    public function getTrendingProperties(int $limit = 10): array
    {
        try {
            $db = $this->database->getConnection();
            
            $sql = "SELECT p.*, 
                COUNT(DISTINCT ub.user_id) as unique_viewers,
                COUNT(DISTINCT CASE WHEN ub.action_type = 'inquiry' THEN ub.user_id END) as inquiries,
                (SELECT AVG(rating) FROM property_reviews WHERE property_id = p.id) as avg_rating
                FROM properties p
                LEFT JOIN ai_user_behavior ub ON p.id = ub.property_id
                WHERE p.status = 'available'
                AND ub.created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY p.id
                HAVING unique_viewers >= 5
                ORDER BY unique_viewers DESC, inquiries DESC
                LIMIT ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            return [];
        }
    }
}?>