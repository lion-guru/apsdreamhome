<?php
/**
 * API - Properties Endpoint
 * Returns property listings with filtering and pagination
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    global $pdo;

    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Database connection not available'], 500);
    }

    // Get query parameters
    $page = (int)($_GET['page'] ?? 1);
    $limit = min((int)($_GET['limit'] ?? 10), 50); // Max 50 per page
    $offset = ($page - 1) * $limit;

    // Build filters
    $filters = [];
    $where_conditions = [];
    $params = [];

    if (isset($_GET['property_type']) && !empty($_GET['property_type'])) {
        $where_conditions[] = "p.property_type = ?";
        $params[] = $_GET['property_type'];
    }

    if (isset($_GET['city']) && !empty($_GET['city'])) {
        $where_conditions[] = "p.city = ?";
        $params[] = $_GET['city'];
    }

    if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
        $where_conditions[] = "p.price >= ?";
        $params[] = $_GET['min_price'];
    }

    if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
        $where_conditions[] = "p.price <= ?";
        $params[] = $_GET['max_price'];
    }

    if (isset($_GET['featured']) && $_GET['featured'] === 'true') {
        $where_conditions[] = "p.featured = 1";
    }

    // Always filter by available status
    $where_conditions[] = "p.status = 'available'";

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : '';

    // Get properties
    $sql = "SELECT p.*, pt.name as property_type_name
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            {$where_clause}
            ORDER BY p.featured DESC, p.created_at DESC
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $properties = $stmt->fetchAll();

    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM properties p {$where_clause}";
    $count_params = array_slice($params, 0, -2); // Remove limit and offset

    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($count_params);
    $total_count = (int)$count_stmt->fetch()['total'];

    // Format properties for API response
    $formatted_properties = [];
    foreach ($properties as $property) {
        $formatted_properties[] = [
            'id' => $property['id'],
            'title' => $property['title'],
            'description' => $property['description'],
            'price' => (float)$property['price'],
            'city' => $property['city'],
            'state' => $property['state'],
            'property_type' => $property['property_type_name'],
            'bedrooms' => (int)$property['bedrooms'],
            'bathrooms' => (int)$property['bathrooms'],
            'area_sqft' => (float)$property['area_sqft'],
            'featured' => (bool)$property['featured'],
            'status' => $property['status'],
            'latitude' => $property['latitude'] ? (float)$property['latitude'] : null,
            'longitude' => $property['longitude'] ? (float)$property['longitude'] : null,
            'created_at' => $property['created_at']
        ];
    }

    sendJsonResponse([
        'success' => true,
        'data' => [
            'properties' => $formatted_properties,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total_pages' => ceil($total_count / $limit),
                'total_count' => $total_count
            ],
            'filters' => $_GET
        ]
    ]);

} catch (Exception $e) {
    error_log('API Properties Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
