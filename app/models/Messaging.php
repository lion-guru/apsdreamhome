<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * In-app Messaging Model
 * Handles conversations, messages, participants, and real-time messaging
 */
class Messaging extends Model
{
    protected $table = 'conversations';

    const CONVERSATION_DIRECT = 'direct';
    const CONVERSATION_GROUP = 'group';
    const CONVERSATION_SUPPORT = 'support';
    const CONVERSATION_ANNOUNCEMENT = 'announcement';

    const MESSAGE_TEXT = 'text';
    const MESSAGE_IMAGE = 'image';
    const MESSAGE_FILE = 'file';
    const MESSAGE_LOCATION = 'location';
    const MESSAGE_CONTACT = 'contact';
    const MESSAGE_PROPERTY = 'property';
    const MESSAGE_INVOICE = 'invoice';
    const MESSAGE_SYSTEM = 'system';

    /**
     * Create a new conversation
     */
    public function createConversation(array $conversationData, array $participants): array
    {
        $db = Database::getInstance();

        $conversation = [
            'conversation_type' => $conversationData['conversation_type'] ?? self::CONVERSATION_DIRECT,
            'title' => $conversationData['title'] ?? null,
            'description' => $conversationData['description'] ?? null,
            'created_by' => $conversationData['created_by'],
            'created_by_type' => $conversationData['created_by_type'] ?? 'customer',
            'is_active' => 1,
            'metadata' => json_encode($conversationData['metadata'] ?? []),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $conversationId = $this->insert($conversation);

        // Add participants
        $this->addParticipants($conversationId, $participants);

        return [
            'success' => true,
            'conversation_id' => $conversationId,
            'message' => 'Conversation created successfully'
        ];
    }

    /**
     * Send a message
     */
    public function sendMessage(array $messageData): array
    {
        $db = Database::getInstance();

        // Validate conversation exists and user is participant
        if (!$this->isUserInConversation($messageData['conversation_id'], $messageData['sender_id'], $messageData['sender_type'])) {
            return ['success' => false, 'message' => 'User is not a participant in this conversation'];
        }

        $message = [
            'conversation_id' => $messageData['conversation_id'],
            'sender_id' => $messageData['sender_id'],
            'sender_type' => $messageData['sender_type'] ?? 'customer',
            'message_type' => $messageData['message_type'] ?? self::MESSAGE_TEXT,
            'content' => $messageData['content'] ?? null,
            'metadata' => json_encode($messageData['metadata'] ?? []),
            'reply_to_message_id' => $messageData['reply_to_message_id'] ?? null,
            'sent_at' => date('Y-m-d H:i:s')
        ];

        $messageId = $db->query(
            "INSERT INTO messages (conversation_id, sender_id, sender_type, message_type, content, metadata, reply_to_message_id, sent_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($message)
        );

        // Handle attachments if any
        if (!empty($messageData['attachments'])) {
            $this->addMessageAttachments($messageId, $messageData['attachments']);
        }

        // Update conversation last message
        $this->updateConversationLastMessage($messageData['conversation_id'], $messageData['content'], $messageData['sent_at']);

        // Update unread counts for other participants
        $this->updateUnreadCounts($messageData['conversation_id'], $messageData['sender_id'], $messageData['sender_type']);

        return [
            'success' => true,
            'message_id' => $messageId,
            'message' => 'Message sent successfully'
        ];
    }

    /**
     * Get user's conversations
     */
    public function getUserConversations(int $userId, string $userType, int $limit = 20, int $offset = 0): array
    {
        $db = Database::getInstance();

        $conversations = $db->query(
            "SELECT c.*,
                    cp.unread_count,
                    cp.last_seen_at,
                    cp.role as user_role,
                    (SELECT COUNT(*) FROM conversation_participants WHERE conversation_id = c.id AND is_active = 1) as total_participants
             FROM conversations c
             LEFT JOIN conversation_participants cp ON c.id = cp.conversation_id
                 AND cp.user_id = ? AND cp.user_type = ?
             WHERE cp.is_active = 1 AND c.is_active = 1 AND c.is_archived = 0
             ORDER BY c.last_message_at DESC
             LIMIT ? OFFSET ?",
            [$userId, $userType, $limit, $offset]
        )->fetchAll();

        // Get participant info for each conversation
        foreach ($conversations as &$conversation) {
            $conversation['participants'] = $this->getConversationParticipants($conversation['id']);
            $conversation['last_message'] = $this->getConversationLastMessage($conversation['id']);
        }

        return $conversations;
    }

    /**
     * Get conversation messages
     */
    public function getConversationMessages(int $conversationId, int $userId, string $userType, int $limit = 50, int $beforeMessageId = null): array
    {
        // Validate user is participant
        if (!$this->isUserInConversation($conversationId, $userId, $userType)) {
            return ['error' => 'Access denied'];
        }

        $db = Database::getInstance();

        $query = "SELECT m.*,
                         ma.file_name, ma.file_path, ma.file_size, ma.mime_type, ma.file_type,
                         mr.reaction_type, COUNT(mr.id) as reaction_count
                  FROM messages m
                  LEFT JOIN message_attachments ma ON m.id = ma.message_id
                  LEFT JOIN message_reactions mr ON m.id = mr.message_id
                  WHERE m.conversation_id = ? AND m.is_deleted = 0";

        $params = [$conversationId];

        if ($beforeMessageId) {
            $query .= " AND m.id < ?";
            $params[] = $beforeMessageId;
        }

        $query .= " ORDER BY m.sent_at DESC LIMIT ?";
        $params[] = $limit;

        $messages = $db->query($query, $params)->fetchAll();

        // Group reactions and format messages
        $formattedMessages = [];
        foreach ($messages as $message) {
            $messageId = $message['id'];

            if (!isset($formattedMessages[$messageId])) {
                $formattedMessages[$messageId] = [
                    'id' => $message['id'],
                    'conversation_id' => $message['conversation_id'],
                    'sender_id' => $message['sender_id'],
                    'sender_type' => $message['sender_type'],
                    'message_type' => $message['message_type'],
                    'content' => $message['content'],
                    'metadata' => json_decode($message['metadata'], true),
                    'reply_to_message_id' => $message['reply_to_message_id'],
                    'is_edited' => $message['is_edited'],
                    'sent_at' => $message['sent_at'],
                    'delivered_at' => $message['delivered_at'],
                    'read_at' => $message['read_at'],
                    'attachments' => [],
                    'reactions' => []
                ];
            }

            // Add attachment if exists
            if ($message['file_name']) {
                $formattedMessages[$messageId]['attachments'][] = [
                    'file_name' => $message['file_name'],
                    'file_path' => $message['file_path'],
                    'file_size' => $message['file_size'],
                    'mime_type' => $message['mime_type'],
                    'file_type' => $message['file_type']
                ];
            }

            // Add reaction if exists
            if ($message['reaction_type']) {
                $reactionKey = $message['reaction_type'];
                if (!isset($formattedMessages[$messageId]['reactions'][$reactionKey])) {
                    $formattedMessages[$messageId]['reactions'][$reactionKey] = [
                        'type' => $reactionKey,
                        'count' => 0,
                        'users' => []
                    ];
                }
                $formattedMessages[$messageId]['reactions'][$reactionKey]['count'] = $message['reaction_count'];
            }
        }

        return array_values($formattedMessages);
    }

    /**
     * Mark messages as read
     */
    public function markMessagesAsRead(int $conversationId, int $userId, string $userType, int $lastMessageId = null): array
    {
        if (!$this->isUserInConversation($conversationId, $userId, $userType)) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        $db = Database::getInstance();

        // Mark messages as read
        $query = "UPDATE messages SET read_at = NOW() WHERE conversation_id = ? AND sender_id != ? AND read_at IS NULL";
        $params = [$conversationId, $userId];

        if ($lastMessageId) {
            $query .= " AND id <= ?";
            $params[] = $lastMessageId;
        }

        $db->query($query, $params);

        // Reset unread count
        $db->query(
            "UPDATE conversation_participants SET unread_count = 0, last_seen_at = NOW()
             WHERE conversation_id = ? AND user_id = ? AND user_type = ?",
            [$conversationId, $userId, $userType]
        );

        return ['success' => true, 'message' => 'Messages marked as read'];
    }

    /**
     * Add reaction to message
     */
    public function addMessageReaction(int $messageId, int $userId, string $userType, string $reactionType): array
    {
        $db = Database::getInstance();

        // Check if message exists and user can access it
        $message = $db->query("SELECT conversation_id FROM messages WHERE id = ?", [$messageId])->fetch();
        if (!$message) {
            return ['success' => false, 'message' => 'Message not found'];
        }

        if (!$this->isUserInConversation($message['conversation_id'], $userId, $userType)) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        // Add or remove reaction (toggle)
        $existing = $db->query(
            "SELECT id FROM message_reactions WHERE message_id = ? AND user_id = ? AND user_type = ? AND reaction_type = ?",
            [$messageId, $userId, $userType, $reactionType]
        )->fetch();

        if ($existing) {
            // Remove reaction
            $db->query("DELETE FROM message_reactions WHERE id = ?", [$existing['id']]);
            $action = 'removed';
        } else {
            // Add reaction
            $db->query(
                "INSERT INTO message_reactions (message_id, user_id, user_type, reaction_type)
                 VALUES (?, ?, ?, ?)",
                [$messageId, $userId, $userType, $reactionType]
            );
            $action = 'added';
        }

        return [
            'success' => true,
            'message' => "Reaction {$action} successfully",
            'action' => $action
        ];
    }

    /**
     * Start typing indicator
     */
    public function startTyping(int $conversationId, int $userId, string $userType): array
    {
        if (!$this->isUserInConversation($conversationId, $userId, $userType)) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        $db = Database::getInstance();

        $db->query(
            "INSERT INTO typing_indicators (conversation_id, user_id, user_type, started_at, last_updated)
             VALUES (?, ?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE last_updated = NOW()",
            [$conversationId, $userId, $userType]
        );

        return ['success' => true, 'message' => 'Typing indicator started'];
    }

    /**
     * Stop typing indicator
     */
    public function stopTyping(int $conversationId, int $userId, string $userType): array
    {
        $db = Database::getInstance();

        $db->query(
            "DELETE FROM typing_indicators
             WHERE conversation_id = ? AND user_id = ? AND user_type = ?",
            [$conversationId, $userId, $userType]
        );

        return ['success' => true, 'message' => 'Typing indicator stopped'];
    }

    /**
     * Get typing indicators for conversation
     */
    public function getTypingIndicators(int $conversationId): array
    {
        $db = Database::getInstance();

        // Clean up old typing indicators (older than 10 seconds)
        $db->query(
            "DELETE FROM typing_indicators WHERE last_updated < DATE_SUB(NOW(), INTERVAL 10 SECOND)"
        );

        return $db->query(
            "SELECT ti.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
             FROM typing_indicators ti
             LEFT JOIN users u ON ti.user_id = u.id AND ti.user_type = 'customer'
             WHERE ti.conversation_id = ?
             ORDER BY ti.last_updated DESC",
            [$conversationId]
        )->fetchAll();
    }

    /**
     * Add participants to conversation
     */
    private function addParticipants(int $conversationId, array $participants): void
    {
        $db = Database::getInstance();

        foreach ($participants as $participant) {
            $db->query(
                "INSERT INTO conversation_participants
                 (conversation_id, user_id, user_type, role, joined_at)
                 VALUES (?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE is_active = 1",
                [
                    $conversationId,
                    $participant['user_id'],
                    $participant['user_type'] ?? 'customer',
                    $participant['role'] ?? 'member'
                ]
            );
        }
    }

    /**
     * Add message attachments
     */
    private function addMessageAttachments(int $messageId, array $attachments): void
    {
        $db = Database::getInstance();

        foreach ($attachments as $attachment) {
            $db->query(
                "INSERT INTO message_attachments
                 (message_id, file_name, file_path, file_size, mime_type, file_type, thumbnail_path)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $messageId,
                    $attachment['file_name'],
                    $attachment['file_path'],
                    $attachment['file_size'],
                    $attachment['mime_type'],
                    $attachment['file_type'] ?? 'other',
                    $attachment['thumbnail_path'] ?? null
                ]
            );
        }
    }

    /**
     * Update conversation last message
     */
    private function updateConversationLastMessage(int $conversationId, string $content, string $timestamp): void
    {
        $preview = strlen($content) > 100 ? substr($content, 0, 100) . '...' : $content;

        $this->update($conversationId, [
            'last_message_at' => $timestamp,
            'last_message_preview' => $preview,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update unread counts for participants
     */
    private function updateUnreadCounts(int $conversationId, int $senderId, string $senderType): void
    {
        $db = Database::getInstance();

        $db->query(
            "UPDATE conversation_participants
             SET unread_count = unread_count + 1
             WHERE conversation_id = ?
             AND (user_id != ? OR user_type != ?)
             AND is_active = 1",
            [$conversationId, $senderId, $senderType]
        );
    }

    /**
     * Check if user is in conversation
     */
    private function isUserInConversation(int $conversationId, int $userId, string $userType): bool
    {
        $participant = $this->query(
            "SELECT id FROM conversation_participants
             WHERE conversation_id = ? AND user_id = ? AND user_type = ? AND is_active = 1",
            [$conversationId, $userId, $userType]
        )->fetch();

        return $participant !== false;
    }

    /**
     * Get conversation participants
     */
    private function getConversationParticipants(int $conversationId): array
    {
        return $this->query(
            "SELECT cp.*, u.first_name, u.last_name, u.email
             FROM conversation_participants cp
             LEFT JOIN users u ON cp.user_id = u.id AND cp.user_type = 'customer'
             WHERE cp.conversation_id = ? AND cp.is_active = 1
             ORDER BY cp.joined_at ASC",
            [$conversationId]
        )->fetchAll();
    }

    /**
     * Get conversation last message
     */
    private function getConversationLastMessage(int $conversationId): ?array
    {
        return $this->query(
            "SELECT m.*, u.first_name, u.last_name
             FROM messages m
             LEFT JOIN users u ON m.sender_id = u.id AND m.sender_type = 'customer'
             WHERE m.conversation_id = ? AND m.is_deleted = 0
             ORDER BY m.sent_at DESC LIMIT 1",
            [$conversationId]
        )->fetch();
    }

    /**
     * Get message templates
     */
    public function getMessageTemplates(string $category = null): array
    {
        $query = "SELECT * FROM message_templates WHERE is_active = 1";
        $params = [];

        if ($category) {
            $query .= " AND category = ?";
            $params[] = $category;
        }

        $query .= " ORDER BY name ASC";

        return $this->query($query, $params)->fetchAll();
    }

    /**
     * Save quick reply
     */
    public function saveQuickReply(int $userId, string $userType, array $quickReplyData): array
    {
        $db = Database::getInstance();

        $db->query(
            "INSERT INTO quick_replies (user_id, user_type, title, content, category, is_active)
             VALUES (?, ?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE content = ?, category = ?, updated_at = NOW()",
            [
                $userId,
                $userType,
                $quickReplyData['title'],
                $quickReplyData['content'],
                $quickReplyData['category'] ?? null,
                $quickReplyData['content'],
                $quickReplyData['category'] ?? null
            ]
        );

        return ['success' => true, 'message' => 'Quick reply saved successfully'];
    }

    /**
     * Get user quick replies
     */
    public function getUserQuickReplies(int $userId, string $userType): array
    {
        return $this->query(
            "SELECT * FROM quick_replies
             WHERE user_id = ? AND user_type = ? AND is_active = 1
             ORDER BY usage_count DESC, created_at DESC",
            [$userId, $userType]
        )->fetchAll();
    }

    /**
     * Update quick reply usage
     */
    public function updateQuickReplyUsage(int $quickReplyId): void
    {
        $this->query(
            "UPDATE quick_replies SET usage_count = usage_count + 1, updated_at = NOW() WHERE id = ?",
            [$quickReplyId]
        );
    }

    /**
     * Get messaging statistics
     */
    public function getMessagingStats(string $period = '30 days'): array
    {
        $db = Database::getInstance();

        $startDate = date('Y-m-d', strtotime("-{$period}"));

        $stats = $db->query(
            "SELECT
                (SELECT COUNT(*) FROM conversations WHERE created_at >= ?) as total_conversations,
                (SELECT COUNT(*) FROM messages WHERE sent_at >= ?) as total_messages,
                (SELECT COUNT(*) FROM conversation_participants WHERE joined_at >= ?) as active_participants,
                (SELECT COUNT(DISTINCT conversation_id) FROM messages WHERE sent_at >= ?) as active_conversations",
            [$startDate, $startDate, $startDate, $startDate]
        )->fetch();

        // Get message type breakdown
        $messageTypes = $db->query(
            "SELECT message_type, COUNT(*) as count
             FROM messages
             WHERE sent_at >= ?
             GROUP BY message_type
             ORDER BY count DESC",
            [$startDate]
        )->fetchAll();

        $stats['message_types'] = $messageTypes;

        return $stats;
    }

    /**
     * Archive conversation
     */
    public function archiveConversation(int $conversationId, int $userId, string $userType): array
    {
        if (!$this->isUserInConversation($conversationId, $userId, $userType)) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        $this->update($conversationId, [
            'is_archived' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return ['success' => true, 'message' => 'Conversation archived successfully'];
    }

    /**
     * Create support ticket conversation
     */
    public function createSupportConversation(int $customerId, string $subject, string $initialMessage): array
    {
        $participants = [
            ['user_id' => $customerId, 'user_type' => 'customer', 'role' => 'owner'],
            // Add support agents here based on your assignment logic
            // For now, we'll add a default support user
        ];

        $conversationResult = $this->createConversation([
            'conversation_type' => self::CONVERSATION_SUPPORT,
            'title' => $subject,
            'created_by' => $customerId,
            'created_by_type' => 'customer'
        ], $participants);

        if ($conversationResult['success']) {
            // Send initial message
            $this->sendMessage([
                'conversation_id' => $conversationResult['conversation_id'],
                'sender_id' => $customerId,
                'sender_type' => 'customer',
                'message_type' => self::MESSAGE_TEXT,
                'content' => $initialMessage
            ]);
        }

        return $conversationResult;
    }

    /**
     * Create announcement conversation
     */
    public function createAnnouncement(array $announcementData): array
    {
        $db = Database::getInstance();

        // Get target users based on criteria
        $targetUsers = $this->getAnnouncementTargets($announcementData['target_criteria'] ?? []);

        if (empty($targetUsers)) {
            return ['success' => false, 'message' => 'No target users found for announcement'];
        }

        // Create announcement conversation
        $conversationResult = $this->createConversation([
            'conversation_type' => self::CONVERSATION_ANNOUNCEMENT,
            'title' => $announcementData['title'],
            'description' => $announcementData['description'] ?? null,
            'created_by' => $announcementData['created_by'],
            'created_by_type' => $announcementData['created_by_type'] ?? 'admin',
            'metadata' => [
                'announcement_type' => $announcementData['announcement_type'] ?? 'general',
                'priority' => $announcementData['priority'] ?? 'normal',
                'scheduled_at' => $announcementData['scheduled_at'] ?? null,
                'expires_at' => $announcementData['expires_at'] ?? null,
                'target_criteria' => $announcementData['target_criteria'] ?? [],
                'is_broadcast' => true
            ]
        ], $targetUsers);

        if ($conversationResult['success']) {
            // Send announcement message
            $this->sendMessage([
                'conversation_id' => $conversationResult['conversation_id'],
                'sender_id' => $announcementData['created_by'],
                'sender_type' => $announcementData['created_by_type'] ?? 'admin',
                'message_type' => self::MESSAGE_SYSTEM,
                'content' => $announcementData['content'],
                'metadata' => [
                    'announcement_id' => $conversationResult['conversation_id'],
                    'announcement_type' => $announcementData['announcement_type'] ?? 'general',
                    'priority' => $announcementData['priority'] ?? 'normal',
                    'action_url' => $announcementData['action_url'] ?? null,
                    'action_text' => $announcementData['action_text'] ?? null
                ]
            ]);

            // Schedule announcement if specified
            if (!empty($announcementData['scheduled_at'])) {
                $this->scheduleAnnouncement($conversationResult['conversation_id'], $announcementData['scheduled_at']);
            }
        }

        return $conversationResult;
    }

    /**
     * Get announcement targets based on criteria
     */
    private function getAnnouncementTargets(array $criteria): array
    {
        $db = Database::getInstance();

        $query = "SELECT id as user_id, 'customer' as user_type FROM users WHERE 1=1";
        $params = [];

        // Apply targeting criteria
        if (!empty($criteria['user_type'])) {
            if ($criteria['user_type'] === 'customer') {
                $query = "SELECT id as user_id, 'customer' as user_type FROM users WHERE 1=1";
            } elseif ($criteria['user_type'] === 'employee') {
                $query = "SELECT id as user_id, 'employee' as user_type FROM employees WHERE status = 'active'";
            } elseif ($criteria['user_type'] === 'associate') {
                $query = "SELECT id as user_id, 'associate' as user_type FROM associates WHERE status = 'active'";
            }
        }

        if (!empty($criteria['city'])) {
            $query .= " AND city = ?";
            $params[] = $criteria['city'];
        }

        if (!empty($criteria['registration_date_from'])) {
            $query .= " AND created_at >= ?";
            $params[] = $criteria['registration_date_from'];
        }

        if (!empty($criteria['registration_date_to'])) {
            $query .= " AND created_at <= ?";
            $params[] = $criteria['registration_date_to'];
        }

        // Limit targets for performance (max 1000 users per announcement)
        $query .= " LIMIT 1000";

        $users = $db->query($query, $params)->fetchAll();

        // Convert to participant format
        $participants = [];
        foreach ($users as $user) {
            $participants[] = [
                'user_id' => $user['user_id'],
                'user_type' => $user['user_type'],
                'role' => 'member'
            ];
        }

        return $participants;
    }

    /**
     * Schedule announcement for future delivery
     */
    private function scheduleAnnouncement(int $conversationId, string $scheduledAt): void
    {
        $db = Database::getInstance();

        // Mark conversation as inactive until scheduled time
        $this->update($conversationId, [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Add to scheduling system (could integrate with a job queue)
        // For now, we'll use a simple approach
    }

    /**
     * Send scheduled announcements
     */
    public function processScheduledAnnouncements(): array
    {
        $db = Database::getInstance();

        // Find conversations that should be activated now
        $scheduledConversations = $db->query(
            "SELECT c.id, JSON_UNQUOTE(JSON_EXTRACT(c.metadata, '$.scheduled_at')) as scheduled_at
             FROM conversations c
             WHERE c.conversation_type = ?
             AND c.is_active = 0
             AND JSON_UNQUOTE(JSON_EXTRACT(c.metadata, '$.scheduled_at')) <= NOW()",
            [self::CONVERSATION_ANNOUNCEMENT]
        )->fetchAll();

        $processed = 0;
        foreach ($scheduledConversations as $conversation) {
            // Activate conversation
            $this->update($conversation['id'], [
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $processed++;
        }

        return [
            'success' => true,
            'processed' => $processed,
            'message' => "Processed {$processed} scheduled announcements"
        ];
    }

    /**
     * Get announcements for user
     */
    public function getUserAnnouncements(int $userId, string $userType, int $limit = 20): array
    {
        return $this->getUserConversations($userId, $userType, $limit, 0, [self::CONVERSATION_ANNOUNCEMENT]);
    }

    /**
     * Mark announcement as read
     */
    public function markAnnouncementAsRead(int $conversationId, int $userId, string $userType): array
    {
        return $this->markMessagesAsRead($conversationId, $userId, $userType);
    }

    /**
     * Get announcement statistics
     */
    public function getAnnouncementStats(string $period = '30 days'): array
    {
        $db = Database::getInstance();

        $startDate = date('Y-m-d', strtotime("-{$period}"));

        $stats = $db->query(
            "SELECT
                COUNT(*) as total_announcements,
                COUNT(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.announcement_type')) = 'urgent' THEN 1 END) as urgent_announcements,
                COUNT(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.announcement_type')) = 'marketing' THEN 1 END) as marketing_announcements,
                (SELECT COUNT(*) FROM conversation_participants cp
                 LEFT JOIN conversations c ON cp.conversation_id = c.id
                 WHERE c.conversation_type = ? AND cp.joined_at >= ?) as total_recipients,
                (SELECT AVG(unread_count) FROM conversation_participants cp
                 LEFT JOIN conversations c ON cp.conversation_id = c.id
                 WHERE c.conversation_type = ? AND cp.joined_at >= ?) as avg_unread_count
             FROM conversations
             WHERE conversation_type = ? AND created_at >= ?",
            [
                self::CONVERSATION_ANNOUNCEMENT, $startDate,
                self::CONVERSATION_ANNOUNCEMENT, $startDate,
                self::CONVERSATION_ANNOUNCEMENT, $startDate
            ]
        )->fetch();

        return $stats ?: [
            'total_announcements' => 0,
            'urgent_announcements' => 0,
            'marketing_announcements' => 0,
            'total_recipients' => 0,
            'avg_unread_count' => 0
        ];
    }

    /**
     * Create recurring announcement
     */
    public function createRecurringAnnouncement(array $announcementData): array
    {
        $db = Database::getInstance();

        // Create base announcement template
        $templateId = $db->query(
            "INSERT INTO message_templates (template_key, name, category, content, variables, is_active)
             VALUES (?, ?, 'announcement', ?, ?, 1)",
            [
                'recurring_' . uniqid(),
                $announcementData['title'],
                $announcementData['content'],
                json_encode($announcementData['variables'] ?? [])
            ]
        );

        // Schedule recurring announcements
        $recurringData = [
            'template_id' => $templateId,
            'frequency' => $announcementData['frequency'] ?? 'weekly',
            'start_date' => $announcementData['start_date'],
            'end_date' => $announcementData['end_date'] ?? null,
            'target_criteria' => $announcementData['target_criteria'] ?? [],
            'is_active' => 1,
            'created_by' => $announcementData['created_by']
        ];

        // This would integrate with a job scheduler for recurring announcements
        // For now, just store the configuration

        return [
            'success' => true,
            'template_id' => $templateId,
            'message' => 'Recurring announcement created successfully'
        ];
    }

    /**
     * Archive old announcements
     */
    public function archiveOldAnnouncements(int $daysOld = 90): array
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$daysOld} days"));

        $db = Database::getInstance();

        // Mark old announcements as archived
        $db->query(
            "UPDATE conversations SET is_archived = 1, updated_at = NOW()
             WHERE conversation_type = ? AND created_at < ? AND is_archived = 0",
            [self::CONVERSATION_ANNOUNCEMENT, $cutoffDate]
        );

        $affectedRows = $db->rowCount();

        return [
            'success' => true,
            'archived_count' => $affectedRows,
            'message' => "Archived {$affectedRows} old announcements"
        ];
    }

    /**
     * Send announcement to specific user groups
     */
    public function sendGroupAnnouncement(array $announcementData, array $userGroups): array
    {
        $allTargets = [];

        foreach ($userGroups as $group) {
            $targets = $this->getAnnouncementTargets(['user_type' => $group]);
            $allTargets = array_merge($allTargets, $targets);
        }

        if (empty($allTargets)) {
            return ['success' => false, 'message' => 'No target users found in specified groups'];
        }

        // Remove duplicates
        $uniqueTargets = array_unique($allTargets, SORT_REG);

        $announcementData['target_criteria'] = ['user_groups' => $userGroups];

        return $this->createAnnouncement($announcementData);
    }

    /**
     * Get announcement delivery report
     */
    public function getAnnouncementDeliveryReport(int $announcementId): array
    {
        $db = Database::getInstance();

        // Get announcement details
        $announcement = $this->find($announcementId);
        if (!$announcement || $announcement['conversation_type'] !== self::CONVERSATION_ANNOUNCEMENT) {
            return ['error' => 'Announcement not found'];
        }

        // Get delivery statistics
        $stats = $db->query(
            "SELECT
                COUNT(*) as total_participants,
                COUNT(CASE WHEN last_seen_at IS NOT NULL THEN 1 END) as seen_by,
                COUNT(CASE WHEN unread_count = 0 THEN 1 END) as read_by,
                AVG(unread_count) as avg_unread_count
             FROM conversation_participants
             WHERE conversation_id = ? AND is_active = 1",
            [$announcementId]
        )->fetch();

        // Get message details
        $message = $db->query(
            "SELECT * FROM messages
             WHERE conversation_id = ? AND message_type = ?
             ORDER BY sent_at DESC LIMIT 1",
            [$announcementId, self::MESSAGE_SYSTEM]
        )->fetch();

        return [
            'announcement' => $announcement,
            'message' => $message,
            'delivery_stats' => $stats,
            'delivery_rate' => $stats['total_participants'] > 0
                ? round(($stats['seen_by'] / $stats['total_participants']) * 100, 2)
                : 0,
            'read_rate' => $stats['total_participants'] > 0
                ? round(($stats['read_by'] / $stats['total_participants']) * 100, 2)
                : 0
        ];
    }

    /**
     * Emergency broadcast to all users
     */
    public function emergencyBroadcast(array $emergencyData): array
    {
        $emergencyData['announcement_type'] = 'emergency';
        $emergencyData['priority'] = 'urgent';

        // Target all active users
        $emergencyData['target_criteria'] = [
            'include_all' => true
        ];

        return $this->createAnnouncement($emergencyData);
    }

    /**
     * Get announcement categories and types
     */
    public function getAnnouncementCategories(): array
    {
        return [
            'general' => [
                'name' => 'General Announcements',
                'description' => 'General company announcements and updates',
                'icon' => 'fas fa-bullhorn'
            ],
            'marketing' => [
                'name' => 'Marketing Announcements',
                'description' => 'Promotional content and marketing campaigns',
                'icon' => 'fas fa-ad'
            ],
            'urgent' => [
                'name' => 'Urgent Announcements',
                'description' => 'Critical updates requiring immediate attention',
                'icon' => 'fas fa-exclamation-triangle'
            ],
            'maintenance' => [
                'name' => 'Maintenance Notices',
                'description' => 'System maintenance and downtime notices',
                'icon' => 'fas fa-tools'
            ],
            'policy' => [
                'name' => 'Policy Updates',
                'description' => 'Company policy changes and updates',
                'icon' => 'fas fa-file-contract'
            ],
            'emergency' => [
                'name' => 'Emergency Alerts',
                'description' => 'Emergency situations and alerts',
                'icon' => 'fas fa-siren'
            ]
        ];
    }
}
