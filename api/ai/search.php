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
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }
    
    if (empty($input['query'])) {
        throw new Exception('Search query is required');
    }
    
    // Initialize PropertyAI
    $propertyAI = new PropertyAI($conn);
    
    // Process the natural language query
    $filters = $propertyAI->processNaturalLanguageQuery($input['query']);
    
    // Build SQL query based on filters
    $sql = "SELECT p.*, 
                   (SELECT image_url FROM property_images WHERE property_id = p.id LIMIT 1) as main_image,
                   u.first_name as agent_name
            FROM properties p
            LEFT JOIN users u ON p.agent_id = u.id
            WHERE p.status = 'available'";
    
    $params = [];
    $types = '';
    $conditions = [];
    
    // Add filters to query
    if (!empty($filters['type'])) {
        $conditions[] = "p.type = ?";
        $params[] = $filters['type'];
        $types .= 's';
    }
    
    if (!empty($filters['bedrooms'])) {
        $conditions[] = "p.bedrooms >= ?";
        $params[] = $filters['bedrooms'];
        $types .= 'i';
    }
    
    if (!empty($filters['max_price'])) {
        $conditions[] = "p.price <= ?";
        $params[] = $filters['max_price'];
        $types .= 'd';
    }
    
    if (!empty($filters['location'])) {
        $conditions[] = "p.address LIKE ?";
        $params[] = "%{$filters['location']}%";
        $types .= 's';
    }
    
    // Add keyword search
    if (!empty($filters['keywords'])) {
        $keywordConditions = [];
        foreach ($filters['keywords'] as $keyword) {
            $keywordConditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.features LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $types .= 'sss';
        }
        if (!empty($keywordConditions)) {
            $conditions[] = "(" . implode(" OR ", $keywordConditions) . ")";
        }
    }
    
    // Add conditions to SQL
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }
    
    // Add sorting
    $sql .= " ORDER BY p.created_at DESC LIMIT 12";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $properties = [];
    while ($row = $result->fetch_assoc()) {
        // Format price
        $row['price_formatted'] = '₹' . number_format($row['price']);
        
        // Get first image
        if (empty($row['main_image'])) {
            $row['main_image'] = '/assets/img/default-property.jpg';
        }
        
        $properties[] = $row;
    }
    
    $response['success'] = true;
    $response['data'] = $properties;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
?>
