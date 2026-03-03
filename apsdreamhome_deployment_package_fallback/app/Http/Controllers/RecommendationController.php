<?php
namespace App\Http\Controllers;

use App\Services\RecommendationEngine;

class RecommendationController extends Controller {
    private $recommendationEngine;
    
    public function __construct() {
        $this->recommendationEngine = new RecommendationEngine();
    }
    
    public function getPropertyRecommendations($userId) {
        $recommendations = $this->recommendationEngine->getPropertyRecommendations($userId);
        
        return json_encode([
            "success" => true,
            "recommendations" => $recommendations,
            "count" => count($recommendations)
        ]);
    }
    
    public function getUserRecommendations($userId) {
        $recommendations = $this->recommendationEngine->getUserRecommendations($userId);
        
        return json_encode([
            "success" => true,
            "recommendations" => $recommendations,
            "count" => count($recommendations)
        ]);
    }
    
    public function updateRecommendationModel() {
        $result = $this->recommendationEngine->updateRecommendationModel();
        
        return json_encode([
            "success" => $result,
            "message" => $result ? "Model updated successfully" : "Failed to update model"
        ]);
    }
}
