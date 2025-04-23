<?php
/**
 * Property Search API Endpoint
 * Requires API key with 'properties.read' permission
 */

require_once __DIR__ . '/../../../includes/config/config.php';
require_once __DIR__ . '/../../../includes/middleware/api_auth_middleware.php';
require_once __DIR__ . '/../../../includes/input_validation.php';

// Initialize response
$response = [
    'success' => false,
    'data' => null,
    'error' => null
];

try {
    // Validate API key and check permissions
    if (!$apiAuthMiddleware
        ->requirePermissions(['properties.read'])
        ->handle()
    ) {
        throw new Exception('Authentication failed');
    }

    // Get database connection
    $con = getDbConnection();
    if (!$con) {
        throw new Exception('Database connection failed');
    }

    // Initialize input validator
    $validator = new InputValidator($con);

    // Validate and sanitize input
    $filters = [
        'location' => $validator->sanitizeString($_GET['location'] ?? ''),
        'type' => $validator->sanitizeString($_GET['type'] ?? ''),
        'min_price' => $validator->validateFloat($_GET['min_price'] ?? 0),
        'max_price' => $validator->validateFloat($_GET['max_price'] ?? PHP_FLOAT_MAX),
        'bedrooms' => $validator->validateInt($_GET['bedrooms'] ?? 0),
        'page' => max(1, $validator->validateInt($_GET['page'] ?? 1)),
        'limit' => min(50, max(1, $validator->validateInt($_GET['limit'] ?? 10)))
    ];

    // Build query
    $query = "SELECT 
                p.*,
                u.name as owner_name,
                u.email as owner_email,
                GROUP_CONCAT(pi.image_url) as images
              FROM properties p
              LEFT JOIN users u ON p.owner_id = u.id
              LEFT JOIN property_images pi ON p.id = pi.property_id
              WHERE 1=1";
    
    $params = [];
    $types = '';

    // Add filters
    if ($filters['location']) {
        $query .= " AND (p.location LIKE ? OR p.address LIKE ?)";
        $params[] = "%{$filters['location']}%";
        $params[] = "%{$filters['location']}%";
        $types .= 'ss';
    }

    if ($filters['type']) {
        $query .= " AND p.type = ?";
        $params[] = $filters['type'];
        $types .= 's';
    }

    if ($filters['min_price'] > 0) {
        $query .= " AND p.price >= ?";
        $params[] = $filters['min_price'];
        $types .= 'd';
    }

    if ($filters['max_price'] < PHP_FLOAT_MAX) {
        $query .= " AND p.price <= ?";
        $params[] = $filters['max_price'];
        $types .= 'd';
    }

    if ($filters['bedrooms'] > 0) {
        $query .= " AND p.bedrooms >= ?";
        $params[] = $filters['bedrooms'];
        $types .= 'i';
    }

    // Add grouping
    $query .= " GROUP BY p.id";

    // Get total count for pagination
    $countQuery = "SELECT COUNT(DISTINCT p.id) as total FROM properties p WHERE 1=1";
    if ($filters['location']) {
        $countQuery .= " AND (p.location LIKE ? OR p.address LIKE ?)";
    }
    if ($filters['type']) {
        $countQuery .= " AND p.type = ?";
    }
    if ($filters['min_price'] > 0) {
        $countQuery .= " AND p.price >= ?";
    }
    if ($filters['max_price'] < PHP_FLOAT_MAX) {
        $countQuery .= " AND p.price <= ?";
    }
    if ($filters['bedrooms'] > 0) {
        $countQuery .= " AND p.bedrooms >= ?";
    }

    $countStmt = $con->prepare($countQuery);
    if (!empty($params)) {
        $countStmt->bind_param($types, ...$params);
    }
    $countStmt->execute();
    $totalCount = $countStmt->get_result()->fetch_assoc()['total'];

    // Add pagination
    $offset = ($filters['page'] - 1) * $filters['limit'];
    $query .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $filters['limit'];
    $types .= 'ii';

    // Execute main query
    $stmt = $con->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Format results
    $properties = [];
    while ($row = $result->fetch_assoc()) {
        // Clean sensitive data based on permissions
        $hasDetailedAccess = in_array('properties.detailed_read', $_REQUEST['api_key_data']['permissions'] ?? []);
        
        if (!$hasDetailedAccess) {
            unset($row['owner_email']);
            unset($row['commission_rate']);
            unset($row['internal_notes']);
        }

        // Format images array
        $row['images'] = $row['images'] ? explode(',', $row['images']) : [];
        
        $properties[] = $row;
    }

    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'properties' => $properties,
            'pagination' => [
                'total' => (int)$totalCount,
                'page' => $filters['page'],
                'limit' => $filters['limit'],
                'pages' => ceil($totalCount / $filters['limit'])
            ],
            'filters' => $filters
        ]
    ];

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
    http_response_code(500);
}

// Send response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
