<?php
namespace App\Services;

use App\Core\Database;

class FraudDetectionService {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function analyzeUserBehavior($userId) {
        $sql = "SELECT DATEDIFF(NOW(), created_at) as account_age,
                       verification_status
                FROM users WHERE id = ?";
        
        $user = $this->db->query($sql, [$userId])->fetch();
        
        $riskScore = 0;
        
        // Account age factor
        if ($user["account_age"] < 7) {
            $riskScore += 0.3;
        } elseif ($user["account_age"] < 30) {
            $riskScore += 0.1;
        }
        
        // Verification status
        if ($user["verification_status"] !== "verified") {
            $riskScore += 0.2;
        }
        
        return [
            "risk_score" => min($riskScore, 1.0),
            "suspicious_activities" => [],
            "risk_factors" => [
                "new_account" => $user["account_age"] < 30,
                "unverified" => $user["verification_status"] !== "verified"
            ],
            "recommendation" => $this->generateRecommendation($riskScore)
        ];
    }
    
    public function analyzePropertyListing($propertyId) {
        $sql = "SELECT * FROM properties WHERE id = ?";
        $property = $this->db->query($sql, [$propertyId])->fetch();
        
        $fraudProbability = 0.1; // Base probability
        
        // Check for unrealistic pricing
        if ($property && $property["price"] < 100000) {
            $fraudProbability += 0.3;
        }
        
        return [
            "fraud_probability" => min($fraudProbability, 1.0),
            "suspicious_indicators" => [],
            "verification_required" => $fraudProbability > 0.5,
            "risk_level" => $this->assessRiskLevel($fraudProbability)
        ];
    }
    
    public function analyzeTransaction($transactionId) {
        return [
            "fraud_risk" => [
                "risk_score" => 0.2,
                "risk_level" => "low"
            ],
            "anomalies" => [],
            "verification_needed" => false,
            "action_required" => "proceed"
        ];
    }
    
    private function generateRecommendation($riskScore) {
        if ($riskScore > 0.8) {
            return [
                "action" => "block",
                "reason" => "High fraud risk detected",
                "verification_required" => true
            ];
        } elseif ($riskScore > 0.6) {
            return [
                "action" => "monitor",
                "reason" => "Medium fraud risk",
                "verification_required" => true
            ];
        } elseif ($riskScore > 0.4) {
            return [
                "action" => "verify",
                "reason" => "Low to medium fraud risk",
                "verification_required" => false
            ];
        } else {
            return [
                "action" => "allow",
                "reason" => "Low fraud risk",
                "verification_required" => false
            ];
        }
    }
    
    private function assessRiskLevel($probability) {
        if ($probability > 0.8) {
            return "high";
        } elseif ($probability > 0.5) {
            return "medium";
        } else {
            return "low";
        }
    }
}
