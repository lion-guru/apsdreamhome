<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../includes/db_settings.php';
require_once __DIR__ . '/../../../includes/ai/PropertyAI.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    // Get user ID from session or token (simplified for example)
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Get limit from query params
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
    
    // Initialize PropertyAI
    $propertyAI = new PropertyAI($conn);
    
    // Get recommended properties
    $properties = $propertyAI->getRecommendedProperties($userId, $limit);
    
    // Format the response
    $formattedProperties = [];
    foreach ($properties as $property) {
        // Format price
        $property['price_formatted'] = '₹' . number_format($property['price']);
        
        // Set default image if none exists
        if (empty($property['main_image'])) {
            $property['main_image'] = '/assets/img/default-property.jpg';
        }
        
        $formattedProperties[] = $property;
    }
    
    $response['success'] = true;
    $response['data'] = $formattedProperties;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
?>
