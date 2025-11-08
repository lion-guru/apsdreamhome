<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/db_settings.php';
require_once __DIR__ . '/../../../includes/ai/PropertyAI.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'response' => '',
    'conversation_id' => null
];

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    if (empty($input['message'])) {
        throw new Exception('Message is required');
    }
    
    // Initialize PropertyAI
    $propertyAI = new PropertyAI($conn);
    
    // Process the message
    $result = $propertyAI->processChatMessage([
        'message' => $input['message'],
        'conversation_id' => $input['conversation_id'] ?? null,
        'context' => $input['context'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
    ]);
    
    $response['success'] = true;
    $response['response'] = $result['response'];
    $response['conversation_id'] = $result['conversation_id'];
    
    // Add any additional data
    if (!empty($result['data'])) {
        $response['data'] = $result['data'];
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
?>
