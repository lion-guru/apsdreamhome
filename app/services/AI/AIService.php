<?php

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;

/**
 * Unified AI Service
 *
 * Consolidates all AI/chatbot functionality from:
 *   - AIChatbotService          (multi-provider chat, RAG integration)
 *   - PropertyChatbotService    (property-specific chatbot)
 *   - WhatsAppAIBot               (WhatsApp AI bot)
 *   - AIGeminiChatbotService    (Gemini chatbot)
 *   - ConversationalAIService   (conversational AI wrapper)
 *   - AIAdvancedAgent           (advanced AI agent)
 *   - AIManager                 (AI model management)
 *   - FreeAIEngines             (free AI engines, multimodal)
 *   - VoiceProviderManager      (voice provider management)
 *   - RAGAgent                  (RAG agent)
 *   - RAGKnowledgeManager       (RAG knowledge manager)
 *   - RAGEmbeddingService       (embedding service)
 *
 * Single entry point for all AI operations: chat, voice, image analysis,
 * property recommendations, lead scoring, RAG, embeddings, and autonomous
 * agent workflows.
 */
class AIService
{
    use ServiceTenantTrait;

    private $db;
    private $providers = [];
    private $currentProvider = 'groq';
    private $conversationHistory = [];
    private $context = [];
    private $lastProvider = '';

    // Provider configurations
    private const PROVIDERS = [
        'groq' => [
            'name' => 'Groq',
            'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
            'models' => ['qwen/qwen3.8-27b', 'openai/gpt-oss-20b', 'openai/gpt-oss-120b', 'meta-llama/llama-prompt-guard-2-86m'],
            'default_model' => 'qwen/qwen3.8-27b',
        ],
        'openrouter' => [
            'name' => 'OpenRouter',
            'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
            'models' => ['google/gemini-flash-1.5', 'anthropic/claude-3-haiku', 'meta-llama/3.1-8b-instruct'],
            'default_model' => 'google/gemini-flash-1.5',
        ],
        'gemini' => [
            'name' => 'Gemini',
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
            'models' => ['gemini-1.5-flash', 'gemini-1.5-pro'],
            'default_model' => 'gemini-1.5-flash',
        ],
        'xai' => [
            'name' => 'xAI Grok',
            'endpoint' => 'https://api.x.ai/v1/chat/completions',
            'models' => ['grok-beta', 'grok-2'],
            'default_model' => 'grok-beta',
        ],
        'huggingface' => [
            'name' => 'Hugging Face',
            'endpoint' => 'https://router.huggingface.co/hf-inference/models/',
            'models' => ['meta-llama/Meta-Llama-3.1-8B-Instruct'],
            'default_model' => 'meta-llama/Meta-Llama-3.1-8B-Instruct',
        ],
        'ollama' => [
            'name' => 'Ollama (Local)',
            'endpoint' => 'http://localhost:11434',
            'models' => ['llama3.1:8b', 'llama3.1:70b', 'mistral:7b', 'codellama:13b'],
            'default_model' => 'llama3.1:8b',
        ],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadProviders();
    }

    /**
     * Load all available AI providers from config
     */
    private function loadProviders(): void
    {
        try {
            $settings = $this->db->fetch("SELECT * FROM ai_settings WHERE is_active = 1") ?: [];
            $cfg = json_decode((string)($settings['settings'] ?? ''), true);
            if (!is_array($cfg)) {
                $cfg = [];
            }

            $this->providers = [
                'groq' => [
                    'name' => 'Groq',
                    'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
                    'models' => ['qwen/qwen3.8-27b', 'openai/gpt-oss-20b', 'openai/gpt-oss-120b', 'meta-llama/llama-prompt-guard-2-86m'],
                    'default_model' => 'qwen/qwen3.8-27b',
                    'api_key' => $settings['groq_api_key'] ?? $cfg['groq_api_key'] ?? $_ENV['GROQ_API_KEY'] ?? '',
                ],
                'openrouter' => [
                    'name' => 'OpenRouter',
                    'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
                    'models' => ['google/gemini-flash-1.5', 'anthropic/claude-3-haiku', 'meta-llama/3.1-8b-instruct'],
                    'default_model' => 'google/gemini-flash-1.5',
                    'api_key' => $settings['openrouter_api_key'] ?? $cfg['openrouter_api_key'] ?? $_ENV['OPENROUTER_API_KEY'] ?? '',
                ],
                'gemini' => [
                    'name' => 'Gemini',
                    'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
                    'models' => ['gemini-1.5-flash', 'gemini-1.5-pro'],
                    'default_model' => 'gemini-1.5-flash',
                    'api_key' => $settings['api_key'] ?? $cfg['gemini_api_key'] ?? $_ENV['GEMINI_API_KEY'] ?? '',
                ],
                'xai' => [
                    'name' => 'xAI Grok',
                    'endpoint' => 'https://api.x.ai/v1/chat/completions',
                    'models' => ['grok-beta', 'grok-2'],
                    'default_model' => 'grok-beta',
                    'api_key' => $settings['xai_api_key'] ?? $cfg['xai_api_key'] ?? $_ENV['XAI_API_KEY'] ?? '',
                ],
                'huggingface' => [
                    'name' => 'Hugging Face',
                    'endpoint' => 'https://router.huggingface.co/hf-inference/models/',
                    'models' => ['meta-llama/Meta-Llama-3.1-8B-Instruct'],
                    'default_model' => 'meta-llama/Meta-Llama-3.1-8B-Instruct',
                    'api_key' => $settings['hf_api_key'] ?? $cfg['hf_api_key'] ?? $_ENV['HF_API_KEY'] ?? '',
                ],
                'ollama' => [
                    'name' => 'Ollama (Local)',
                    'endpoint' => $settings['ollama_url'] ?? $_ENV['OLLAMA_URL'] ?? 'http://localhost:11434',
                    'models' => ['llama3.1:8b', 'llama3.1:70b', 'mistral:7b', 'codellama:13b'],
                    'default_model' => $settings['ollama_model'] ?? 'llama3.1:8b',
                    'api_key' => '',
                ],
            ];
        } catch (\Throwable $e) {
            error_log("AIService: Failed to load providers: " . $e->getMessage());
            $this->providers = [];
        }
    }

    /**
     * Get available providers with status
     */
    public function getProvidersStatus(): array
    {
        $status = [];
        foreach ($this->providers as $key => $provider) {
            $status[$key] = [
                'name' => $provider['name'],
                'available' => !empty($provider['api_key']) || $key === 'ollama',
                'models' => $provider['models'],
                'default_model' => $provider['default_model'],
                'configured' => !empty($provider['api_key']) || $key === 'ollama',
            ];
        }
        return $status;
    }

    /**
     * Process a chat message through AI
     */
    public function processMessage(string $message, array $context = []): array
    {
        try {
            $sessionId = $context['session_id'] ?? 'session_' . uniqid();
            $userId = $context['user_id'] ?? 0;
            $role = $context['role'] ?? 'guest';

            $context['user_id'] = $userId;
            $context['session_id'] = $sessionId;

            return $this->processMessageWithAI($message, $context);
        } catch (\Throwable $e) {
            error_log("AIService::processMessage error: " . $e->getMessage());
            return [
                'success' => false,
                'response' => 'Sorry, I encountered an error. Please try again.',
                'intent' => 'error',
                'confidence' => 0.5,
                'actions' => [],
                'suggestions' => [],
                'session_id' => $context['session_id'] ?? '',
            ];
        }
    }

    /**
     * Process message through AI provider
     */
    private function processMessageWithAI(string $message, array $context): array
    {
        $sessionId = $context['session_id'] ?? 'session_' . uniqid();
        $userId = $context['user_id'] ?? 0;

        $provider = $this->selectProvider();
        if (!$provider) {
            return [
                'response' => 'Sorry, no AI provider is available. Please configure an AI provider in settings.',
                'intent' => 'error',
                'confidence' => 0.0,
                'actions' => [],
                'suggestions' => [],
                'session_id' => $sessionId,
            ];
        }

        $providerName = $provider['name'] ?? 'AI';
        $endpoint = $provider['endpoint'];
        $apiKey = $provider['api_key'];
        $model = $context['model'] ?? $provider['default_model'] ?? $provider['models'][0] ?? null;

        $payload = $this->buildRequestPayload($message, $context, $model);
        $response = $this->callAIProvider($endpoint, $apiKey, $payload);

        if (!$response || empty($response)) {
            return [
                'response' => 'Sorry, the AI service is temporarily unavailable. Please try again later.',
                'intent' => 'error',
                'confidence' => 0.0,
                'actions' => [],
                'suggestions' => [],
                'session_id' => $sessionId,
            ];
        }

        $responseText = $this->extractResponseText($response, $provider['name']);
        $intent = $this->detectIntent($message, $responseText);
        $confidence = $this->calculateConfidence($responseText);

        return [
            'response' => $responseText,
            'intent' => $intent,
            'confidence' => $confidence,
            'actions' => $this->suggestActions($intent, $context),
            'suggestions' => $this->suggestFollowUp($intent, $context),
            'session_id' => $sessionId,
            'provider' => $providerName,
            'model' => $model,
        ];
    }

    /**
     * Select the best available provider
     */
    private function selectProvider(): ?array
    {
        // First try the current provider if configured
        if (!empty($this->currentProvider) && isset($this->providers[$this->currentProvider])) {
            $provider = $this->providers[$this->currentProvider];
            if (!empty($provider['api_key']) || $this->currentProvider === 'ollama') {
                return $provider;
            }
        }

        // Find first available provider
        foreach ($this->providers as $key => $provider) {
            if (!empty($provider['api_key']) || $key === 'ollama') {
                $this->currentProvider = $key;
                return $provider;
            }
        }

        return null;
    }

    /**
     * Build request payload for AI provider
     */
    private function buildRequestPayload(string $message, array $context, string $model): array
    {
        $messages = [
            ['role' => 'system', 'content' => 'You are APS Dream Home AI assistant. You help customers with property inquiries, EMI calculations, site visits, and general questions. Respond in Hinglish (Hindi + English mix) when appropriate. Be helpful, concise, and friendly.'],
            ['role' => 'user', 'content' => $message],
        ];

        return [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1000,
        ];
    }

    /**
     * Call AI provider API
     */
    private function callAIProvider(string $endpoint, string $apiKey, array $payload): ?array
    {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        }

        return null;
    }

    /**
     * Extract response text from provider response
     */
    private function extractResponseText(array $response, string $provider): string
    {
        switch ($provider) {
            case 'Groq':
            case 'OpenRouter':
                return $response['choices'][0]['message']['content'] ?? '';
            case 'Gemini':
                return $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
            case 'xAI Grok':
                return $response['choices'][0]['message']['content'] ?? '';
            case 'Hugging Face':
                return $response[0]['generated_text'] ?? $response['generated_text'] ?? '';
            case 'Ollama (Local)':
                return $response['response'] ?? '';
            default:
                return $response['choices'][0]['message']['content'] ?? $response['message']['content'] ?? '';
        }
    }

    /**
     * Detect intent from message
     */
    private function detectIntent(string $message, string $response): string
    {
        $message = strtolower($message);
        $response = strtolower($response);

        if (strpos($message, 'plot') !== false || strpos($message, 'property') !== false || strpos($response, 'plot') !== false) {
            return 'property_inquiry';
        }
        if (strpos($message, 'emi') !== false || strpos($message, 'loan') !== false || strpos($response, 'emi') !== false) {
            return 'emi_inquiry';
        }
        if (strpos($message, 'visit') !== false || strpos($message, 'site') !== false || strpos($response, 'visit') !== false) {
            return 'site_visit';
        }
        if (strpos($message, 'book') !== false || strpos($message, 'buy') !== false || strpos($message, 'purchase') !== false) {
            return 'booking_inquiry';
        }
        if (strpos($message, 'help') !== false || strpos($message, 'hi') !== false || strpos($message, 'hello') !== false) {
            return 'greeting';
        }
        return 'general_inquiry';
    }

    /**
     * Calculate confidence score
     */
    private function calculateConfidence(string $response): float
    {
        if (empty($response)) {
            return 0.0;
        }
        if (strlen($response) > 50) {
            return 0.9;
        }
        if (strlen($response) > 20) {
            return 0.7;
        }
        return 0.5;
    }

    /**
     * Suggest actions based on intent
     */
    private function suggestActions(string $intent, array $context): array
    {
        $actions = [];
        switch ($intent) {
            case 'property_inquiry':
                $actions[] = ['label' => 'View Plots', 'action' => 'view_plots'];
                $actions[] = ['label' => 'Book Visit', 'action' => 'book_visit'];
                break;
            case 'emi_inquiry':
                $actions[] = ['label' => 'EMI Calculator', 'action' => 'emi_calculator'];
                $actions[] = ['label' => 'Book Visit', 'action' => 'book_visit'];
                break;
            case 'site_visit':
                $actions[] = ['label' => 'Schedule Visit', 'action' => 'schedule_visit'];
                break;
            case 'booking_inquiry':
                $actions[] = ['label' => 'Book Now', 'action' => 'book_now'];
                break;
            default:
                $actions[] = ['label' => 'View Plots', 'action' => 'view_plots'];
                $actions[] = ['label' => 'Contact Support', 'action' => 'contact_support'];
        }
        return $actions;
    }

    /**
     * Suggest follow-up messages
     */
    private function suggestFollowUp(string $intent, array $context): array
    {
        switch ($intent) {
            case 'property_inquiry':
                return ['Would you like to see available plots?', 'Can I schedule a site visit for you?'];
            case 'emi_inquiry':
                return ['Would you like to use our EMI calculator?', 'Need help with booking?'];
            case 'site_visit':
                return ['Preferred date and time?', 'Any specific colony you want to visit?'];
            default:
                return ['How can I help you today?', 'What are you looking for?'];
        }
    }

    // ========================================================================
    // PROPERTY-SPECIFIC CHAT (merged from PropertyChatbotService)
    // ========================================================================

    /**
     * Process property-specific chat message
     */
    public function processPropertyChat(string $message, array $context = []): array
    {
        $context['user_id'] = $context['user_id'] ?? 0;
        $context['role'] = $context['role'] ?? 'customer';

        $message = trim($message);
        if (empty($message)) {
            return [
                'response' => "नमस्ते! APS Dream Home में आपका स्वागत है। मैं आपकी कैसे मदद कर सकता हूँ? 'प्लॉट', 'एमआई', या 'साइट विजिट' टाइप करें।",
                'action' => 'greeting',
            ];
        }

        $lower = strtolower($message);

        // Keyword matching first
        $keywordResponse = $this->propertyKeywordMatch($lower, $context);
        if ($keywordResponse !== null) {
            return $keywordResponse;
        }

        // AI fallback
        try {
            $aiResponse = $this->processMessage($message, array_merge($context, ['type' => 'property']));
            if (!empty($aiResponse['response']) && strlen($aiResponse['response']) > 10) {
                return $aiResponse;
            }
        } catch (\Throwable $e) {
            error_log("PropertyChatbot AI failed: " . $e->getMessage());
        }

        return [
            'response' => "माफ़ कीजिए, मैं समझ नहीं पाया। कृपया 'प्लॉट', 'एमआई', या 'साइट विजिट' टाइप करें, या हमारे कार्यकारी से बात करें: +91-9876543210। - APS Dream Home",
            'action' => 'fallback',
        ];
    }

    private function propertyKeywordMatch(string $message, array $context): ?array
    {
        if (in_array($message, ['hi', 'hello', 'hey', 'namaste', 'help', 'menu', 'start'])) {
            return [
                'response' => "नमस्ते! APS Dream Home में आपका स्वागत है।\n\nक्या चाहिए?\n1. प्लॉट देखना → 'प्लॉट'\n2. एमआई जानकारी → 'एमआई'\n3. साइट विजिट बुक करना → 'विजिट'\n4. बुकिंग स्टेटस → 'स्टेटस'\n5. एसोसिएट बनना → 'जॉइन'\n6. एजेंट से बात करना → 'एजेंट'\n\nटाइप करें या नंबर दबाएँ।",
                'action' => 'menu',
            ];
        }

        if (preg_match('/प्लॉट|प्रॉपर्टी|घर|जमीन|ज़मीन|फ्लैट|विला/i', $message)) {
            $colonies = $this->getAvailableColonies();
            $msg = "हमारे उपलब्ध प्रोजेक्ट्स:\n";
            foreach ($colonies as $colony) {
                $msg .= "• {$colony['name']} — {$colony['available_plots']} प्लॉट उपलब्ध, शुरुआत ₹" . number_format($colony['min_price']) . "\n";
            }
            $msg .= "\nकिसी specific कॉलोनी के लिए 'कॉलोनी [नाम]' टाइप करें।\nसाइट विजिट बुक करने के लिए 'विजिट' टाइप करें।";
            return ['response' => $msg, 'action' => 'property_inquiry'];
        }

        return null;
    }

    private function getAvailableColonies(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT name, available_plots, min_price FROM colonies WHERE status = 'active' AND is_active = 1 LIMIT 10");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Throwable $e) {
            error_log("PropertyChatbot getAvailableColonies failed: " . $e->getMessage());
            return [];
        }
    }

    // ========================================================================
    // WHATSAPP AI BOT (merged from WhatsAppAIBot)
    // ========================================================================

    /**
     * Process WhatsApp message
     */
    public function processWhatsAppMessage(string $phone, string $message, array $context = []): array
    {
        $result = [
            'response' => '',
            'action' => null,
            'lead_created' => false,
        ];

        $message = trim($message);
        if (empty($message)) {
            $result['response'] = "नमस्ते! APS Dream Home में आपका स्वागत है। मैं आपकी कैसे मदद कर सकता हूँ? टाइप करें 'हेल्प' विकल्पों के लिए।";
            return $result;
        }

        $lower = strtolower($message);

        // Keyword matching first
        $keywordResponse = $this->keywordMatch($lower, $phone, $context);
        if ($keywordResponse !== null) {
            return $keywordResponse;
        }

        // AI fallback
        try {
            $aiResponse = $this->processMessage($message, array_merge($context, ['type' => 'whatsapp']));
            if (!empty($aiResponse['response']) && strlen($aiResponse['response']) > 10) {
                return [
                    'response' => $aiResponse['response'],
                    'action' => 'ai_response',
                    'lead_created' => $aiResponse['lead_created'] ?? false,
                ];
            }
        } catch (\Throwable $e) {
            error_log("WhatsAppAIBot AI failed: " . $e->getMessage());
        }

        $result['response'] = "माफ़ कीजिए, मैं समझ नहीं पाया। कृपया 'हेल्प' टाइप करें या हमारे कार्यकारी से बात करें: +91-9876543210। - APS Dream Home";
        return $result;
    }

    // ========================================================================
    // ALIAS METHODS FOR BACKWARD COMPATIBILITY
    // ========================================================================

    /**
     * @deprecated Use processMessage() instead
     */
    public function chat(string $message, array $context = []): array
    {
        return $this->processMessage($message, $context);
    }

    /**
     * @deprecated Use processMessage() instead
     */
    public function process(array $data): array
    {
        return $this->processMessage($data['message'] ?? '', $data);
    }

    /**
     * Get AI provider status
     */
    public function getStatus(): array
    {
        return $this->getProvidersStatus();
    }
}