<?php
/**
 * ConversationalAIService - Multi-turn conversational AI with context memory
 * Maintains conversation history, context awareness, and multi-turn dialogue
 */

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

class ConversationalAIService
{
    use ServiceTenantTrait;

    private $db;
    private $freeEngines;
    private $maxHistoryTurns = 10;
    private $contextWindowTokens = 4000;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Process a message in a conversation context
     */
    public function processMessage(string $sessionId, string $userId, string $message, array $context = []): array
    {
        $tid = $this->tenantId();

        // 1. Load conversation history
        $history = $this->getConversationHistory($sessionId, $tid);

        // 2. Build context with history + current message + user context
        $context = $this->buildContext($history, $message, $context, $userId);

        // 3. Generate response using AI Gateway
        $response = $this->generateResponse($context);

        // 4. Extract actions/intent from response
        $actions = $this->extractActions($response['text']);

        // 5. Save conversation turn
        $this->saveConversationTurn($sessionId, $userId, $message, $response['text'], $actions);

        // 6. Update session metadata
        $this->updateSessionMetadata($sessionId, $userId);

        return [
            'success' => true,
            'response' => $response['text'],
            'intent' => $response['intent'] ?? 'general',
            'confidence' => $response['confidence'] ?? 0.8,
            'actions' => $actions,
            'suggestions' => $this->generateSuggestions($actions),
            'session_id' => $sessionId
        ];
    }

    /**
     * Get conversation history for context
     */
    private function getConversationHistory(string $sessionId, int $tid): array
    {
        try {
            $sql = "SELECT role, content, intent, actions, created_at 
                    FROM conversation_history 
                    WHERE session_id = ? AND tenant_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $params = [$sessionId, $tid, $this->maxHistoryTurns * 2];
            return array_reverse($this->db->fetchAll($sql, $params) ?: []);
        } catch (\Throwable $e) {
            error_log("ConversationAI: getHistory error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Build context for AI generation
     */
    private function buildContext(array $history, string $currentMessage, array $context, int $userId): array
    {
        $systemPrompt = $this->buildSystemPrompt();

        // Build conversation context
        $conversationContext = '';
        foreach ($history as $turn) {
            $role = $turn['role'] === 'user' ? 'User' : 'Assistant';
            $conversationContext .= "$role: {$turn['content']}\n";
        }

        // Add user context
        $userContext = $this->getUserContext($userId);

        $fullPrompt = $systemPrompt . "\n\n" .
                      "USER CONTEXT:\n$userContext\n\n" .
                      "CONVERSATION HISTORY:\n$conversationContext\n" .
                      "Current User Message: $currentMessage\n\n" .
                      "Respond naturally, maintain context, and suggest relevant actions.";

        return [
            'prompt' => $fullPrompt,
            'system' => $systemPrompt,
            'history' => $history,
            'user_context' => $userContext,
            'current_message' => $currentMessage,
            'metadata' => $context
        ];
    }

    /**
     * Build system prompt for real estate conversational AI
     */
    private function buildSystemPrompt(): string
    {
        return <<<PROMPT
You are APS Dream Home's AI Assistant, a knowledgeable real estate expert for APS Dream Home (premium real estate in Gorakhpur/Lucknow).

YOUR ROLE:
- Help customers with property inquiries, bookings, site visits, EMI calculations
- Guide associates with lead management, commissions, team building
- Assist admins with reports, analytics, operations
- Be conversational, helpful, and proactive

KNOWLEDGE BASE:
- 7 active colonies: Suryoday Heights, Suryoday Colony, Braj Radha Nagri, Lakeview Residency, Budh Bihar Colony, Raghunath Nagri, APS Motiram Township
- Plot sizes: 1000-5000 sq ft, Prices: ₹999-6000/sq ft
- EMI: 0% for 12-24 months, 8-10% for 36-60 months
- PLC: Corner +10%, Park facing +5%, Wide road +10%
- Stamp duty UP: Male 7%, Female 6%, Joint 6.5%
- RERA registered, bank loans available

CONVERSATION STYLE:
- Hinglish (Hindi-English mix) preferred
- Professional but warm
- Proactive with suggestions
- Ask clarifying questions when needed
- Always offer next steps/actions

RESPONSE FORMAT:
- Natural conversational response
- Include relevant data/facts
- End with 2-3 actionable suggestions (buttons/links)
- Use emojis sparingly for warmth

ACTION EXTRACTION:
If user intends action (book visit, calculate EMI, create lead, etc.), include:
ACTION: [action_name] {parameters}
PROMPT;
    }

    /**
     * Generate response using AI Gateway
     */
    private function generateResponse(array $context): array
{
        // Use AIGateway for intelligent routing
        $gateway = \App\Services\AI\AIGateway::getInstance();
        
        $result = $gateway->process('chat', [
            'message' => $context['prompt'],
            'system_prompt' => $context['system'],
            'history' => $context['history'],
            'user_context' => $context['user_context']
        ], [
            'user_id' => $context['metadata']['user_id'] ?? 0,
            'role' => $context['metadata']['role'] ?? 'customer'
        ]);
        
        // Extract intent properly (IntentDetector returns array with 'intent' key)
        $intentData = $result['result']['intent'] ?? ['intent' => 'general', 'confidence' => 0.5];
        $intent = is_array($intentData) ? ($intentData['intent'] ?? 'general') : ($intentData ?? 'general');
        $confidence = is_array($intentData) ? ($intentData['confidence'] ?? 0.8) : ($result['confidence'] ?? 0.8);
        
        return [
            'text' => $result['result']['response'] ?? $result['result']['text'] ?? 'Main aapki kaise madad kar sakta hoon?',
            'intent' => $intent,
            'confidence' => $confidence,
            'engine' => $result['engine'] ?? 'groq'
        ];
    }

    /**
     * Extract actions from AI response
     */
    private function extractActions(string $response): array
    {
        $actions = [];
        
        // Pattern: ACTION: [action_name] {json_params}
        preg_match_all('/ACTION:\s*\[(\w+)\]\s*(\{.*?\})/s', $response, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $params = json_decode($match[2], true);
            if ($params) {
                $actions[] = ['action' => $match[1], 'params' => $params];
            }
        }

        // Also check for implicit intents
        $text = strtolower($response);
        if (stripos($response, 'book visit') !== false || stripos($response, 'site visit') !== false) {
            $actions[] = ['action' => 'schedule_visit', 'params' => ['source' => 'conversation']];
        }
        if (stripos($response, 'emi') !== false || stripos($response, 'emi calculator') !== false) {
            $actions[] = ['action' => 'emi_calculator', 'params' => ['source' => 'conversation']];
        }
        if (stripos($response, 'brochure') !== false || stripos($response, 'details') !== false) {
            $actions[] = ['action' => 'send_brochure', 'params' => ['source' => 'conversation']];
        }

        return $actions;
    }

    /**
     * Generate follow-up suggestions based on actions
     */
    private function generateSuggestions(array $actions): array
    {
        $suggestions = [];
        
        foreach ($actions as $action) {
            switch ($action['action']) {
                case 'schedule_visit':
                    $suggestions[] = ['label' => '📅 Book Site Visit', 'action' => 'schedule_visit'];
                    $suggestions[] = ['label' => '📍 View Colony Map', 'action' => 'view_map'];
                    break;
                case 'emi_calculator':
                    $suggestions[] = ['label' => '💰 Calculate EMI', 'action' => 'emi_calculator'];
                    $suggestions[] = ['label' => '📋 View Payment Plans', 'action' => 'payment_plans'];
                    break;
                case 'send_brochure':
                    $suggestions[] = ['label' => '📄 Get Brochure', 'action' => 'send_brochure'];
                    break;
                case 'create_lead':
                    $suggestions[] = ['label' => '👤 Create Lead', 'action' => 'create_lead'];
                    break;
            }
        }

        // Default suggestions
        if (empty($suggestions)) {
            $suggestions = [
                ['label' => '🏘️ View Properties', 'action' => 'view_properties'],
                ['label' => '💰 Calculate EMI', 'action' => 'emi_calculator'],
                ['label' => '📅 Book Visit', 'action' => 'schedule_visit'],
            ];
        }

        return array_slice($suggestions, 0, 3);
    }

    /**
     * Save conversation turn to database
     */
    private function saveConversationTurn(string $sessionId, int $userId, string $userMessage, string $assistantResponse, array $actions): void
    {
        try {
            $tid = $this->tenantId();
            
            // Save user message
            $this->db->execute(
                "INSERT INTO conversation_history (session_id, user_id, role, content, intent, actions, tenant_id, created_at)
                 VALUES (?, ?, 'user', ?, 'user_message', ?, ?, NOW())",
                [$sessionId, $userId, $userMessage, json_encode([]), $tid > 1 ? $tid : 1]
            );

            // Save assistant response
            $this->db->execute(
                "INSERT INTO conversation_history (session_id, user_id, role, content, intent, actions, tenant_id, created_at)
                 VALUES (?, ?, 'assistant', ?, ?, ?, ?, NOW())",
                [$sessionId, $userId, $assistantResponse, 'assistant_response', json_encode($actions), $tid > 1 ? $tid : 1]
            );
        } catch (\Throwable $e) {
            error_log("ConversationalAI: saveTurn error: " . $e->getMessage());
        }
    }

    /**
     * Update session metadata
     */
    private function updateSessionMetadata(string $sessionId, int $userId): void
    {
        try {
            $tid = $this->tenantId();
            $this->db->execute(
                "INSERT INTO conversation_sessions (session_id, user_id, tenant_id, last_activity, message_count, created_at)
                 VALUES (?, ?, ?, NOW(), 1, NOW())
                 ON DUPLICATE KEY UPDATE last_activity = NOW(), message_count = message_count + 1",
                [$sessionId, $userId, $tid > 1 ? $tid : 1]
            );
        } catch (\Throwable $e) {
            error_log("ConversationalAI: updateSession error: " . $e->getMessage());
        }
    }

    /**
     * Get user context for personalization
     */
    private function getUserContext(int $userId): string
    {
        try {
            $tid = $this->tenantId();
            $user = $this->db->fetch(
                "SELECT u.*, w.points_balance, u.referral_code 
                 FROM users u 
                 LEFT JOIN wallet_points w ON u.id = w.user_id 
                 WHERE u.id = ?" . ($this->tenantId() > 1 ? " AND u.tenant_id = ?" : ""),
                array_merge([$userId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
            
            if (!$user) return "New user";
            
            return "User: {$user['name']} ({$user['role']}), Email: {$user['email']}, " .
                   "Wallet: ₹" . number_format($user['points_balance'] ?? 0) . 
                   ", Referral: {$user['referral_code']}";
        } catch (\Throwable $e) {
            return "User context unavailable";
        }
    }

    /**
     * Get or create conversation session
     */
    public function getOrCreateSession(int $userId, ?string $sessionId = null): string
    {
        if ($sessionId) {
            $tid = $this->tenantId();
            $existing = $this->db->fetch(
                "SELECT session_id FROM conversation_sessions WHERE session_id = ? AND user_id = ?" . ($this->tenantId() > 1 ? " AND tenant_id = ?" : ""),
                array_merge([$sessionId, $userId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );
            if ($existing) return $sessionId;
        }

        $sessionId = 'sess_' . uniqid('conv_', true);
        $tid = $this->tenantId();
        $this->db->execute(
            "INSERT INTO conversation_sessions (session_id, user_id, tenant_id, created_at, last_activity, message_count)
             VALUES (?, ?, ?, NOW(), NOW(), 0)",
            [$sessionId, $userId, $tid > 1 ? $tid : 1]
        );
        return $sessionId;
    }

    /**
     * Get conversation history for display
     */
    public function getHistory(string $sessionId, int $limit = 20): array
    {
        $tid = $this->tenantId();
        return $this->db->fetchAll(
            "SELECT role, content, intent, actions, created_at 
             FROM conversation_history 
             WHERE session_id = ? AND tenant_id = ? 
             ORDER BY created_at ASC LIMIT ?",
            [$sessionId, $tid, $limit]
        ) ?: [];
    }

    /**
     * End conversation session
     */
    public function endSession(string $sessionId): void
    {
        try {
            $tid = $this->tenantId();
            $this->db->execute(
                "UPDATE conversation_sessions SET ended_at = NOW() WHERE session_id = ? AND tenant_id = ?",
                [$sessionId, $tid]
            );
        } catch (\Throwable $e) {
            error_log("ConversationalAI: endSession error: " . $e->getMessage());
        }
    }

    /**
     * Get session info
     */
    public function getSessionInfo(string $sessionId): ?array
    {
        $tid = $this->tenantId();
        return $this->db->fetch(
            "SELECT * FROM conversation_sessions WHERE session_id = ? AND tenant_id = ?",
            [$sessionId, $tid]
        );
    }
}