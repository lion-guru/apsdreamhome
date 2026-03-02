<?php

namespace App\Services\AI;
/**
 * Advanced AI Property Recommendation Engine (Proxy)
 * 
 * This class is a proxy for the modern RecommendationService.
 */

use App\Services\RecommendationService;

class AIRecommendationEngine {
    private $recommendationService;

    public function __construct($db = null, $config = []) {
        $this->recommendationService = new RecommendationService();
    }

    /**
     * Get personalized property recommendations for a user
     */
    public function getPersonalizedRecommendations($userId, $limit = 10) {
        return $this->recommendationService->getRecommendations((int)$userId, (int)$limit);
    }

    /**
     * Get collaborative filtering recommendations
     */
    public function getCollaborativeRecommendations($userId, $limit = 5) {
        return $this->recommendationService->getCollaborativeRecommendations((int)$userId, (int)$limit);
    }

    /**
     * Magic method to handle other calls to modern RecommendationService
     */
    public function __call($name, $arguments) {
        if (method_exists($this->recommendationService, $name)) {
            return call_user_func_array([$this->recommendationService, $name], $arguments);
        }
        throw new \Exception("Method {$name} does not exist in AIRecommendationEngine proxy.");
    }
}
