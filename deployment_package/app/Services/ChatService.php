<?php

namespace App\Services;

use Exception;
use PDO;
use App\Models\Model;

/**
 * Modern Chat Service
 * Handles real-time chat between users and agents.
 */
class ChatService
{
    private $db;
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
            $sql = "SELECT cs.*, u.uname as user_name, u.uemail as user_email,
                           a.auser as agent_name, a.aemail as agent_email,
                           p.title as property_title, p.image_url as property_image
                    FROM chat_sessions cs
                    LEFT JOIN users u ON cs.user_id = u.id
                    LEFT JOIN admin a ON cs.agent_id = a.aid
                    LEFT JOIN properties p ON cs.property_id = p.id
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
            $agentStatus = $session['agent_id'] ? $this->getAgentStatus($session['agent_id']) : null;

            return [
                'session' => $session,
                'messages' => $messages,
                'agent_status' => $agentStatus,
                'queue_position' => $session['session_status'] === 'waiting' ? $this->getQueuePosition($sessionId) : 0
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
            $sql = "SELECT cs.*, a.auser as agent_name, p.title as property_title,
                           COUNT(cm.id) as message_count,
                           MAX(cm.created_at) as last_message_time
                    FROM chat_sessions cs
                    LEFT JOIN admin a ON cs.agent_id = a.aid
                    LEFT JOIN properties p ON cs.property_id = p.id
                    LEFT JOIN chat_messages cm ON cs.id = cm.session_id
                    WHERE cs.user_id = ? AND cs.session_status IN ('active', 'waiting')
                    GROUP BY cs.id
                    ORDER BY cs.started_at DESC";

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
            $stmt = $this->db->prepare("CALL EndChatSession(?, ?, ?)");
            $stmt->execute([$sessionId, $rating, $feedback]);

            $this->sendSystemMessage($sessionId, "Thank you for chatting with us! Your session has been ended.");
            $this->websocketServer->closeSessionConnections($sessionId);

            return true;
        } catch (Exception $e) {
            error_log("End Session Error: " . $e->getMessage());
            throw new Exception("Failed to end session: " . $e->getMessage());
        }
    }

    /**
     * Get available agents for a department
     */
    public function getAvailableAgents(string $department): array
    {
        try {
            $sql = "SELECT aa.*, a.auser as agent_name, a.aemail as agent_email,
                           (SELECT COUNT(*) FROM chat_sessions WHERE agent_id = aa.agent_id AND session_status = 'active') as active_chats
                    FROM agent_availability aa
                    JOIN admin a ON aa.agent_id = a.aid
                    WHERE aa.is_available = TRUE 
                      AND aa.is_online = TRUE 
                      AND aa.current_chats < aa.max_chats
                      AND aa.department = ?
                      AND (aa.break_until IS NULL OR aa.break_until < NOW())
                    ORDER BY (aa.current_chats / aa.max_chats) ASC, aa.customer_satisfaction DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$department]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get Available Agents Error: " . $e->getMessage());
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

            $stmt = $this->db->prepare("INSERT INTO chat_transfers (session_id, from_agent_id, to_agent_id, transfer_reason, transfer_notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$sessionId, $fromAgentId, $toAgentId, $reason, $notes]);

            $stmt = $this->db->prepare("UPDATE chat_sessions SET agent_id = ?, assigned_at = NOW() WHERE id = ?");
            $stmt->execute([$toAgentId, $sessionId]);

            $this->updateAgentChatCount($fromAgentId, -1);
            $this->updateAgentChatCount($toAgentId, +1);

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
            $sql = "SELECT DATE(cs.started_at) as date, cs.department, COUNT(*) as total_sessions,
                           SUM(CASE WHEN cs.session_status = 'active' THEN 1 ELSE 0 END) as active_sessions,
                           AVG(cs.wait_time) as avg_wait_time, AVG(cs.session_duration) as avg_session_duration,
                           COUNT(cm.id) as total_messages, AVG(cr.rating) as avg_rating, COUNT(cr.id) as total_ratings
                    FROM chat_sessions cs
                    LEFT JOIN chat_messages cm ON cs.id = cm.session_id
                    LEFT JOIN chat_ratings cr ON cs.id = cr.session_id
                    WHERE DATE(cs.started_at) BETWEEN ? AND ? ";

            $params = [$startDate, $endDate];
            if ($department) {
                $sql .= " AND cs.department = ? ";
                $params[] = $department;
            }
            $sql .= " GROUP BY DATE(cs.started_at), cs.department ORDER BY date DESC";

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
        $sql = "INSERT INTO chat_sessions (user_id, property_id, department, session_type, priority)
                VALUES (?, ?, ?, ?, 
                    CASE 
                        WHEN ? = 'support' THEN 'high'
                        WHEN ? = 'technical' THEN 'high'
                        WHEN ? = 'sales' THEN 'medium'
                        ELSE 'low'
                    END
                )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $propertyId, $department, $sessionType, $department, $department, $department]);
        return $this->db->lastInsertId();
    }

    private function findAvailableAgent($department)
    {
        $stmt = $this->db->prepare("CALL GetNextAvailableAgent(?, @agent_id)");
        $stmt->execute([$department]);
        return $this->db->query("SELECT @agent_id")->fetch(PDO::FETCH_COLUMN) ?: null;
    }

    private function assignAgentToSession($sessionId, $agentId)
    {
        $stmt = $this->db->prepare("CALL AssignChatToAgent(?, ?, ?)");
        $stmt->execute([$sessionId, $agentId, 'general']);
    }

    private function addToQueue($sessionId, $department)
    {
        $session = $this->getSessionById($sessionId);
        $stmt = $this->db->prepare("INSERT INTO chat_queue (session_id, user_id, department, priority) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sessionId, $session['user_id'], $department, $session['priority']]);
    }

    private function getSessionById($sessionId)
    {
        $stmt = $this->db->prepare("SELECT * FROM chat_sessions WHERE id = ?");
        $stmt->execute([$sessionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getQueuePosition($sessionId)
    {
        $sql = "SELECT COUNT(*) as position FROM chat_queue 
                WHERE joined_queue_at <= (SELECT joined_queue_at FROM chat_queue WHERE session_id = ?)
                AND session_id != ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId, $sessionId]);
        return $stmt->fetchColumn() + 1;
    }

    private function getEstimatedWaitTime($department)
    {
        $sql = "SELECT AVG(wait_time) as avg_wait FROM chat_sessions 
                WHERE department = ? AND session_status = 'closed'
                AND started_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
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
        $stmt = $this->db->prepare("INSERT INTO chat_messages (session_id, sender_type, message_type, message_content) VALUES (?, 'system', 'system_notification', ?)");
        $stmt->execute([$sessionId, $message]);
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
        $sql = "INSERT INTO chat_messages (session_id, sender_type, sender_id, message_type, message_content, attachment_url, file_name, file_size, mime_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $sessionId,
            $senderType,
            $senderId,
            $messageType,
            $message,
            $attachment['url'] ?? null,
            $attachment['name'] ?? null,
            $attachment['size'] ?? null,
            $attachment['mime_type'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    private function validateSessionAccess($sessionId, $senderType, $senderId)
    {
        $session = $this->getSessionById($sessionId);
        if (!$session) throw new Exception("Session not found");
        if ($senderType === 'user' && $session['user_id'] != $senderId) throw new Exception("Access denied");
        if ($senderType === 'agent' && $session['agent_id'] != $senderId) throw new Exception("Access denied");
        if ($session['session_status'] === 'closed') throw new Exception("Session is closed");
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
        if (!$session || $session['agent_id'] != $fromAgentId) throw new Exception("Invalid session or agent");
    }

    private function updateSessionActivity($sessionId)
    {
        $stmt = $this->db->prepare("UPDATE chat_sessions SET last_activity = NOW() WHERE id = ?");
        $stmt->execute([$sessionId]);
    }

    private function broadcastMessage($sessionId, $messageData)
    {
        $this->websocketServer->broadcastToSession($sessionId, $messageData);
    }

    private function shouldUseBotResponse($sessionId)
    {
        $session = $this->getSessionById($sessionId);
        return $session['session_status'] === 'waiting' && !$session['agent_id'];
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
        $sql = "SELECT cm.*, CASE WHEN cm.sender_type = 'user' THEN u.name WHEN cm.sender_type = 'agent' THEN a.auser ELSE 'System' END as sender_name
                FROM chat_messages cm
                LEFT JOIN users u ON cm.sender_id = u.id AND cm.sender_type = 'user'
                LEFT JOIN admin a ON cm.sender_id = a.aid AND cm.sender_type = 'agent'
                WHERE cm.session_id = ? ORDER BY cm.created_at ASC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$sessionId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAgentStatus($agentId)
    {
        $sql = "SELECT aa.*, a.auser as agent_name FROM agent_availability aa JOIN admin a ON aa.agent_id = a.aid WHERE aa.agent_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$agentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function updateAgentChatCount($agentId, $change)
    {
        $stmt = $this->db->prepare("UPDATE agent_availability SET current_chats = GREATEST(current_chats + ?, 0), last_activity = NOW() WHERE agent_id = ?");
        $stmt->execute([$change, $agentId]);
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
