<?php

namespace App\Http\Controllers\Api;

use App\Services\AI\AIGeminiChatbotService;

/**
 * Gemini AI Chatbot API Controller
 * Advanced chatbot with Google Gemini AI integration
 */
class GeminiChatbotController extends BaseApiController
{
    private $chatbotService;

    public function __construct()
    {
        parent::__construct();
        $this->chatbotService = new AIGeminiChatbotService();
    }

    /**
     * Main chat endpoint
     * POST /api/chatbot/message
     */
    public function message(): void
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            if (empty($data['message'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Message is required'
                ]);
                return;
            }

            // Capture user_id and role from $_POST since frontend sends application/x-www-form-urlencoded
            $userId = $data['user_id'] ?? ($_POST['user_id'] ?? 0);
            $role = $data['role'] ?? ($_POST['role'] ?? 'guest');

            $context = $data['context'] ?? [];

            // Inject the user role into context so AI knows who it is talking to
            $context['role'] = $role;

            // Process message with Gemini AI
            $response = $this->chatbotService->processMessage($data['message'], $context);

            // Save conversation to database
            if ($userId > 0) {
                $this->chatbotService->saveConversation(
                    $userId,
                    $data['message'],
                    $response['response'],
                    $response['intent']
                );
            }

            echo json_encode([
                'success' => true,
                'response' => $response['response'],
                'intent' => $response['intent'],
                'language' => $response['language'],
                'confidence' => $response['confidence'],
                'source' => $response['source'],
                'actions' => $response['actions'] ?? []
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get chat history for a user
     * GET /api/chatbot/history/{userId}
     */
    public function history(int $userId): void
    {
        header('Content-Type: application/json');

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT * FROM ai_conversations 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT 50
            ");
            $stmt->execute([$userId]);
            $conversations = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'history' => $conversations
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Quick intent detection endpoint
     * POST /api/chatbot/detect-intent
     */
    public function detectIntent(): void
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            if (empty($data['message'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Message is required'
                ]);
                return;
            }

            // Get intent only (faster response)
            $response = $this->chatbotService->processMessage($data['message'], []);

            echo json_encode([
                'success' => true,
                'intent' => $response['intent'],
                'language' => $response['language'],
                'confidence' => $response['confidence']
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get suggested responses for quick replies
     * GET /api/chatbot/suggestions
     */
    public function suggestions(): void
    {
        header('Content-Type: application/json');

        $suggestions = [
            'buy' => [
                'en' => ['I want to buy a property', 'Show me houses in Gorakhpur', 'What plots are available?'],
                'hi' => ['Mujhe property kharidni hai', 'Gorakhpur mein ghar dikhaye', 'Kitne ka plot hai?']
            ],
            'sell' => [
                'en' => ['I want to sell my property', 'How to list my house?', 'Property valuation'],
                'hi' => ['Mujhe property bechni hai', 'Ghar kaise bechu?', 'Property ka rate kya hai?']
            ],
            'rent' => [
                'en' => ['Property for rent', 'Rent in Lucknow', 'Commercial space'],
                'hi' => ['Rent pe property chahiye', 'Lucknow mein kiraya', 'Dukaan chahiye']
            ],
            'loan' => [
                'en' => ['Home loan information', 'EMI calculator', 'Loan eligibility'],
                'hi' => ['Home loan ke baare mein', 'EMI kitni hogi', 'Loan kaise milega']
            ],
            'join' => [
                'en' => ['Join as associate', 'Agent registration', 'Commission structure'],
                'hi' => ['Associate kaise bane', 'Agent kaise bane', 'Commission kitna milta hai']
            ]
        ];

        echo json_encode([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Health check endpoint
     * GET /api/chatbot/health
     */
    public function health(): void
    {
        header('Content-Type: application/json');

        $geminiKey = $_ENV['GEMINI_API_KEY'] ?? '';

        echo json_encode([
            'success' => true,
            'status' => 'operational',
            'gemini_enabled' => !empty($geminiKey),
            'fallback_available' => true,
            'languages_supported' => ['en', 'hi'],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
