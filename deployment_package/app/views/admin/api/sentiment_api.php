<?php
// Dummy sentiment API endpoint for local testing
require_once __DIR__ . '/../core/init.php';

header('Content-Type: application/json');

// Verify admin authentication
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized'))]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Security validation failed'))]);
        exit();
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $feedback = strtolower($input['feedback'] ?? '');
    $sentiment = 'neutral';
    if (strpos($feedback, 'good') !== false || strpos($feedback, 'excellent') !== false) $sentiment = 'positive';
    if (strpos($feedback, 'bad') !== false || strpos($feedback, 'poor') !== false) $sentiment = 'negative';
    
    echo json_encode(['sentiment' => h($sentiment)]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => h($mlSupport->translate('Invalid request'))]);
exit;
