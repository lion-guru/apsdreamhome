<?php
/**
 * Property Search API Endpoint
 * Rate limited to prevent abuse
 */

// Unified bootstrap for autoloading, json helper, timezone, session, and base JSON header
require_once __DIR__ . '/includes/bootstrap.php';

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/middleware/rate_limit_middleware.php';
require_once __DIR__ . '/../includes/input_validation.php';

// Apply rate limiting
$rateLimitMiddleware->handle('search');
// Get database connection
$con = getDbConnection();
if (!$con) {
    http_response_code(500);
    if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
        $out = \App\Common\Transformers\ResponseTransformer::error('Database connection failed', 'DB_CONNECTION_FAILED', 500);
        json_response($out, 500);
    } else {
        echo json_encode(['error' => 'Database connection failed']);
    }
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
$types = '';

if ($location) {
    $query .= " AND (location LIKE ? OR address LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
    $types .= 'ss';
}

if ($type) {
    $query .= " AND type = ?";
    $params[] = $type;
    $types .= 's';
}

if ($minPrice > 0) {
    $query .= " AND price >= ?";
    $params[] = $minPrice;
    $types .= 'd';
}

if ($maxPrice < PHP_FLOAT_MAX) {
    $query .= " AND price <= ?";
    $params[] = $maxPrice;
    $types .= 'd';
}

if ($bedrooms > 0) {
    $query .= " AND bedrooms >= ?";
    $params[] = $bedrooms;
    $types .= 'i';
}

// Prepare and execute query
try {
    $stmt = $con->prepare($query);
    if (!empty($params)) {
        // If types string is empty but params exist, default to string types
        $bindTypes = $types !== '' ? $types : str_repeat('s', count($params));
        $stmt->bind_param($bindTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $properties = [];
    while ($row = $result->fetch_assoc()) {
        // Clean sensitive data
        unset($row['owner_contact'], $row['created_by']);
        $properties[] = $row;
    }
    
    // Return results
    http_response_code(200);
    if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
        $out = \App\Common\Transformers\ResponseTransformer::success([
            'count' => count($properties),
            'properties' => $properties
        ], null, 200);
        json_response($out, 200);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'count' => count($properties),
            'properties' => $properties
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
        $out = \App\Common\Transformers\ResponseTransformer::error(
            'An error occurred while searching properties',
            'SEARCH_FAILED',
            500
        );
        json_response($out, 500);
    } else {
        echo json_encode([
            'error' => 'Search failed',
            'message' => 'An error occurred while searching properties'
        ]);
    }
}
