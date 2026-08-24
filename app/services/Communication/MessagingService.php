<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Services\Communication;

use App\Core\Database;
use App\Core\Middleware\TenantContext;
use \App\Traits\ServiceTenantTrait;

/**
 * In-app Messaging Service
 * User-to-user chat system
 */
class MessagingService
{
    use \App\Traits\ServiceTenantTrait;

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
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
     * Send message to user
     */
    public function sendMessage(int $senderId, int $receiverId, array $data): array
    {
        $message = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'conversation_id' => $this->getOrCreateConversation($senderId, $receiverId),
            'message' => $data['message'] ?? '',
            'message_type' => $data['type'] ?? 'text',
            'attachment_url' => $data['attachment_url'] ?? null,
            'metadata' => json_encode($data['metadata'] ?? []),
            'status' => 'sent',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $sql = "INSERT INTO messages (" . implode(', ', array_keys($message)) . ") 
                VALUES (" . implode(', ', array_fill(0, count($message), '?')) . ")";

        $this->db->query($sql, array_values($message));
        $messageId = $this->db->lastInsertId();

        // Send notification to receiver
        $this->notifyReceiver($receiverId, $messageId, $data['message'] ?? '');

        return [
            'success' => true,
            'message_id' => $messageId,
            'conversation_id' => $message['conversation_id']
        ];
    }

    /**
     * Get or create conversation between users
     */
    private function getOrCreateConversation(int $userId1, int $userId2): int
    {
        // Check existing conversation
        $conversationId = $this->db->query(
            "SELECT c.id FROM conversations c
             JOIN conversation_participants cp1 ON c.id = cp1.conversation_id AND cp1.user_id = ?
             JOIN conversation_participants cp2 ON c.id = cp2.conversation_id AND cp2.user_id = ?
             WHERE c.conversation_type = 'direct'
             LIMIT 1",
            [$userId1, $userId2]
        )->fetchColumn();

        if ($conversationId) {
            return (int)$conversationId;
        }

        // Create new conversation
        $this->db->query(
            "INSERT INTO conversations (conversation_type, created_at) VALUES ('direct', NOW())"
        );
        $conversationId = $this->db->lastInsertId();

        // Add participants
        $this->db->query(
            "INSERT INTO conversation_participants (conversation_id, user_id, joined_at) VALUES (?, ?, NOW()), (?, ?, NOW())",
            [$conversationId, $userId1, $conversationId, $userId2]
        );

        return (int)$conversationId;
    }

    /**
     * Get conversation messages
     */
    public function getConversationMessages(int $conversationId, int $userId, int $limit = 50, int $offset = 0): array
    {
        // Verify user is participant
        $isParticipant = $this->db->query(
            "SELECT 1 FROM conversation_participants WHERE conversation_id = ? AND user_id = ?",
            [$conversationId, $userId]
        )->fetchColumn();

        if (!$isParticipant) {
            return [];
        }

        $tid = $this->getTenantId();
        $tenantSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $params = $tid > 1 ? [$conversationId, $limit, $offset, $tid] : [$conversationId, $limit, $offset];

        $messages = $this->db->query(
            "SELECT m.*, u.name as sender_name, u.avatar as sender_avatar
             FROM messages m
             JOIN users u ON m.sender_id = u.id{$tenantSql}
             WHERE m.conversation_id = ?
             ORDER BY m.created_at DESC
             LIMIT ? OFFSET ?",
            $params
        )->fetchAll(\PDO::FETCH_ASSOC);

        // Mark as read
        $this->markAsRead($conversationId, $userId);

        return array_reverse($messages);
    }

    /**
     * Get user conversations
     */
    public function getUserConversations(int $userId): array
    {
        $tid = $this->getTenantId();
        $tenantSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $params = [$userId, $userId, $userId, $userId, $userId];
        if ($tid > 1) { $params[] = $tid; }
        return $this->db->query(
            "SELECT c.id, c.type, c.last_message_at,
                    (SELECT m.message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.created_at DESC LIMIT 1) as last_message,
                    (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.receiver_id = ? AND m.status != 'read') as unread_count,
                    (SELECT u.name FROM conversation_participants cp JOIN users u ON cp.user_id = u.id WHERE cp.conversation_id = c.id AND cp.user_id != ?{$tenantSql} LIMIT 1) as other_user_name,
                    (SELECT u.id FROM conversation_participants cp JOIN users u ON cp.user_id = u.id WHERE cp.conversation_id = c.id AND cp.user_id != ?{$tenantSql} LIMIT 1) as other_user_id,
                    (SELECT u.avatar FROM conversation_participants cp JOIN users u ON cp.user_id = u.id WHERE cp.conversation_id = c.id AND cp.user_id != ?{$tenantSql} LIMIT 1) as other_user_avatar
             FROM conversations c
             JOIN conversation_participants cp ON c.id = cp.conversation_id
             WHERE cp.user_id = ?
             ORDER BY c.last_message_at DESC",
            $params
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(int $conversationId, int $userId): bool
    {
        return $this->db->query(
            "UPDATE messages SET read_at = NOW() 
             WHERE conversation_id = ? AND receiver_id = ? AND read_at IS NULL",
            [$conversationId, $userId]
        )->rowCount() > 0;
    }

    /**
     * Delete message
     */
    public function deleteMessage(int $messageId, int $userId): array
    {
        $message = $this->db->query(
            "SELECT * FROM messages WHERE id = ?",
            [$messageId]
        )->fetch(\PDO::FETCH_ASSOC);

        if (!$message) {
            return ['success' => false, 'error' => 'Message not found'];
        }

        if ($message['sender_id'] != $userId) {
            return ['success' => false, 'error' => 'Not authorized'];
        }

        // Soft delete
        $this->db->query(
            "UPDATE messages SET deleted_at = NOW() WHERE id = ?",
            [$messageId]
        );

        return ['success' => true];
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(int $userId): int
    {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND status != 'read'",
            [$userId]
        )->fetchColumn();
    }

    /**
     * Send typing indicator
     */
    public function sendTypingIndicator(int $conversationId, int $userId): void
    {
        try {
            $this->db->query(
                "INSERT INTO typing_indicators (conversation_id, user_id, created_at) VALUES (?, ?, NOW())
                 ON DUPLICATE KEY UPDATE created_at = NOW()",
                [$conversationId, $userId]
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
    }

    /**
     * Check if user is typing
     */
    public function isTyping(int $conversationId, int $userId): bool
    {
        return (bool)$this->db->query(
            "SELECT 1 FROM typing_indicators 
             WHERE conversation_id = ? AND user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)",
            [$conversationId, $userId]
        )->fetchColumn();
    }

    /**
     * Create group conversation
     */
    public function createGroup(int $creatorId, array $participantIds, string $name = null): array
    {
        $this->db->query(
            "INSERT INTO conversations (conversation_type, title, created_by, created_at) VALUES ('group', ?, ?, NOW())",
            [$name, $creatorId]
        );
        $conversationId = $this->db->lastInsertId();

        // Add all participants
        $values = [];
        $params = [];
        foreach (array_merge([$creatorId], $participantIds) as $userId) {
            $values[] = "(?, ?, NOW())";
            $params[] = $conversationId;
            $params[] = $userId;
        }

        $this->db->query(
            "INSERT IGNORE INTO conversation_participants (conversation_id, user_id, joined_at) VALUES " . implode(', ', $values),
            $params
        );

        return [
            'success' => true,
            'conversation_id' => $conversationId
        ];
    }

    /**
     * Add participant to group
     */
    public function addParticipant(int $conversationId, int $userId, int $addedBy): array
    {
        // Verify it's a group
        $isGroup = $this->db->query(
            "SELECT type FROM conversations WHERE id = ?",
            [$conversationId]
        )->fetchColumn();

        if ($isGroup !== 'group') {
            return ['success' => false, 'error' => 'Not a group conversation'];
        }

        $this->db->query(
            "INSERT INTO conversation_participants (conversation_id, user_id, joined_at) VALUES (?, ?, NOW())",
            [$conversationId, $userId]
        );

        return ['success' => true];
    }

    /**
     * Remove participant from group
     */
    public function removeParticipant(int $conversationId, int $userId): bool
    {
        return $this->db->query(
            "DELETE FROM conversation_participants WHERE conversation_id = ? AND user_id = ?",
            [$conversationId, $userId]
        )->rowCount() > 0;
    }

    /**
     * Notify receiver of new message
     */
    private function notifyReceiver(int $receiverId, int $messageId, string $message): void
    {
        $pushService = new PushNotificationService();
        $pushService->sendToUser($receiverId, [
            'title' => 'New Message',
            'body' => strlen($message) > 50 ? substr($message, 0, 50) . '...' : $message,
            'icon' => 'message',
            'data' => [
                'type' => 'new_message',
                'message_id' => $messageId
            ]
        ]);
    }

    /**
     * Search messages
     */
    public function searchMessages(int $userId, string $query): array
    {
        $tid = $this->getTenantId();
        $tenantSql = $tid > 1 ? " AND u.tenant_id = ?" : "";
        $params = $tid > 1 ? [$userId, "%{$query}%", $tid] : [$userId, "%{$query}%"];
        return $this->db->query(
            "SELECT m.*, u.name as sender_name
             FROM messages m
             JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
             JOIN users u ON m.sender_id = u.id{$tenantSql}
             WHERE cp.user_id = ? AND m.message LIKE ?
             ORDER BY m.created_at DESC
             LIMIT 50",
            $params
        )->fetchAll(\PDO::FETCH_ASSOC);
    }
}
