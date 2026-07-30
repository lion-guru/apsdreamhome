<?php
/**
 * RecommendationEngine - Self-learning property recommendations
 * Collaborative filtering + content-based hybrid
 * No external API
 */

namespace App\Services\AI;

use PDO;

class RecommendationEngine
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    /**
     * Get personalized recommendations for a user
     */
    public function recommend(int $userId, int $limit = 10): array
    {
        // Update profile from recent behavior first
        $this->updateProfileFromBehavior($userId);
        
        $profile = $this->getOrCreateProfile($userId);
        $behavior = $this->getUserBehavior($userId);
        $candidates = $this->getCandidates($profile, $behavior, $limit);
        
        $scored = [];
        foreach ($candidates as $item) {
            $score = $this->scoreItem($item, $profile, $behavior);
            if ($score > 0) {
                $scored[] = [
                    'item' => $item,
                    'score' => $score,
                    'reason' => $this->explainScore($item, $profile)
                ];
            }
        }
        
        // Sort by score descending
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        $scored = array_slice($scored, 0, $limit);
        
        // Save recommendations
        $this->saveRecommendations($userId, 'property', $scored);
        
        return $scored;
    }

    /**
     * Get or create user AI profile
     */
    public function getOrCreateProfile(int $userId): array
    {
        $tenantSql = $this->tenantSql();
        $tenantVal = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $stmt = $this->db->prepare("SELECT * FROM ai_user_profiles WHERE user_id = ?{$tenantSql}");
        $stmt->execute(array_merge([$userId], $tenantVal));
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$profile) {
            $tenantData = $this->tenantInsertData();
            $tenantCols = array_keys($tenantData);
            $tenantVals = array_values($tenantData);
            $columns = array_merge(['user_id'], $tenantCols);
            $values  = array_merge([$userId], $tenantVals);
            $colStr = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            $stmt = $this->db->prepare("INSERT INTO ai_user_profiles ($colStr) VALUES ($placeholders)");
            $stmt->execute($values);
            $profile = ['user_id' => $userId, 'preferred_locations' => null, 'preferred_types' => null, 'budget_min' => null, 'budget_max' => null];
        }

        $profile['preferred_locations'] = json_decode($profile['preferred_locations'] ?? '[]', true) ?: [];
        $profile['preferred_types'] = json_decode($profile['preferred_types'] ?? '[]', true) ?: [];
        return $profile;
    }

    /**
     * Update user profile from behavior
     */
    public function updateProfileFromBehavior(int $userId): void
    {
        // Aggregate user views/inquiries
        $stmt = $this->db->prepare("
            SELECT page_url, COUNT(*) as cnt
            FROM user_behavior_tracking
            WHERE user_id = ?{$this->tenantSql()} AND action_type IN ('view_property', 'inquiry', 'favorite')
              AND tracked_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY page_url
            ORDER BY cnt DESC
            LIMIT 50
        ");
        $stmt->execute(array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : []));
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Extract preferred locations from URLs
        $locations = [];
        $types = [];
        foreach ($events as $e) {
            if (preg_match('/location\/([\w-]+)/', $e['page_url'], $m)) $locations[$m[1]] = ($locations[$m[1]] ?? 0) + $e['cnt'];
            if (preg_match('/property-type\/([\w-]+)/', $e['page_url'], $m)) $types[$m[1]] = ($types[$m[1]] ?? 0) + $e['cnt'];
        }

        arsort($locations);
        arsort($types);

        $upd = $this->db->prepare("
            UPDATE ai_user_profiles
            SET preferred_locations = ?,
                preferred_types = ?,
                interaction_count = interaction_count + 1,
                last_interaction_at = NOW()
            WHERE user_id = ?{$this->tenantSql()}
        ");
        $upd->execute(array_merge([
            json_encode(array_slice(array_keys($locations), 0, 5)),
            json_encode(array_slice(array_keys($types), 0, 5)),
            $userId
        ], $this->tenantId() > 1 ? [$this->tenantId()] : []));
    }

    /**
     * Score a single item against user profile
     */
    private function scoreItem(array $item, array $profile, array $behavior): float
    {
        $score = 0.0;

        // Location match
        if (!empty($profile['preferred_locations'])) {
            foreach ($profile['preferred_locations'] as $loc) {
                if (stripos($item['location'] ?? '', $loc) !== false) $score += 30;
            }
        }

        // Type match
        if (!empty($profile['preferred_types'])) {
            foreach ($profile['preferred_types'] as $type) {
                if (stripos($item['property_type'] ?? '', $type) !== false) $score += 25;
            }
        }

        // Budget match
        $price = (float)($item['price'] ?? 0);
        if ($price > 0) {
            if ($profile['budget_max'] && $price <= $profile['budget_max']) $score += 20;
            if ($profile['budget_min'] && $price >= $profile['budget_min']) $score += 10;
        }

        // Popularity boost
        $score += min(15, log10(max(1, (int)($item['view_count'] ?? 0))) * 5);

        // Recency boost (newer listings get slight boost)
        if (!empty($item['created_at'])) {
            $days = (time() - strtotime($item['created_at'])) / 86400;
            if ($days < 7) $score += 10;
            elseif ($days < 30) $score += 5;
        }

        return round($score, 2);
    }

    private function explainScore(array $item, array $profile): string
    {
        $reasons = [];
        if (!empty($profile['preferred_locations'])) {
            foreach ($profile['preferred_locations'] as $loc) {
                if (stripos($item['location'] ?? '', $loc) !== false) {
                    $reasons[] = "Matches your preferred location: $loc";
                    break;
                }
            }
        }
        if (!empty($reasons)) return implode('; ', $reasons);
        return "Popular in your area";
    }

    private function getCandidates(array $profile, array $behavior, int $limit): array
    {
        // Try multiple tables for properties
        $tables = ['properties', 'user_properties', 'plots'];
        $candidates = [];

        foreach ($tables as $t) {
            try {
                $cols = $this->db->query("SHOW COLUMNS FROM $t")->fetchAll(PDO::FETCH_COLUMN);
                $select = ['id'];
                foreach (['title', 'name', 'property_type', 'type', 'location', 'address', 'city', 'price', 'amount', 'created_at'] as $c) {
                    if (in_array($c, $cols)) $select[] = $c;
                }
                $tenantFilter = $this->tenantSql();
                $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
                $stmt = $this->db->query("SELECT " . implode(',', $select) . " FROM $t WHERE 1=1{$tenantFilter} ORDER BY id DESC LIMIT $limit");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $row['__source'] = $t;
                    $candidates[] = $row;
                }
            } catch (\Exception $e) {
            // table may not exist
            error_log($e->getMessage());
            }
        }

        return array_slice($candidates, 0, $limit);
    }

    private function getUserBehavior(int $userId): array
    {
        $tenantSql = $this->tenantSql();
        $tenantParam = $this->tenantId() > 1 ? [$this->tenantId()] : [];
        $stmt = $this->db->prepare("
            SELECT action_type, target_type, target_id, COUNT(*) as cnt
            FROM user_behavior_tracking
            WHERE user_id = ?{$tenantSql} AND tracked_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY action_type, target_type, target_id
        ");
        $stmt->execute(array_merge([$userId], $tenantParam));
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function saveRecommendations(int $userId, string $itemType, array $items): void
    {
        $tenantIns = $this->tenantInsertData();
        $insCols = array_merge(['user_id', 'item_type', 'item_id', 'score', 'reason'], array_keys($tenantIns));
        $insVals = array_merge([$userId, $itemType], array_map(fn($r) => $r['item']['id'] ?? 0, $items), array_map(fn($r) => $r['score'], $items), array_map(fn($r) => $r['reason'], $items), array_values($tenantIns));
        $colStr = implode(', ', $insCols);
        $placeholders = implode(', ', array_fill(0, count($insVals), '?'));
        $stmt = $this->db->prepare("INSERT INTO ai_recommendations ($colStr) VALUES ($placeholders)");
        $stmt->execute($insVals);
    }

    /**
     * Mark recommendation as shown/clicked/converted
     */
    public function trackAction(int $recommendationId, string $action): void
    {
        $col = match ($action) {
            'shown' => 'shown_at',
            'clicked' => 'clicked_at',
            'converted' => 'converted_at',
            'dismissed' => 'dismissed_at',
            default => null
        };
        if ($col) {
            $stmt = $this->db->prepare("UPDATE ai_recommendations SET $col = NOW() WHERE id = ?{$this->tenantSql()}");
            $params = [$recommendationId];
            if ($this->tenantId() > 1) $params[] = $this->tenantId();
            $stmt->execute($params);
        }
    }
}
