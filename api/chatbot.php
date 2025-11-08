<?php
/**
 * API - AI Chatbot
 * Handles chatbot conversations and responses
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    global $pdo;

    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Database connection not available'], 500);
    }

    $request_uri = $_SERVER['REQUEST_URI'];
    $path_segments = explode('/', trim($request_uri, '/'));
    $endpoint = end($path_segments);

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            switch ($endpoint) {
                case 'message':
                    // Handle chatbot message
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

                    $chatbot = new \App\Models\AIChatbot();
                    $response = $chatbot->processMessage($message, $context);

                    // Save conversation if user is logged in
                    if ($user_id) {
                        $intent = recognizeIntent($message);
                        $chatbot->saveConversation($user_id, $message, $response['response'], $intent);
                    }

                    sendJsonResponse([
                        'success' => true,
                        'data' => $response
                    ]);
                    break;

                case 'feedback':
                    // Handle chatbot feedback
                    $input = json_decode(file_get_contents('php://input'), true);

                    $required = ['conversation_id', 'rating', 'feedback'];
                    foreach ($required as $field) {
                        if (!isset($input[$field])) {
                            sendJsonResponse(['success' => false, 'error' => "Field '{$field}' is required"], 400);
                        }
                    }

                    // Save feedback (placeholder)
                    sendJsonResponse([
                        'success' => true,
                        'message' => 'Feedback submitted successfully'
                    ]);
                    break;

                default:
                    sendJsonResponse(['success' => false, 'error' => 'Invalid endpoint'], 404);
            }
            break;

        case 'GET':
            switch ($endpoint) {
                case 'history':
                    // Get conversation history
                    session_start();
                    if (!isset($_SESSION['user_id'])) {
                        sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
                    }

                    $chatbot = new \App\Models\AIChatbot();
                    $history = $chatbot->getConversationHistory($_SESSION['user_id']);

                    sendJsonResponse([
                        'success' => true,
                        'data' => $history
                    ]);
                    break;

                case 'stats':
                    // Get chatbot statistics (admin only)
                    session_start();
                    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                        sendJsonResponse(['success' => false, 'error' => 'Admin access required'], 403);
                    }

                    $chatbot = new \App\Models\AIChatbot();
                    $stats = $chatbot->getChatbotStats();

                    sendJsonResponse([
                        'success' => true,
                        'data' => $stats
                    ]);
                    break;

                case 'suggestions':
                    // Get quick reply suggestions
                    $chatbot = new \App\Models\AIChatbot();
                    $suggestions = $chatbot->getQuickReplies();

                    sendJsonResponse([
                        'success' => true,
                        'data' => $suggestions
                    ]);
                    break;

                default:
                    sendJsonResponse(['success' => false, 'error' => 'Invalid endpoint'], 404);
            }
            break;

        default:
            sendJsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
    }

} catch (Exception $e) {
    error_log('API Chatbot Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}

/**
 * Recognize intent from message
 */
function recognizeIntent($message) {
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
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
