<?php

namespace App\Services\ML;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

/**
 * FraudDetectionService - ML-based Fraud Detection
 * Detects fraudulent users, property listings, and transactions
 */
class FraudDetectionService
{
    private $db;

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Analyze user behavior for fraud detection
     * @param int $userId
     * @return array
     */
    public function analyzeUserBehavior($userId)
    {
        $tid = $this->getTenantId();
        $sql = "SELECT DATEDIFF(NOW(), created_at) as account_age, 
                       status as verification_status, 
                       COALESCE(activity_logs_unified, 0) as activity_logs_unified,
                       '' as last_login_ip,
                       status
                FROM users WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : "");

        $user = $this->db->fetch($sql, $tid > 1 ? [$userId, $tid] : [$userId]);
        
        if (!$user) {
            return [
                "risk_score" => 1.0,
                "suspicious_activities" => ["User not found"],
                "risk_factors" => [],
                "recommendation" => $this->generateRecommendation(1.0)
            ];
        }

        $riskScore = 0;
        $riskFactors = [];
        $suspiciousActivities = [];

        // Account age factor
        if ($user["account_age"] < 7) {
            $riskScore += 0.3;
            $riskFactors["new_account_7d"] = true;
        } elseif ($user["account_age"] < 30) {
            $riskScore += 0.1;
            $riskFactors["new_account_30d"] = true;
        }

        // Verification status
        if ($user["verification_status"] !== "verified") {
            $riskScore += 0.2;
            $riskFactors["unverified"] = true;
        }

        // Login attempts
        if ($user["activity_logs_unified"] > 5) {
            $riskScore += 0.2;
            $riskFactors["multiple_activity_logs_unified"] = true;
            $suspiciousActivities[] = "Multiple failed login attempts";
        }

        // Account status
        if (($user["status"] ?? 'active') !== 'active') {
            $riskScore += 0.1;
            $riskFactors["inactive_account"] = true;
        }

        // Check for rapid activity
        $recentActivity = $this->db->fetch(
            "SELECT COUNT(*) as count FROM activity_logs_unified 
             WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$userId]
        );
        
        if ($recentActivity && $recentActivity["count"] > 50) {
            $riskScore += 0.15;
            $riskFactors["rapid_activity"] = true;
            $suspiciousActivities[] = "Unusually high activity in last hour";
        }

        return [
            "risk_score" => min($riskScore, 1.0),
            "suspicious_activities" => $suspiciousActivities,
            "risk_factors" => $riskFactors,
            "account_age_days" => $user["account_age"],
            "verification_status" => $user["verification_status"],
            "recommendation" => $this->generateRecommendation($riskScore)
        ];
    }

    /**
     * Analyze property listing for fraud
     * @param int $propertyId
     * @return array
     */
    public function analyzePropertyListing($propertyId)
    {
        $tid = $this->getTenantId();
        $sql = "SELECT p.*, u.name as owner_name, u.created_at as owner_since 
                FROM properties p 
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
                WHERE p.id = ?";
        
        $property = $this->db->fetch($sql, $tid > 1 ? [$tid, $propertyId] : [$propertyId]);

        if (!$property) {
            return [
                "fraud_probability" => 1.0,
                "suspicious_indicators" => ["Property not found"],
                "verification_required" => true,
                "risk_level" => "high",
                "recommendation" => "block"
            ];
        }

        $fraudProbability = 0.1; // Base probability
        $suspiciousIndicators = [];

        // Check for unrealistic pricing
        if ($property["price"] < 100000) {
            $fraudProbability += 0.3;
            $suspiciousIndicators[] = "Price below ₹1,00,000 (unrealistic)";
        }

        // Check for extremely high price
        if ($property["price"] > 100000000) { // 10 Crore
            $fraudProbability += 0.15;
            $suspiciousIndicators[] = "Price above ₹10 Crore (verify authenticity)";
        }

        // Check for missing critical information
        if (empty($property["title"]) || empty($property["description"])) {
            $fraudProbability += 0.2;
            $suspiciousIndicators[] = "Missing property details";
        }

        // Check owner account age
        if ($property["owner_since"]) {
            $ownerAge = strtotime($property["owner_since"]);
            $daysSinceRegistration = floor((time() - $ownerAge) / 86400);
            
            if ($daysSinceRegistration < 7) {
                $fraudProbability += 0.25;
                $suspiciousIndicators[] = "Owner account less than 7 days old";
            }
        }

        // Check if multiple properties listed by same user in short time
        $recentListings = $this->db->fetch(
            "SELECT COUNT(*) as count FROM properties 
             WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            [$property["user_id"]]
        );
        
        if ($recentListings && $recentListings["count"] > 5) {
            $fraudProbability += 0.2;
            $suspiciousIndicators[] = "Multiple listings in 24 hours (" . $recentListings["count"] . " properties)";
        }

        // Check for duplicate listings
        $similarProperties = $this->db->fetch(
            "SELECT COUNT(*) as count FROM properties 
             WHERE title = ? AND price = ? AND id != ?",
            [$property["title"], $property["price"], $propertyId]
        );
        
        if ($similarProperties && $similarProperties["count"] > 0) {
            $fraudProbability += 0.15;
            $suspiciousIndicators[] = "Duplicate/similar property listings found";
        }

        $riskLevel = $this->assessRiskLevel($fraudProbability);

        return [
            "fraud_probability" => min($fraudProbability, 1.0),
            "suspicious_indicators" => $suspiciousIndicators,
            "verification_required" => $fraudProbability > 0.5,
            "risk_level" => $riskLevel,
            "property_id" => $propertyId,
            "owner_id" => $property["user_id"],
            "recommendation" => $this->generateRecommendation($fraudProbability)
        ];
    }

    /**
     * Analyze transaction for fraud
     * @param int $transactionId
     * @return array
     */
    public function analyzeTransaction($transactionId)
    {
        // Fetch transaction details
        $tid = $this->getTenantId();
        $transaction = $this->db->fetch(
            "SELECT t.*, u.name as user_name, u.verification_status 
             FROM transactions t 
             LEFT JOIN users u ON t.user_id = u.id AND u.deleted_at IS NULL" . ($tid > 1 ? " AND u.tenant_id = ?" : "") . "
             WHERE t.id = ?",
            $tid > 1 ? [$tid, $transactionId] : [$transactionId]
        );

        if (!$transaction) {
            return [
                "fraud_risk" => [
                    "risk_score" => 1.0,
                    "risk_level" => "high"
                ],
                "anomalies" => ["Transaction not found"],
                "verification_needed" => true,
                "action_required" => "block"
            ];
        }

        $riskScore = 0.2; // Base risk
        $anomalies = [];

        // Large transaction amount check
        if ($transaction["amount"] > 5000000) { // 50 Lakh
            $riskScore += 0.2;
            $anomalies[] = "High transaction amount (₹" . number_format($transaction["amount"]) . ")";
        }

        // Check user verification
        if ($transaction["verification_status"] !== "verified") {
            $riskScore += 0.15;
            $anomalies[] = "Unverified user";
        }

        // Multiple transactions in short time
        $recentTransactions = $this->db->fetch(
            "SELECT COUNT(*) as count, SUM(amount) as total 
             FROM transactions 
             WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
            [$transaction["user_id"]]
        );
        
        if ($recentTransactions && $recentTransactions["count"] > 3) {
            $riskScore += 0.15;
            $anomalies[] = "Multiple transactions in 1 hour (" . $recentTransactions["count"] . ")";
        }

        // Check for unusual payment method
        $unusualMethods = ["crypto", "foreign_wire", "gift_card"];
        if (in_array($transaction["payment_method"], $unusualMethods)) {
            $riskScore += 0.25;
            $anomalies[] = "Unusual payment method: " . $transaction["payment_method"];
        }

        $riskLevel = $this->assessRiskLevel($riskScore);

        return [
            "fraud_risk" => [
                "risk_score" => min($riskScore, 1.0),
                "risk_level" => $riskLevel
            ],
            "anomalies" => $anomalies,
            "verification_needed" => $riskScore > 0.5,
            "action_required" => $this->getActionForRisk($riskLevel),
            "transaction_id" => $transactionId,
            "amount" => $transaction["amount"]
        ];
    }

    /**
     * Batch analyze multiple users
     * @param array $userIds
     * @return array
     */
    public function batchAnalyzeUsers($userIds)
    {
        $results = [];
        foreach ($userIds as $userId) {
            $results[$userId] = $this->analyzeUserBehavior($userId);
        }
        return $results;
    }

    /**
     * Get high risk users
     * @param float $threshold
     * @return array
     */
    public function getHighRiskUsers($threshold = 0.6)
    {
        // Get all users and analyze them
        $tid = $this->getTenantId();
        $users = $this->db->fetchAll("SELECT id FROM users WHERE status = 'active'" . ($tid > 1 ? " AND tenant_id = ?" : "") . " LIMIT 1000", $tid > 1 ? [$tid] : []);
        
        $highRiskUsers = [];
        foreach ($users as $user) {
            $analysis = $this->analyzeUserBehavior($user["id"]);
            if ($analysis["risk_score"] >= $threshold) {
                $highRiskUsers[] = [
                    "user_id" => $user["id"],
                    "risk_score" => $analysis["risk_score"],
                    "risk_factors" => $analysis["risk_factors"]
                ];
            }
        }

        // Sort by risk score
        usort($highRiskUsers, function ($a, $b) {
            return $b["risk_score"] <=> $a["risk_score"];
        });

        return $highRiskUsers;
    }

    /**
     * Generate recommendation based on risk score
     * @param float $riskScore
     * @return array
     */
    private function generateRecommendation($riskScore)
    {
        if ($riskScore > 0.8) {
            return [
                "action" => "block",
                "reason" => "High fraud risk detected",
                "verification_required" => true,
                "priority" => "urgent"
            ];
        } elseif ($riskScore > 0.6) {
            return [
                "action" => "monitor",
                "reason" => "Medium fraud risk - requires monitoring",
                "verification_required" => true,
                "priority" => "high"
            ];
        } elseif ($riskScore > 0.4) {
            return [
                "action" => "verify",
                "reason" => "Low to medium fraud risk",
                "verification_required" => false,
                "priority" => "medium"
            ];
        } else {
            return [
                "action" => "allow",
                "reason" => "Low fraud risk",
                "verification_required" => false,
                "priority" => "low"
            ];
        }
    }

    /**
     * Assess risk level
     * @param float $probability
     * @return string
     */
    private function assessRiskLevel($probability)
    {
        if ($probability > 0.8) {
            return "high";
        } elseif ($probability > 0.5) {
            return "medium";
        } elseif ($probability > 0.3) {
            return "low-medium";
        } else {
            return "low";
        }
    }

    /**
     * Get action based on risk level
     * @param string $riskLevel
     * @return string
     */
    private function getActionForRisk($riskLevel)
    {
        $actions = [
            "high" => "block",
            "medium" => "review",
            "low-medium" => "monitor",
            "low" => "proceed"
        ];

        return $actions[$riskLevel] ?? "review";
    }
}
