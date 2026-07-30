<?php

namespace App\Services\Communication;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;

/**
 * Real-time Chat Service
 * Customer to Agent communication
 */
class ChatService
{
    private $database;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }

    private function getTenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable $e) {
            return 1;
        }
    }
    
    /**
     * Ensure chat tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Chat conversations
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Chat messages
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Chat participants (for group chats in future)
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Quick replies / templates
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Chat settings per user
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Start new conversation
     */
    public function startConversation(int $customerId, int $agentId, array $context = []): array
    {
        try {
            // Check if active conversation exists
            $existingSql = "SELECT id FROM chat_conversations 
                WHERE customer_id = ? AND agent_id = ? AND status = 'active'
                LIMIT 1";
            $existingStmt = $this->database->prepare($existingSql);
            $existingStmt->execute([$customerId, $agentId]);
            $existing = $existingStmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($existing) {
                return [
                    'success' => true,
                    'conversation_id' => $existing['id'],
                    'is_new' => false
                ];
            }
            
            // Create new conversation
            $sql = "INSERT INTO chat_conversations 
                (customer_id, agent_id, property_id, lead_id, source, metadata)
                VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $customerId,
                $agentId,
                $context['property_id'] ?? null,
                $context['lead_id'] ?? null,
                $context['source'] ?? 'website',
                json_encode($context['metadata'] ?? [])
            ]);
            
            $conversationId = $this->database->lastInsertId();
            
            // Add system welcome message
            $this->sendMessage($conversationId, 0, 'system', 
                'Welcome to APS Dream Home! How can we help you today?', 
                ['type' => 'system_welcome']
            );
            
            return [
                'success' => true,
                'conversation_id' => $conversationId,
                'is_new' => true
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send message
     */
    public function sendMessage(int $conversationId, int $senderId, string $senderType, 
        string $message, array $metadata = [], array $attachments = []): array
    {
        try {
            // Get conversation details
            $convSql = "SELECT * FROM chat_conversations WHERE id = ?";
            $convStmt = $this->database->prepare($convSql);
            $convStmt->execute([$conversationId]);
            $conversation = $convStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$conversation) {
                return ['success' => false, 'error' => 'Conversation not found'];
            }
            
            // Insert message
            $sql = "INSERT INTO chat_messages 
                (conversation_id, sender_id, sender_type, message_type, message, 
                 attachments, metadata, reply_to_message_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $conversationId,
                $senderId,
                $senderType,
                $metadata['type'] ?? 'text',
                $message,
                json_encode($attachments),
                json_encode($metadata),
                $metadata['reply_to'] ?? null
            ]);
            
            $messageId = $this->database->lastInsertId();
            
            // Update conversation
            $updateSql = "UPDATE chat_conversations SET 
                last_message_at = NOW(),
                last_message_preview = ?,
                customer_unread_count = CASE WHEN ? = 'agent' THEN customer_unread_count + 1 ELSE customer_unread_count END,
                agent_unread_count = CASE WHEN ? = 'customer' THEN agent_unread_count + 1 ELSE agent_unread_count END,
                updated_at = NOW()
                WHERE id = ?";
            
            $updateStmt = $this->database->prepare($updateSql);
            $preview = substr($message, 0, 100);
            $updateStmt->execute([$preview, $senderType, $senderType, $conversationId]);
            
            // Send push notification
            $this->sendNotification($conversation, $senderType, $message);
            
            return [
                'success' => true,
                'message_id' => $messageId,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get conversation messages
     */
    public function getMessages(int $conversationId, int $userId, string $userType, 
        int $limit = 50, ?int $beforeId = null): array
    {
        try {
            // Verify user is part of conversation
            $verifySql = "SELECT id FROM chat_conversations 
                WHERE id = ? AND (customer_id = ? OR agent_id = ?)
                LIMIT 1";
            $verifyStmt = $this->database->prepare($verifySql);
            $verifyStmt->execute([$conversationId, $userId, $userId]);
            
            if (!$verifyStmt->fetch()) {
                return ['success' => false, 'error' => 'Access denied'];
            }
            
            // Get messages
            $tid = $this->getTenantId();
            $tenantSql = $tid > 1 ? " AND c.tenant_id = ?" : "";
            $agentTenantSql = $tid > 1 ? " AND a.tenant_id = ?" : "";
            $sql = "SELECT m.*, 
                CASE 
                    WHEN m.sender_type = 'customer' THEN u.name
                    WHEN m.sender_type = 'agent' THEN a.name
                    ELSE 'System'
                END as sender_name
                FROM chat_messages m
                LEFT JOIN users u ON m.sender_type = 'customer' AND u.id = (SELECT c.user_id FROM users c WHERE c.id = m.sender_id{$tenantSql})
                LEFT JOIN users a ON m.sender_id = a.id AND m.sender_type = 'agent'{$agentTenantSql}
                WHERE m.conversation_id = ? 
                " . ($beforeId ? "AND m.id < ?" : "") . "
                ORDER BY m.id DESC
                LIMIT ?";
            
            $stmt = $this->database->prepare($sql);
            $params = [$conversationId];
            if ($beforeId) $params[] = $beforeId;
            $params[] = $limit;
            if ($tid > 1) { $params[] = $tid; $params[] = $tid; }
            $stmt->execute($params);
            
            $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Mark messages as read
            $this->markAsRead($conversationId, $userType);
            
            return [
                'success' => true,
                'messages' => array_reverse($messages),
                'has_more' => count($messages) >= $limit
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get user conversations
     */
    public function getConversations(int $userId, string $userType, string $status = 'active'): array
    {
        $column = $userType === 'customer' ? 'customer_id' : 'agent_id';
        $tid = $this->getTenantId();
        $custTenantSql = $tid > 1 ? " AND cust.tenant_id = ?" : "";
        $agentTenantSql = $tid > 1 ? " AND a.tenant_id = ?" : "";
        
        $sql = "SELECT c.*,
            CASE 
                WHEN ? = 'customer' THEN a.name
                ELSE u.name
            END as other_party_name,
            CASE 
                WHEN ? = 'customer' THEN a.phone
                ELSE u.phone
            END as other_party_phone,
            p.title as property_title
            FROM chat_conversations c
            LEFT JOIN users u ON u.id = (SELECT cust.user_id FROM users cust WHERE cust.id = c.customer_id{$custTenantSql})
            LEFT JOIN users a ON c.agent_id = a.id{$agentTenantSql}
            LEFT JOIN properties p ON c.property_id = p.id
            WHERE c.{$column} = ? AND c.status = ?
            ORDER BY c.last_message_at DESC";
        
        $stmt = $this->database->prepare($sql);
        $params = [$userType, $userType, $userId, $status];
        if ($tid > 1) { $params[] = $tid; $params[] = $tid; }
        $stmt->execute($params);
        
        return [
            'success' => true,
            'conversations' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
        ];
    }
    
    /**
     * Mark messages as read
     */
    private function markAsRead(int $conversationId, string $userType): void
    {
        $counterColumn = $userType === 'customer' ? 'agent_unread_count' : 'customer_unread_count';
        $senderTypeToMark = $userType === 'customer' ? 'agent' : 'customer';
        
        // Reset unread count
        $updateSql = "UPDATE chat_conversations SET {$counterColumn} = 0 WHERE id = ?";
        $updateStmt = $this->database->prepare($updateSql);
        $updateStmt->execute([$conversationId]);
        
        // Mark messages as read
        $msgSql = "UPDATE chat_messages SET is_read = 1, read_at = NOW()
            WHERE conversation_id = ? AND sender_type = ? AND is_read = 0";
        $msgStmt = $this->database->prepare($msgSql);
        $msgStmt->execute([$conversationId, $senderTypeToMark]);
    }
    
    /**
     * Send push notification
     */
    private function sendNotification(array $conversation, string $senderType, string $message): void
    {
        // Get recipient
        $recipientId = $senderType === 'customer' ? $conversation['agent_id'] : $conversation['customer_id'];
        $recipientType = $senderType === 'customer' ? 'agent' : 'customer';
        
        // This would integrate with NotificationService
        // For now, just log it
        // NotificationService::sendPush($recipientId, $recipientType, 'New message', substr($message, 0, 100));
    }
    
    /**
     * Close conversation
     */
    public function closeConversation(int $conversationId, int $closedBy, string $reason = ''): array
    {
        $sql = "UPDATE chat_conversations SET 
            status = 'closed',
            metadata = JSON_SET(COALESCE(metadata, '{}'), '$.closed_by', ?, '$.closed_reason', ?, '$.closed_at', NOW())
            WHERE id = ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$closedBy, $reason, $conversationId]);
        
        // Add system message
        $this->sendMessage($conversationId, 0, 'system', 
            'This conversation has been closed.', 
            ['type' => 'system_close']
        );
        
        return ['success' => true];
    }
    
    /**
     * Get or create quick replies
     */
    public function getQuickReplies(?int $agentId = null): array
    {
        $sql = "SELECT * FROM chat_quick_replies 
            WHERE is_global = 1 OR agent_id = ?
            AND is_active = 1
            ORDER BY usage_count DESC, category ASC";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$agentId ?? 0]);
        
        return [
            'success' => true,
            'replies' => $stmt->fetchAll(\PDO::FETCH_ASSOC)
        ];
    }
    
    /**
     * Save quick reply
     */
    public function saveQuickReply(int $agentId, string $title, string $message, 
        string $category = '', ?string $shortcut = null): array
    {
        $sql = "INSERT INTO chat_quick_replies 
            (agent_id, title, message, category, shortcut)
            VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$agentId, $title, $message, $category, $shortcut]);
        
        return [
            'success' => true,
            'reply_id' => $this->database->lastInsertId()
        ];
    }
    
    /**
     * Get chat statistics
     */
    public function getChatStats(int $agentId, string $dateFrom = null, string $dateTo = null): array
    {
        $dateFrom = $dateFrom ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $dateTo ?? date('Y-m-d');
        
        $sql = "SELECT 
            COUNT(DISTINCT c.id) as total_conversations,
            COUNT(DISTINCT CASE WHEN c.status = 'active' THEN c.id END) as active_conversations,
            COUNT(m.id) as total_messages,
            AVG(CASE WHEN m.sender_type = 'agent' THEN 1 END) as agent_messages,
            AVG(TIMESTAMPDIFF(MINUTE, c.created_at, c.last_message_at)) as avg_conversation_duration
            FROM chat_conversations c
            LEFT JOIN chat_messages m ON c.id = m.conversation_id
            WHERE c.agent_id = ? AND DATE(c.created_at) BETWEEN ? AND ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$agentId, $dateFrom, $dateTo]);
        
        return [
            'success' => true,
            'stats' => $stmt->fetch(\PDO::FETCH_ASSOC)
        ];
    }
    
    /**
     * Get unread count
     */
    public function getUnreadCount(int $userId, string $userType): array
    {
        if ($userType === 'customer') {
            $sql = "SELECT SUM(customer_unread_count) as total,
                COUNT(CASE WHEN customer_unread_count > 0 THEN 1 END) as conversations
                FROM chat_conversations WHERE customer_id = ? AND status = 'active'";
        } else {
            $sql = "SELECT SUM(agent_unread_count) as total,
                COUNT(CASE WHEN agent_unread_count > 0 THEN 1 END) as conversations
                FROM chat_conversations WHERE agent_id = ? AND status = 'active'";
        }
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId]);
        
        return [
            'success' => true,
            'unread' => $stmt->fetch(\PDO::FETCH_ASSOC)
        ];
    }
}
