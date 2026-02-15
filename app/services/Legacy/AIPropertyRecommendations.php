<?php

namespace App\Services\Legacy;
/**
 * AI-Powered Property Recommendations - APS Dream Homes
 * Machine learning-based property matching and recommendations
 */

require_once __DIR__ . '/ai/AIManager.php';

class AIPropertyRecommendations {
    private $db;
    private $aiManager;
    
    public function __construct() {
        $this->db = \App\Core\App::database();
        $this->aiManager = new AIManager();
        $this->initAI();
    }
    
    /**
     * Initialize AI system
     */
    private function initAI() {
        // AIEcosystemManager handles table creation via AIManager
    }
    
    /**
     * Get personalized recommendations for user
     * Uses the centralized AI Manager
     */
    public function getRecommendations($userId, $limit = 10) {
        // Use the new RecommendationEngine via AIManager
        return $this->aiManager->getPropertyRecommendations($userId, ['limit' => $limit]);
    }
    
    /**
     * Track user action for learning
     */
    public function trackAction($userId, $propertyId, $actionType) {
        return $this->aiManager->trackAction($userId, $propertyId, $actionType);
    }
}

