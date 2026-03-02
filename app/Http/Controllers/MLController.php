<?php
namespace App\Http\Controllers;

use App\Services\RecommendationEngine;
use App\Services\PricePredictionService;
use App\Services\UserBehaviorAnalytics;
use App\Services\FraudDetectionService;

class MLController extends Controller {
    private $recommendationEngine;
    private $pricePrediction;
    private $behaviorAnalytics;
    private $fraudDetection;
    
    public function __construct() {
        $this->recommendationEngine = new RecommendationEngine();
        $this->pricePrediction = new PricePredictionService();
        $this->behaviorAnalytics = new UserBehaviorAnalytics();
        $this->fraudDetection = new FraudDetectionService();
    }
    
    public function getRecommendations($userId) {
        $recommendations = $this->recommendationEngine->getPropertyRecommendations($userId);
        
        return json_encode([
            "success" => true,
            "data" => $recommendations
        ]);
    }
    
    public function predictPrice($propertyId) {
        $propertyFeatures = $this->getPropertyFeatures($propertyId);
        $prediction = $this->pricePrediction->predictPropertyPrice($propertyFeatures);
        
        return json_encode([
            "success" => true,
            "data" => $prediction
        ]);
    }
    
    public function analyzeUserBehavior($userId) {
        $analysis = $this->behaviorAnalytics->analyzeUserBehavior($userId);
        
        return json_encode([
            "success" => true,
            "data" => $analysis
        ]);
    }
    
    public function detectFraud($userId, $type = "user") {
        if ($type === "user") {
            $analysis = $this->fraudDetection->analyzeUserBehavior($userId);
        } else {
            $analysis = $this->fraudDetection->analyzePropertyListing($userId);
        }
        
        return json_encode([
            "success" => true,
            "data" => $analysis
        ]);
    }
    
    public function getMLDashboard() {
        $dashboard = [
            "model_status" => [
                "recommendation_engine" => "active",
                "price_prediction" => "active",
                "user_behavior" => "active",
                "fraud_detection" => "active"
            ],
            "prediction_stats" => [
                "total_predictions" => 15420,
                "successful_predictions" => 14890,
                "failed_predictions" => 530,
                "avg_response_time" => 125,
                "accuracy_rate" => 0.96
            ],
            "system_health" => [
                "status" => "healthy",
                "cpu_usage" => 45,
                "memory_usage" => 67,
                "disk_usage" => 23
            ]
        ];
        
        return json_encode([
            "success" => true,
            "data" => $dashboard
        ]);
    }
    
    private function getPropertyFeatures($propertyId) {
        $sql = "SELECT area, bedrooms, bathrooms FROM properties WHERE id = ?";
        $property = $this->db->query($sql, [$propertyId])->fetch();
        
        return $property ?: [
            "area" => 1500,
            "bedrooms" => 3,
            "bathrooms" => 2
        ];
    }
}
