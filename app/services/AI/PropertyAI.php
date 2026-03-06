<?php

namespace App\Services\Legacy;
/**
 * Enhanced PropertyAI - Advanced AI-powered property search and recommendations
 * Comprehensive AI system with chat, recommendations, and predictive analytics
 */

require_once __DIR__ . '/ai/AIManager.php';

class PropertyAI {
    private $db;
    private $config;
    private $aiManager;
    private $chat_history = [];

    /**
     * Constructor
     */
    public function __construct($db = null, $config = []) {
        $this->db = $db ?: \App\Core\App::database();
        $this->config = array_merge([
            'use_mock_data' => false,
            'cache_ttl' => 3600, // 1 hour cache
            'max_chat_history' => 50,
            'enable_ml_predictions' => true
        ], $config);

        $this->aiManager = new AIManager($this->db);
    }

    /**
     * Process chat message with AI
     */
    public function processChatMessage($messageData) {
        $message = $messageData['message'] ?? '';
        $conversationId = $messageData['conversation_id'] ?? null;
        $context = $messageData['context'] ?? 'general_inquiry';
        $ipAddress = $messageData['ip_address'] ?? '';

        // Validate input
        if (empty($message)) {
            return [
                'response' => 'Please provide a message to chat about.',
                'conversation_id' => $conversationId,
                'context' => $context,
                'confidence' => 0
            ];
        }

        // Use AIManager for NLP analysis
        $analysis = $this->aiManager->analyzeLead($message);

        // Use AIManager to generate a personalized response based on analysis and memory
        $response = $this->aiManager->generateResponse($analysis, $message);

        // Log to chat history for dashboard monitoring
        $this->logToChatHistory($conversationId, $message, $response, $analysis['intent']['name']);

        return [
            'response' => $response,
            'analysis' => $analysis
        ];
    }

    private function logToChatHistory($userId, $message, $response, $intent) {
        $sql = "INSERT INTO ai_chat_history (conversation_id, message, ai_response, context) VALUES (?, ?, ?, ?)";
        try {
            $this->db->execute($sql, [$userId, $message, $response, $intent]);
        } catch (\Exception $e) {
            error_log("Log Chat History Error: " . $e->getMessage());
        }
    }

    /**
     * Store chat message in database
     */
    private function storeChatMessage($message, $conversationId, $context, $ipAddress) {
        try {
            $sql = "INSERT INTO ai_chat_history (conversation_id, message, sender, context, ip_address)
                    VALUES (?, ?, 'user', ?, ?)";

            $this->db->execute($sql, [$conversationId, $message, $context, $ipAddress]);
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log("Store Chat Error: " . $e->getMessage());
        }
        return \App\Helpers\SecurityHelper::secureRandomInt(1000, 9999);
    }

    /**
     * Store AI response in database
     */
    private function storeAIResponse($chatId, $response, $confidence) {
        try {
            $sql = "UPDATE ai_chat_history SET ai_response = ?, confidence_score = ? WHERE id = ?";
            $this->db->execute($sql, [$response, $confidence, $chatId]);
        } catch (\Exception $e) {
            error_log("Store AI Response Error: " . $e->getMessage());
        }
    }
}
