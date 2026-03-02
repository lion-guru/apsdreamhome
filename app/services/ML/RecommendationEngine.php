<?php
namespace App\Services;

use App\Core\Database;

class RecommendationEngine {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getPropertyRecommendations($userId, $limit = 10) {
        $recommendations = [];
        
        // Get user viewed properties
        $sql = "SELECT p.* FROM properties p
                JOIN property_views pv ON p.id = pv.property_id
                WHERE pv.user_id = ?
                AND p.status = \"active\"
                ORDER BY pv.created_at DESC
                LIMIT 5";
        
        $viewedProperties = $this->db->query($sql, [$userId])->fetchAll();
        
        // Get similar properties
        foreach ($viewedProperties as $property) {
            $similarSql = "SELECT p.* FROM properties p
                          WHERE p.property_type = ?
                          AND p.location = ?
                          AND p.id != ?
                          AND p.status = \"active\"
                          ORDER BY p.created_at DESC
                          LIMIT 2";
            
            $similarProperties = $this->db->query($similarSql, [
                $property["property_type"],
                $property["location"],
                $property["id"]
            ])->fetchAll();
            
            foreach ($similarProperties as $similar) {
                $recommendations[] = [
                    "property_id" => $similar["id"],
                    "property" => $similar,
                    "score" => 0.8,
                    "type" => "similar"
                ];
            }
        }
        
        return array_slice($recommendations, 0, $limit);
    }
    
    public function getUserRecommendations($userId, $limit = 5) {
        $recommendations = [];
        
        // Get users with similar viewing patterns
        $sql = "SELECT u2.id as user_id, u2.name, u2.email
                FROM users u1
                JOIN property_views pv1 ON u1.id = pv1.user_id
                JOIN property_views pv2 ON pv1.property_id = pv2.property_id
                JOIN users u2 ON pv2.user_id = u2.id
                WHERE u1.id = ? AND u2.id != ?
                GROUP BY u2.id
                ORDER BY COUNT(*) DESC
                LIMIT 5";
        
        $similarUsers = $this->db->query($sql, [$userId, $userId])->fetchAll();
        
        foreach ($similarUsers as $user) {
            $recommendations[] = [
                "user_id" => $user["user_id"],
                "name" => $user["name"],
                "email" => $user["email"],
                "similarity_score" => 0.7
            ];
        }
        
        return array_slice($recommendations, 0, $limit);
    }
    
    public function updateRecommendationModel() {
        // Simple model update
        return true;
    }
}
