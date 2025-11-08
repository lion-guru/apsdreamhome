<?php
/**
 * AI Chatbot API Endpoint
 * Handles chatbot requests via AJAX
 */

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!$config['ai']['enabled']) {
    echo json_encode(['error' => 'AI features are disabled']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Only POST requests allowed']);
    exit;
}

$user_query = trim($_POST['query'] ?? '');

if (empty($user_query)) {
    echo json_encode(['error' => 'Query is required']);
    exit;
}

try {
    $ai = new AIDreamHome();
    $result = $ai->generateChatbotResponse($user_query);

    if (isset($result['error'])) {
        echo json_encode(['error' => $result['error']]);
    } else {
        echo json_encode(['response' => $result['success']]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
