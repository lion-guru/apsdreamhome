<?php

namespace App\Services;

use Exception;
use PDO;
use App\Models\Model;

/**
 * Modern Chat Service
 * Handles real-time chat between users and users.
 */
class ChatService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;
    private $pdo;
    private $websocketServer;
    private $messageQueue;
    private $agentManager;

    public function __construct()
    {
        $this->db = Model::query()->getConnection();
        $this->websocketServer = new WebSocketServer();
        $this->messageQueue = new MessageQueue($this->db);
        $this->agentManager = new AgentManager($this->db);
    }

    /**
     * Initiate a new chat session
     */
    public function initiateChat(int $userId, ?int $propertyId = null, string $department = 'general', string $sessionType = 'general'): array
    {
        try {
            $sessionId = $this->createChatSession($userId, $propertyId, $department, $sessionType);
            $agentId = $this->findAvailableAgent($department);

            if ($agentId) {
                $this->assignAgentToSession($sessionId, $agentId);
                $status = 'active';
            } else {
                $this->addToQueue($sessionId, $department);
                $status = 'waiting';
            }

            $this->sendSystemMessage($sessionId, $this->getWelcomeMessage($department));

            return [
                'session_id' => $sessionId,
                'status' => $status,
                'agent_id' => $agentId,
                'queue_position' => $status === 'waiting' ? $this->getQueuePosition($sessionId) : 0,
                'estimated_wait_time' => $this->getEstimatedWaitTime($department)
            ];
        } catch (Exception $e) {
            error_log("Chat Initiation Error: " . $e->getMessage());
            throw new Exception("Failed to initiate chat: " . $e->getMessage());
        }
    }

    /**
     * Send a message in a chat session
     */
    public function sendMessage(int $sessionId, string $senderType, ?int $senderId, string $message, string $messageType = 'text', array $attachment = []): array
    {
        try {
            $this->validateSessionAccess($sessionId, $senderType, $senderId);
            $messageId = $this->createMessage($sessionId, $senderType, $senderId, $message, $messageType, $attachment);
            $this->updateSessionActivity($sessionId);

            $this->broadcastMessage($sessionId, [
                'message_id' => $messageId,
                'sender_type' => $senderType,
                'sender_id' => $senderId,
                'message' => $message,
                'message_type' => $messageType,
                'attachment' => $attachment,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            if ($senderType === 'user' && $this->shouldUseBotResponse($sessionId)) {
                $this->handleBotResponse($sessionId, $message);
            }

            return [
                'message_id' => $messageId,
                'status' => 'sent',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("Send Message Error: " . $e->getMessage());
            throw new Exception("Failed to send message: " . $e->getMessage());
        }
    }

    /**
     * Get chat session details
     */
    public function getSessionDetails(int $sessionId, ?int $userId = null): array
    {
        try {
            $sql = "SELECT cs.*, u.name as user_name, u.email as user_email,
                           a.auser as agent_name, a.email as agent_email,
                           cs.subject as property_title
                    FROM chat_sessions cs
                    LEFT JOIN users u ON cs.user_id = u.id
                    LEFT JOIN admin a ON cs.assigned_agent_id = a.id
                    WHERE cs.id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$session) {
                throw new Exception("Session not found");
            }

            if ($userId && $session['user_id'] != $userId) {
                throw new Exception("Access denied");
            }

            $messages = $this->getSessionMessages($sessionId);
            $agentStatus = !empty($session['assigned_agent_id']) ? $this->getAgentStatus((int)$session['assigned_agent_id']) : null;

            return [
                'session' => $session,
                'messages' => $messages,
                'agent_status' => $agentStatus,
                'queue_position' => $session['status'] === 'open' ? $this->getQueuePosition($sessionId) : 0
            ];
        } catch (Exception $e) {
            error_log("Get Session Details Error: " . $e->getMessage());
            throw new Exception("Failed to get session details: " . $e->getMessage());
        }
    }

    /**
     * Get user's active chat sessions
     */
    public function getUserSessions(int $userId): array
    {
        try {
            $sql = "SELECT cs.*, a.auser as agent_name,
                           COUNT(cm.id) as message_count,
                           MAX(cm.created_at) as last_message_time
                    FROM chat_sessions cs
                    LEFT JOIN admin a ON cs.assigned_agent_id = a.id
                    LEFT JOIN chat_messages cm ON cs.id = cm.session_id
                    WHERE cs.user_id = ? AND cs.status IN ('active', 'open')
                    GROUP BY cs.id
                    ORDER BY cs.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get User Sessions Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * End a chat session
     */
    public function endSession(int $sessionId, int $userId, ?int $rating = null, string $feedback = ''): bool
    {
        try {
            $this->validateSessionOwnership($sessionId, $userId);
            // EndChatSession stored procedure does not exist; direct update on real columns
            $stmt = $this->db->prepare("UPDATE chat_sessions SET status = 'closed', closed_at = NOW(), closed_by = ?, rating = ?, feedback_text = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$userId, $rating, $feedback, $sessionId, $userId]);

            $this->sendSystemMessage($sessionId, "Thank you for chatting with us! Your session has been ended.");
            $this->websocketServer->closeSessionConnections($sessionId);

            return true;
        } catch (Exception $e) {
            error_log("End Session Error: " . $e->getMessage());
            throw new Exception("Failed to end session: " . $e->getMessage());
        }
    }

    /**
     * Get available users for a department
     */
    public function getAvailableAgents(string $department): array
    {
        try {
            // agent_availability table does not exist; derive availability from users + live chat load
            $sql = "SELECT u.id as agent_id, u.name as agent_name, u.email as agent_email,
                           (SELECT COUNT(*) FROM chat_sessions cs
                             WHERE cs.assigned_agent_id = u.id AND cs.status IN ('open','assigned','active')) as active_chats
                    FROM users u
                    WHERE u.role IN ('admin','employee','telecaller','agent')
                      AND u.status = 'active' AND u.is_active = 1
                    ORDER BY active_chats ASC, u.id ASC
                    LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get Available users Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Transfer chat to another agent
     */
    public function transferChat(int $sessionId, int $fromAgentId, int $toAgentId, string $reason, string $notes = ''): bool
    {
        try {
            $this->validateTransfer($sessionId, $fromAgentId, $toAgentId);

            // chat_transfers table does not exist; record transfer on the session only
            $stmt = $this->db->prepare("UPDATE chat_sessions SET assigned_agent_id = ?, first_response_at = COALESCE(first_response_at, NOW()) WHERE id = ?");
            $stmt->execute([$toAgentId, $sessionId]);

            $this->sendSystemMessage($sessionId, "Chat has been transferred to another agent.");
            return true;
        } catch (Exception $e) {
            error_log("Transfer Chat Error: " . $e->getMessage());
            throw new Exception("Failed to transfer chat: " . $e->getMessage());
        }
    }

    /**
     * Get chat analytics
     */
    public function getChatAnalytics(string $startDate, string $endDate, ?string $department = null): array
    {
        try {
            $sql = "SELECT DATE(cs.created_at) as date, COALESCE(cs.category,'general') as department, COUNT(*) as total_sessions,
                           SUM(CASE WHEN cs.status IN ('active','assigned') THEN 1 ELSE 0 END) as active_sessions,
                           AVG(CASE WHEN cs.first_response_at IS NOT NULL THEN TIMESTAMPDIFF(SECOND, cs.created_at, cs.first_response_at) END) as avg_wait_time,
                           AVG(CASE WHEN cs.closed_at IS NOT NULL THEN TIMESTAMPDIFF(SECOND, cs.created_at, cs.closed_at) END) as avg_session_duration,
                           COUNT(cm.id) as total_messages, AVG(cs.rating) as avg_rating, COUNT(cs.rating) as total_ratings
                    FROM chat_sessions cs
                    LEFT JOIN chat_messages cm ON cs.id = cm.session_id
                    WHERE DATE(cs.created_at) BETWEEN ? AND ? ";

            $params = [$startDate, $endDate];
            if ($department) {
                $sql .= " AND COALESCE(cs.category,'general') = ? ";
                $params[] = $department;
            }
            $sql .= " GROUP BY DATE(cs.created_at), COALESCE(cs.category,'general') ORDER BY date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get Chat Analytics Error: " . $e->getMessage());
            return [];
        }
    }

    // Helper Methods

    private function createChatSession($userId, $propertyId, $department, $sessionType)
    {
        $tid = $this->isTenantScoped() ? $this->tenantId() : null;
        // chat_sessions has no property_id/department/session_type columns;
        // department maps to category, property context goes to subject
        $subject = $propertyId ? ('Property #' . (int)$propertyId) : ($sessionType !== 'general' ? ucfirst($sessionType) : null);
        $cols = 'user_id, category, priority, subject';
        $vals = '?, ?, CASE WHEN ? = \'support\' THEN \'high\' WHEN ? = \'technical\' THEN \'high\' WHEN ? = \'sales\' THEN \'medium\' ELSE \'low\' END, ?';
        if ($tid) { $cols .= ', tenant_id'; $vals .= ', ?'; }
        $sql = "INSERT INTO chat_sessions ($cols) VALUES ($vals)";
        $params = [$userId, $department, $department, $department, $department, $subject];
        if ($tid) { $params[] = $tid; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $this->db->lastInsertId();
    }

    private function findAvailableAgent($department)
    {
        // GetNextAvailableAgent procedure does not exist; pick least-loaded active staff user
        $stmt = $this->db->prepare("SELECT u.id FROM users u
                WHERE u.role IN ('admin','employee','telecaller','agent')
                  AND u.status = 'active' AND u.is_active = 1
                ORDER BY (SELECT COUNT(*) FROM chat_sessions cs
                          WHERE cs.assigned_agent_id = u.id AND cs.status IN ('open','assigned','active')) ASC, u.id ASC
                LIMIT 1");
        $stmt->execute([]);
        return $stmt->fetch(PDO::FETCH_COLUMN) ?: null;
    }

    private function assignAgentToSession($sessionId, $agentId)
    {
        // AssignChatToAgent procedure does not exist; direct update on real columns
        $stmt = $this->db->prepare("UPDATE chat_sessions cs
                JOIN users u ON u.id = ?
                SET cs.assigned_agent_id = ?, cs.agent_name = u.name, cs.status = 'assigned', cs.first_response_at = COALESCE(cs.first_response_at, NOW())
                WHERE cs.id = ?");
        $stmt->execute([$agentId, $agentId, $sessionId]);
    }

    private function addToQueue($sessionId, $department)
    {
        // chat_queue table does not exist; unassigned sessions simply stay status='open'
        $stmt = $this->db->prepare("UPDATE chat_sessions SET status = 'open' WHERE id = ?");
        $stmt->execute([$sessionId]);
    }

    private function getSessionById($sessionId)
    {
        $stmt = $this->db->prepare("SELECT * FROM chat_sessions WHERE id = ?");
        $stmt->execute([$sessionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getQueuePosition($sessionId)
    {
        $sql = "SELECT COUNT(*) as position FROM chat_sessions
                WHERE status = 'open'
                AND created_at < (SELECT created_at FROM chat_sessions WHERE id = ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId]);
        return $stmt->fetchColumn() + 1;
    }

    private function getEstimatedWaitTime($department)
    {
        $sql = "SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, first_response_at)) as avg_wait FROM chat_sessions 
                WHERE category = ? AND status = 'closed'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$department]);
        $avgWait = $stmt->fetchColumn();
        return $avgWait ? round($avgWait) : 180;
    }

    private function getWelcomeMessage($department)
    {
        $messages = [
            'general' => "Hello! Welcome to APS Dream Home. How can I help you today?",
            'sales' => "Hi there! I'm here to help you find your dream property. What are you looking for?",
            'support' => "Welcome to our support team! How can I assist you today?",
            'technical' => "Hello! I'm here to help with any technical issues you may be experiencing."
        ];
        return $messages[$department] ?? $messages['general'];
    }

    private function sendSystemMessage($sessionId, $message)
    {
        $tid = $this->isTenantScoped() ? $this->tenantId() : null;
        $cols = 'session_id, sender_type, message_type, message';
        $vals = '?, \'system\', \'system_notification\', ?';
        if ($tid) { $cols .= ', tenant_id'; $vals .= ', ?'; }
        $stmt = $this->db->prepare("INSERT INTO chat_messages ($cols) VALUES ($vals)");
        $params = [$sessionId, $message];
        if ($tid) { $params[] = $tid; }
        $stmt->execute($params);
        $messageId = $this->db->lastInsertId();

        $this->broadcastMessage($sessionId, [
            'message_id' => $messageId,
            'sender_type' => 'system',
            'message' => $message,
            'message_type' => 'system_notification',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    private function createMessage($sessionId, $senderType, $senderId, $message, $messageType, $attachment)
    {
        $tid = $this->isTenantScoped() ? $this->tenantId() : null;
        // chat_messages.sender_type enum is ('visitor','agent','bot','system') — legacy 'user' maps to 'visitor'
        if ($senderType === 'user') { $senderType = 'visitor'; }
        $cols = 'session_id, sender_type, sender_id, message_type, message, attachment_url';
        $vals = '?, ?, ?, ?, ?, ?';
        if ($tid) { $cols .= ', tenant_id'; $vals .= ', ?'; }
        $sql = "INSERT INTO chat_messages ($cols) VALUES ($vals)";
        $params = [
            $sessionId, $senderType, $senderId, $messageType, $message,
            $attachment['url'] ?? null
        ];
        if ($tid) { $params[] = $tid; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $this->db->lastInsertId();
    }

    private function validateSessionAccess($sessionId, $senderType, $senderId)
    {
        $session = $this->getSessionById($sessionId);
        if (!$session) throw new Exception("Session not found");
        if (in_array($senderType, ['user', 'visitor'], true) && $session['user_id'] != $senderId) throw new Exception("Access denied");
        if ($senderType === 'agent' && $session['assigned_agent_id'] != $senderId) throw new Exception("Access denied");
        if ($session['status'] === 'closed') throw new Exception("Session is closed");
    }

    private function validateSessionOwnership($sessionId, $userId)
    {
        $session = $this->getSessionById($sessionId);
        if (!$session || $session['user_id'] != $userId) throw new Exception("Access denied");
        return $session;
    }

    private function validateTransfer($sessionId, $fromAgentId, $toAgentId)
    {
        $session = $this->getSessionById($sessionId);
        if (!$session || $session['assigned_agent_id'] != $fromAgentId) throw new Exception("Invalid session or agent");
    }

    private function updateSessionActivity($sessionId)
    {
        $tid = $this->isTenantScoped() ? $this->tenantId() : null;
        $sql = "UPDATE chat_sessions SET last_message_at = NOW() WHERE id = ?";
        $params = [$sessionId];
        if ($tid) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    private function broadcastMessage($sessionId, $messageData)
    {
        $this->websocketServer->broadcastToSession($sessionId, $messageData);
    }

    private function shouldUseBotResponse($sessionId)
    {
        $session = $this->getSessionById($sessionId);
        return $session['status'] === 'open' && empty($session['assigned_agent_id']);
    }

    private function handleBotResponse($sessionId, $userMessage)
    {
        $botResponses = [
            'price' => "I can help you with pricing information. Our properties range from affordable to luxury options. What's your budget range?",
            'location' => "We have properties in various locations. Which area are you interested in?",
            'appointment' => "I'd be happy to help you schedule an appointment. What date and time works best for you?",
            'contact' => "You can reach our team via phone, email, or this chat. What's your preferred contact method?"
        ];
        $response = $this->generateBotResponse($userMessage, $botResponses);
        if ($response) {
            $this->sendMessage($sessionId, 'bot', null, $response, 'text');
        }
    }

    private function generateBotResponse($message, $responses)
    {
        $message = strtolower($message);
        foreach ($responses as $keyword => $response) {
            if (strpos($message, $keyword) !== false) return $response;
        }
        return "I understand you're looking for help. An agent will be with you shortly. In the meantime, feel free to tell me more about what you need.";
    }

    private function getSessionMessages($sessionId, $limit = 50)
    {
        $sql = "SELECT cm.*, CASE WHEN cm.sender_type = 'visitor' THEN u.name WHEN cm.sender_type = 'agent' THEN a.auser ELSE 'System' END as sender_name
                FROM chat_messages cm
                LEFT JOIN users u ON cm.sender_id = u.id AND cm.sender_type = 'visitor'
                LEFT JOIN admin a ON cm.sender_id = a.id AND cm.sender_type = 'agent'
                WHERE cm.session_id = ? ORDER BY cm.created_at ASC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAgentStatus($agentId)
    {
        // agent_availability table does not exist; report basic user status
        $sql = "SELECT u.id as agent_id, u.name as agent_name, u.status FROM users u WHERE u.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$agentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function updateAgentChatCount($agentId, $change)
    {
        // agent_availability table does not exist; nothing to update
    }
}

/**
 * Internal Components (Simplified for ChatService)
 */

class WebSocketServer
{
    private $sessions = [];

    public function broadcastToSession($sessionId, $messageData)
    {
        // WebSocket broadcast logic would go here
    }

    public function closeSessionConnections($sessionId)
    {
        // WebSocket close logic would go here
    }
}

class MessageQueue
{
    private $db;
    private $pdo;
    public function __construct($db)
    {
        $this->db = $db;
    }
    public function enqueue($sessionId, $messageData)
    {
        $stmt = $this->db->prepare("INSERT INTO message_queue (session_id, message_data, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$sessionId, json_encode($messageData)]);
    }
}

class AgentManager
{
    private $db;
    private $pdo;
    public function __construct($db)
    {
        $this->db = $db;
    }
    public function setAgentStatus($agentId, $isOnline, $isAvailable)
    {
        $stmt = $this->db->prepare("UPDATE agent_availability SET is_online = ?, is_available = ?, last_activity = NOW() WHERE agent_id = ?");
        $stmt->execute([$isOnline, $isAvailable, $agentId]);
    }
}

//
// PERFORMANCE OPTIMIZATION GUIDELINES
//
// This file contains 510 lines. Consider optimizations:
//
// 1. Use database indexing
// 2. Implement caching
// 3. Use prepared statements
// 4. Optimize loops
// 5. Use lazy loading
// 6. Implement pagination
// 7. Use connection pooling
// 8. Consider Redis for sessions
// 9. Implement output buffering
// 10. Use gzip compression
//
//