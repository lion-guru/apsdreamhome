<?php

namespace App\Services\Legacy\Classes;

/**
 * AI-Powered Property Recommendation Engine (Proxy)
 * 
 * This class is a proxy for the modern RecommendationService.
 */

use App\Services\RecommendationService;

class PropertyRecommendationEngine {
    private $recommendationService;
    
    public function __construct($database = null) {
        $this->recommendationService = new RecommendationService();
    }
    
    /**
     * Generate personalized property recommendations for a user
     */
    public function generateRecommendations($userId, $limit = 10) {
        return $this->recommendationService->getRecommendations((int)$userId, (int)$limit);
    }
    
    /**
     * Magic method to handle other calls to modern RecommendationService
     */
    public function __call($name, $arguments) {
        if (method_exists($this->recommendationService, $name)) {
            return call_user_func_array([$this->recommendationService, $name], $arguments);
        }
        throw new \Exception("Method {$name} does not exist in PropertyRecommendationEngine proxy.");
    }
}
