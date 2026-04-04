<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\BaseController;
use App\Services\PropertyChatbotService;

class ChatbotAPIController extends BaseController
{
    private $chatbotService;

    public function __construct()
    {
        parent::__construct();
        $this->chatbotService = new PropertyChatbotService();
    }

    public function handleMessage()
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $message = trim($input['message'] ?? '');
            
            if (empty($message)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Message is required'
                ]);
                return;
            }

            $response = $this->chatbotService->processMessage($message);

            $userId = $_SESSION['user_id'] ?? ($_SESSION['admin_id'] ?? 'guest');
            $this->chatbotService->saveConversation($userId, $message, $response);

            echo json_encode([
                'success' => true,
                'reply' => $response['reply'],
                'quick_replies' => $response['quick_replies'],
                'intent' => $response['intent']
            ]);
        } catch (\Exception $e) {
            error_log("Chatbot error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Something went wrong. Please try again.'
            ]);
        }
    }

    public function getHistory()
    {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/login');
            return;
        }

        $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'];
        
        try {
            $db = \App\Core\Database\Database::getInstance();
            $sql = "SELECT * FROM chatbot_conversations 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC LIMIT 50";
            $history = $db->fetchAll($sql, [$userId]);
            
            return $history;
        } catch (\Exception $e) {
            error_log("Chatbot history error: " . $e->getMessage());
            return [];
        }
    }
}
