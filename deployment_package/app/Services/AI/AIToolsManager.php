<?php

namespace App\Services\AI;
/**
 * AI Tools Manager
 * Manages the database of 1000+ AI tools and provides recommendation engine logic.
 */
class AIToolsManager {
    private $db;

    public function __construct() {
        $this->db = \App\Core\App::database();
    }

    /**
     * Get tools based on category and filters
     */
    public function getTools($filters = []) {
        $sql = "SELECT * FROM ai_tools_directory WHERE 1=1";
        $params = [];

        if (isset($filters['category'])) {
            $sql .= " AND category = ?";
            $params[] = $filters['category'];
        }

        if (isset($filters['complexity'])) {
            $sql .= " AND integration_complexity = ?";
            $params[] = $filters['complexity'];
        }

        if (isset($filters['search'])) {
            $sql .= " AND (name LIKE ? OR description LIKE ? OR tags LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Recommendation Engine (Simulated Machine Learning logic)
     */
    public function getRecommendations($userId) {
        // Fetch user profile
        $profile = $this->db->fetch("SELECT * FROM user_ai_profiles WHERE user_id = ?", [$userId]);

        if (!$profile) return $this->getTools(['category' => 'free']); // Default

        $industry = $profile['industry'];
        $skills = json_decode($profile['technical_skills'], true) ?: [];
        $interests = json_decode($profile['interests'], true) ?: [];

        // Simple scoring algorithm based on profile matching
        $tools = $this->getTools();
        $scoredTools = [];

        foreach ($tools as $tool) {
            $score = 0;
            $toolTags = explode(',', $tool['tags']);
            
            // Match industry
            if (stripos($tool['description'], $industry) !== false) $score += 10;
            
            // Match interests
            foreach ($interests as $interest) {
                if (in_array($interest, $toolTags)) $score += 5;
            }

            // Technical complexity matching
            if ($profile['org_level'] === 'executive' && $tool['integration_complexity'] === 'low') $score += 5;
            if ($profile['org_level'] === 'operational' && $tool['integration_complexity'] === 'high') $score += 3;

            $tool['relevance_score'] = $score;
            $scoredTools[] = $tool;
        }

        // Sort by score
        usort($scoredTools, function($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });

        return array_slice($scoredTools, 0, 10);
    }

    /**
     * Add or Update tool information
     */
    public function saveTool($data) {
        $fields = ['name', 'category', 'description', 'features', 'use_cases', 'technical_requirements', 'integration_complexity', 'pricing_structure', 'api_available', 'tags'];
        
        if (isset($data['id'])) {
            // Update logic
            $sql = "UPDATE ai_tools_directory SET " . implode('=?, ', $fields) . "=? WHERE id=?";
            $params = [];
            foreach ($fields as $f) $params[] = is_array($data[$f]) ? json_encode($data[$f]) : $data[$f];
            $params[] = $data['id'];
            return $this->db->execute($sql, $params);
        } else {
            // Insert logic
            $sql = "INSERT INTO ai_tools_directory (" . implode(', ', $fields) . ") VALUES (" . str_repeat('?,', count($fields)-1) . "?)";
            $params = [];
            foreach ($fields as $f) $params[] = is_array($data[$f] ?? '') ? json_encode($data[$f]) : ($data[$f] ?? '');
            return $this->db->execute($sql, $params);
        }
    }

    /**
     * Get Implementation Guide for a tool
     */
    public function getImplementationGuide($toolId) {
        return $this->db->fetch("SELECT * FROM ai_implementation_guides WHERE tool_id = ?", [$toolId]);
    }

    /**
     * Track Learning Progress
     */
    public function updateLearningProgress($userId, $toolId, $status, $score = 0) {
        $sql = "INSERT INTO ai_learning_progress (user_id, tool_id, status, completion_score) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE status = VALUES(status), completion_score = VALUES(completion_score), last_updated = CURRENT_TIMESTAMP";
        return $this->db->execute($sql, [$userId, $toolId, $status, $score]);
    }

    /**
     * Get Weekly Knowledge Updates (Simulated)
     */
    public function getWeeklyUpdates() {
        return [
            ['title' => 'New GPT-5 Integration Patterns', 'date' => date('Y-m-d'), 'relevance' => 'high'],
            ['title' => 'Stable Diffusion WebUI 2.0 Released', 'date' => date('Y-m-d', strtotime('-2 days')), 'relevance' => 'medium'],
            ['title' => 'Open Source LLM Performance Benchmarks Q1 2026', 'date' => date('Y-m-d', strtotime('-4 days')), 'relevance' => 'high']
        ];
    }
}
?>
