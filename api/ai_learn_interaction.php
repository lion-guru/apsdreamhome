<?php
/**
 * AI Learning Interaction API
 * Stores user interactions for AI learning
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

$required_fields = ['user_input', 'ai_response'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode(['error' => "Field '$field' is required"]);
        exit;
    }
}

try {
    // Learn from this interaction using the AI learning system
    $ai_learner = new AILearningSystem();
    $success = $ai_learner->learnFromInteraction(
        $input['user_input'],
        $input['ai_response'],
        'javascript_client',
        $input['context'] ?? []
    );

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Interaction learned successfully',
            'learning_recorded' => true
        ]);
    } else {
        echo json_encode(['error' => 'Failed to record learning']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Learning system error: ' . $e->getMessage()]);
}
