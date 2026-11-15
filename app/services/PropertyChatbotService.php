<?php

namespace App\Services;

use App\Services\AI\AIService as UnifiedAIService;

/**
 * PropertyChatbotService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\AIService instead
 * Property-specific chatbot merged into unified AIService.
 */
class PropertyChatbotService extends UnifiedAIService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Persist a chatbot exchange to ai_conversations.
     *
     * ChatbotAPIController (public property assistant, /api/ai/chatbot) calls this.
     * The unified AIService does not provide it, so it exists here as a thin,
     * failure-proof adapter — a write failure must never break the conversation.
     */
    public function saveConversation($userId, string $message, array $response = []): void
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $sessionId = $response['session_id'] ?? (session_id() ?: 'web_' . uniqid());
            $reply = (string)($response['response'] ?? $response['reply'] ?? '');
            $role = (string)($_SESSION['user_role'] ?? $_SESSION['role'] ?? ($_SESSION['admin_role'] ?? 'guest'));
            $uid = null;
            if (is_numeric($userId) && (int)$userId > 0) {
                try {
                    $exists = $db->fetch("SELECT id FROM users WHERE id = ?", [(int)$userId]);
                    if (!empty($exists)) {
                        $uid = (int)$userId;
                    }
                } catch (\Throwable $ignore) {
                    $uid = null;
                }
            }
            $intent = $response['intent'] ?? null;

            $db->query(
                "INSERT INTO ai_conversations (session_id, user_id, user_role, message, response, intent, platform, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, 'website', NOW())",
                [$sessionId, $uid, $role, $message, $reply, $intent]
            );
        } catch (\Throwable $e) {
            error_log('PropertyChatbotService::saveConversation error: ' . $e->getMessage());
        }
    }
}
