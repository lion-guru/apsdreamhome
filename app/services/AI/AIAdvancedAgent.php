<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Services\AI;

use App\Traits\ServiceTenantTrait;

/**
 * AI Advanced Agent
 * Orchestrates high-level functionalities: Implementation guidance, Learning, and Multi-mode operations.
 */
class AIAdvancedAgent {
    use ServiceTenantTrait;

    private $db;
    private $aiManager;
    private $toolsManager;

    public function __construct($aiManager) {
        $this->db = \App\Core\Database\Database::getInstance();
        $this->aiManager = $aiManager;
        $this->toolsManager = new AIToolsManager();
    }

    /**
     * Switch Agent Mode (Assistant vs Leader)
     */
    public function setMode($userId, $mode) {
        if (!in_array($mode, ['assistant', 'leader'])) return false;
        
        $sql = "INSERT INTO ai_agent_state (user_id, current_mode) VALUES (?, ?) ON DUPLICATE KEY UPDATE current_mode = ?";
        return $this->db->execute($sql, [$userId, $mode, $mode]);
    }

    /**
     * Get Implementation Guide for a tool
     */
    public function getImplementationGuide($toolId, $platform = 'web') {
        $sql = "SELECT * FROM ai_implementation_guides WHERE tool_id = ? AND platform = ?";
        return $this->db->fetch($sql, [$toolId, $platform]);
    }

    /**
     * Track Learning Progress
     */
    public function updateLearningProgress($userId, $moduleName, $status, $score = 0, $skills = []) {
        $skillsJson = json_encode($skills);
        $completedAt = ($status === 'completed') ? date('Y-m-d H:i:s') : null;
        
        $columns = array_merge(['user_id', 'module_name', 'status', 'score', 'skills_acquired', 'completed_at'], array_keys($this->tenantInsertData()));
        $values  = array_merge([$userId, $moduleName, $status, $score, $skillsJson, $completedAt], array_values($this->tenantInsertData()));
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = "INSERT INTO ai_learning_progress (" . implode(', ', $columns) . ") 
                VALUES ({$placeholders}) 
                ON DUPLICATE KEY UPDATE status = ?, score = ?, skills_acquired = ?, completed_at = ?";
        
        $params = array_merge($values, [$status, $score, $skillsJson, $completedAt]);
        return $this->db->execute($sql, $params);
    }

    /**
     * Generate Personal Learning Plan (Automated)
     */
    public function generateLearningPlan($userId) {
        try {
            $profile = $this->db->fetch("SELECT * FROM user_ai_profiles WHERE user_id = ?", [$userId]);
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        $plan = [
            'userId' => $userId,
            'modules' => [],
            'suggested_tools' => $this->toolsManager->getRecommendations($userId)
        ];

        // logic to build plan based on profile
        if ($profile && $profile['org_level'] === 'executive') {
            $plan['modules'][] = ['name' => 'AI Strategy for Leaders', 'duration' => '4 weeks'];
            $plan['modules'][] = ['name' => 'Resource Allocation with AI', 'duration' => '2 weeks'];
        } else {
            $plan['modules'][] = ['name' => 'Python for AI Development', 'duration' => '8 weeks'];
            $plan['modules'][] = ['name' => 'API Integration Masterclass', 'duration' => '4 weeks'];
        }

        return $plan;
    }

    /**
     * Handle Leadership Decisions (Leader Mode logic)
     */
    public function executeStrategicTask($userId, $taskDescription) {
        $state = $this->db->fetch("SELECT current_mode FROM ai_agent_state WHERE user_id = ?", [$userId]);

        if (!$state || $state['current_mode'] !== 'leader') {
            return ['status' => 'error', 'message' => 'Agent is in Assistant mode. Switch to Leader mode for strategic decisions.'];
        }

        // Use AI Manager to process strategic task
        return $this->aiManager->executeTask(0, 'strategic_planning', ['task' => $taskDescription]);
    }
}
?>
