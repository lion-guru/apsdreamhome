<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/db_settings.php';
require_once __DIR__ . '/../../../includes/ai/PropertyAI.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
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
    
    // Validate required fields
    $requiredFields = ['property_type', 'location', 'area'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            throw new Exception("$field is required");
        }
    }
    
    // Initialize PropertyAI
    $propertyAI = new PropertyAI($conn);
    
    // Get price prediction
    $prediction = $propertyAI->predictPrice([
        'type' => $input['property_type'],
        'location' => $input['location'],
        'area' => (float)$input['area'],
        'bedrooms' => isset($input['bedrooms']) ? (int)$input['bedrooms'] : 2,
        'bathrooms' => isset($input['bathrooms']) ? (int)$input['bathrooms'] : 2,
        'year_built' => isset($input['year_built']) ? (int)$input['year_built'] : date('Y'),
        'condition' => $input['condition'] ?? 'good',
        'amenities' => $input['amenities'] ?? []
    ]);
    
    if (!$prediction) {
        throw new Exception('Failed to generate price prediction');
    }
    
    $response['success'] = true;
    $response['data'] = $prediction;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
?>
