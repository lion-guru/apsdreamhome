<?php
/**
 * Property Search API Endpoint
 * Handles AJAX property search requests with filtering, sorting, and pagination
 */

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
require_once '../includes/db_connection.php';

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'properties' => [],
    'total' => 0,
    'filters' => []
];

try {
    // Get database connection
    $conn = getDbConnection();
    
    // Get and sanitize search parameters
    $params = [
        'keyword' => isset($_GET['keyword']) ? sanitizeInput($_GET['keyword']) : '',
        'property_type' => isset($_GET['property_type']) ? sanitizeInput($_GET['property_type']) : '',
        'purpose' => isset($_GET['purpose']) ? sanitizeInput($_GET['purpose']) : '',
        'price_range' => isset($_GET['price_range']) ? (int)$_GET['price_range'] : 0,
        'bedrooms' => isset($_GET['bedrooms']) ? (int)$_GET['bedrooms'] : 0,
        'bathrooms' => isset($_GET['bathrooms']) ? (int)$_GET['bathrooms'] : 0,
        'page' => isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1,
        'limit' => isset($_GET['limit']) ? min(12, (int)$_GET['limit']) : 12
    ];
    
    // Build the base query
    $query = "
        SELECT 
            p.*,
            pt.name as property_type,
            pt.purpose,
            u.first_name,
            u.last_name,
            u.phone as agent_phone,
            u.email as agent_email,
            (SELECT pi.image_url 
             FROM property_images pi 
             WHERE pi.property_id = p.id 
             ORDER BY pi.is_primary DESC, pi.id ASC 
             LIMIT 1) as main_image
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        LEFT JOIN users u ON p.agent_id = u.id
        WHERE p.status = 'available' AND p.is_active = 1
    ";
    
    $conditions = [];
    $bindParams = [];
    $types = '';
    
    // Add keyword search condition
    if (!empty($params['keyword'])) {
        $conditions[] = "(
            p.title LIKE ? OR 
            p.description LIKE ? OR 
            p.address LIKE ? OR 
            p.city LIKE ? OR 
            p.state LIKE ? OR 
            p.country LIKE ? OR
            pt.name LIKE ?
        )";
        $searchTerm = "%{$params['keyword']}%";
        $bindParams = array_merge($bindParams, array_fill(0, 7, $searchTerm));
        $types .= str_repeat('s', 7);
    }
    
    // Add property type filter
    if (!empty($params['property_type'])) {
        $conditions[] = "pt.name = ?";
        $bindParams[] = $params['property_type'];
        $types .= 's';
    }
    
    // Add purpose filter
    if (!empty($params['purpose'])) {
        $conditions[] = "pt.purpose = ?";
        $bindParams[] = $params['purpose'];
        $types .= 's';
    }
    
    // Add price range filter
    if ($params['price_range'] > 0) {
        $conditions[] = "p.price <= ?";
        $bindParams[] = $params['price_range'];
        $types .= 'i';
    }
    
    // Add bedrooms filter
    if ($params['bedrooms'] > 0) {
        $conditions[] = "p.bedrooms >= ?";
        $bindParams[] = $params['bedrooms'];
        $types .= 'i';
    }
    
    // Add bathrooms filter
    if ($params['bathrooms'] > 0) {
        $conditions[] = "p.bathrooms >= ?";
        $bindParams[] = $params['bathrooms'];
        $types .= 'i';
    }
    
    // Combine all conditions
    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM (" . str_replace("SELECT p.*, pt.name as property_type, pt.purpose, u.first_name, u.last_name, u.phone as agent_phone, u.email as agent_email, (SELECT pi.image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) as main_image", "SELECT p.id", $query) . ") as count_table";
    
    // Prepare and execute count query
    $countStmt = $conn->prepare($countQuery);
    
    if (!empty($bindParams)) {
        $countStmt->bind_param($types, ...$bindParams);
    }
    
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total = $countResult->fetch_assoc()['total'];
    $countStmt->close();
    
    // Add sorting and pagination
    $offset = ($params['page'] - 1) * $params['limit'];
    $query .= " ORDER BY p.featured DESC, p.created_at DESC LIMIT ? OFFSET ?";
    
    // Add pagination parameters
    $bindParams[] = $params['limit'];
    $bindParams[] = $offset;
    $types .= 'ii';
    
    // Prepare and execute main query
    $stmt = $conn->prepare($query);
    
    if (!empty($bindParams)) {
        $stmt->bind_param($types, ...$bindParams);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $properties = [];
    
    // Process results
    while ($row = $result->fetch_assoc()) {
        // Format property data
        $property = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'slug' => $row['slug'],
            'description' => $row['description'],
            'price' => (float)$row['price'],
            'price_formatted' => '₹' . number_format($row['price']),
            'bedrooms' => (int)$row['bedrooms'],
            'bathrooms' => (int)$row['bathrooms'],
            'area' => (float)$row['area'],
            'area_unit' => $row['area_unit'],
            'address' => $row['address'],
            'city' => $row['city'],
            'state' => $row['state'],
            'country' => $row['country'],
            'postal_code' => $row['postal_code'],
            'latitude' => (float)$row['latitude'],
            'longitude' => (float)$row['longitude'],
            'property_type' => $row['property_type'],
            'purpose' => $row['purpose'],
            'is_featured' => (bool)$row['is_featured'],
            'status' => $row['status'],
            'year_built' => $row['year_built'] ? (int)$row['year_built'] : null,
            'garage' => $row['garage'] ? (int)$row['garage'] : 0,
            'garage_size' => $row['garage_size'] ? (float)$row['garage_size'] : null,
            'image' => $row['main_image'] ?: 'assets/images/properties/default.jpg',
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
            'agent' => [
                'name' => trim($row['first_name'] . ' ' . $row['last_name']),
                'phone' => $row['agent_phone'],
                'email' => $row['agent_email']
            ]
        ];
        
        $properties[] = $property;
    }
    
    // Get available filters for the current search
    $filters = [
        'property_types' => [],
        'purposes' => [
            ['value' => 'sale', 'label' => 'For Sale'],
            ['value' => 'rent', 'label' => 'For Rent']
        ],
        'bedrooms' => [1, 2, 3, 4, 5],
        'bathrooms' => [1, 2, 3, 4],
        'max_price' => 10000000 // Default max price, can be fetched from DB
    ];
    
    // Get available property types
    $typeQuery = "SELECT DISTINCT name FROM property_types WHERE is_active = 1 ORDER BY name";
    $typeResult = $conn->query($typeQuery);
    
    while ($type = $typeResult->fetch_assoc()) {
        $filters['property_types'][] = [
            'value' => $type['name'],
            'label' => ucfirst($type['name'])
        ];
    }
    
    // Get max price from database if needed
    $maxPriceQuery = "SELECT MAX(price) as max_price FROM properties WHERE status = 'available' AND is_active = 1";
    $maxPriceResult = $conn->query($maxPriceQuery);
    if ($maxPriceResult && $maxPriceRow = $maxPriceResult->fetch_assoc()) {
        $filters['max_price'] = (int)ceil($maxPriceRow['max_price'] / 1000000) * 1000000; // Round up to nearest million
    }
    
    // Prepare success response
    $response = [
        'success' => true,
        'message' => count($properties) . ' properties found',
        'total' => (int)$total,
        'page' => $params['page'],
        'limit' => $params['limit'],
        'pages' => (int)ceil($total / $params['limit']),
        'properties' => $properties,
        'filters' => $filters
    ];
    
    $stmt->close();
    
} catch (Exception $e) {
    // Handle errors
    http_response_code(500);
    $response['message'] = 'An error occurred while processing your request: ' . $e->getMessage();
    error_log('Search API Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
}

// Close database connection
if (isset($conn) && $conn) {
    $conn->close();
}

// Return JSON response
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
