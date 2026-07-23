<?php

namespace App\Http\Controllers\Admin;

// AdminController resolved via namespace

/**
 * Agentic AI System — Multi-Agent Autonomous Platform
 *
 * Specialized AI Agents that run the company 24/7:
 * 1. Lead Generation Agent — auto-qualifies, scores, nurtures leads
 * 2. Sales Agent — booking follow-ups, payment reminders, closing
 * 3. Marketing Agent — campaigns, social media, content
 * 4. CEO Dashboard Agent — executive insights, alerts, decisions
 * 5. HR Agent — attendance, leaves, payroll, hiring
 * 6. Finance Agent — TDS, GST, reconciliation, reports
 * 7. Operations Agent — site visits, maintenance, inventory
 * 8. Customer Success Agent — complaints, NPS, retention
 *
 * Each agent has:
 * - Autonomous task execution (cron-based)
 * - Human escalation when confidence < threshold
 * - Full audit trail in agent_logs table
 * - RBAC-aware responses
 */
class AgenticAIController extends AdminController
{
    private $agents = [
        'lead_gen'     => ['name' => 'Lead Generation Agent',  'icon' => 'fa-magnet',              'color' => '#3b82f6', 'description' => 'Auto-qualifies, scores, and nurtures leads 24/7'],
        'sales'        => ['name' => 'Sales Agent',            'icon' => 'fa-handshake',            'color' => '#10b981', 'description' => 'Booking follow-ups, payment reminders, closing deals'],
        'marketing'    => ['name' => 'Marketing Agent',        'icon' => 'fa-bullhorn',             'color' => '#f59e0b', 'description' => 'Campaigns, social media, content, SEO'],
        'ceo'          => ['name' => 'CEO Dashboard Agent',    'icon' => 'fa-crown',                'color' => '#8b5cf6', 'description' => 'Executive insights, P&L alerts, strategic decisions'],
        'hr'           => ['name' => 'HR Agent',               'icon' => 'fa-users',                'color' => '#ec4899', 'description' => 'Attendance, leaves, payroll, hiring pipeline'],
        'finance'      => ['name' => 'Finance Agent',          'icon' => 'fa-calculator',           'color' => '#06b6d4', 'description' => 'TDS, GST, reconciliation, cash flow, budgets'],
        'operations'   => ['name' => 'Operations Agent',       'icon' => 'fa-cogs',                 'color' => '#f97316', 'description' => 'Site visits, maintenance, inventory, scheduling'],
        'customer'     => ['name' => 'Customer Success Agent', 'icon' => 'fa-heart',                'color' => '#ef4444', 'description' => 'Complaints, NPS surveys, retention, feedback'],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureAgentTables();
    }

    /**
     * Main Agentic AI Dashboard — shows all agents, their status, and recent actions
     */
    public function index()
    {
        $db = $this->db;
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

        // Get each agent's stats
        $agentStats = [];
        foreach ($this->agents as $key => $agent) {
            try {
                $stat = $db->fetch("SELECT
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                    MAX(created_at) as last_run
                FROM agent_task_logs WHERE agent_type = ?", [$key]) ?: [];
                $agentStats[$key] = $stat;
            } catch (\Exception $e) {
                $agentStats[$key] = ['total_tasks' => 0, 'completed' => 0, 'running' => 0, 'failed' => 0, 'last_run' => null];
            }
        }

        // System-wide stats
        try {
            $sysStats = $db->fetch("SELECT
                (SELECT COUNT(*) FROM agent_task_logs WHERE DATE(created_at) = CURDATE()) as today_tasks,
                (SELECT COUNT(*) FROM agent_task_logs WHERE status = 'running') as running_now,
                (SELECT COUNT(*) FROM agent_task_logs WHERE status = 'escalated') as escalated,
                (SELECT COUNT(*) FROM agent_conversations WHERE DATE(created_at) = CURDATE()) as today_conversations,
                (SELECT COUNT(*) FROM whatsapp_click_log WHERE DATE(clicked_at) = CURDATE()) as wa_clicks,
                (SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()) as new_leads
            ") ?: [];
        } catch (\Exception $e) {
            $sysStats = [];
        }

        // Recent agent activity
        try {
            $recentActivity = $db->fetchAll("SELECT atl.*, u.name as agent_name
                FROM agent_task_logs atl
                LEFT JOIN users u ON atl.triggered_by = u.id
                ORDER BY atl.created_at DESC LIMIT 20") ?: [];
        } catch (\Exception $e) {
            $recentActivity = [];
        }

        // Auto-reply settings
        try {
            $row = $db->query("SELECT settings_value FROM system_settings WHERE settings_key = 'agent_auto_reply' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
            $autoReply = $row ? json_decode($row['settings_value'], true) : [];
        } catch (\Exception $e) {
            $autoReply = [];
        }

        $data = [
            'page_title' => 'Agentic AI System',
            'agents' => $this->agents,
            'agent_stats' => $agentStats,
            'sys_stats' => $sysStats,
            'recent_activity' => $recentActivity,
            'auto_reply' => $autoReply,
        ];

        $this->render('admin/agentic-ai/dashboard', $data);
    }

    /**
     * Individual Agent Dashboard
     */
    public function agent($agentType)
    {
        if (!isset($this->agents[$agentType])) {
            header('Location: /admin/agentic-ai');
            exit;
        }

        $db = $this->db;
        $agent = $this->agents[$agentType];

        // Get agent's tasks
        try {
            $tasks = $db->fetchAll("SELECT * FROM agent_task_logs WHERE agent_type = ? ORDER BY created_at DESC LIMIT 50", [$agentType]) ?: [];
        } catch (\Exception $e) {
            $tasks = [];
        }

        // Get agent's insights (AI-generated summaries)
        try {
            $insights = $db->fetchAll("SELECT * FROM agent_insights WHERE agent_type = ? ORDER BY created_at DESC LIMIT 10", [$agentType]) ?: [];
        } catch (\Exception $e) {
            $insights = [];
        }

        // Get pending escalations for this agent
        try {
            $escalations = $db->fetchAll("SELECT * FROM agent_escalations WHERE agent_type = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 10", [$agentType]) ?: [];
        } catch (\Exception $e) {
            $escalations = [];
        }

        $data = [
            'page_title' => $agent['name'],
            'agent' => $agent,
            'agent_type' => $agentType,
            'tasks' => $tasks,
            'insights' => $insights,
            'escalations' => $escalations,
        ];

        $this->render('admin/agentic-ai/agent-detail', $data);
    }

    /**
     * Auto-reply settings
     */
    public function autoReply()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = [
                'auto_reply_enabled' => isset($_POST['auto_reply_enabled']) ? 1 : 0,
                'greeting_message' => trim($_POST['greeting_message'] ?? ''),
                'away_message' => trim($_POST['away_message'] ?? ''),
                'business_hours_start' => $_POST['business_hours_start'] ?? '09:00',
                'business_hours_end' => $_POST['business_hours_end'] ?? '19:00',
                'max_auto_replies' => (int)($_POST['max_auto_replies'] ?? 5),
            ];
            try {
                $db = \App\Core\Database\Database::getInstance();
                $json = json_encode($settings);
                $existing = $db->query("SELECT settings_key FROM system_settings WHERE settings_key = 'agent_auto_reply'")->fetch();
                if ($existing) {
                    $db->execute("UPDATE system_settings SET settings_value = ? WHERE settings_key = 'agent_auto_reply'", [$json]);
                } else {
                    $db->execute("INSERT INTO system_settings (settings_key, settings_value, created_at) VALUES ('agent_auto_reply', ?, NOW())", [$json]);
                }
                $_SESSION['success'] = 'Auto-reply settings saved!';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Error: ' . $e->getMessage();
            }
            header('Location: /admin/agentic-ai/auto-reply');
            exit;
        }

        try {
            $db = \App\Core\Database\Database::getInstance();
            $row = $db->query("SELECT settings_value FROM system_settings WHERE settings_key = 'agent_auto_reply' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
            $settings = $row ? json_decode($row['settings_value'], true) : [];
        } catch (\Exception $e) {
            $settings = [];
        }

        $data = ['page_title' => 'Auto-Reply Settings', 'settings' => $settings, 'agents' => $this->agents];
        $this->render('admin/agentic-ai/auto-reply', $data);
    }

    /**
     * All conversations
     */
    public function conversations()
    {
        $db = $this->db;
        try {
            $conversations = $db->fetchAll("SELECT ac.*, l.name as lead_name, l.phone as lead_phone,
                u.name as agent_name
                FROM agent_conversations ac
                LEFT JOIN leads l ON ac.lead_id = l.id
                LEFT JOIN users u ON ac.agent_id = u.id
                ORDER BY ac.last_message_at DESC LIMIT 100") ?: [];
        } catch (\Exception $e) {
            $conversations = [];
        }

        $data = ['page_title' => 'All Conversations', 'conversations' => $conversations, 'agents' => $this->agents];
        $this->render('admin/agentic-ai/conversations', $data);
    }

    /**
     * Conversation detail
     */
    public function conversation($id)
    {
        $db = $this->db;
        try {
            $conv = $db->fetch("SELECT ac.*, l.name as lead_name, l.phone as lead_phone
                FROM agent_conversations ac LEFT JOIN leads l ON ac.lead_id = l.id WHERE ac.id = ?", [$id]);
            $messages = $db->fetchAll("SELECT am.*, u.name as sender_name
                FROM agent_messages am LEFT JOIN users u ON am.sender_id = u.id
                WHERE am.conversation_id = ? ORDER BY am.created_at ASC", [$id]) ?: [];
        } catch (\Exception $e) {
            $conv = null;
            $messages = [];
        }

        $data = ['page_title' => 'Conversation', 'conversation' => $conv, 'messages' => $messages];
        $this->render('admin/agentic-ai/conversation', $data);
    }

    /**
     * Agent logs / audit trail
     */
    public function logs()
    {
        $db = $this->db;
        $filter = $_GET['agent'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d');

        $where = "DATE(atl.created_at) = ?";
        $params = [$date];
        if ($filter) {
            $where .= " AND atl.agent_type = ?";
            $params[] = $filter;
        }

        try {
            $logs = $db->fetchAll("SELECT atl.*, u.name as agent_name
                FROM agent_task_logs atl
                LEFT JOIN users u ON atl.triggered_by = u.id
                WHERE {$where}
                ORDER BY atl.created_at DESC LIMIT 200", $params) ?: [];
        } catch (\Exception $e) {
            $logs = [];
        }

        $data = ['page_title' => 'Agent Logs', 'logs' => $logs, 'agents' => $this->agents, 'filter' => $filter, 'date' => $date];
        $this->render('admin/agentic-ai/logs', $data);
    }

    /**
     * Run all agents — triggers the Agentic AI Orchestrator
     */
    public function runAll()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $orchestrator = new \App\Services\AgenticAI\AgentOrchestrator();
            $results = $orchestrator->runAll();

            $success = true;
            $summary = [];
            foreach ($results as $type => $result) {
                if (isset($result['error'])) {
                    $success = false;
                    $summary[] = "$type: ERROR - {$result['error']}";
                } else {
                    $summary[] = "$type: " . count($result) . " tasks";
                }
            }

            echo json_encode([
                'success' => $success,
                'message' => implode(' | ', $summary),
                'results' => $results
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

    // ═══════════════════════════════════════════════
    // API ENDPOINTS
    // ═══════════════════════════════════════════════

    public function sendMessage()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $convId = $input['conversation_id'] ?? 0;
        $message = trim($input['message'] ?? '');
        $agentId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

        if (!$convId || !$message || !$agentId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing fields']);
            return;
        }

        try {
            $this->db->execute("INSERT INTO agent_messages (conversation_id, sender_type, sender_id, message, created_at) VALUES (?, 'agent', ?, ?, NOW())",
                [$convId, $agentId, $message]);
            $this->db->execute("UPDATE agent_conversations SET last_message_at = NOW(), last_message = ? WHERE id = ?",
                [mb_substr($message, 0, 200), $convId]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function claimConversation()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $convId = $input['conversation_id'] ?? 0;
        $agentId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

        try {
            $this->db->execute("UPDATE agent_conversations SET agent_id = ?, claimed_at = NOW() WHERE id = ? AND agent_id IS NULL",
                [$agentId, $convId]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function resolveConversation()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $convId = $input['conversation_id'] ?? 0;

        try {
            $this->db->execute("UPDATE agent_conversations SET status = 'resolved', resolved_at = NOW() WHERE id = ?", [$convId]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getMessages()
    {
        header('Content-Type: application/json');
        $convId = $_GET['conversation_id'] ?? 0;
        $after = $_GET['after'] ?? '0000-00-00 00:00:00';

        try {
            $messages = $this->db->fetchAll("SELECT am.*, u.name as sender_name
                FROM agent_messages am LEFT JOIN users u ON am.sender_id = u.id
                WHERE am.conversation_id = ? AND am.created_at > ? ORDER BY am.created_at ASC",
                [$convId, $after]) ?: [];
            echo json_encode(['success' => true, 'messages' => $messages]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'messages' => []]);
        }
    }

    // ═══════════════════════════════════════════════
    // TABLE CREATION
    // ═══════════════════════════════════════════════

    private function ensureAgentTables()
    {
        try {
            $this->db->execute("CREATE TABLE IF NOT EXISTS agent_task_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                agent_type VARCHAR(50) NOT NULL,
                task_name VARCHAR(200) NOT NULL,
                task_data JSON,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                result JSON,
                confidence FLOAT DEFAULT 0,
                triggered_by INT NULL,
                started_at DATETIME NULL,
                completed_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_agent (agent_type),
                INDEX idx_status (status),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $this->db->execute("CREATE TABLE IF NOT EXISTS agent_insights (
                id INT AUTO_INCREMENT PRIMARY KEY,
                agent_type VARCHAR(50) NOT NULL,
                insight_type VARCHAR(50) NOT NULL,
                title VARCHAR(200) NOT NULL,
                summary TEXT,
                data JSON,
                priority VARCHAR(10) DEFAULT 'normal',
                is_read TINYINT(1) DEFAULT 0,
                created_at DATETIME NOT NULL,
                INDEX idx_agent (agent_type),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $this->db->execute("CREATE TABLE IF NOT EXISTS agent_escalations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                agent_type VARCHAR(50) NOT NULL,
                escalation_type VARCHAR(50) NOT NULL,
                title VARCHAR(200) NOT NULL,
                description TEXT,
                context JSON,
                status VARCHAR(20) DEFAULT 'pending',
                assigned_to INT NULL,
                resolved_by INT NULL,
                created_at DATETIME NOT NULL,
                resolved_at DATETIME NULL,
                INDEX idx_agent (agent_type),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $this->db->execute("CREATE TABLE IF NOT EXISTS agent_conversations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                lead_id INT NULL,
                agent_id INT NULL,
                channel VARCHAR(20) NOT NULL DEFAULT 'chatbot',
                status VARCHAR(20) NOT NULL DEFAULT 'active',
                last_message TEXT,
                last_message_at DATETIME,
                claimed_at DATETIME NULL,
                resolved_at DATETIME NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_agent (agent_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $this->db->execute("CREATE TABLE IF NOT EXISTS agent_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conversation_id INT NOT NULL,
                sender_type VARCHAR(20) NOT NULL DEFAULT 'customer',
                sender_id INT NULL,
                message TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_conv (conversation_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        } catch (\Exception $e) {
            error_log("AgenticAI table creation error: " . $e->getMessage());
        }
    }
}
