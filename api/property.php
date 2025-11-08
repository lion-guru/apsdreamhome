<?php
/**
 * API - Single Property Endpoint
 * Returns detailed information about a specific property
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    global $pdo;

    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Database connection not available'], 500);
    }

    // Get property ID from URL
    $request_uri = $_SERVER['REQUEST_URI'];
    $path_segments = explode('/', trim($request_uri, '/'));
    $property_id = end($path_segments);

    if (!$property_id || !is_numeric($property_id)) {
        sendJsonResponse(['success' => false, 'error' => 'Invalid property ID'], 400);
    }

    // Get property details
    $sql = "SELECT p.*, pt.name as property_type_name
            FROM properties p
            LEFT JOIN property_types pt ON p.property_type_id = pt.id
            WHERE p.id = ? AND p.status = 'available'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$property_id]);
    $property = $stmt->fetch();

    if (!$property) {
        sendJsonResponse(['success' => false, 'error' => 'Property not found'], 404);
    }

    // Get property images
    $images_sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_main DESC, sort_order";
    $images_stmt = $pdo->prepare($images_sql);
    $images_stmt->execute([$property_id]);
    $images = $images_stmt->fetchAll();

    // Get property features
    $features_sql = "SELECT * FROM property_features WHERE property_id = ?";
    $features_stmt = $pdo->prepare($features_sql);
    $features_stmt->execute([$property_id]);
    $features = $features_stmt->fetchAll();

    // Format property data
    $formatted_property = [
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
        'latitude' => $property['latitude'] ? (float)$property['latitude'] : null,
        'longitude' => $property['longitude'] ? (float)$property['longitude'] : null,
        'status' => $property['status'],
        'featured' => (bool)$property['featured'],
        'created_at' => $property['created_at'],
        'images' => array_map(function($image) {
            return [
                'id' => $image['id'],
                'url' => $image['image_url'],
                'alt' => $image['alt_text'] ?? '',
                'is_main' => (bool)$image['is_main']
            ];
        }, $images),
        'features' => array_map(function($feature) {
            return [
                'id' => $feature['id'],
                'name' => $feature['feature_name'],
                'icon' => $feature['icon_class'] ?? 'fas fa-check'
            ];
        }, $features)
    ];

    sendJsonResponse([
        'success' => true,
        'data' => $formatted_property
    ]);

} catch (Exception $e) {
    error_log('API Single Property Error: ' . $e->getMessage());
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
