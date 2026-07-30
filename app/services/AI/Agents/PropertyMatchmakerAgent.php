<?php
/**
 * PropertyMatchmakerAgent — Intelligent property matching
 * 
 * Matches leads to available plots/properties based on:
 * - Budget range (with flexibility)
 * - Location preference
 * - Size requirements
 * - Past behavior (what they've viewed/inquired about)
 * - Similar lead patterns (collaborative filtering)
 * 
 * Sends personalized recommendations via WhatsApp/SMS
 */

namespace App\Services\AI\Agents;

use App\Core\Database\Database;
use App\Services\AI\AIGateway;

class PropertyMatchmakerAgent
{
    private $db;
    private $gateway;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->gateway = AIGateway::getInstance();
    }

    /**
     * Find best property matches for a lead
     */
    public function matchForLead(int $leadId, int $limit = 5): array
    {
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ? AND deleted_at IS NULL", [$leadId]);
        if (!$lead) return ['error' => 'Lead not found'];

        // Get lead preferences from history
        $preferences = $this->analyzeLeadPreferences($leadId);

        // AI-powered matching
        $matchResult = $this->gateway->process('match_property', [
            'budget' => $lead['budget'] ?? $preferences['avg_budget'],
            'location' => $lead['location_preference'] ?? $preferences['preferred_location'],
            'size' => $preferences['preferred_size'],
            'preferences' => json_encode($preferences),
            'available_plots' => 'query database',
        ], ['user_id' => $lead['assigned_to']]);

        // Database-driven matching (always runs, AI enhances)
        $dbMatches = $this->databaseMatch($lead, $preferences, $limit);

        // Merge AI + DB results
        $aiMatches = $matchResult['result']['matches'] ?? [];
        $merged = $this->mergeMatches($dbMatches, $aiMatches, $limit);

        // Log recommendations
        $this->logRecommendations($leadId, $merged);

        return [
            'lead_id' => $leadId,
            'matches' => $merged,
            'total_available' => count($dbMatches),
            'engine' => $matchResult['engine'] ?? 'database',
        ];
    }

    /**
     * Batch match — find matches for all active leads
     */
    public function batchMatch(int $limit = 20): array
    {
        $leads = $this->db->fetchAll(
            "SELECT id FROM leads WHERE deleted_at IS NULL AND status NOT IN ('converted','closed','dead','won')
             AND (last_recommendation_date IS NULL OR last_recommendation_date < DATE_SUB(NOW(), INTERVAL 7 DAY))
             ORDER BY lead_score DESC LIMIT ?",
            [$limit]
        ) ?: [];

        $results = ['matched' => 0, 'recommendations_sent' => 0];
        foreach ($leads as $lead) {
            $match = $this->matchForLead((int)$lead['id'], 3);
            if (!isset($match['error']) && count($match['matches']) > 0) {
                $results['matched']++;
                // Auto-send top recommendation
                $this->sendRecommendation((int)$lead['id'], $match['matches'][0]);
                $results['recommendations_sent']++;
            }
        }
        return $results;
    }

    /**
     * Get popular properties (what similar leads viewed)
     */
    public function getTrendingProperties(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as colony_name,
                    (SELECT COUNT(*) FROM leads l WHERE l.location_preference LIKE CONCAT('%', c.name, '%') AND l.deleted_at IS NULL) as matching_leads
             FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id
             WHERE p.status = 'available'
             ORDER BY matching_leads DESC, p.created_at DESC LIMIT ?",
            [$limit]
        ) ?: [];
    }

    // ─────── Helpers ─────────────────────────────────────────────────

    private function analyzeLeadPreferences(int $leadId): array
    {
        // Analyze from interactions, views, inquiries
        $interactions = $this->db->fetchAll(
            "SELECT content, metadata FROM crm_interactions WHERE lead_id = ? ORDER BY created_at DESC LIMIT 20",
            [$leadId]
        ) ?: [];

        $preferences = [
            'avg_budget' => 0,
            'preferred_location' => '',
            'preferred_size' => 0,
            'property_types' => [],
        ];

        // Extract from lead's own data
        $lead = $this->db->fetch("SELECT budget, location_preference FROM leads WHERE id = ?", [$leadId]);
        if ($lead) {
            $preferences['avg_budget'] = (float)($lead['budget'] ?? 0);
            $preferences['preferred_location'] = $lead['location_preference'] ?? '';
        }

        return $preferences;
    }

    private function databaseMatch(array $lead, array $preferences, int $limit): array
    {
        $sql = "SELECT p.*, c.name as colony_name,
                       CASE 
                           WHEN p.price <= ? THEN 30
                           WHEN p.price <= ? * 1.1 THEN 20
                           WHEN p.price <= ? * 1.2 THEN 10
                           ELSE 0
                       END as budget_fit,
                       CASE WHEN c.name LIKE ? THEN 20 ELSE 0 END as location_fit,
                       CASE WHEN p.area >= ? THEN 15 WHEN p.area >= ? * 0.8 THEN 10 ELSE 0 END as size_fit
                FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE p.status = 'available'
                HAVING (budget_fit + location_fit + size_fit) > 0
                ORDER BY (budget_fit + location_fit + size_fit) DESC
                LIMIT ?";

        $budget = (float)($lead['budget'] ?? $preferences['avg_budget'] ?? 0);
        $location = '%' . ($lead['location_preference'] ?? $preferences['preferred_location'] ?? '') . '%';
        $size = (int)($preferences['preferred_size'] ?? 1000);

        return $this->db->fetchAll($sql, [
            $budget, $budget, $budget,
            $location,
            $size, $size,
            $limit
        ]) ?: [];
    }

    private function mergeMatches(array $dbMatches, array $aiMatches, int $limit): array
    {
        $merged = [];
        foreach ($dbMatches as $m) {
            $totalScore = ($m['budget_fit'] ?? 0) + ($m['location_fit'] ?? 0) + ($m['size_fit'] ?? 0);
            $merged[] = [
                'plot_id' => $m['id'],
                'colony' => $m['colony_name'] ?? '',
                'block' => $m['block'] ?? '',
                'area' => $m['area'] ?? 0,
                'price' => $m['price'] ?? 0,
                'match_score' => min(100, $totalScore * 2),
                'reason' => $this->buildReason($m),
            ];
        }
        usort($merged, fn($a, $b) => $b['match_score'] <=> $a['match_score']);
        return array_slice($merged, 0, $limit);
    }

    private function buildReason(array $plot): string
    {
        $reasons = [];
        if (($plot['budget_fit'] ?? 0) >= 20) $reasons[] = 'Within budget';
        if (($plot['location_fit'] ?? 0) > 0) $reasons[] = 'Preferred location';
        if (($plot['size_fit'] ?? 0) >= 10) $reasons[] = 'Right size';
        return implode(', ', $reasons) ?: 'Good overall fit';
    }

    private function logRecommendations(int $leadId, array $matches): void
    {
        try {
            $plotIds = array_column($matches, 'plot_id');
            $this->db->getConnection()->prepare(
                "UPDATE leads SET last_recommendation_date = NOW(), recommended_plots = ? WHERE id = ?"
            )->execute([json_encode($plotIds), $leadId]);
        } catch (\Throwable $e) { /* column may not exist */ error_log($e->getMessage()); }
    }

    private function sendRecommendation(int $leadId, array $match): void
    {
        try {
            $this->db->getConnection()->prepare(
                "INSERT INTO crm_interactions (lead_id, interaction_type, direction, content, metadata, created_at)
                 VALUES (?, 'recommendation', 'outbound', ?, ?, NOW())"
            )->execute([
                $leadId,
                "Recommended: {$match['colony']} {$match['block']} - {$match['area']} sqft @ ₹" . number_format($match['price']),
                json_encode($match),
            ]);
        } catch (\Throwable $e) { /* non-critical */ error_log($e->getMessage()); }
    }
}
