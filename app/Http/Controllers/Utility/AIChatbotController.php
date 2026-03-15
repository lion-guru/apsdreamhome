<?php

/**
 * AI Chatbot Controller
 * Handles chatbot interactions and responses
 */

namespace App\Http\Controllers\Utility;

use App\Http\Controllers\BaseController;
use App\Services\SystemLogger;
use Exception;

class AIChatbotController extends BaseController
{

    private $chatbot;

    public function __construct()
    {
        $this->chatbot = new \App\Models\AIChatbot();
    }

    /**
     * Display chatbot interface
     */
    public function index()
    {
        // Check if user is logged in (optional for chatbot)
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $this->data['page_title'] = 'AI Assistant - APS Dream Home';
        $this->data['user_id'] = $user_id;
        $this->data['quick_replies'] = $this->getQuickReplies(['type' => 'greeting']);

        return $this->renderView('chatbot/index', $this->data);
    }

    /**
     * Handle chatbot message via AJAX
     */
    public function sendMessage()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || !isset($input['message'])) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Message is required'], 400);
            }

            $message = trim($input['message']);
            if (empty($message)) {
                $this->sendJsonResponse(['success' => false, 'error' => 'Message cannot be empty'], 400);
            }

            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $context = $input['context'] ?? [];

            // Process message with AI chatbot
            $response = $this->processMessage($message, $context);

            // Save conversation if user is logged in
            if ($user_id) {
                $intent = $this->recognizeIntent($message);
                $this->saveConversation($user_id, $message, $response['response'], $intent);
            }

            // Add quick replies based on response type
            $response['quick_replies'] = $this->getQuickReplies();

            $this->sendJsonResponse([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            $logger = new SystemLogger();
            $logger->error('Chatbot message error: ' . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get conversation history
     */
    public function getHistory()
    {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            $this->sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
        }

        $user_id = $_SESSION['user_id'];
        $history = $this->getConversationHistory($user_id);

        $this->sendJsonResponse([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Get chatbot statistics (admin only)
     */
    public function getStats()
    {
        header('Content-Type: application/json');

        if (!$this->isAdmin()) {
            $this->sendJsonResponse(['success' => false, 'error' => 'Admin access required'], 403);
        }

        $stats = $this->getChatbotStats();

        $this->sendJsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Recognize intent (helper method)
     */
    private function recognizeIntent($message)
    {
        $message = trim(strtolower($message));

        if (strpos($message, 'property') !== false || strpos($message, 'house') !== false) {
            return 'property_search';
        }

        if (strpos($message, 'price') !== false || strpos($message, 'cost') !== false) {
            return 'price_inquiry';
        }

        if (strpos($message, 'location') !== false || strpos($message, 'area') !== false) {
            return 'location_info';
        }

        if (strpos($message, 'contact') !== false || strpos($message, 'call') !== false) {
            return 'contact_request';
        }

        return 'general_inquiry';
    }

    /**
     * Send JSON response
     */
    private function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Render view
     */
    private function renderView($view, $data = [])
    {
        // Simple view rendering for custom MVC
        extract($data);
        $viewPath = __DIR__ . '/../../../views/' . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "<h1>View not found: $view</h1>";
        }
    }

    /**
     * Get quick replies for chatbot
     */
    private function getQuickReplies($context = [])
    {
        $quick_replies = [
            'greeting' => [
                'Find a property',
                'Check property prices',
                'Contact support',
                'Schedule a visit'
            ],
            'property_search' => [
                'Show apartments',
                'Show villas',
                'Show plots',
                'Filter by price'
            ],
            'price_inquiry' => [
                'Check EMI options',
                'Get loan information',
                'Compare prices',
                'Price trends'
            ],
            'general' => [
                'Help',
                'About APS Dream Home',
                'Contact us',
                'More options'
            ]
        ];

        $type = $context['type'] ?? 'general';
        return $quick_replies[$type] ?? $quick_replies['general'];
    }

    /**
     * Process chatbot message
     */
    private function processMessage($message, $context = [])
    {
        $intent = $this->recognizeIntent($message);

        $responses = [
            'property_search' => 'I can help you find the perfect property! What type of property are you looking for? We have apartments, villas, and plots available in various locations.',
            'price_inquiry' => 'Our properties range from ₹20 Lakhs to ₹5 Crores depending on location, size, and amenities. Would you like to see properties in a specific price range?',
            'location_info' => 'We have properties in prime locations across Mumbai, Delhi, Bangalore, Pune, Hyderabad, and Chennai. Which city interests you the most?',
            'contact_request' => 'I\'d be happy to connect you with our property experts! You can call us at +91-98765-43210 or fill out the contact form on our website.',
            'general_inquiry' => 'Welcome to APS Dream Home! I\'m here to help you find your dream property. How can I assist you today?'
        ];

        $response = $responses[$intent] ?? $responses['general_inquiry'];

        return [
            'response' => $response,
            'intent' => $intent,
            'confidence' => 0.85,
            'suggestions' => $this->getQuickReplies(['type' => $intent])
        ];
    }

    /**
     * Save conversation to database
     */
    private function saveConversation($user_id, $message, $response, $intent)
    {
        try {
            $sql = "INSERT INTO chatbot_conversations (user_id, user_message, bot_response, intent, created_at) VALUES (?, ?, ?, ?, NOW())";
            $this->db->execute($sql, [$user_id, $message, $response, $intent]);
            return true;
        } catch (Exception $e) {
            error_log('Save conversation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get conversation history
     */
    private function getConversationHistory($user_id, $limit = 20)
    {
        try {
            $sql = "SELECT * FROM chatbot_conversations WHERE user_id = ? ORDER BY created_at DESC LIMIT ?";
            return $this->db->fetchAll($sql, [$user_id, $limit]);
        } catch (Exception $e) {
            error_log('Get conversation history error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get chatbot statistics
     */
    private function getChatbotStats()
    {
        try {
            $stats = [];

            // Total conversations
            $sql = "SELECT COUNT(*) as total FROM chatbot_conversations";
            $result = $this->db->fetchOne($sql);
            $stats['total_conversations'] = $result['total'] ?? 0;

            // Today's conversations
            $sql = "SELECT COUNT(*) as today FROM chatbot_conversations WHERE DATE(created_at) = CURDATE()";
            $result = $this->db->fetchOne($sql);
            $stats['today_conversations'] = $result['today'] ?? 0;

            // Intent distribution
            $sql = "SELECT intent, COUNT(*) as count FROM chatbot_conversations GROUP BY intent ORDER BY count DESC";
            $stats['intent_distribution'] = $this->db->fetchAll($sql);

            // Active users
            $sql = "SELECT COUNT(DISTINCT user_id) as active_users FROM chatbot_conversations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $result = $this->db->fetchOne($sql);
            $stats['active_users_7_days'] = $result['active_users'] ?? 0;

            return $stats;
        } catch (Exception $e) {
            error_log('Get chatbot stats error: ' . $e->getMessage());
            return [
                'total_conversations' => 0,
                'today_conversations' => 0,
                'intent_distribution' => [],
                'active_users_7_days' => 0
            ];
        }
    }
}
