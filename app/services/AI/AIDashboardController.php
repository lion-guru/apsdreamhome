<?php

namespace App\Services\AI;
/**
 * AI Dashboard Controller
 * Fetches and formats data for the AI monitoring dashboard.
 */
class AIDashboardController {
    private $db;
    private $healthMonitor;
    private $aiManager;

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        require_once __DIR__ . '/AIHealthMonitor.php';
        require_once __DIR__ . '/AIManager.php';
        $this->healthMonitor = new AIHealthMonitor($this->db);
        $this->aiManager = new AIManager($this->db);
    }

    /**
     * Get consolidated dashboard data
     */
    public function getDashboardData() {
        return [
            'health' => $this->healthMonitor->checkHealth(),
            'recent_audit_logs' => $this->getRecentAuditLogs(10),
            'recent_workflows' => $this->getRecentWorkflows(5),
            'evolution_insights' => $this->aiManager->generateEvolutionInsights(),
            'queue_stats' => $this->getQueueStats(),
            'performance_metrics' => $this->getPerformanceMetrics(),
            'ai_insights' => $this->getAIInsights(),
            'user_profiling' => $this->getUserProfilingStats(),
            'pending_suggestions' => $this->getPendingSuggestions(5),
            'recent_chats' => $this->getRecentChats(5)
        ];
    }

    private function getRecentWorkflows($limit = 5) {
        $sql = "SELECT e.*, w.name as workflow_name
                FROM workflow_executions e
                JOIN ai_workflows w ON e.workflow_id = w.id
                ORDER BY e.created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }

    private function getUserProfilingStats() {
        $sql = "SELECT entity_type, entity_value, COUNT(*) as frequency
                FROM ai_knowledge_graph
                WHERE related_to_user IS NOT NULL
                GROUP BY entity_type, entity_value
                ORDER BY frequency DESC
                LIMIT 10";
        $data = $this->db->fetchAll($sql);

        $stats = [
            'top_interests' => $data,
            'total_profiles' => 0
        ];

        $resCount = $this->db->fetch("SELECT COUNT(DISTINCT related_to_user) as count FROM ai_knowledge_graph");
        if ($resCount) {
            $stats['total_profiles'] = (int)($resCount['count'] ?? 0);
        }

        return $stats;
    }

    private function getRecentChats($limit = 5) {
        $sql = "SELECT * FROM ai_chat_history ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }

    private function getPendingSuggestions($limit = 5) {
        $sql = "SELECT * FROM ai_user_suggestions WHERE status = 'pending' ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }

    public function updateSuggestionStatus($id, $status) {
        $sql = "UPDATE ai_user_suggestions SET status = ? WHERE id = ?";
        return $this->db->execute($sql, [$status, $id]);
    }

    /**
     * Get deep AI-driven insights from various modules
     */
    private function getAIInsights() {
        require_once __DIR__ . '/modules/DataAnalyst.php';
        require_once __DIR__ . '/modules/NLPProcessor.php';

        $analyst = new DataAnalyst($this->db);
        $nlp = new NLPProcessor();

        // 1. Lead Quality Insights
        $leadInsights = $analyst->analyzeData('leads');

        // 2. Intent Trend Analysis (Simulated from logs)
        $sql = "SELECT details FROM ai_audit_log WHERE action = 'nlp_analysis' ORDER BY created_at DESC LIMIT 50";
        $logs = $this->db->fetchAll($sql);

        $intents = [];
        foreach ($logs as $log) {
            $details = json_decode($log['details'], true);
            if (isset($details['intent']['name'])) {
                $name = $details['intent']['name'];
                $intents[$name] = ($intents[$name] ?? 0) + 1;
            }
        }
        arsort($intents);

        // 3. User Suggestion Summary
        $sql = "SELECT category, COUNT(*) as count FROM ai_user_suggestions GROUP BY category";
        $suggestions = $this->db->fetchAll($sql);

        $suggestionStats = [];
        foreach ($suggestions as $row) {
            $suggestionStats[$row['category']] = (int)$row['count'];
        }

        return [
            'lead_quality' => $leadInsights['quality_distribution'] ?? [],
            'top_intents' => array_slice($intents, 0, 5, true),
            'user_suggestions' => $suggestionStats,
            'last_updated' => date('Y-m-d H:i:s')
        ];
    }

    private function getRecentAuditLogs($limit = 10) {
        $sql = "SELECT * FROM ai_audit_log ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }

    private function getQueueStats() {
        $sql = "SELECT status, COUNT(*) as count FROM ai_jobs GROUP BY status";
        $stats = $this->db->fetchAll($sql);

        $formatted = ['pending' => 0, 'processing' => 0, 'completed' => 0, 'failed' => 0];
        foreach ($stats as $row) {
            $formatted[$row['status']] = (int)$row['count'];
        }
        return $formatted;
    }

    private function getPerformanceMetrics() {
        $sql = "SELECT task_type, AVG(execution_time_ms) as avg_time, COUNT(*) as total
                FROM ai_agent_logs
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY task_type";
        return $this->db->fetchAll($sql);
    }
}
