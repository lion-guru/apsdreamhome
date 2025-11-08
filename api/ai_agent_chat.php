<?php
/**
 * AI Agent Chat API
 * Handles chat interactions with the AI agent
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

$message = trim($_POST['message'] ?? '');
$context = $_POST['context'] ?? '[]';

if (empty($message)) {
    echo json_encode(['error' => 'Message is required']);
    exit;
}

try {
    // Parse context if provided
    $context_data = json_decode($context, true) ?: [];

    // Use AI personality system for enhanced responses
    $ai_personality = new AIAgentPersonality();
    $response = $ai_personality->generatePersonalizedResponse($message, $context_data);

    // Learn from this interaction
    $ai_learner = new AILearningSystem();
    $ai_learner->learnFromInteraction($message, $response, 'chat', $context_data);

    echo json_encode(['response' => $response]);

} catch (Exception $e) {
    echo json_encode(['error' => 'AI processing error: ' . $e->getMessage()]);
}
