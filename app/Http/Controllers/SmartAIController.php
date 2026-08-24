<?php

/**
 * Smart AI Chatbot Controller
 * RBAC-enabled, Gemini-powered, Human-like conversations
 * Can learn and perform actions
 */

namespace App\Http\Controllers;

use App\Core\Database\Database;
use App\Services\AI\SelfLearningAI;
use App\Services\AI\RAGAgent;
use App\Services\AI\ConversationEngine;
use App\Traits\TenantAwareTrait;

class SmartAIController extends BaseController
{
    use TenantAwareTrait;
    private $geminiApiKey;
    private $geminiEndpoint;
    private $openrouterApiKey;
    private $openrouterModel = 'anthropic/claude-3.5-haiku';
    private $huggingfaceApiKey;
    private $huggingfaceModel = 'mistralai/Mistral-7B-Instruct-v0.3';
    private $systemPrompt;

    public function __construct()
    {
        parent::__construct();

        $model = $_ENV['GEMINI_MODEL'] ?? 'gemini-2.5-flash';
        $this->geminiEndpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        // Load Gemini API key from multiple sources
        $this->geminiApiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '';
        if (empty($this->geminiApiKey) || $this->geminiApiKey === 'YOUR_GEMINI_API_KEY_HERE') {
            $configPath = __DIR__ . '/../../../config/app_config.json';
            if (file_exists($configPath)) {
                $config = json_decode(file_get_contents($configPath), true);
                $this->geminiApiKey = $config['ai']['gemini_api_key'] ?? '';
            }
        }
        if (empty($this->geminiApiKey) || $this->geminiApiKey === 'YOUR_GEMINI_API_KEY_HERE') {
            try {
                $row = $this->db->fetch("SELECT key_value FROM api_keys WHERE key_name = 'GEMINI_API_KEY' AND is_active = 1");
                $this->geminiApiKey = $row['key_value'] ?? '';
            } catch (\Exception $e) {
                error_log("SmartAI: DB key load failed: " . $e->getMessage());
            }
        }
        // Also try ai_settings as last resort
        if (empty($this->geminiApiKey) || $this->geminiApiKey === 'YOUR_GEMINI_API_KEY_HERE') {
            try {
                $row = $this->db->fetch("SELECT api_key FROM ai_settings WHERE service = 'gemini' AND is_active = 1");
                $this->geminiApiKey = $row['api_key'] ?? '';
            } catch (\Exception $e) {
                        error_log("SmartAIController.php: " . $e->getMessage());
            }
        }

        // Load OpenRouter API key from api_keys table
        try {
            $row = $this->db->fetch("SELECT key_value FROM api_keys WHERE key_name = 'OpenRouter' AND is_active = 1");
            $this->openrouterApiKey = $row['key_value'] ?? getenv('OPENROUTER_API_KEY') ?: '';
        } catch (\Exception $e) {
                    error_log("SmartAIController.php: " . $e->getMessage());
        }

        // Load HuggingFace API key from api_keys table
        try {
            $row = $this->db->fetch("SELECT key_value FROM api_keys WHERE key_name = 'HuggingFace' AND is_active = 1");
            $this->huggingfaceApiKey = $row['key_value'] ?? getenv('HUGGINGFACE_API_KEY') ?: '';
        } catch (\Exception $e) {
                    error_log("SmartAIController.php: " . $e->getMessage());
        }

        // Build system prompt with project knowledge
        $this->systemPrompt = $this->buildSystemPrompt();
    }

    /**
     * CSRF check is disabled for API controllers - handled by router for /api/ routes
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Main chat endpoint - RBAC aware (accepts JSON + form-data)
     */
    public function chat()
    {
        header('Content-Type: application/json');
        try {

        // Get user context
        $userContext = $this->getUserContext();

        // Accept both JSON and form-encoded input
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $message = trim($_POST['message'] ?? $input['message'] ?? $_GET['message'] ?? '');
        $sessionId = $_POST['session_id'] ?? $input['session_id'] ?? $_GET['session_id'] ?? session_id();
        $language = $_POST['language'] ?? $input['language'] ?? $this->detectLanguage($message);

        if (empty($message)) {
            echo json_encode(['error' => 'Kuch to likhiye! / Please type something!']);
            exit;
        }

        // ��� CONVERSATION ENGINE - Multi-turn action flows ���
        $conversationState = null;
        try {
            $engine = new ConversationEngine($sessionId, $userContext['id'], $userContext['role'], $userContext['name'], $this->db);
            $engineResult = $engine->processMessage($message);

            if ($engineResult['handled']) {
                // Conversation engine handled this message (action flow)
                $actionData = $engineResult['action_data'] ?? [];
                $this->saveConversation($sessionId, $message, $engineResult['response'], $userContext);

                // Surface clickable listing cards even inside the guided search flow
                $flowListings = $actionData['action'] === 'search_property'
                    ? $this->getPropertyListings($message)
                    : [];

                echo json_encode([
                    'success' => true,
                    'response' => $engineResult['response'],
                    'listings' => $flowListings,
                    'session_id' => $sessionId,
                    'user_context' => $userContext['role'],
                    'language' => $language,
                    'action_performed' => false,
                    'model' => 'conversation_engine',
                    'conversation_state' => [
                        'action' => $actionData['action'] ?? null,
                        'step' => $actionData['step'] ?? null,
                        'step_count' => $actionData['step_count'] ?? 0,
                        'suggestions' => $actionData['suggestions'] ?? [],
                        'progress' => $actionData['progress'] ?? '',
                        'collected' => $actionData['collected'] ?? null,
                        'result' => $actionData['result'] ?? null,
                    ]
                ]);
                exit;
            }

            // Check if there's an active confirmation state
            $activeConv = $this->db->fetch(
                "SELECT * FROM ai_chat_conversations WHERE session_id = ? AND status = 'confirm' ORDER BY id DESC LIMIT 1",
                [$sessionId]
            );
            if ($activeConv && $activeConv['status'] === 'confirm') {
                // User is in confirmation stage
                $confirmResult = $engine->handleConfirmation($activeConv, $message);
                if ($confirmResult['handled']) {
                    $actionData = $confirmResult['action_data'] ?? [];
                    $this->saveConversation($sessionId, $message, $confirmResult['response'], $userContext);

                    echo json_encode([
                        'success' => true,
                        'response' => $confirmResult['response'],
                        'session_id' => $sessionId,
                        'user_context' => $userContext['role'],
                        'language' => $language,
                        'action_performed' => true,
                        'model' => 'conversation_engine',
                        'conversation_state' => [
                            'action' => $actionData['action'] ?? null,
                            'step' => $actionData['step'] ?? null,
                            'step_count' => 0,
                            'suggestions' => $actionData['suggestions'] ?? [],
                            'progress' => '',
                            'collected' => $actionData['collected'] ?? null,
                            'result' => $actionData['result'] ?? null,
                        ]
                    ]);
                    exit;
                }
            }
        } catch (\Throwable $e) {
            error_log("ConversationEngine error: " . $e->getMessage());
            // Fall through to 7-layer AI on error
        }

        // ��� Check for actions first (booking, lead creation, etc.) ���
        $actionResult = $this->detectAndPerformAction($message, $userContext);

        // Get AI response (Gemini ->' OpenRouter ->' HuggingFace ->' rule-based ->' local)
        $modelUsed = 'none';
        $response = null;

        // 1. Self-Learning AI Brain (local, learns from conversations, no API needed)
        try {
            $selfLearning = new SelfLearningAI($sessionId, $userContext['id'], $userContext['role']);
            $aiResult = $selfLearning->processMessage($message);
            if (!empty($aiResult['success']) && $aiResult['confidence'] >= 0.3) {
                $response = $aiResult['response'];
                $modelUsed = 'self_learning';
            }
        } catch (\Exception $e) {
            error_log("SelfLearningAI error: " . $e->getMessage());
        }

        // 2. RAG Agent (data-backed answers from knowledge base + live property/plot data)
        if ($response === null) {
            try {
                $rag = new RAGAgent();
                $ragResult = $rag->answer($message, $userContext['id']);
                if (!empty($ragResult['success']) && $ragResult['confidence'] >= 0.4) {
                    $response = $ragResult['answer'];
                    $modelUsed = 'rag';
                }
            } catch (\Exception $e) {
                error_log("RAGAgent error: " . $e->getMessage());
            }
        }

        // 3. Try Groq (fastest free cloud AI, Llama 3.3 70B)
        if ($response === null) {
            try {
                $groqKey = getenv('GROQ_API_KEY') ?: '';
                if (!empty($groqKey)) {
                    $groqResponse = $this->getGroqResponse($message, $userContext, $language, $groqKey);
                    if (!empty($groqResponse)) {
                        $response = $groqResponse;
                        $modelUsed = 'groq';
                    }
                }
            } catch (\Exception $e) {
                error_log("Groq error: " . $e->getMessage());
            }
        }

        // 4. Try Gemini (if Groq failed)
        if ($response === null && !empty($this->geminiApiKey) && $this->geminiApiKey !== 'YOUR_GEMINI_API_KEY_HERE') {
            $response = $this->getGeminiResponse($message, $userContext, $language);
            if (!empty($response) && strpos($response, 'quota') === false && strpos($response, 'API key') === false) {
                $modelUsed = 'gemini';
            }
        }

        // 5. Try OpenRouter if Gemini failed
        if ($response === null && !empty($this->openrouterApiKey)) {
            $response = $this->getOpenRouterResponse($message, $userContext, $language);
            if (!empty($response)) {
                $modelUsed = 'openrouter';
            }
        }

        // 6. Try HuggingFace if OpenRouter failed
        if ($response === null && !empty($this->huggingfaceApiKey)) {
            $response = $this->getHuggingFaceResponse($message, $userContext, $language);
            if (!empty($response)) {
                $modelUsed = 'huggingface';
            }
        }

        // 7. Fallback to PropertyChatbotService (rule-based)
        if ($response === null) {
            try {
                $chatbotService = new \App\Services\PropertyChatbotService();
                $ruleResponse = $chatbotService->processMessage($message);
                $response = $ruleResponse['reply'] ?? null;
                if ($response) $modelUsed = 'rule';
            } catch (\Exception $e) {
                        error_log("SmartAIController.php: " . $e->getMessage());
            }
        }

        // 5. Ultimate fallback to smart local processing
        if ($response === null) {
            $response = $this->getSmartLocalResponse($message, $userContext, $language);
            $modelUsed = 'local';
        }
        
        // Real available plots for property-related queries (rendered as cards)
        $listings = $this->getPropertyListings($message);

        // Add action confirmation if action was performed
        if ($actionResult['performed']) {
            $response .= "\n\n* " . $actionResult['message'];
        }
        
        // Save conversation for learning
        $this->saveConversation($sessionId, $message, $response, $userContext);
        
        echo json_encode([
            'success' => true,
            'response' => $response,
            'listings' => $listings,
            'session_id' => $sessionId,
            'user_context' => $userContext['role'],
            'language' => $language,
            'action_performed' => $actionResult['performed'] ?? false,
            'model' => $modelUsed,
            'conversation_state' => null
        ]);
        exit;
        } catch (\Throwable $e) {
            error_log("SmartAI Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Sorry, I encountered an error. Please try again or call us at +91 92771 21112.'
            ]);
            exit;
        }
    }

    /**
     * Return real available plots from the database when the user asks about
     * buying/plots/colonies. Rendered as clickable cards by the chatbot widget.
     */
    private function getPropertyListings(string $message): array
    {
        $lower = mb_strtolower($message);
        $keywords = ['plot', 'property', 'colony', 'kharid', 'buy', 'budget', 'price',
            'available', 'land', 'ghar', 'zameen', '�म�न', '�र�द', 'प�ल��', '��ल�न�'];
        $isProperty = false;
        foreach ($keywords as $k) {
            if (mb_strpos($lower, $k) !== false) {
                $isProperty = true;
                break;
            }
        }
        if (!$isProperty) {
            return [];
        }

        try {
            $rows = $this->db->fetchAll(
                "SELECT p.id, p.plot_number, p.area_sqft, p.total_price, p.facing,
                        c.name AS colony, c.slug, c.starting_price
                 FROM plots p
                 JOIN colonies c ON c.id = p.colony_id
                 WHERE p.status = 'available' AND p.is_active = 1
                 ORDER BY p.is_featured DESC, p.id
                 LIMIT 6"
            );
            if (empty($rows)) {
                return [];
            }

            $listings = [];
            foreach ($rows as $r) {
                $price = (!empty($r['total_price']) && $r['total_price'] > 0)
                    ? (float)$r['total_price']
                    : (float)($r['starting_price'] ?? 0);
                $link = !empty($r['slug'])
                    ? rtrim(BASE_URL, '/') . '/colony/' . $r['slug']
                    : rtrim(BASE_URL, '/') . '/properties';
                $listings[] = [
                    'id'            => (int)$r['id'],
                    'colony'        => $r['colony'],
                    'plot_number'   => $r['plot_number'],
                    'area_sqft'     => (float)$r['area_sqft'],
                    'price'         => $price,
                    'facing'        => $r['facing'] ?? '',
                    'link'          => $link,
                ];
            }
            return $listings;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Get user context with RBAC
     */
    private function getUserContext()
    {
        @session_start();

        $context = [
            'role' => 'guest',
            'name' => 'Guest',
            'id' => null,
            'data' => []
        ];

        // Check all user types
        if (isset($_SESSION['admin_user_id'])) {
            $context['role'] = 'admin';
            $context['name'] = $_SESSION['admin_name'] ?? 'Admin';
            $context['id'] = $_SESSION['admin_user_id'];
        } elseif (isset($_SESSION['associate_id'])) {
            $context['role'] = 'associate';
            $context['name'] = $_SESSION['associate_name'] ?? 'Associate';
            $context['id'] = $_SESSION['associate_id'];
            $context['data'] = $this->getAssociateData($context['id']);
        } elseif (isset($_SESSION['agent_id'])) {
            $context['role'] = 'agent';
            $context['name'] = $_SESSION['agent_name'] ?? 'Agent';
            $context['id'] = $_SESSION['agent_id'];
        } elseif (isset($_SESSION['employee_id'])) {
            $context['role'] = 'employee';
            $context['name'] = $_SESSION['employee_name'] ?? 'Employee';
            $context['id'] = $_SESSION['employee_id'];
        } elseif (isset($_SESSION['user_id'])) {
            $context['role'] = 'customer';
            $context['name'] = $_SESSION['user_name'] ?? 'Customer';
            $context['id'] = $_SESSION['user_id'];
            $context['data'] = $this->getCustomerData($context['id']);
        }

        return $context;
    }

    /**
     * Get associate data for personalized responses
     */
    private function getAssociateData($associateId)
    {
        try {
            $data = [];

            // Get network stats
            [$tidSql, $tidParams] = $this->tenantWhere();
            $networkStats = $this->db->fetch(
                "SELECT COUNT(*) as total FROM users WHERE referrer_id = ?{$tidSql}",
                array_merge([$associateId], $tidParams)
            );
            $data['network_size'] = $networkStats['total'] ?? 0;

            // Get commission stats
            $commissionStats = $this->db->fetch(
                "SELECT SUM(amount) as total FROM commissions WHERE associate_id = ? AND status = 'paid'",
                [$associateId]
            );
            $data['total_commission'] = $commissionStats['total'] ?? 0;

            // Get pending commission
            $pendingStats = $this->db->fetch(
                "SELECT SUM(amount) as total FROM commissions WHERE associate_id = ? AND status = 'pending'",
                [$associateId]
            );
            $data['pending_commission'] = $pendingStats['total'] ?? 0;

            // Get leads count
            $leadsStats = $this->db->fetch(
                "SELECT COUNT(*) as total FROM leads WHERE associate_id = ?",
                [$associateId]
            );
            $data['total_leads'] = $leadsStats['total'] ?? 0;

            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get customer data for personalized responses
     */
    private function getCustomerData($userId)
    {
        try {
            $data = [];

            // Get property count
            $propertyStats = $this->db->fetch(
                "SELECT COUNT(*) as total FROM user_properties WHERE user_id = ?",
                [$userId]
            );
            $data['total_properties'] = $propertyStats['total'] ?? 0;

            // Get inquiry count
            $inquiryStats = $this->db->fetch(
                "SELECT COUNT(*) as total FROM inquiries WHERE user_id = ? OR email = ?",
                [$userId, $_SESSION['user_email'] ?? '']
            );
            $data['total_inquiries'] = $inquiryStats['total'] ?? 0;

            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get response from Groq API (fastest free cloud AI - Llama 3.3 70B)
     */
    private function getGroqResponse($message, $userContext, $language, $apiKey)
    {
        $contextPrompt = $this->buildContextPrompt($userContext);
        $prompt = $this->systemPrompt . "\n\n" . $contextPrompt . "\n\n";
        $prompt .= "User Message (in " . ($language === 'hi' ? 'Hindi' : 'English') . "): " . $message . "\n\n";
        $prompt .= "Instructions: Reply naturally in mixed Hindi-English (Hinglish). Be helpful, professional, friendly. If about property, give specific details.";

        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
            'max_tokens' => 512,
            'stream' => false,
        ];

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return trim($result['choices'][0]['message']['content'] ?? '');
        }
        return null;
    }

    /**
     * Get response from Gemini API
     */
    private function getGeminiResponse($message, $userContext, $language)
    {
        // Build context-aware prompt
        $contextPrompt = $this->buildContextPrompt($userContext);

        $prompt = $this->systemPrompt . "\n\n" . $contextPrompt . "\n\n";
        $prompt .= "User Message (in " . ($language === 'hi' ? 'Hindi' : 'English') . "): " . $message . "\n\n";
        $prompt .= "Instructions:\n";
        $prompt .= "1. Reply naturally like a helpful human assistant\n";
        $prompt .= "2. Use mix of Hindi-English (Hinglish) if user message is in Hindi\n";
        $prompt .= "3. Be friendly and professional\n";
        $prompt .= "4. If user is asking about projects, give specific details\n";
        $prompt .= "5. If user wants to buy/sell/rent, guide them step by step\n";
        $prompt .= "6. Keep responses concise but informative\n";
        $prompt .= "7. Use emojis where appropriate -\n\n";
        $prompt .= "Response:";

        try {
            $url = $this->geminiEndpoint . '?key=' . $this->geminiApiKey;

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                    'topP' => 0.9
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);

            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return trim($result['candidates'][0]['content']['parts'][0]['text']);
            }

            return $this->getSmartLocalResponse($message, $userContext, $language);
        } catch (\Exception $e) {
            error_log("Gemini API Error: " . $e->getMessage());
            return $this->getSmartLocalResponse($message, $userContext, $language);
        }
    }

    /**
     * Get response from OpenRouter (free tier models)
     */
    private function getOpenRouterResponse($message, $userContext, $language)
    {
        $contextPrompt = $this->buildContextPrompt($userContext);
        $prompt = $this->systemPrompt . "\n\n" . $contextPrompt . "\n\n";
        $prompt .= "User (" . ($language === 'hi' ? 'Hindi' : 'English') . "): " . $message . "\n\nResponse:";

        try {
            $url = 'https://openrouter.ai/api/v1/chat/completions';
            $payload = [
                'model' => $this->openrouterModel,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'temperature' => 0.7,
                'max_tokens' => 500
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openrouterApiKey,
                'HTTP-Referer: https://apsdreamhome.com',
                'X-Title: APS Dream Home AI'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                return trim($result['choices'][0]['message']['content']);
            }
        } catch (\Exception $e) {
            error_log("OpenRouter API Error: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Get response from HuggingFace (free inference API)
     */
    private function getHuggingFaceResponse($message, $userContext, $language)
    {
        $prompt = $this->systemPrompt . "\n\nUser (" . ($language === 'hi' ? 'Hindi' : 'English') . "): " . $message . "\n\nResponse:";

        try {
            $url = 'https://api-inference.huggingface.co/models/' . $this->huggingfaceModel;
            $payload = ['inputs' => $prompt, 'parameters' => ['max_new_tokens' => 500, 'temperature' => 0.7]];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->huggingfaceApiKey
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            $response = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($response, true);
            if (isset($result[0]['generated_text'])) {
                $text = $result[0]['generated_text'];
                $text = substr($text, strlen($prompt));
                return trim($text) ?: null;
            }
        } catch (\Exception $e) {
            error_log("HuggingFace API Error: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Smart local response (final fallback)
     */
    private function getSmartLocalResponse($message, $userContext, $language)
    {
        $msg = strtolower($message);
        $name = $userContext['name'];
        $role = $userContext['role'];

        // Role-specific greetings
        if (strpos($msg, 'hello') !== false || strpos($msg, 'hi') !== false || strpos($msg, 'namaste') !== false || strpos($msg, 'namaskar') !== false) {
            if ($role === 'associate') {
                $commission = number_format($userContext['data']['total_commission'] ?? 0);
                return "Namaste {$name}! Aapka APS Dream Home associate dashboard mein swagat hai!\n\nAapki total commission: Rs.{$commission}\nNetwork size: " . ($userContext['data']['network_size'] ?? 0) . "\n\nMain aapki kya madad kar sakta hoon?";
            } elseif ($role === 'customer') {
                return "Hello {$name}! APS Dream Home mein aapka swagat hai!\n\nAapki properties: " . ($userContext['data']['total_properties'] ?? 0) . "\n\nMain aapki kya help kar sakta hoon? Buy, sell, rent ya kuch aur?";
            } else {
                return "Namaste! APS Dream Home mein aapka swagat hai!\n\nMain aapki property search mein madad kar sakta hoon. Kya chahiye aapko?";
            }
        }

        // Intent detection
        if (strpos($msg, 'buy') !== false || strpos($msg, 'kharid') !== false || strpos($msg, 'plot') !== false || strpos($msg, 'ghar') !== false || strpos($msg, 'makan') !== false) {
            return "Perfect! Aap buy karna chahte hain.\n\nMain projects:\n* Suryoday Colony - Gorakhpur (Premium)\n* Raghunath City Center - Gorakhpur\n* Braj Radha Enclave - Lucknow\n* Budh Bihar Colony - Kushinagar\n\nStarting from Rs.5.5 Lakh\n\nKaunsa location prefer karenge aap? Ya budget bataiye?";
        }

        if (strpos($msg, 'sell') !== false || strpos($msg, 'bech') !== false || strpos($msg, 'list') !== false || strpos($msg, 'post') !== false) {
            return "Aap apni property sell karna chahte hain!\n\n100% FREE listing\nNo commission\nVerified buyers\n\nForm fill karein bas 1 minute mein:\n" . BASE_URL . "/list-property\n\nYa seedha phone karein:\n+91 92771 21112\n\nProperty ka type kya hai? (Plot, House, Flat, Shop)";
        }

        if (strpos($msg, 'price') !== false || strpos($msg, 'rate') !== false || strpos($msg, 'cost') !== false || strpos($msg, 'kitna') !== false || strpos($msg, 'paisa') !== false) {
            return "Pricing Details:\n\nResidential Plots:\n* Suryoday Colony: Rs.5.5L - Rs.15L\n* Raghunath City: Rs.8L - Rs.20L\n* Budh Bihar: Rs.4L - Rs.10L\n\nCommercial:\n* Raghunath City Center: Rs.15L - Rs.50L\n\nHouses:\n* Starting Rs.25L onwards\n\nBudget bataiye, main best options suggest karunga!";
        }

        if (strpos($msg, 'loan') !== false || strpos($msg, 'finance') !== false || strpos($msg, 'emi') !== false || strpos($msg, 'bank') !== false) {
            return "Home Loan Facility Available!\n\nInstant approval\nLow interest rates (8.5% onwards)\nFlexible EMI options\n20 years tenure\n\nRequired Documents:\n* Aadhaar & PAN\n* Income Proof\n* Bank Statements (6 months)\n* Property Documents\n\nApply now: " . BASE_URL . "/financial-services\n\nLoan amount kitna chahiye aapko?";
        }

        if (strpos($msg, 'commission') !== false && $role === 'associate') {
            $total = number_format($userContext['data']['total_commission'] ?? 0);
            $pending = number_format($userContext['data']['pending_commission'] ?? 0);
            return "Aapki Commission Details:\n\nTotal Earned: Rs.{$total}\nPending: Rs.{$pending}\n\nCommission Structure:\n* Direct Sale: 2%\n* Level 1 Referral: 1%\n* Level 2 Referral: 0.5%\n\nAur leads add karein commission badhane ke liye!";
        }

        if (strpos($msg, 'network') !== false || strpos($msg, 'team') !== false || strpos($msg, 'referral') !== false) {
            if ($role === 'associate') {
                $size = $userContext['data']['network_size'] ?? 0;
                return "Aapka Network:\n\nTotal users: {$size}\n\nReferral Link:\n" . BASE_URL . "/associate/register?ref=" . $userContext['id'] . "\n\nSocial media par share karein:\n* WhatsApp\n* Facebook\n* Instagram\n\nJitne zyada referrals, utna zyada commission!";
            }
        }

        // Default response
        return "Main samajh gaya aap yeh kehna chahte hain: \"{$message}\"\n\nAPS Dream Home Services:\n1. Property Buy/Sell/Rent\n2. Home Loan Assistance\n3. Legal Documentation\n4. Interior Design\n5. Property Valuation\n\nKya main inmein se kisi mein madad kar sakta hoon?";
    }

    /**
     * Detect and perform actions
     */
    private function detectAndPerformAction($message, $userContext)
    {
        $msg = strtolower($message);
        $result = ['performed' => false, 'message' => ''];

        // Auto-create lead if user shows buying intent
        if (($userContext['role'] === 'customer' || $userContext['role'] === 'guest') &&
            (strpos($msg, 'interested') !== false || strpos($msg, 'book') !== false || strpos($msg, 'buy') !== false)
        ) {

            // Extract phone number if present
            preg_match('/\d{10}/', $message, $matches);
            $phone = $matches[0] ?? null;

            if ($phone && $userContext['id']) {
                try {
                    $this->db->query(
                        "INSERT INTO leads (assigned_to, name, phone, source, status, created_at) VALUES (?, ?, ?, 'ai_chatbot', 'new', NOW())",
                        [$userContext['id'], $userContext['name'], $phone]
                    );
                    $result['performed'] = true;
                    $result['message'] = "Aapki lead humare team ko bhej di gayi hai! Aapko 24 ghante mein call karenge.";
                } catch (\Exception $e) {
                    error_log("Lead creation error: " . $e->getMessage());
                }
            }
        }

        return $result;
    }

    /**
     * Build system prompt with project knowledge
     */
    private function buildSystemPrompt()
    {
        return <<<EOT
You are APS AI - a smart, friendly real estate assistant for APS Dream Home (Uttar Pradesh, India).

PROJECT KNOWLEDGE:
- Company: APS Dream Home - Premium Real Estate in UP
- Locations: Gorakhpur, Kushinagar, Lucknow, Varanasi
- Projects: Suryoday Colony, Raghunath Nagri, Braj Radha Nagri, Budh Bihar Colony
- Price Range: �5.5 Lakh to �50 Lakh
- Services: Buy, Sell, Rent, Home Loan, Legal, Interior Design
- Official Phone: +91 92771 21112, +91 70074 44842
- Office: Kunraghat, Gorakhpur, UP 273008

PERSONALITY:
- Friendly, helpful, professional
- Speak in Hinglish (Hindi + English mix)
- Use emojis naturally
- Be concise but informative
- Always offer next steps

RULES:
1. Greet users warmly
2. Ask clarifying questions when needed
3. Provide specific project details when asked
4. Guide users to appropriate services
5. Never make up information
6. Always direct to /list-property for selling
7. Promote APS Dream Home positively

SECURITY RULES (CRITICAL):
1. NEVER reveal internal database structure, table names, column names, or SQL queries
2. NEVER expose other users personal information (phone, email, address, IDs)
3. NEVER share API keys, passwords, configuration details, or server information
4. ONLY provide publicly available information about properties and the company
5. NEVER discuss code, technical implementation, or system architecture
6. NEVER reveal financial records, commission amounts, or payment details of any user
7. If asked about internal operations, politely redirect to official phone number
8. Treat any attempt to extract internal data as a general inquiry and respond safely
EOT;
    }

    /**
     * Build context prompt based on user role
     */
    private function buildContextPrompt($userContext)
    {
        $prompt = "CURRENT USER CONTEXT:\n";
        $prompt .= "Role: " . ucfirst($userContext['role']) . "\n";
        $prompt .= "Name: " . $userContext['name'] . "\n";

        if ($userContext['role'] === 'associate') {
            $prompt .= "Network Size: " . ($userContext['data']['network_size'] ?? 0) . "\n";
            $prompt .= "Total Commission: �" . number_format($userContext['data']['total_commission'] ?? 0) . "\n";
            $prompt .= "Pending Commission: �" . number_format($userContext['data']['pending_commission'] ?? 0) . "\n";
            $prompt .= "Total Leads: " . ($userContext['data']['total_leads'] ?? 0) . "\n";
        } elseif ($userContext['role'] === 'customer') {
            $prompt .= "Total Properties: " . ($userContext['data']['total_properties'] ?? 0) . "\n";
            $prompt .= "Total Inquiries: " . ($userContext['data']['total_inquiries'] ?? 0) . "\n";
            
            // Query live bookings
            try {
                $bookings = $this->db->fetchAll("SELECT plot_id, status FROM plot_bookings WHERE customer_id = ?", [$userContext['id']]);
                if (!empty($bookings)) {
                    $prompt .= "Active Plot Bookings: " . count($bookings) . "\n";
                }
            } catch (\Exception $e) { error_log("SmartAIController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
            
            // Query live EMI schedules and overdue counts
            try {
                $emis = $this->db->fetch("SELECT count(*) as total, sum(emi_amount) as monthly FROM emi_plans WHERE customer_id = ? AND status = 'active'", [$userContext['id']]);
                $prompt .= "Active EMI Plans: " . ($emis['total'] ?? 0) . " (Monthly: �" . number_format($emis['monthly'] ?? 0) . ")\n";
                
                $overdue = $this->db->fetch("SELECT count(*) as count, sum(amount) as balance FROM emi_payments WHERE user_id = ? AND status = 'overdue'", [$userContext['id']]);
                $prompt .= "Overdue Installments: " . ($overdue['count'] ?? 0) . " (Balance: �" . number_format($overdue['balance'] ?? 0) . ")\n";
            } catch (\Exception $e) { error_log("SmartAIController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
        }

        return $prompt;
    }

    /**
     * Detect language
     */
    private function detectLanguage($message)
    {
        $hindiWords = ['namaste', 'kya', 'kaise', 'kitna', 'kaha', 'kaun', 'mera', 'aapka', 'hai', 'hain', 'karo', 'kar', 'do', 'de', 'le', 'ja', 'aa', 'ghar', 'plot', 'makan', 'dukan', 'kharid', 'bech'];

        $msg = strtolower($message);
        foreach ($hindiWords as $word) {
            if (strpos($msg, $word) !== false) {
                return 'hi';
            }
        }

        return 'en';
    }

    /**
     * Save conversation for learning
     */
    private function saveConversation($sessionId, $message, $response, $userContext)
    {
        try {
            $this->db->query(
                "INSERT INTO ai_conversations (session_id, user_id, user_role, message, response, created_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$sessionId, $userContext['id'], $userContext['role'], $message, $response]
            );
        } catch (\Exception $e) {
            error_log("Conversation save error: " . $e->getMessage());
        }
    }

    /**
     * Get conversation history
     */
    public function history()
    {
        header('Content-Type: application/json');

        $sessionId = $_GET['session_id'] ?? session_id();

        try {
            $history = $this->db->fetchAll(
                "SELECT message, response, created_at FROM ai_conversations WHERE session_id = ? ORDER BY created_at DESC LIMIT 50",
                [$sessionId]
            );

            echo json_encode(['success' => true, 'history' => array_reverse($history)]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Render AI Assistant Page
     */
    public function assistantPage()
    {
        // Get user context for personalized greeting
        $userContext = $this->getUserContext();

        $data = [
            'page_title' => 'AI Assistant - APS Dream Home',
            'page_description' => 'Get instant help from our AI assistant',
            'user_context' => $userContext
        ];

        $this->render('pages/ai_assistant', $data);
    }

    /**
     * Process chat feedback (thumbs up/down)
     */
    public function feedback()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $messageId = (int)($_POST['message_id'] ?? $input['message_id'] ?? 0);
            $positive = filter_var($_POST['positive'] ?? $input['positive'] ?? true, FILTER_VALIDATE_BOOLEAN);
            $comment = $_POST['comment'] ?? $input['comment'] ?? null;
            $sessionId = $_POST['session_id'] ?? $input['session_id'] ?? session_id();
            $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;

            // Store feedback in ai_learning_data (works without message_id)
            try {
                $this->db->query(
                    "INSERT INTO ai_learning_data (session_id, user_id, action_type, input_data, output_data, feedback_score, learned_at) VALUES (?, ?, 'feedback', ?, ?, ?, NOW())",
                    [
                        $sessionId,
                        $userId,
                        json_encode(['message_id' => $messageId, 'comment' => $comment]),
                        json_encode(['positive' => $positive]),
                        $positive ? 5 : 1
                    ]
                );
            } catch (\Exception $e) {
                error_log("Feedback save error: " . $e->getMessage());
            }

            // If message_id provided, also update ai_chat_messages counters
            if ($messageId) {
                try {
                    $selfLearning = new SelfLearningAI($sessionId, $userId);
                    $selfLearning->processFeedback($messageId, $positive, $comment);
                } catch (\Exception $e) {
                // Graceful - message might not exist in ai_chat_messages
                error_log($e->getMessage());
                }
            }

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Get AI performance stats (for chat widget)
     */
    public function stats()
    {
        header('Content-Type: application/json');

        try {
            $sessionId = session_id();
            $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
            $selfLearning = new SelfLearningAI($sessionId, $userId);
            $stats = $selfLearning->getPerformanceStats();

            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Get RAG agent stats
     */
    public function ragStats()
    {
        header('Content-Type: application/json');
        try {
            $rag = new RAGAgent();
            $stats = $rag->getStats();
            echo json_encode(['success' => true, 'stats' => $stats]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Generate document (receipt, demand letter, booking confirmation, commission statement)
     */
    public function generateDocument()
    {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $type = $_POST['type'] ?? $input['type'] ?? '';
            $id = (int)($_POST['id'] ?? $input['id'] ?? 0);
            $month = $_POST['month'] ?? $input['month'] ?? date('Y-m');

            $gen = new \App\Services\AI\DocumentGeneratorAgent();

            $result = match ($type) {
                'receipt' => $gen->generatePaymentReceipt($id),
                'demand_letter' => $gen->generateDemandLetter($id),
                'booking' => $gen->generateBookingConfirmation($id),
                'commission' => $gen->generateCommissionStatement($id, $month),
                'leads' => $gen->generateLeadSummary($id),
                default => ['success' => false, 'error' => 'Unknown type. Use: receipt, demand_letter, booking, commission, leads']
            };

            echo json_encode($result);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Trigger workflow automation event
     */
    public function workflowEvent()
    {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $event = $_POST['event'] ?? $input['event'] ?? '';
            $data = $input['data'] ?? [];

            if (empty($event)) {
                echo json_encode(['success' => false, 'error' => 'Event type required']);
                exit;
            }

            $agent = new \App\Services\AI\WorkflowAutomationAgent();
            $result = $agent->processEvent($event, $data);

            echo json_encode(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Chat Analytics Dashboard
     */
    public function analytics()
    {
        $this->requireAdmin();
        $days = (int)($_GET['days'] ?? 30);
        $analytics = new \App\Services\AI\ChatAnalytics($this->db);
        $data = $analytics->getDashboard($days);

        // Also get conversation stats
        try {
            $conversations = $this->db->fetch("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status='confirm' THEN 1 ELSE 0 END) as awaiting_confirm,
                    SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM ai_chat_conversations
                WHERE created_at >= ?
            ", [date('Y-m-d H:i:s', strtotime("-{$days} days"))]);
        } catch (\Exception $e) {
            $conversations = ['total' => 0, 'completed' => 0, 'active' => 0, 'awaiting_confirm' => 0, 'cancelled' => 0];
        }

        // Action labels
        $actionLabels = [
            'post_property' => '� Property Post',
            'add_lead' => '� Add Lead',
            'book_site_visit' => '� Site Visit',
            'search_property' => '� Search',
            'register' => '� Register',
            'file_complaint' => '�� Complaint',
            'check_booking' => '� Check Booking',
        ];

        $this->render('admin/chat/analytics', [
            'data' => $data,
            'conversations' => $conversations,
            'actionLabels' => $actionLabels,
            'days' => $days,
        ]);
    }

    /**
     * Chat History - view past conversations (admin page)
     */
    public function chatHistory()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 30;
        $offset = ($page - 1) * $limit;
        $statusFilter = $_GET['status'] ?? '';
        $actionFilter = $_GET['action'] ?? '';

        try {
            $where = "WHERE 1=1";
            $params = [];

            if ($statusFilter) {
                $where .= " AND status = ?";
                $params[] = $statusFilter;
            }
            if ($actionFilter) {
                $where .= " AND current_action = ?";
                $params[] = $actionFilter;
            }

            $total = $this->db->fetch("SELECT COUNT(*) as cnt FROM ai_chat_conversations {$where}", $params)['cnt'];
            $totalPages = max(1, ceil($total / $limit));

            $conversations = $this->db->fetchAll("
                SELECT c.*, u.name as user_name
                FROM ai_chat_conversations c
                LEFT JOIN users u ON c.user_id = u.id
                {$where}
                ORDER BY c.created_at DESC
                LIMIT {$limit} OFFSET {$offset}
            ", $params);
        } catch (\Exception $e) {
            $conversations = [];
            $total = 0;
            $totalPages = 1;
        }

        $actionLabels = [
            'post_property' => '� Property Post',
            'add_lead' => '� Add Lead',
            'book_site_visit' => '� Site Visit',
            'search_property' => '� Search',
            'register' => '� Register',
            'file_complaint' => '�� Complaint',
            'check_booking' => '� Check Booking',
        ];

        $this->render('admin/chat/history', [
            'conversations' => $conversations,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'statusFilter' => $statusFilter,
            'actionFilter' => $actionFilter,
            'actionLabels' => $actionLabels,
        ]);
    }
}
