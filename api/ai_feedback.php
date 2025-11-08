<?php
/**
 * AI Feedback API
 * Handles user feedback for AI learning
 */

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Only POST requests allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

$required_fields = ['rating'];
foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

try {
    // Learn from feedback using the AI personality system
    $ai_personality = new AIAgentPersonality();
    $success = $ai_personality->learnFromFeedback(
        'rating_' . $input['rating'],
        $input['feedback'] ?? '',
        $_SESSION['user_id'] ?? 1
    );

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Feedback recorded successfully',
            'learning_updated' => true
        ]);
    } else {
        echo json_encode(['error' => 'Failed to record feedback']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Feedback system error: ' . $e->getMessage()]);
}
