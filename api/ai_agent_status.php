<?php
/**
 * AI Agent Status API
 * Provides current AI agent status and personality information
 */

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $ai_personality = new AIAgentPersonality();
    $status = $ai_personality->getAgentStatus();
    $reflection = $ai_personality->generateSelfReflection();

    echo json_encode([
        'success' => true,
        'agent_status' => $status,
        'self_reflection' => $reflection,
        'generated_at' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Unable to get agent status: ' . $e->getMessage()]);
}
