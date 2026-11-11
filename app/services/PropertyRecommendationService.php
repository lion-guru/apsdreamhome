<?php
/**
 * PropertyRecommendationService — "Customers like you bought..." recommendations
 */
namespace App\Services;
use App\Core\Database\Database;
class PropertyRecommendationService
{
    private $db;
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    public function getRecommendations(array $params): array
    {
        $userId = $params['user_id'] ?? null;
        $leadId = $params['lead_id'] ?? null;
        $limit = $params['limit'] ?? 10;
        $excludeBooked = $params['exclude_booked'] ?? true;
        $profile = $this->buildUserProfile($userId, $leadId);
        $recommendations = [];
        $collaborative = $this->getCollaborativeRecommendations($profile, $limit);
        $recommendations = array_merge($recommendations, $collaborative);
        $contentBased = $this->getContentBasedRecommendations($profile, $limit);
        $recommendations = array_merge($recommendations, $contentBased);
        $popular = $this->getPopularInArea($profile, $limit);
        $recommendations = array_merge($recommendations, $popular);
        $priceMatched = $this->getPriceMatchedRecommendations($profile, $limit);
        $recommendations = array_merge($recommendations, $priceMatched);
        $recommendations = $this->deduplicateAndScore($recommendations, $profile, $limit);
        if ($excludeBooked) {
            $recommendations = $this->excludeBooked($recommendations);
        }
        return [
            'success' => true,
            'recommendations' => array_slice($recommendations, 0, $limit),
            'profile_used' => $profile,
        ];
    }
    private function buildUserProfile($userId, $leadId): array
    {
        $profile = [
            'budget_min' => 0,
            'budget_max' => PHP_INT_MAX,
            'preferred_cities' => [],
            'preferred_colonies' => [],
            'property_types' => [],
            'facing_preference' => null,
            'min_area' => 0,
            'max_area' => PHP_INT_MAX,
            'viewed_properties' => [],
            'booked_properties' => [],
        ];
        if ($leadId) {
            $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);
            if ($lead) {
                $profile['budget_max'] = $lead['budget_max'] ?? $lead['budget'] ?? PHP_INT_MAX;
                $profile['budget_min'] = $lead['budget_min'] ?? max(0, ($lead['budget'] ?? 0) * 0.7);
                if (!empty($lead['city'])) $profile['preferred_cities'][] = $lead['city'];
                if (!empty($lead['preferred_location'])) $profile['preferred_cities'][] = $lead['preferred_location'];
                if (!empty($lead['property_type'])) $profile['property_types'][] = $lead['property_type'];
                if (!empty($lead['facing_preference'])) $profile['facing_preference'] = $lead['facing_preference'];
            }
        }
        if ($userId) {
            $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            if ($user) {
                $bookings = $this->db->fetchAll("
                    SELECT p.* FROM plot_bookings pb
                    JOIN plots p ON pb.plot_id = p.id
                    WHERE pb.user_id = ? AND pb.status NOT IN ('cancelled')
                ", [$userId]);
                foreach ($bookings as $b) {
                    $profile['booked_properties'][] = $b['id'];
                    if (!empty($b['colony_id'])) $profile['preferred_colonies'][] = $b['colony_id'];
                    if (!empty($b['plot_type'])) $profile['property_types'][] = $b['plot_type'];
                }
                // user_activity_log table does not exist in this database —
                // guard so profile building never fatals on it.
                try {
                    $viewed = $this->db->fetchAll("
                        SELECT DISTINCT entity_id FROM user_activity_log 
                        WHERE user_id = ? AND entity_type = 'plot' AND action = 'view'
                        ORDER BY created_at DESC LIMIT 20
                    ", [$userId]);
                    $profile['viewed_properties'] = array_column($viewed, 'entity_id');
                } catch (\Throwable $e) {
                    error_log('user_activity_log unavailable (table missing): ' . $e->getMessage());
                }
            }
        }
        // NOTE: lead_activities has no entity/type columns (real schema: activity_type,
        // description, old_value, new_value, metadata). Plot-view tracking from lead
        // activities is not recorded — viewed_properties for lead path stays empty.
        // User path above uses user_activity_log which does have entity_type/entity_id.
        if (!empty($leadId)) {
            $activities = $this->db->fetchAll("
                SELECT activity_type, old_value, new_value FROM lead_activities 
                WHERE lead_id = ?
                ORDER BY created_at DESC LIMIT 20
            ", [$leadId]);
            // Activity values don't carry plot entity ids in current writers,
            // so no viewed properties are inferred here (honest empty).
            unset($activities);
        }
        $profile['viewed_properties'] = array_unique($profile['viewed_properties']);
        $profile['booked_properties'] = array_unique($profile['booked_properties']);
        $profile['preferred_cities'] = array_unique($profile['preferred_cities']);
        $profile['preferred_colonies'] = array_unique($profile['preferred_colonies']);
        $profile['property_types'] = array_unique($profile['property_types']);
        return $profile;
    }
    private function getCollaborativeRecommendations(array $profile, int $limit): array
    {
        $recommendations = [];
        if (empty($profile['booked_properties'])) return [];
        $placeholders = implode(',', array_fill(0, count($profile['booked_properties']), '?'));
        $similarUsers = $this->db->fetchAll("
            SELECT DISTINCT pb2.user_id, COUNT(*) as common_count
            FROM plot_bookings pb1
            JOIN plot_bookings pb2 ON pb2.plot_id = pb1.plot_id AND pb2.user_id != pb1.user_id
            WHERE pb1.user_id IN (SELECT user_id FROM plot_bookings WHERE plot_id IN (" . implode(',', array_fill(0, count($profile['booked_properties']), '?')) . "))
            AND pb2.status NOT IN ('cancelled')
            GROUP BY pb2.user_id
            HAVING common_count >= 1
            ORDER BY common_count DESC
            LIMIT 10
        ", $profile['booked_properties']);
        if (empty($similarUsers)) return [];
        $similarUserIds = array_column($similarUsers, 'user_id');
        $placeholders = implode(',', array_fill(0, count($similarUserIds), '?'));
        $excludedIds = array_merge($profile['booked_properties'], $profile['viewed_properties']);
        if (empty($excludedIds)) $excludedIds = [0]; // prevent NOT IN () syntax error (0 never matches)
        $excludedPlaceholders = implode(',', array_fill(0, count($excludedIds), '?'));
        $allParams = array_merge($similarUserIds, $excludedIds);
        $recs = $this->db->fetchAll("
            SELECT DISTINCT p.*, c.name as colony_name, c.location as city,
                   p.plot_type as plot_type_name,
                   (SELECT COUNT(*) FROM plot_bookings WHERE plot_id = p.id AND status NOT IN ('cancelled')) as booking_count
            FROM plot_bookings pb
            JOIN plots p ON pb.plot_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            WHERE pb.user_id IN ($placeholders)
            AND pb.status NOT IN ('cancelled')
            AND p.status = 'available'
            AND p.id NOT IN ($excludedPlaceholders)
            ORDER BY booking_count DESC
            LIMIT ?
        ", array_merge($allParams, [$limit]));
        foreach ($recs as $rec) {
            $recs[] = array_merge($rec, [
                'reason' => 'Customers with similar preferences also booked this',
                'source' => 'collaborative',
                'confidence' => 0.85,
            ]);
        }
        return $recs;
    }
    private function getContentBasedRecommendations(array $profile, int $limit): array
    {
        $referenceIds = array_merge($profile['booked_properties'], $profile['viewed_properties']);
        if (empty($referenceIds)) return [];
        $placeholders = implode(',', array_fill(0, count($referenceIds), '?'));
        $refs = $this->db->fetchAll("
            SELECT p.*, c.location as city, p.plot_type as plot_type_name
            FROM plots p
            LEFT JOIN colonies c ON p.colony_id = c.id
            WHERE p.id IN ($placeholders)
        ", $referenceIds);
        $featureVector = $this->extractFeatureVector($refs);
        $excludedIds = array_merge($profile['booked_properties'], $profile['viewed_properties']);
        if (empty($excludedIds)) $excludedIds = [0];
        $excludedPlaceholders = implode(',', array_fill(0, count($excludedIds), '?'));
        $allParams = array_merge($excludedIds, [$limit * 2]);
        $candidates = $this->db->fetchAll("
            SELECT p.*, c.location as city, c.name as colony_name,
                   p.plot_type as plot_type_name
            FROM plots p
            LEFT JOIN colonies c ON p.colony_id = c.id
            WHERE p.status = 'available'
            AND p.id NOT IN ($excludedPlaceholders)
            ORDER BY p.created_at DESC
            LIMIT ?
        ", $allParams);
        $recommendations = [];
        foreach ($candidates as $candidate) {
            $candidateVector = $this->extractFeatureVector([$candidate]);
            $similarity = $this->cosineSimilarity($featureVector, $candidateVector);
            if ($similarity > 0.3) {
                $recommendations[] = array_merge($candidate, [
                    'reason' => 'Similar to properties you viewed',
                    'source' => 'content_based',
                    'similarity' => round($similarity * 100),
                    'confidence' => min(0.9, $similarity + 0.1),
                ]);
            }
        }
        usort($recommendations, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        return array_slice($recommendations, 0, $limit);
    }
    private function extractFeatureVector(array $properties): array
    {
        $vector = [
            'price' => 0,
            'area' => 0,
            'facing' => [],
            'colony' => [],
            'plot_type' => [],
            'city' => [],
            'road_width' => 0,
        ];
        $count = count($properties);
        if ($count == 0) return $vector;
        foreach ($properties as $p) {
            $vector['price'] += $p['base_price'] ?? $p['total_price'] ?? 0;
            $vector['area'] += $p['plot_size'] ?? $p['area'] ?? 0;
            if (!empty($p['facing'])) $vector['facing'][] = $p['facing'];
            if (!empty($p['colony_id'])) $vector['colony'][] = $p['colony_id'];
            if (!empty($p['plot_type_id'])) $vector['plot_type'][] = $p['plot_type_id'];
            if (!empty($p['city'])) $vector['city'][] = $p['city'];
            $vector['road_width'] += $p['road_width_ft'] ?? 0;
        }
        $vector['price'] = $count > 0 ? $vector['price'] / $count : 0;
        $vector['area'] = $count > 0 ? $vector['area'] / $count : 0;
        $vector['road_width'] = $count > 0 ? $vector['road_width'] / $count : 0;
        $vector['facing'] = $this->mostCommon($vector['facing']);
        $vector['colony'] = $this->mostCommon($vector['colony']);
        $vector['plot_type'] = $this->mostCommon($vector['plot_type']);
        $vector['city'] = $this->mostCommon($vector['city']);
        return $vector;
    }
    private function mostCommon(array $arr)
    {
        if (empty($arr)) return null;
        $counts = array_count_values($arr);
        arsort($counts);
        return array_key_first($counts);
    }
    private function cosineSimilarity(array $a, array $b): float
    {
        $numA = [$a['price'], $a['area'], $a['road_width']];
        $numB = [$b['price'], $b['area'], $b['road_width']];
        $maxPrice = max(max($numA), max($numB), 1);
        $maxArea = max(max($numA), max($numB), 1);
        $maxRoad = max(max($numA), max($numB), 1);
        $numA = [$numA[0]/$maxPrice, $numA[1]/$maxArea, $numA[2]/$maxRoad];
        $numB = [$numB[0]/$maxPrice, $numB[1]/$maxArea, $numB[2]/$maxRoad];
        $catMatch = 0;
        $catTotal = 0;
        foreach (['facing', 'colony', 'plot_type', 'city'] as $cat) {
            $catTotal++;
            if (!empty($a[$cat]) && !empty($b[$cat]) && $a[$cat] === $b[$cat]) {
                $catMatch++;
            }
        }
        $dot = 0;
        $normA = 0;
        $normB = 0;
        for ($i = 0; $i < 3; $i++) {
            $dot += $numA[$i] * $numB[$i];
            $normA += $numA[$i] * $numA[$i];
            $normB += $numB[$i] * $numB[$i];
        }
        $numSim = ($normA > 0 && $normB > 0) ? $dot / (sqrt($normA) * sqrt($normB)) : 0;
        $catSim = $catTotal > 0 ? $catMatch / $catTotal : 0;
        return ($numSim * 0.6) + ($catSim * 0.4);
    }
    private function getPopularInArea(array $profile, int $limit): array
    {
        if (empty($profile['preferred_colonies']) && empty($profile['preferred_cities'])) return [];
        $conditions = [];
        $params = [];
        if (!empty($profile['preferred_colonies'])) {
            $placeholders = implode(',', array_fill(0, count($profile['preferred_colonies']), '?'));
            $conditions[] = "p.colony_id IN ($placeholders)";
            $params = array_merge($params, $profile['preferred_colonies']);
        }
        if (!empty($profile['preferred_cities'])) {
            $placeholders = implode(',', array_fill(0, count($profile['preferred_cities']), '?'));
            $conditions[] = "c.location IN ($placeholders)";
            $params = array_merge($params, $profile['preferred_cities']);
        }
        if (empty($conditions)) return [];
        $whereClause = 'WHERE ' . implode(' OR ', $conditions);
        $excludedIds = array_merge($profile['booked_properties'], $profile['viewed_properties']);
        if (!empty($excludedIds)) {
            $excludedPlaceholders = implode(',', array_fill(0, count($excludedIds), '?'));
            $whereClause .= " AND p.id NOT IN ($excludedPlaceholders)";
            $params = array_merge($params, $excludedIds);
        }
        $params[] = $limit;
        $recs = $this->db->fetchAll("
            SELECT p.*, c.name as colony_name, c.location as city,
                   p.plot_type as plot_type_name,
                   (SELECT COUNT(*) FROM plot_bookings WHERE plot_id = p.id AND status NOT IN ('cancelled')) as booking_count
            FROM plots p
            LEFT JOIN colonies c ON p.colony_id = c.id
            $whereClause
            AND p.status = 'available'
            ORDER BY booking_count DESC, p.created_at DESC
            LIMIT ?
        ", $params);
        return array_map(function($r) {
            return array_merge($r, [
                'reason' => 'Popular in your preferred area',
                'source' => 'popular_in_area',
                'confidence' => 0.75,
            ]);
        }, $recs);
    }
    private function getPriceMatchedRecommendations(array $profile, int $limit): array
    {
        if ($profile['budget_max'] == PHP_INT_MAX) return [];
        $minPrice = $profile['budget_min'] ?? ($profile['budget_max'] * 0.7);
        $maxPrice = $profile['budget_max'] * 1.2;
        $excludedIds = array_merge($profile['booked_properties'], $profile['viewed_properties']);
        if (empty($excludedIds)) $excludedIds = [0];
        $excludedPlaceholders = implode(',', array_fill(0, count($excludedIds), '?'));
        $params = array_merge([$minPrice, $maxPrice], $excludedIds);
        $recs = $this->db->fetchAll("
            SELECT p.*, c.name as colony_name, c.location as city,
                   p.plot_type as plot_type_name
            FROM plots p
            LEFT JOIN colonies c ON p.colony_id = c.id
            WHERE p.status = 'available'
            AND p.total_price BETWEEN ? AND ?
            AND p.id NOT IN (" . $excludedPlaceholders . ")
            ORDER BY ABS(p.total_price - ?) ASC
            LIMIT ?
        ", array_merge($params, [($minPrice + $maxPrice) / 2, $limit]));
        return array_map(function($r) {
            return array_merge($r, [
                'reason' => 'Matches your budget range',
                'source' => 'price_matched',
                'confidence' => 0.7,
            ]);
        }, $recs);
    }
    private function deduplicateAndScore(array $recommendations, array $profile, int $limit): array
    {
        $seen = [];
        $unique = [];
        foreach ($recommendations as $rec) {
            $id = $rec['id'] ?? 0;
            if (!$id || isset($seen[$id])) continue;
            $seen[$id] = true;
            $score = 0;
            $score += ($rec['confidence'] ?? 0.5) * 40;
            $score += ($rec['similarity'] ?? 0) * 30;
            if (isset($rec['base_price']) && $profile['budget_max'] < PHP_INT_MAX) {
                $budgetMid = ($profile['budget_min'] + $profile['budget_max']) / 2;
                $diff = abs($rec['base_price'] - $budgetMid) / max(1, $budgetMid);
                $score += max(0, (1 - $diff) * 20);
            }
            if (!empty($profile['preferred_colonies']) && in_array($rec['colony_id'] ?? 0, $profile['preferred_colonies'])) {
                $score += 15;
            }
            $rec['composite_score'] = round($score, 2);
            $unique[] = $rec;
        }
        usort($unique, fn($a, $b) => $b['composite_score'] <=> $a['composite_score']);
        return array_slice($unique, 0, $limit);
    }
    private function excludeBooked(array $recommendations): array
    {
        $ids = array_column($recommendations, 'id');
        if (empty($ids)) return $recommendations;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $booked = $this->db->fetchAll("
            SELECT plot_id FROM plot_bookings 
            WHERE plot_id IN ($placeholders) AND status NOT IN ('cancelled')
        ", $ids);
        $bookedIds = array_column($booked, 'plot_id');
        return array_filter($recommendations, fn($r) => !in_array($r['id'], $bookedIds));
    }
}