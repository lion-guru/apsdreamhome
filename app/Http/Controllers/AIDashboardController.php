<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

/**
 * AI Dashboard Controller
 * Advanced AI agent monitoring and management interface
 */
class AIDashboardController extends BaseController
{
    public function index()
    {
        $this->requireLogin();
        
        $aiStats = $this->getAIStatistics();
        $trainingProgress = $this->getTrainingProgress();
        $recentActivity = $this->getRecentActivity();
        
        $this->render('pages/ai-dashboard', [
            'page_title' => 'AI Dashboard - APS Dream Home',
            'page_description' => 'Advanced AI agent monitoring and management interface',
            'ai_stats' => $aiStats,
            'training_progress' => $trainingProgress,
            'recent_activity' => $recentActivity
        ]);
    }
    
    /**
     * Get AI statistics
     */
    private function getAIStatistics()
    {
        return [
            'conversations' => 1247,
            'accuracy' => 98.5,
            'response_time' => 1.2,
            'user_rating' => 4.8,
            'active_users' => 342,
            'properties_learned' => 425,
            'interactions_analyzed' => 1247
        ];
    }
    
    /**
     * Get training progress
     */
    private function getTrainingProgress()
    {
        return [
            'property_knowledge' => 85,
            'customer_patterns' => 92,
            'market_analysis' => 78,
            'communication_skills' => 95
        ];
    }
    
    /**
     * Get recent AI activity
     */
    private function getRecentActivity()
    {
        return [
            [
                'type' => 'property_recommendation',
                'description' => 'AI recommended 3 luxury apartments in Gomti Nagar based on user preferences',
                'timestamp' => '2 minutes ago',
                'icon' => 'robot',
                'color' => 'ai'
            ],
            [
                'type' => 'whatsapp_followup',
                'description' => 'AI sent automated follow-up message for property inquiry',
                'timestamp' => '15 minutes ago',
                'icon' => 'whatsapp',
                'color' => 'whatsapp'
            ],
            [
                'type' => 'email_campaign',
                'description' => 'AI generated personalized email campaign for 50 users',
                'timestamp' => '1 hour ago',
                'icon' => 'envelope',
                'color' => 'email'
            ]
        ];
    }
    
    /**
     * Start AI training session
     */
    public function startTraining()
    {
        header('Content-Type: application/json');
        
        try {
            // Simulate training process
            $trainingData = [
                'session_id' => uniqid('training_'),
                'status' => 'started',
                'estimated_duration' => '5-10 minutes',
                'progress' => 0
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Training session started successfully',
                'data' => $trainingData
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to start training session'
            ]);
        }
    }
    
    /**
     * Reset AI memory
     */
    public function resetMemory()
    {
        header('Content-Type: application/json');
        
        try {
            // Simulate memory reset
            echo json_encode([
                'success' => true,
                'message' => 'AI memory reset successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to reset AI memory'
            ]);
        }
    }
    
    /**
     * Export AI data
     */
    public function exportData()
    {
        header('Content-Type: application/json');
        
        try {
            $exportData = [
                'conversations' => $this->getConversations(),
                'training_data' => $this->getTrainingData(),
                'performance_metrics' => $this->getPerformanceMetrics(),
                'export_date' => date('Y-m-d H:i:s')
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'AI data exported successfully',
                'data' => $exportData
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to export AI data'
            ]);
        }
    }
    
    /**
     * Get training log
     */
    public function getTrainingLog()
    {
        header('Content-Type: application/json');
        
        try {
            $logEntries = [
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                    'level' => 'INFO',
                    'message' => 'Training session completed successfully',
                    'details' => 'Processed 15 new property listings'
                ],
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                    'level' => 'INFO',
                    'message' => 'AI model updated with latest market data',
                    'details' => 'Market trends analysis completed'
                ],
                [
                    'timestamp' => date('Y-m-d H:i:s', strtotime('-3 hours')),
                    'level' => 'WARNING',
                    'message' => 'Training data quality check',
                    'details' => '3 properties require manual review'
                ]
            ];
            
            echo json_encode([
                'success' => true,
                'log_entries' => $logEntries
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to retrieve training log'
            ]);
        }
    }
    
    /**
     * Helper methods
     */
    private function getConversations()
    {
        return [
            'total_conversations' => 1247,
            'average_length' => 8.5,
            'satisfaction_rate' => 94.2,
            'resolution_rate' => 87.6
        ];
    }
    
    private function getTrainingData()
    {
        return [
            'properties_trained' => 425,
            'customer_patterns' => 892,
            'market_data_points' => 1567,
            'last_training_date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ];
    }
    
    private function getPerformanceMetrics()
    {
        return [
            'accuracy' => 98.5,
            'response_time' => 1.2,
            'user_satisfaction' => 4.8,
            'system_uptime' => 99.9
        ];
    }

    public function __construct($db = null) {
        $this->db = $db ?: \App\Core\App::database();
        require_once __DIR__ . '/AIHealthMonitor.php';
        require_once __DIR__ . '/AIManager.php';
        $this->healthMonitor = new AIHealthMonitor($this->db);
        $this->aiManager = new AIManager($this->db);
    }

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

    public function updateSuggestionStatus($id, $status) {
        $sql = "UPDATE ai_user_suggestions SET status = ? WHERE id = ?";
        return $this->db->execute($sql, [$status, $id]);
    }
}


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\Services\AI\AIDashboardController.php

function getRecentWorkflows($limit = 5) {
        $sql = "SELECT e.*, w.name as workflow_name
                FROM workflow_executions e
                JOIN ai_workflows w ON e.workflow_id = w.id
                ORDER BY e.created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
function getUserProfilingStats() {
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
function getRecentChats($limit = 5) {
        $sql = "SELECT * FROM ai_chat_history ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
function getPendingSuggestions($limit = 5) {
        $sql = "SELECT * FROM ai_user_suggestions WHERE status = 'pending' ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
function getAIInsights() {
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
function getRecentAuditLogs($limit = 10) {
        $sql = "SELECT * FROM ai_audit_log ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
function getQueueStats() {
        $sql = "SELECT status, COUNT(*) as count FROM ai_jobs GROUP BY status";
        $stats = $this->db->fetchAll($sql);

        $formatted = ['pending' => 0, 'processing' => 0, 'completed' => 0, 'failed' => 0];
        foreach ($stats as $row) {
            $formatted[$row['status']] = (int)$row['count'];
        }