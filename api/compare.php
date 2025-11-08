<?php
/**
 * API - Property Comparison
 * Compare multiple properties side by side
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    global $pdo;

    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Database connection not available'], 500);
    }

    $request_method = $_SERVER['REQUEST_METHOD'];
    $request_uri = $_SERVER['REQUEST_URI'];
    $path_segments = explode('/', trim($request_uri, '/'));
    $endpoint = end($path_segments);

    switch ($request_method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || !isset($input['property_ids']) || !is_array($input['property_ids'])) {
                sendJsonResponse(['success' => false, 'error' => 'Property IDs array is required'], 400);
            }

            $property_ids = array_map('intval', $input['property_ids']);

            if (count($property_ids) < 2) {
                sendJsonResponse(['success' => false, 'error' => 'At least 2 properties required for comparison'], 400);
            }

            if (count($property_ids) > 5) {
                sendJsonResponse(['success' => false, 'error' => 'Maximum 5 properties allowed for comparison'], 400);
            }

            // Build placeholders for IN clause
            $placeholders = str_repeat('?,', count($property_ids) - 1) . '?';

            // Get properties
            $sql = "SELECT p.*, pt.name as property_type_name
                    FROM properties p
                    LEFT JOIN property_types pt ON p.property_type_id = pt.id
                    WHERE p.id IN ({$placeholders}) AND p.status = 'available'
                    ORDER BY FIELD(p.id, " . implode(',', $property_ids) . ")";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($property_ids);
            $properties = $stmt->fetchAll();

            if (count($properties) !== count($property_ids)) {
                sendJsonResponse(['success' => false, 'error' => 'Some properties not found or not available'], 404);
            }

            // Get images for each property
            $properties_with_images = [];
            foreach ($properties as $property) {
                $images_sql = "SELECT * FROM property_images WHERE property_id = ? ORDER BY is_main DESC, sort_order LIMIT 3";
                $images_stmt = $pdo->prepare($images_sql);
                $images_stmt->execute([$property['id']]);
                $images = $images_stmt->fetchAll();

                $features_sql = "SELECT * FROM property_features WHERE property_id = ? LIMIT 5";
                $features_stmt = $pdo->prepare($features_sql);
                $features_stmt->execute([$property['id']]);
                $features = $features_stmt->fetchAll();

                $properties_with_images[] = [
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
                    'featured' => (bool)$property['featured'],
                    'status' => $property['status'],
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
            }

            // Generate comparison analysis
            $comparison_data = generateComparisonAnalysis($properties_with_images);

            sendJsonResponse([
                'success' => true,
                'data' => [
                    'properties' => $properties_with_images,
                    'comparison' => $comparison_data,
                    'total_properties' => count($properties_with_images)
                ]
            ]);
            break;

        case 'GET':
            if (preg_match('/\/compare\/(\d+)/', $request_uri, $matches)) {
                $comparison_id = (int)$matches[1];

                // Get saved comparison (if implemented)
                sendJsonResponse([
                    'success' => false,
                    'error' => 'Saved comparisons not implemented yet'
                ], 501);
            } else {
                sendJsonResponse(['success' => false, 'error' => 'Invalid endpoint'], 404);
            }
            break;

        default:
            sendJsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
    }

} catch (Exception $e) {
    error_log('API Property Comparison Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}

/**
 * Generate comparison analysis
 */
function generateComparisonAnalysis($properties) {
    if (empty($properties)) {
        return [];
    }

    $prices = array_column($properties, 'price');
    $areas = array_column($properties, 'area_sqft');

    $analysis = [
        'price_range' => [
            'min' => min($prices),
            'max' => max($prices),
            'average' => array_sum($prices) / count($prices)
        ],
        'area_range' => [
            'min' => min($areas),
            'max' => max($areas),
            'average' => array_sum($areas) / count($areas)
        ],
        'price_per_sqft_range' => [
            'min' => min(array_map(function($p) { return $p['price'] / $p['area_sqft']; }, $properties)),
            'max' => max(array_map(function($p) { return $p['price'] / $p['area_sqft']; }, $properties)),
            'average' => array_sum(array_map(function($p) { return $p['price'] / $p['area_sqft']; }, $properties)) / count($properties)
        ],
        'bedroom_distribution' => array_count_values(array_column($properties, 'bedrooms')),
        'bathroom_distribution' => array_count_values(array_column($properties, 'bathrooms')),
        'location_summary' => [
            'cities' => array_unique(array_column($properties, 'city')),
            'states' => array_unique(array_column($properties, 'state'))
        ],
        'feature_comparison' => compareFeatures($properties)
    ];

    return $analysis;
}

/**
 * Compare features across properties
 */
function compareFeatures($properties) {
    $all_features = [];

    foreach ($properties as $property) {
        foreach ($property['features'] as $feature) {
            $feature_name = $feature['name'];
            if (!isset($all_features[$feature_name])) {
                $all_features[$feature_name] = [
                    'name' => $feature_name,
                    'icon' => $feature['icon'],
                    'properties' => []
                ];
            }
            $all_features[$feature_name]['properties'][] = $property['id'];
        }
    }

    // Sort by frequency
    uasort($all_features, function($a, $b) {
        return count($b['properties']) <=> count($a['properties']);
    });

    return array_values($all_features);
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
