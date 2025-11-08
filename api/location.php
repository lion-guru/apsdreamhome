<?php
/**
 * API - Location Services
 * Get location-based information and nearby properties
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    global $pdo;

    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Database connection not available'], 500);
    }

    $request_uri = $_SERVER['REQUEST_URI'];
    $path_segments = explode('/', trim($request_uri, '/'));
    $endpoint = end($path_segments);

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            switch ($endpoint) {
                case 'nearby':
                    // Get properties near coordinates
                    if (!isset($_GET['lat']) || !isset($_GET['lng'])) {
                        sendJsonResponse(['success' => false, 'error' => 'Latitude and longitude are required'], 400);
                    }

                    $lat = (float)$_GET['lat'];
                    $lng = (float)$_GET['lng'];
                    $radius = min((float)($_GET['radius'] ?? 10), 50); // Max 50km
                    $limit = min((int)($_GET['limit'] ?? 20), 50);

                    // Haversine formula for distance calculation
                    $sql = "SELECT p.*, pt.name as property_type_name,
                                   (6371 * acos(cos(radians(?)) * cos(radians(p.latitude)) * cos(radians(p.longitude) - radians(?)) + sin(radians(?)) * sin(radians(p.latitude)))) AS distance
                            FROM properties p
                            LEFT JOIN property_types pt ON p.property_type_id = pt.id
                            WHERE p.status = 'available'
                              AND p.latitude IS NOT NULL
                              AND p.longitude IS NOT NULL
                            HAVING distance < ?
                            ORDER BY distance
                            LIMIT ?";

                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$lat, $lng, $lat, $radius, $limit]);
                    $properties = $stmt->fetchAll();

                    // Format properties with distance
                    $formatted_properties = [];
                    foreach ($properties as $property) {
                        $formatted_properties[] = [
                            'id' => $property['id'],
                            'title' => $property['title'],
                            'price' => (float)$property['price'],
                            'city' => $property['city'],
                            'state' => $property['state'],
                            'property_type' => $property['property_type_name'],
                            'bedrooms' => (int)$property['bedrooms'],
                            'bathrooms' => (int)$property['bathrooms'],
                            'area_sqft' => (float)$property['area_sqft'],
                            'featured' => (bool)$property['featured'],
                            'distance' => round((float)$property['distance'], 2),
                            'latitude' => (float)$property['latitude'],
                            'longitude' => (float)$property['longitude']
                        ];
                    }

                    sendJsonResponse([
                        'success' => true,
                        'data' => [
                            'properties' => $formatted_properties,
                            'search_center' => [
                                'latitude' => $lat,
                                'longitude' => $lng,
                                'radius_km' => $radius
                            ],
                            'total_found' => count($formatted_properties)
                        ]
                    ]);
                    break;

                case 'amenities':
                    // Get amenities in an area
                    $city = $_GET['city'] ?? '';
                    $area_type = $_GET['type'] ?? 'schools'; // schools, hospitals, transport, shopping

                    if (empty($city)) {
                        sendJsonResponse(['success' => false, 'error' => 'City parameter is required'], 400);
                    }

                    $amenities = getAmenitiesForArea($city, $area_type);

                    sendJsonResponse([
                        'success' => true,
                        'data' => [
                            'city' => $city,
                            'area_type' => $area_type,
                            'amenities' => $amenities,
                            'total_count' => count($amenities)
                        ]
                    ]);
                    break;

                case 'cities':
                    // Get all cities with property counts
                    $sql = "SELECT p.city, p.state, COUNT(*) as property_count,
                                   AVG(p.price) as avg_price, MIN(p.price) as min_price,
                                   MAX(p.price) as max_price
                            FROM properties p
                            WHERE p.status = 'available'
                            GROUP BY p.city, p.state
                            ORDER BY property_count DESC";

                    $stmt = $pdo->query($sql);
                    $cities = $stmt->fetchAll();

                    $formatted_cities = [];
                    foreach ($cities as $city) {
                        $formatted_cities[] = [
                            'city' => $city['city'],
                            'state' => $city['state'],
                            'property_count' => (int)$city['property_count'],
                            'price_range' => [
                                'min' => (float)$city['min_price'],
                                'max' => (float)$city['max_price'],
                                'average' => (float)$city['avg_price']
                            ]
                        ];
                    }

                    sendJsonResponse([
                        'success' => true,
                        'data' => $formatted_cities
                    ]);
                    break;

                case 'trends':
                    // Get market trends for a location
                    $city = $_GET['city'] ?? '';

                    if (empty($city)) {
                        sendJsonResponse(['success' => false, 'error' => 'City parameter is required'], 400);
                    }

                    $trends = getMarketTrends($city);

                    sendJsonResponse([
                        'success' => true,
                        'data' => [
                            'city' => $city,
                            'trends' => $trends
                        ]
                    ]);
                    break;

                default:
                    sendJsonResponse(['success' => false, 'error' => 'Invalid endpoint'], 404);
            }
            break;

        default:
            sendJsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
    }

} catch (Exception $e) {
    error_log('API Location Services Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}

/**
 * Get amenities for a specific area
 */
function getAmenitiesForArea($city, $area_type) {
    // This would typically integrate with external APIs like Google Places
    // For now, return sample data based on common amenities

    $sample_amenities = [
        'schools' => [
            ['name' => 'Delhi Public School', 'type' => 'School', 'distance' => '2.5 km', 'rating' => 4.2],
            ['name' => 'St. Mary\'s Convent', 'type' => 'School', 'distance' => '1.8 km', 'rating' => 4.5],
            ['name' => 'Kendriya Vidyalaya', 'type' => 'School', 'distance' => '3.2 km', 'rating' => 4.0]
        ],
        'hospitals' => [
            ['name' => 'Apollo Hospital', 'type' => 'Hospital', 'distance' => '3.1 km', 'rating' => 4.3],
            ['name' => 'Max Super Speciality', 'type' => 'Hospital', 'distance' => '2.7 km', 'rating' => 4.1],
            ['name' => 'Fortis Hospital', 'type' => 'Hospital', 'distance' => '4.2 km', 'rating' => 4.4]
        ],
        'transport' => [
            ['name' => 'Metro Station', 'type' => 'Metro', 'distance' => '1.5 km', 'rating' => 4.0],
            ['name' => 'Bus Terminal', 'type' => 'Bus', 'distance' => '2.3 km', 'rating' => 3.8],
            ['name' => 'Railway Station', 'type' => 'Railway', 'distance' => '5.1 km', 'rating' => 3.9]
        ],
        'shopping' => [
            ['name' => 'City Mall', 'type' => 'Shopping Mall', 'distance' => '1.2 km', 'rating' => 4.1],
            ['name' => 'Local Market', 'type' => 'Market', 'distance' => '0.8 km', 'rating' => 3.7],
            ['name' => 'Super Market', 'type' => 'Supermarket', 'distance' => '1.7 km', 'rating' => 4.0]
        ]
    ];

    return $sample_amenities[$area_type] ?? [];
}

/**
 * Get market trends for a city
 */
function getMarketTrends($city) {
    // Sample market trends data
    return [
        'price_trend' => '+5.2%',
        'demand_trend' => 'High',
        'supply_trend' => 'Moderate',
        'avg_price_per_sqft' => 8500,
        'price_growth_rate' => 8.5,
        'rental_yield' => 3.2,
        'market_sentiment' => 'Positive',
        'forecast' => 'Stable growth expected'
    ];
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
