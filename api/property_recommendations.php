<?php
/**
 * Property Recommendations API
 * Uses AI and user behavior to suggest properties
 */

// Unified bootstrap for autoloading, json helper, timezone, session, and base JSON header
require_once __DIR__ . '/includes/bootstrap.php';

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/middleware/rate_limit_middleware.php';
require_once __DIR__ . '/../includes/input_validation.php';

// Apply rate limiting
$rateLimitMiddleware->handle('recommendations');

// Get database connection
global $con;
if (!$con) {
    http_response_code(500);
    if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
        $out = \App\Common\Transformers\ResponseTransformer::error('Database connection failed', 'DB_CONNECTION_FAILED', 500);
        json_response($out, 500);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database connection failed', 'code' => 'DB_CONNECTION_FAILED']);
    }
    exit;
}

// Initialize input validator
$validator = new InputValidator($con);

// Validate input
$property_id = $validator->validateInt($_GET['property_id'] ?? 0);
$user_id = $validator->validateInt($_GET['user_id'] ?? 0);

// Basic input validation
if (!$property_id || $property_id <= 0) {
    http_response_code(422);
    if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
        $out = \App\Common\Transformers\ResponseTransformer::error('The property_id field is required and must be a positive integer', 'VALIDATION_ERROR', 422, ['property_id' => ['Property ID is required and must be a positive integer']]);
        json_response($out, 422);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid property_id', 'code' => 'VALIDATION_ERROR']);
    }
    exit;
}

try {
    // Get property details for similarity matching
    $property_query = "SELECT p.*, pt.name as property_type, pr.project_name 
                      FROM properties p 
                      LEFT JOIN property_types pt ON p.type_id = pt.id 
                      LEFT JOIN projects pr ON p.project_id = pr.id 
                      WHERE p.id = ?";
    $stmt = $con->prepare($property_query);
    $stmt->bind_param('i', $property_id);
    $stmt->execute();
    $current_property = $stmt->get_result()->fetch_assoc();

    // Initialize recommendations array
    $recommendations = [];

    // 1. Similar Properties by Features
    $similar_query = "SELECT p.*, pt.name as property_type, pr.project_name,
                            (
                                CASE 
                                    WHEN p.type_id = ? THEN 30 ELSE 0 END +
                                CASE 
                                    WHEN ABS(p.price - ?) / ? * 100 < 20 THEN 25 ELSE 0 END +
                                CASE 
                                    WHEN p.location LIKE ? THEN 20 ELSE 0 END +
                                CASE 
                                    WHEN p.area BETWEEN ? * 0.8 AND ? * 1.2 THEN 15 ELSE 0 END
                            ) as similarity_score
                     FROM properties p 
                     LEFT JOIN property_types pt ON p.type_id = pt.id 
                     LEFT JOIN projects pr ON p.project_id = pr.id 
                     WHERE p.id != ? AND p.status = 'available'
                     HAVING similarity_score > 30
                     ORDER BY similarity_score DESC
                     LIMIT 6";
    
    $stmt = $con->prepare($similar_query);
    $location_pattern = "%" . substr($current_property['location'], 0, strpos($current_property['location'] . ',', ',')) . "%";
    $stmt->bind_param('iddsddi', 
        $current_property['type_id'],
        $current_property['price'],
        $current_property['price'],
        $location_pattern,
        $current_property['area'],
        $current_property['area'],
        $property_id
    );
    $stmt->execute();
    $similar_properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $recommendations['similar'] = $similar_properties;

    // 2. Popular Properties in Same Area
    $popular_query = "SELECT p.*, pt.name as property_type, pr.project_name,
                            COUNT(DISTINCT cj.customer_id) as view_count
                     FROM properties p 
                     LEFT JOIN property_types pt ON p.type_id = pt.id 
                     LEFT JOIN projects pr ON p.project_id = pr.id 
                     LEFT JOIN customer_journeys cj ON p.id = cj.property_id
                     WHERE p.id != ? 
                       AND p.location LIKE ? 
                       AND p.status = 'available'
                     GROUP BY p.id
                     ORDER BY view_count DESC
                     LIMIT 4";
    
    $stmt = $con->prepare($popular_query);
    $stmt->bind_param('is', $property_id, $location_pattern);
    $stmt->execute();
    $popular_properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $recommendations['popular'] = $popular_properties;

    // 3. AI Recommended Properties (based on user behavior if logged in)
    if ($user_id) {
        $ai_query = "SELECT p.*, pt.name as property_type, pr.project_name,
                           ai_score.score as ai_score
                    FROM properties p 
                    LEFT JOIN property_types pt ON p.type_id = pt.id 
                    LEFT JOIN projects pr ON p.project_id = pr.id 
                    INNER JOIN ai_lead_scores ai_score ON p.id = ai_score.property_id
                    WHERE ai_score.user_id = ? 
                      AND p.id != ?
                      AND p.status = 'available'
                    ORDER BY ai_score.score DESC
                    LIMIT 4";
        
        $stmt = $con->prepare($ai_query);
        $stmt->bind_param('ii', $user_id, $property_id);
        $stmt->execute();
        $ai_properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $recommendations['ai_recommended'] = $ai_properties;
    }

    // Log this recommendation request
    $log_query = "INSERT INTO ai_logs (type, property_id, user_id, recommendations) 
                  VALUES ('property_recommendations', ?, ?, ?)";
    $stmt = $con->prepare($log_query);
    $recommendations_json = json_encode($recommendations);
    $stmt->bind_param('iis', $property_id, $user_id, $recommendations_json);
    $stmt->execute();

    // Return recommendations
    if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
        $out = \App\Common\Transformers\ResponseTransformer::success(
            ['recommendations' => $recommendations],
            null,
            200
        );
        json_response($out, 200);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'recommendations' => $recommendations
        ]);
    }

} catch (Exception $e) {
    error_log("Property recommendations error: " . $e->getMessage());
    http_response_code(500);
    if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
        $out = \App\Common\Transformers\ResponseTransformer::error('An error occurred while processing your request', 'INTERNAL_SERVER_ERROR', 500);
        json_response($out, 500);
    } else {
        echo json_encode(['success' => false, 'error' => 'Internal server error', 'code' => 'INTERNAL_SERVER_ERROR']);
    }
}
?>
