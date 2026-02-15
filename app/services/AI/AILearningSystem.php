<?php

namespace App\Services\AI;
/**
 * AI Learning System
 * Processes interaction data to update Knowledge Graph and optimize bot performance.
 */
class AILearningSystem {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
    }

    /**
     * Process recent interaction logs to extract knowledge
     */
    public function processInteractions() {
        $sql = "SELECT * FROM ai_interaction_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $logs = $this->db->fetchAll($sql);

        $learnedCount = 0;
        foreach ($logs as $log) {
            if ($log['is_useful'] === '1' || ($log['sentiment'] === 'positive' && $log['intent'] !== 'greeting')) {
                if ($this->updateKnowledgeGraph($log)) {
                    $learnedCount++;
                }
            }
        }
        return $learnedCount;
    }

    /**
     * Update Knowledge Graph with new findings
     */
    private function updateKnowledgeGraph($log) {
        $entityName = $log['intent'];
        $entityType = $log['intent'];
        $summary = $log['bot_response'];
        $contextData = json_encode(['summary' => $summary, 'source_log' => $log['id']]);

        // Check if entity already exists to refine confidence
        $sql = "SELECT id, confidence_score FROM ai_knowledge_graph WHERE entity_name = ?";
        $row = $this->db->fetch($sql, [$entityName]);

        if ($row) {
            $newScore = min(1.0, $row['confidence_score'] + 0.05);
            $sql = "UPDATE ai_knowledge_graph SET confidence_score = ?, context_data = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $this->db->execute($sql, [$newScore, $contextData, $row['id']]);
        } else {
            $sql = "INSERT INTO ai_knowledge_graph (entity_name, entity_type, context_data, confidence_score) VALUES (?, ?, ?, 0.7)";
            $this->db->execute($sql, [$entityName, $entityType, $contextData]);
        }

        return true;
    }

    /**
     * Analyze performance metrics and suggest optimizations
     */
    public function optimizePerformance() {
        $sql = "SELECT AVG(response_time_ms) as avg_time, AVG(accuracy_score) as avg_acc FROM ai_bot_performance WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $res = $this->db->fetch($sql);

        $notes = "Daily performance check: Avg Time " . round($res['avg_time'] ?? 0) . "ms, Avg Accuracy " . round(($res['avg_acc'] ?? 0) * 100) . "%.";

        if (($res['avg_time'] ?? 0) > 500) {
            $notes .= " Suggesting caching for frequent queries.";
        }
        if (($res['avg_acc'] ?? 0) < 0.8) {
            $notes .= " Suggesting more training data for low-confidence intents.";
        }

        $sql = "INSERT INTO ai_bot_performance (optimization_notes) VALUES (?)";
        $this->db->execute($sql, [$notes]);

        return $notes;
    }

    /**
     * Seed initial knowledge graph for MVP
     */
    public function seedInitialKnowledge() {
        $initialData = [
            ['pricing_inquiry', 'pricing', 'हमारे पास 25 लाख से लेकर 2 करोड़ तक के प्रीमियम फ्लैट्स और विला उपलब्ध हैं।'],
            ['location_query', 'location', 'हमारे प्रोजेक्ट्स मुख्य रूप से पुणे, मुंबई और नाशिक के प्राइम लोकेशंस पर स्थित हैं।'],
            ['amenities_query', 'amenities', 'हमारे सभी प्रोजेक्ट्स में क्लब हाउस, स्विमिंग पूल, जिम और 24/7 सुरक्षा उपलब्ध है।']
        ];

        foreach ($initialData as $data) {
            $context = json_encode(['summary' => $data[2]]);
            $sql = "INSERT IGNORE INTO ai_knowledge_graph (entity_name, entity_type, context_data, confidence_score) VALUES (?, ?, ?, 0.95)";
            $this->db->execute($sql, [$data[0], $data[1], $context]);
        }
    }

    /**
     * Get knowledge by entity name
     */
    public function getKnowledge($entityName) {
        $sql = "SELECT * FROM ai_knowledge_graph WHERE entity_name = ? ORDER BY confidence_score DESC LIMIT 1";
        return $this->db->fetch($sql, [$entityName]);
    }

    /**
     * Generate a personalized learning plan for a user
     */
    public function generatePersonalizedPlan($userId, $rank = null) {
        if (!$rank) {
            // Get user profile to personalize if rank not provided
            $sql = "SELECT org_level, industry FROM user_ai_profiles WHERE user_id = ?";
            $profile = $this->db->fetch($sql, [$userId]);

            $orgLevel = $profile['org_level'] ?? 'operational';
            $industry = $profile['industry'] ?? 'general business';

            $rank = 'beginner';
            if ($orgLevel === 'managerial') $rank = 'intermediate';
            if ($orgLevel === 'executive') $rank = 'advanced';
        } else {
            $industry = 'general business';
        }

        // Pick 3 modules based on rank
        $sql = "SELECT id FROM training_modules WHERE required_rank = ? OR required_rank = 'beginner' LIMIT 3";
        $results = $this->db->fetchAll($sql, [$rank]);
        $moduleIds = [];
        foreach ($results as $row) {
            $moduleIds[] = intval($row['id']);
        }

        $title = ucfirst($rank) . " AI Roadmap";
        $goal = "Master AI automation for " . ($rank == 'beginner' ? 'basic tasks' : ($rank == 'intermediate' ? 'complex workflows' : 'strategic leadership'));
        $modulesJson = json_encode($moduleIds);

        $sql = "INSERT INTO personalized_learning_plans (user_id, title, goal, module_ids, created_at) VALUES (?, ?, ?, ?, NOW())";
        if ($this->db->execute($sql, [$userId, $title, $goal, $modulesJson])) {
            return [
                'id' => $this->db->lastInsertId(),
                'user_id' => $userId,
                'title' => $title,
                'goal' => $goal,
                'module_ids' => $moduleIds
            ];
        }
        return null;
    }
}
?>
