<?php
/**
 * Property Comparison API
 * Provides detailed comparison of multiple properties
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/db_settings.php';
require_once __DIR__ . '/../includes/input_validation.php';

// Get database connection
$conn = get_db_connection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Initialize input validator
$validator = new InputValidator($conn);

// Get property IDs from request
$property_ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];
$property_ids = array_map('intval', $property_ids);

if (empty($property_ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'No properties specified for comparison']);
    exit;
}

try {
    // Get property details
    $placeholders = str_repeat('?,', count($property_ids) - 1) . '?';
    $query = "SELECT p.*, 
                     pt.name as property_type,
                     pr.project_name,
                     pr.description as project_description,
                     CONCAT(u.first_name, ' ', u.last_name) as owner_name,
                     (SELECT GROUP_CONCAT(amenity_name) 
                      FROM project_amenities pa 
                      WHERE pa.project_id = pr.id) as amenities,
                     (SELECT predicted_value 
                      FROM ai_property_valuation 
                      WHERE property_id = p.id 
                      ORDER BY created_at DESC 
                      LIMIT 1) as ai_valuation
              FROM properties p 
              LEFT JOIN property_types pt ON p.type_id = pt.id
              LEFT JOIN projects pr ON p.project_id = pr.id
              LEFT JOIN users u ON p.owner_id = u.id
              WHERE p.id IN ($placeholders)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('i', count($property_ids)), ...$property_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $properties = $result->fetch_all(MYSQLI_ASSOC);

    // Get market analysis
    $market_data = [];
    foreach ($properties as $property) {
        $location = $property['location'];
        $type_id = $property['type_id'];

        // Get average price in area
        $price_query = "SELECT 
                           COUNT(*) as total_properties,
                           AVG(price) as avg_price,
                           MIN(price) as min_price,
                           MAX(price) as max_price
                       FROM properties 
                       WHERE location LIKE ? 
                         AND type_id = ?
                         AND status = 'available'";
        
        $stmt = $conn->prepare($price_query);
        $location_pattern = "%" . substr($location, 0, strpos($location . ',', ',')) . "%";
        $stmt->bind_param('si', $location_pattern, $type_id);
        $stmt->execute();
        $market_data[$property['id']] = $stmt->get_result()->fetch_assoc();
    }

    // Calculate comparison scores
    $comparison = [];
    foreach ($properties as $property) {
        $scores = [
            'price_score' => 0,
            'location_score' => 0,
            'amenities_score' => 0,
            'overall_score' => 0
        ];

        // Price score (based on market average)
        $market = $market_data[$property['id']];
        $avg_price = $market['avg_price'];
        if ($avg_price > 0) {
            $price_diff = abs($property['price'] - $avg_price) / $avg_price;
            $scores['price_score'] = max(0, 100 - ($price_diff * 100));
        }

        // Location score (based on nearby amenities)
        $location_query = "SELECT COUNT(*) as poi_count 
                          FROM points_of_interest 
                          WHERE ST_Distance_Sphere(
                              point(longitude, latitude),
                              point(?, ?)
                          ) <= 2000"; // 2km radius
        
        $stmt = $conn->prepare($location_query);
        $stmt->bind_param('dd', $property['longitude'], $property['latitude']);
        $stmt->execute();
        $poi_count = $stmt->get_result()->fetch_object()->poi_count;
        $scores['location_score'] = min(100, ($poi_count / 10) * 100);

        // Amenities score
        $amenities = explode(',', $property['amenities'] ?? '');
        $scores['amenities_score'] = min(100, (count($amenities) / 10) * 100);

        // Overall score
        $scores['overall_score'] = ($scores['price_score'] * 0.4) + 
                                 ($scores['location_score'] * 0.35) + 
                                 ($scores['amenities_score'] * 0.25);

        $comparison[$property['id']] = [
            'property' => $property,
            'market_data' => $market_data[$property['id']],
            'scores' => $scores
        ];
    }

    // Get similar properties recommendations
    $similar_properties = [];
    foreach ($properties as $property) {
        $similar_query = "SELECT p.id, p.title, p.price, p.location, pt.name as property_type
                         FROM properties p
                         JOIN property_types pt ON p.type_id = pt.id
                         WHERE p.id != ?
                           AND p.type_id = ?
                           AND p.price BETWEEN ? * 0.8 AND ? * 1.2
                           AND p.status = 'available'
                         LIMIT 3";
        
        $stmt = $conn->prepare($similar_query);
        $stmt->bind_param('iiii', 
            $property['id'],
            $property['type_id'],
            $property['price'],
            $property['price']
        );
        $stmt->execute();
        $similar_properties[$property['id']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Return comparison data
    echo json_encode([
        'status' => 'success',
        'comparison' => $comparison,
        'similar_properties' => $similar_properties
    ]);

} catch (Exception $e) {
    error_log("Property comparison error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to compare properties']);
}
?>
