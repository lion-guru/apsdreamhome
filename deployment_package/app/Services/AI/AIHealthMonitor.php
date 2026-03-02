<?php

namespace App\Services\AI;

use App\Services\AI\Modules\NLPProcessor;
use Exception;
/**
 * AI Health Monitor
 * Provides real-time diagnostics for the AI ecosystem.
 */
class AIHealthMonitor {
    private $db;

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
    }

    public function checkHealth() {
        $stats = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => []
        ];

        // 1. Database Connection Check
        $dbCheck = $this->checkDatabase();
        $stats['checks']['database'] = $dbCheck;
        if ($dbCheck['status'] !== 'ok') $stats['status'] = 'degraded';

        // 2. Pending Jobs Check
        $jobsCheck = $this->checkPendingJobs();
        $stats['checks']['jobs_queue'] = $jobsCheck;
        if ($jobsCheck['pending_count'] > 50) $stats['status'] = 'warning';

        // 3. Recent Failures Check
        $failuresCheck = $this->checkRecentFailures();
        $stats['checks']['recent_failures'] = $failuresCheck;
        if ($failuresCheck['count'] > 5) $stats['status'] = 'critical';

        // 4. Learning System Check
        $learningCheck = $this->checkLearningSystem();
        $stats['checks']['learning_system'] = $learningCheck;

        // 5. AI Predictive Failure Analysis
        $predictiveCheck = $this->predictiveFailureAnalysis();
        $stats['checks']['predictive_analysis'] = $predictiveCheck;
        if ($predictiveCheck['risk_level'] === 'high') $stats['status'] = 'warning';

        // 6. NLP Processor Check
        $nlpCheck = $this->checkNLP();
        $stats['checks']['nlp'] = $nlpCheck;
        if ($nlpCheck['status'] !== 'ok') $stats['status'] = 'degraded';

        return $stats;
    }

    private function checkNLP() {
        try {
            require_once __DIR__ . '/modules/NLPProcessor.php';
            $nlp = new NLPProcessor();
            $start = microtime(true);
            $nlp->analyze("Health check message");
            $latency = round((microtime(true) - $start) * 1000, 2);
            return [
                'status' => 'ok',
                'latency_ms' => $latency
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * AI-based predictive failure analysis
     * Analyzes error trends to predict potential system failures
     */
    private function predictiveFailureAnalysis() {
        // Get failure counts for last 3 hours (hour by hour)
        $sql = "SELECT
                    HOUR(created_at) as hr,
                    COUNT(*) as cnt
                FROM ai_agent_logs
                WHERE status = 'failed'
                AND created_at > DATE_SUB(NOW(), INTERVAL 3 HOUR)
                GROUP BY hr
                ORDER BY hr ASC";

        $trends = $this->db->fetchAll($sql);

        $risk = 'low';
        $message = 'Error rates are stable.';

        if (count($trends) >= 2) {
            $lastHour = end($trends)['cnt'];
            $prevHour = $trends[count($trends)-2]['cnt'];

            if ($lastHour > $prevHour * 1.5 && $lastHour > 10) {
                $risk = 'high';
                $message = 'Rapidly increasing error rate detected. Potential cascading failure.';
            } elseif ($lastHour > $prevHour && $lastHour > 5) {
                $risk = 'medium';
                $message = 'Slight upward trend in errors. Monitoring closely.';
            }
        }

        return [
            'risk_level' => $risk,
            'prediction' => $message,
            'data_points' => count($trends)
        ];
    }

    private function checkDatabase() {
        try {
            $start = microtime(true);
            $res = $this->db->fetch("SELECT 1");
            $latency = round((microtime(true) - $start) * 1000, 2);

            // Check essential tables
            $required_tables = [
                'ai_agents', 'ai_workflows', 'ai_audit_log',
                'ai_agent_logs', 'workflow_executions', 'ai_user_suggestions'
            ];
            $missing = [];

            foreach ($required_tables as $table) {
                $check = $this->db->fetch("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?", [$table]);
                if (!$check) $missing[] = $table;
            }

            return [
                'status' => empty($missing) ? 'ok' : 'degraded',
                'latency_ms' => $latency,
                'tables_ok' => empty($missing),
                'missing_tables' => $missing,
                'driver' => 'ORM'
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function checkPendingJobs() {
        $sql = "SELECT COUNT(*) as cnt FROM ai_jobs WHERE status = 'pending'";
        $row = $this->db->fetch($sql);
        $count = (int)($row['cnt'] ?? 0);
        return [
            'pending_count' => $count,
            'status' => ($count < 100) ? 'ok' : 'overloaded'
        ];
    }

    private function checkRecentFailures() {
        $sql = "SELECT COUNT(*) as cnt FROM ai_agent_logs WHERE status = 'failed' AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $row = $this->db->fetch($sql);
        $count = (int)($row['cnt'] ?? 0);
        return [
            'count' => $count,
            'status' => ($count < 10) ? 'ok' : 'high_failure_rate'
        ];
    }

    private function checkLearningSystem() {
        $sql = "SELECT COUNT(*) as cnt FROM ai_knowledge_graph";
        $row = $this->db->fetch($sql);
        $count = (int)($row['cnt'] ?? 0);
        return [
            'knowledge_entities' => $count,
            'status' => 'ok'
        ];
    }
}
