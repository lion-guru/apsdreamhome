<?php
/**
 * AISystemController — Unified AI Dashboard + Agent Management
 * 
 * Controls all 5 AI agents from one dashboard:
 * 1. Smart Lead Qualifier
 * 2. Property Matchmaker
 * 3. Hindi Conversational Bot
 * 4. Smart Scheduler
 * 5. Market Intelligence
 */

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use App\Services\AI\AIGateway;
use App\Services\AI\FreeAIEngines;
use App\Services\AI\Agents\SmartLeadQualifierAgent;
use App\Services\AI\Agents\PropertyMatchmakerAgent;
use App\Services\AI\Agents\HindiConversationalBot;
use App\Services\AI\Agents\SmartSchedulerAgent;
use App\Services\AI\Agents\MarketIntelligenceAgent;

class AISystemController extends AdminController
{
    /**
     * Main AI Dashboard
     */
    public function index()
    {
        $this->requireAdmin();
        $db = Database::getInstance()->getConnection();
        $gateway = AIGateway::getInstance();

        // Gateway stats
        $gatewayStats = $gateway->getStats();

        // Free AI Engines status
        $freeEngines = FreeAIEngines::getInstance();
        $engineStatus = $freeEngines->getStatus();

        // Agent stats
        $agents = [
            'qualifier' => ['name' => 'Smart Lead Qualifier', 'icon' => 'fas fa-magnet', 'color' => '#3b82f6', 'class' => SmartLeadQualifierAgent::class],
            'matchmaker' => ['name' => 'Property Matchmaker', 'icon' => 'fas fa-home', 'color' => '#10b981', 'class' => PropertyMatchmakerAgent::class],
            'hindi_bot' => ['name' => 'Hindi Conversational Bot', 'icon' => 'fas fa-comments', 'color' => '#f59e0b', 'class' => HindiConversationalBot::class],
            'scheduler' => ['name' => 'Smart Scheduler', 'icon' => 'fas fa-calendar-check', 'color' => '#8b5cf6', 'class' => SmartSchedulerAgent::class],
            'market' => ['name' => 'Market Intelligence', 'icon' => 'fas fa-chart-line', 'color' => '#ec4899', 'class' => MarketIntelligenceAgent::class],
        ];

        $agentStats = [];
        foreach ($agents as $key => $agent) {
            try {
                $stat = $db->query(
                    "SELECT COUNT(*) as total,
                            SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                            MAX(created_at) as last_run
                     FROM agent_task_logs WHERE agent_type = ? AND DATE(created_at) = CURDATE()",
                    ['ai_' . $key]
                )->fetch(\PDO::FETCH_ASSOC) ?: ['total' => 0, 'completed' => 0, 'last_run' => null];
                $agentStats[$key] = $stat;
            } catch (\Throwable $e) {
                $agentStats[$key] = ['total' => 0, 'completed' => 0, 'last_run' => null];
            }
        }

        // System health
        $health = [
            'gemini_api' => $gateway->isGeminiAvailable() ? 'connected' : 'not_configured',
            'intent_patterns' => (int)$db->query("SELECT COUNT(*) FROM ai_intent_patterns WHERE is_active = 1")->fetchColumn(),
            'leads_today' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
            'unqualified_leads' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE (lead_score IS NULL OR lead_score = 0) AND deleted_at IS NULL")->fetchColumn(),
            'scheduled_visits' => (int)$db->query("SELECT COUNT(*) FROM site_visits WHERE visit_date >= CURDATE() AND status = 'scheduled'")->fetchColumn(),
            'pending_tasks' => (int)$db->query("SELECT COUNT(*) FROM crm_tasks WHERE status = 'pending'")->fetchColumn(),
        ];

        // Recent AI activity
        $recentActivity = $db->query(
            "SELECT * FROM agent_task_logs WHERE DATE(created_at) = CURDATE() ORDER BY created_at DESC LIMIT 15"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return $this->render('admin/ai/dashboard', [
            'agents' => $agents,
            'agent_stats' => $agentStats,
            'gateway_stats' => $gatewayStats,
            'engine_status' => $engineStatus,
            'health' => $health,
            'recent_activity' => $recentActivity,
            'page_title' => 'AI System Dashboard',
        ]);
    }

    /**
     * Run a specific agent
     */
    public function runAgent()
    {
        $this->requireAdmin();
        $agentType = $_POST['agent_type'] ?? $_GET['agent'] ?? '';
        $action = $_POST['action'] ?? 'batch';

        switch ($agentType) {
            case 'qualifier':
                $agent = new SmartLeadQualifierAgent();
                $result = $action === 'batch' ? $agent->processBatch(50) : $agent->qualifyLead((int)($_POST['lead_id'] ?? 0));
                break;

            case 'matchmaker':
                $agent = new PropertyMatchmakerAgent();
                $result = $action === 'batch' ? $agent->batchMatch(20) : $agent->matchForLead((int)($_POST['lead_id'] ?? 0));
                break;

            case 'hindi_bot':
                $agent = new HindiConversationalBot();
                $result = $agent->chat($_POST['message'] ?? 'Namaste', ['user_id' => $_SESSION['admin_id'] ?? null]);
                break;

            case 'scheduler':
                $agent = new SmartSchedulerAgent();
                if ($action === 'reminders') {
                    $result = $agent->sendReminders();
                } elseif ($action === 'reschedule') {
                    $result = $agent->autoReschedule();
                } else {
                    $result = $agent->scheduleVisit($_POST);
                }
                break;

            case 'market':
                $agent = new MarketIntelligenceAgent();
                $result = $agent->getMarketReport();
                break;

            default:
                $result = ['error' => 'Unknown agent type'];
        }

        // Log the execution
        try {
            Database::getInstance()->getConnection()->prepare(
                "INSERT INTO agent_task_logs (agent_type, action_type, details, status, created_at) VALUES (?, ?, ?, 'completed', NOW())"
            )->execute(['ai_' . $agentType, $action, json_encode($result)]);
        } catch (\Throwable $e) { /* non-critical */ }

        if (is_ajax()) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

        $this->setFlash('success', "Agent {$agentType} executed: {$action}");
        return $this->redirect('/admin/ai-system');
    }

    /**
     * Live AI engine health check (AJAX/GET) — pings Ollama + runs a real
     * generate test so admins can confirm the local LLM is actually serving
     * the chatbot, voice agent and all 5 dashboard agents.
     */
    public function engineHealth()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $free = FreeAIEngines::getInstance();
        $gateway = AIGateway::getInstance();

        // Live Ollama ping (ignore static cache by re-pinging)
        $ollamaUp = false;
        $ollamaModel = $free->getOllamaModel();
        $ollamaTest = null;
        try {
            $ch = curl_init(rtrim($free->getOllamaUrl(), '/') . '/api/tags');
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 3]);
            curl_exec($ch);
            $ollamaUp = curl_getinfo($ch, CURLINFO_HTTP_CODE) === 200;
            curl_close($ch);

            if ($ollamaUp) {
                $t0 = microtime(true);
                $res = $free->generate('Reply with exactly: OK', ['max_tokens' => 8, 'temperature' => 0], 'chat');
                $ollamaTest = trim($res['text'] ?? '');
                $ollamaMs = round((microtime(true) - $t0) * 1000);
            }
        } catch (\Throwable $e) {
            $ollamaTest = 'error: ' . $e->getMessage();
        }

        $status = [
            'ollama' => [
                'up' => $ollamaUp,
                'model' => $ollamaModel,
                'test_reply' => $ollamaTest,
                'response_ms' => $ollamaMs ?? null,
                'cost' => 'Free (local)',
            ],
            'gemini' => [
                'configured' => $gateway->isGeminiAvailable(),
                'cost' => 'Free tier',
            ],
            'groq' => [
                'configured' => $free->isGroqConfigured(),
                'cost' => 'Free tier',
            ],
            'openrouter' => [
                'configured' => $free->isOpenRouterConfigured(),
                'cost' => 'Free tier',
            ],
            'primary_engine' => $ollamaUp ? 'ollama (' . $ollamaModel . ')' : ($gateway->isGeminiAvailable() ? 'gemini' : 'rule-fallback'),
            'checked_at' => date('Y-m-d H:i:s'),
        ];

        echo json_encode($status);
        exit;
    }

    /**
     * Chat API endpoint — for chatbot widget
     */
    public function chat()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'POST required']);
            exit;
        }

        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true);
        $message = $data['message'] ?? $_POST['message'] ?? '';
        $sessionId = $data['session_id'] ?? $_SESSION['chat_session_id'] ?? 'web_' . md5(time());

        if (empty($message)) {
            echo json_encode(['error' => 'Message required']);
            exit;
        }

        $bot = new HindiConversationalBot();
        $result = $bot->chat($message, [
            'session_id' => $sessionId,
            'user_id' => $_SESSION['user_id'] ?? null,
            'history' => $data['history'] ?? [],
        ]);

        echo json_encode($result);
        exit;
    }

    /**
     * Market Intelligence report page
     */
    public function marketReport()
    {
        $this->requireAdmin();
        $agent = new MarketIntelligenceAgent();
        $report = $agent->getMarketReport();

        return $this->render('admin/ai/market_report', [
            'report' => $report,
            'page_title' => 'Market Intelligence Report',
        ]);
    }

    /**
     * Lead qualification page — view and manage unqualified leads
     */
    public function qualifier()
    {
        $this->requireAdmin();
        $db = Database::getInstance()->getConnection();

        $unqualified = $db->query(
            "SELECT l.*, u.name as assignee_name FROM leads l
             LEFT JOIN users u ON l.assigned_to = u.id
             WHERE l.deleted_at IS NULL AND (l.lead_score IS NULL OR l.lead_score = 0)
             ORDER BY l.created_at DESC LIMIT 50"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $recentlyQualified = $db->query(
            "SELECT l.*, u.name as assignee_name FROM leads l
             LEFT JOIN users u ON l.assigned_to = u.id
             WHERE l.deleted_at IS NULL AND l.lead_score > 0 AND DATE(l.updated_at) = CURDATE()
             ORDER BY l.lead_score DESC LIMIT 20"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        return $this->render('admin/ai/qualifier', [
            'unqualified' => $unqualified,
            'recently_qualified' => $recentlyQualified,
            'page_title' => 'Smart Lead Qualifier',
        ]);
    }

    /**
     * AI API Settings — configure free API keys
     */
    public function settings()
    {
        $this->requireAdmin();
        $db = Database::getInstance()->getConnection();
        $settings = $db->query("SELECT * FROM ai_settings LIMIT 1")->fetch(\PDO::FETCH_ASSOC) ?: [];
        $engineStatus = FreeAIEngines::getInstance()->getStatus();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $groqKey = trim($_POST['groq_api_key'] ?? '');
            $openrouterKey = trim($_POST['openrouter_api_key'] ?? '');
            $ollamaUrl = trim($_POST['ollama_url'] ?? 'http://localhost:11434');
            $ollamaModel = trim($_POST['ollama_model'] ?? 'llama3.2:3b');
            $geminiKey = trim($_POST['gemini_api_key'] ?? '');

            if (!empty($settings['id'])) {
                $db->prepare("UPDATE ai_settings SET groq_api_key=?, openrouter_api_key=?, ollama_url=?, ollama_model=?, updated_at=NOW() WHERE id=?")
                    ->execute([$groqKey, $openrouterKey, $ollamaUrl, $ollamaModel, $settings['id']]);
            }
            // Also store gemini key in api_key column
            if (!empty($settings['id'])) {
                $db->prepare("UPDATE ai_settings SET api_key=? WHERE id=?")->execute([$geminiKey, $settings['id']]);
            }

            $this->setFlash('success', 'AI API settings updated');
            return $this->redirect('/admin/ai-system/settings');
        }

        return $this->render('admin/ai/settings', [
            'settings' => $settings,
            'engine_status' => $engineStatus,
            'page_title' => 'AI API Settings',
        ]);
    }
}

function is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}
