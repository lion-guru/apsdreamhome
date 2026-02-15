<?php

namespace App\Services\AI\Agents\specialized;

use App\Services\AI\Agents\BaseAgent;
use App\Services\AI\Modules\RecommendationEngine;

/**
 * RecommendationAgent - Specialized agent for personalized property matching
 *
 * @property \App\Core\Database $db Inherited from BaseAgent
 */
class RecommendationAgent extends BaseAgent {
    public function __construct($db = null) {
        parent::__construct('REC_AGENT_001', 'Property Recommendation Agent');
        if ($db) $this->db = $db;
    }

    public function process($input, $context = []) {
        $userId = $input['user_id'] ?? null;
        $propertyId = $input['property_id'] ?? null;
        $type = $input['recommendation_type'] ?? 'personalized';

        $engine = new RecommendationEngine($this->db);

        $recommendations = [];
        if ($type === 'similar' && $propertyId) {
            $recommendations = $engine->getSimilarProperties($propertyId);
        } elseif ($userId) {
            $recommendations = $engine->getRecommendations($userId);
        }

        $this->logActivity("RECOMMENDATION_GENERATED", "Type: $type, User: $userId, Results: " . count($recommendations));

        return [
            'success' => true,
            'recommendations' => $recommendations,
            'count' => count($recommendations),
            'message' => "Successfully generated $type recommendations."
        ];
    }
}
