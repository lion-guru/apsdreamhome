<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;

/**
 * Agentic AI — Auto-Reply Agent System
 *
 * An employee logs in as an AI agent and stays online.
 * The system auto-replies to incoming WhatsApp/chatbot messages 24/7.
 * All conversations are logged with full audit trail.
 *
 * This is the "free agent" — works while you sleep.
 */
class AgenticAIController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Agentic AI Dashboard
     */
    public function index()
    {
        $agentId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
        $db = $this->db;

        // Create tables if not exist
        $this->ensureTables();

        // Get agent stats
        try {
            $stats = $db->fetch("SELECT
                (SELECT COUNT(*) FROM agent_conversations WHERE agent_id = ? AND status = 'active') as active,
                (SELECT COUNT(*) FROM agent_conversations WHERE agent_id = ? AND status = 'resolved') as resolved,
                (SELECT COUNT(*) FROM agent_conversations WHERE DATE(created_at) = CURDATE()) as today_total,
                (SELECT COUNT(*) FROM whatsapp_click_log WHERE DATE(clicked_at) = CURDATE()) as wa_clicks,
                (SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()) as new_leads
            ", [$agentId, $agentId]) ?: [];
        } catch (\Exception $e) {
            $stats = ['active' => 0, 'resolved' => 0, 'today_total' => 0, 'wa_clicks' => 0, 'new_leads' => 0];
        }

        // Active conversations
        try {
            $conversations = $db->fetchAll("SELECT ac.*, l.name as lead_name, l.phone as lead_phone
                FROM agent_conversations ac
                LEFT JOIN leads l ON ac.lead_id = l.id
                WHERE ac.status = 'active'
                ORDER BY ac.last_message_at DESC LIMIT 20") ?: [];
        } catch (\Exception $e) {
            $conversations = [];
        }

        // Auto-reply settings
        try {
            $row = $db->query("SELECT settings_value FROM system_settings WHERE settings_key = 'agent_auto_reply' LIMIT 1")->fetch(\PDO::FETCH_ASSOC);
            $autoReply = $row ? json_decode($row['settings_value'], true) : [];
        } catch (\Exception $e) {
            $autoReply = [];
        }

        $data = [
            'page_title' => 'Agentic AI Dashboard',
            'stats' => $stats,
            'conversations' => $conversations,
            'auto_reply' => $autoReply,
            'agent_id' => $agentId,
        ];

        $this->render('admin/agentic-ai/dashboard', $data);
    }

    /**
     * Auto-reply settings page
     */
    public function autoReply()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = [
                'auto_reply_enabled' => isset($_POST['auto_reply_enabled']) ? 1 : 0,
                'greeting_message' => trim($_POST['greeting_message'] ?? 'Namaste! APS Dream Homes mein aapka swagat hai. Main aapki kya madad kar sakta hoon?'),
                'away_message' => trim($_POST['away_message'] ?? 'Abhi hamare agents busy hain. Ham jald hi aapse sampark karenge.'),
                'business_hours_start' => $_POST['business_hours_start'] ?? '09:00',
                'business_hours_end' => $_POST['business_hours_end'] ?? '19:00',
                'ai_model' => $_POST['ai_model'] ?? 'chatgpt',
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
                $_SESSION['flash_success'] = 'Auto-reply settings saved!';
            } catch (\Exception $e) {
                $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
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

        $data = ['page_title' => 'Auto-Reply Settings', 'settings' => $settings];
        $this->render('admin/agentic-ai/auto-reply', $data);
    }

    /**
     * Conversation detail view
     */
    public function conversation($id)
    {
        $db = $this->db;
        try {
            $conv = $db->fetch("SELECT ac.*, l.name as lead_name, l.phone as lead_phone
                FROM agent_conversations ac
                LEFT JOIN leads l ON ac.lead_id = l.id
                WHERE ac.id = ?", [$id]);
            $messages = $db->fetchAll("SELECT * FROM agent_messages WHERE conversation_id = ? ORDER BY created_at ASC", [$id]) ?: [];
        } catch (\Exception $e) {
            $conv = null;
            $messages = [];
        }

        $data = [
            'page_title' => 'Conversation',
            'conversation' => $conv,
            'messages' => $messages,
        ];
        $this->render('admin/agentic-ai/conversation', $data);
    }

    /**
     * API: Send message in conversation
     */
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

    /**
     * API: Claim conversation
     */
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

    /**
     * API: Resolve conversation
     */
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

    /**
     * API: Get conversation messages (polling)
     */
    public function getMessages()
    {
        header('Content-Type: application/json');
        $convId = $_GET['conversation_id'] ?? 0;
        $after = $_GET['after'] ?? '0000-00-00 00:00:00';

        try {
            $messages = $this->db->fetchAll("SELECT * FROM agent_messages WHERE conversation_id = ? AND created_at > ? ORDER BY created_at ASC",
                [$convId, $after]) ?: [];
            echo json_encode(['success' => true, 'messages' => $messages]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'messages' => []]);
        }
    }

    /**
     * Ensure agent tables exist
     */
    private function ensureTables()
    {
        try {
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
                INDEX idx_status (status),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $this->db->execute("CREATE TABLE IF NOT EXISTS agent_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                conversation_id INT NOT NULL,
                sender_type VARCHAR(20) NOT NULL DEFAULT 'customer',
                sender_id INT NULL,
                message TEXT NOT NULL,
                created_at DATETIME NOT NULL,
                INDEX idx_conv (conversation_id),
                INDEX idx_date (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (\Exception $e) {
            error_log("AgenticAI table creation error: " . $e->getMessage());
        }
    }
}
