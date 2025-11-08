<?php
/**
 * AI Chatbot Controller
 * Handles chatbot interactions and responses
 */

namespace App\Controllers;

class AIChatbotController extends BaseController {

    private $chatbot;

    public function __construct() {
        $this->chatbot = new \App\Models\AIChatbot();
    }

    /**
     * Display chatbot interface
     */
    public function index() {
        // Check if user is logged in (optional for chatbot)
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $this->data['page_title'] = 'AI Assistant - ' . APP_NAME;
        $this->data['user_id'] = $user_id;
        $this->data['quick_replies'] = $this->chatbot->getQuickReplies(['type' => 'greeting']);

        $this->render('chatbot/index');
    }

    /**
     * Handle chatbot message via AJAX
     */
    public function sendMessage() {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || !isset($input['message'])) {
                sendJsonResponse(['success' => false, 'error' => 'Message is required'], 400);
            }

            $message = trim($input['message']);
            if (empty($message)) {
                sendJsonResponse(['success' => false, 'error' => 'Message cannot be empty'], 400);
            }

            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            $context = $input['context'] ?? [];

            // Process message with AI chatbot
            $response = $this->chatbot->processMessage($message, $context);

            // Save conversation if user is logged in
            if ($user_id) {
                $intent = $this->recognizeIntent($message);
                $this->chatbot->saveConversation($user_id, $message, $response['response'], $intent);
            }

            // Add quick replies based on response type
            $response['quick_replies'] = $this->chatbot->getQuickReplies();

            sendJsonResponse([
                'success' => true,
                'data' => $response
            ]);

        } catch (\Exception $e) {
            error_log('Chatbot message error: ' . $e->getMessage());
            sendJsonResponse([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get conversation history
     */
    public function getHistory() {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
        }

        $user_id = $_SESSION['user_id'];
        $history = $this->chatbot->getConversationHistory($user_id);

        sendJsonResponse([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Get chatbot statistics (admin only)
     */
    public function getStats() {
        header('Content-Type: application/json');

        if (!$this->isAdmin()) {
            sendJsonResponse(['success' => false, 'error' => 'Admin access required'], 403);
        }

        $stats = $this->chatbot->getChatbotStats();

        sendJsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Recognize intent (helper method)
     */
    private function recognizeIntent($message) {
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
}
