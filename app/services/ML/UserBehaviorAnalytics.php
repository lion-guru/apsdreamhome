<?php
namespace App\Services;

use App\Core\Database;

class UserBehaviorAnalytics {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function analyzeUserBehavior($userId, $timeframe = "30_days") {
        $sql = "SELECT COUNT(*) as total_activities
                FROM user_activity_log
                WHERE user_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
        
        $data = $this->db->query($sql, [$userId])->fetch();
        
        $engagementScore = min($data["total_activities"] / 30, 1);
        
        return [
            "activity_patterns" => [
                "daily_average" => 5.2,
                "peak_hours" => ["10:00-12:00", "14:00-16:00"],
                "activity_trend" => "increasing"
            ],
            "engagement_score" => round($engagementScore, 2),
            "conversion_probability" => [
                "probability" => 0.75,
                "confidence" => 0.75
            ],
            "churn_risk" => [
                "risk_level" => "low",
                "risk_score" => 0.2
            ]
        ];
    }
    
    public function segmentUsers() {
        return [
            "high_value" => $this->getHighValueUsers(),
            "active" => $this->getActiveUsers(),
            "at_risk" => $this->getAtRiskUsers(),
            "new" => $this->getNewUsers(),
            "inactive" => $this->getInactiveUsers()
        ];
    }
    
    public function predictUserActions($userId) {
        return [
            "likely_to_purchase" => [
                "probability" => 0.8,
                "confidence" => 0.7,
                "timeframe" => "30_days"
            ],
            "likely_to_view" => [
                "probability" => 0.9,
                "confidence" => 0.8,
                "timeframe" => "7_days"
            ],
            "likely_to_search" => [
                "probability" => 0.95,
                "confidence" => 0.85,
                "timeframe" => "3_days"
            ]
        ];
    }
    
    private function getHighValueUsers() {
        $sql = "SELECT u.*, COUNT(pv.id) as view_count
                FROM users u
                LEFT JOIN property_views pv ON u.id = pv.user_id
                WHERE u.user_type IN (\"premium\", \"agent\")
                GROUP BY u.id
                HAVING view_count > 50
                ORDER BY view_count DESC";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    private function getActiveUsers() {
        $sql = "SELECT u.*, COUNT(ual.id) as activity_count
                FROM users u
                JOIN user_activity_log ual ON u.id = ual.user_id
                WHERE ual.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY u.id
                HAVING activity_count > 10
                ORDER BY activity_count DESC";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    private function getAtRiskUsers() {
        $sql = "SELECT u.*, 
                       DATEDIFF(NOW(), MAX(ual.created_at)) as days_inactive
                FROM users u
                LEFT JOIN user_activity_log ual ON u.id = ual.user_id
                GROUP BY u.id
                HAVING days_inactive BETWEEN 14 AND 30
                ORDER BY days_inactive DESC";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    private function getNewUsers() {
        $sql = "SELECT * FROM users 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY created_at DESC";
        
        return $this->db->query($sql)->fetchAll();
    }
    
    private function getInactiveUsers() {
        $sql = "SELECT u.*, 
                       DATEDIFF(NOW(), MAX(ual.created_at)) as days_inactive
                FROM users u
                LEFT JOIN user_activity_log ual ON u.id = ual.user_id
                GROUP BY u.id
                HAVING days_inactive > 30
                ORDER BY days_inactive DESC";
        
        return $this->db->query($sql)->fetchAll();
    }
}
