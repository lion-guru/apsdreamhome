<?php

namespace App\Services\AI\Modules;

use App\Services\RecommendationService;

/**
 * AI Module - RecommendationEngine
 * Proxy for the modern RecommendationService.
 */
class RecommendationEngine {
    private $service;

    public function __construct() {
        $this->service = new RecommendationService();
    }

    /**
     * Get recommendations for a user
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecommendations($userId, $limit = 10) {
        return $this->service->getRecommendations((int)$userId, (int)$limit);
    }

    /**
     * Magic method to proxy other calls
     */
    public function __call($name, $arguments) {
        if (method_exists($this->service, $name)) {
            return \call_user_func_array([$this->service, $name], $arguments);
        }
        return [];
    }
}
