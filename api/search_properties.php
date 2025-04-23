<?php
/**
 * Property Search API Endpoint
 * Rate limited to prevent abuse
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/middleware/rate_limit_middleware.php';
require_once __DIR__ . '/../includes/input_validation.php';

// Apply rate limiting
$rateLimitMiddleware->handle('search');

// Get database connection
$con = getDbConnection();
if (!$con) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Initialize input validator
$validator = new InputValidator($con);

// Validate and sanitize input
$location = $validator->sanitizeString($_GET['location'] ?? '');
$type = $validator->sanitizeString($_GET['type'] ?? '');
$minPrice = $validator->validateFloat($_GET['min_price'] ?? 0);
$maxPrice = $validator->validateFloat($_GET['max_price'] ?? PHP_FLOAT_MAX);
$bedrooms = $validator->validateInt($_GET['bedrooms'] ?? 0);

// Build query
$query = "SELECT * FROM properties WHERE 1=1";
$params = [];

if ($location) {
    $query .= " AND (location LIKE ? OR address LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
}

if ($type) {
    $query .= " AND type = ?";
    $params[] = $type;
}

if ($minPrice > 0) {
    $query .= " AND price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice < PHP_FLOAT_MAX) {
    $query .= " AND price <= ?";
    $params[] = $maxPrice;
}

if ($bedrooms > 0) {
    $query .= " AND bedrooms >= ?";
    $params[] = $bedrooms;
}

// Prepare and execute query
try {
    $stmt = $con->prepare($query);
    if ($params) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $properties = [];
    while ($row = $result->fetch_assoc()) {
        // Clean sensitive data
        unset($row['owner_contact']);
        unset($row['created_by']);
        $properties[] = $row;
    }
    
    // Return results
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'count' => count($properties),
        'properties' => $properties
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Search failed',
        'message' => 'An error occurred while searching properties'
    ]);
}
