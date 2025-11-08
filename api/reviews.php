<?php
/**
 * API - Reviews & Ratings
 * Handle property and agent reviews and ratings
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
                case 'reviews':
                    // Get reviews for a property or agent
                    $target_type = $_GET['type'] ?? 'property'; // property, agent
                    $target_id = $_GET['id'] ?? 0;

                    if (!$target_id) {
                        sendJsonResponse(['success' => false, 'error' => 'Target ID is required'], 400);
                    }

                    $page = (int)($_GET['page'] ?? 1);
                    $limit = min((int)($_GET['limit'] ?? 10), 50);
                    $offset = ($page - 1) * $limit;

                    // Get reviews (placeholder - would need reviews table)
                    $reviews = getSampleReviews($target_type, $target_id, $limit, $offset);

                    // Calculate average rating
                    $total_rating = array_sum(array_column($reviews, 'rating'));
                    $average_rating = count($reviews) > 0 ? round($total_rating / count($reviews), 1) : 0;

                    sendJsonResponse([
                        'success' => true,
                        'data' => [
                            'reviews' => $reviews,
                            'summary' => [
                                'total_reviews' => count($reviews),
                                'average_rating' => $average_rating,
                                'rating_distribution' => calculateRatingDistribution($reviews)
                            ],
                            'pagination' => [
                                'current_page' => $page,
                                'per_page' => $limit,
                                'total_pages' => ceil(100 / $limit), // Placeholder
                                'total_count' => 100 // Placeholder
                            ]
                        ]
                    ]);
                    break;

                case 'rating':
                    // Get rating summary for a property or agent
                    $target_type = $_GET['type'] ?? 'property';
                    $target_id = $_GET['id'] ?? 0;

                    if (!$target_id) {
                        sendJsonResponse(['success' => false, 'error' => 'Target ID is required'], 400);
                    }

                    $rating_summary = getRatingSummary($target_type, $target_id);

                    sendJsonResponse([
                        'success' => true,
                        'data' => $rating_summary
                    ]);
                    break;

                default:
                    sendJsonResponse(['success' => false, 'error' => 'Invalid endpoint'], 404);
            }
            break;

        case 'POST':
            switch ($endpoint) {
                case 'reviews':
                    // Submit a new review
                    $input = json_decode(file_get_contents('php://input'), true);

                    $required = ['target_type', 'target_id', 'user_id', 'rating', 'review_text'];
                    foreach ($required as $field) {
                        if (!isset($input[$field]) || empty($input[$field])) {
                            sendJsonResponse(['success' => false, 'error' => "Field '{$field}' is required"], 400);
                        }
                    }

                    // Validate rating (1-5)
                    $rating = (int)$input['rating'];
                    if ($rating < 1 || $rating > 5) {
                        sendJsonResponse(['success' => false, 'error' => 'Rating must be between 1 and 5'], 400);
                    }

                    // Submit review (placeholder - would insert into reviews table)
                    $review_id = submitReview($input);

                    sendJsonResponse([
                        'success' => true,
                        'message' => 'Review submitted successfully',
                        'data' => [
                            'review_id' => $review_id,
                            'target_type' => $input['target_type'],
                            'target_id' => $input['target_id'],
                            'rating' => $rating,
                            'submitted_at' => date('Y-m-d H:i:s')
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
    error_log('API Reviews Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}

/**
 * Get sample reviews (placeholder function)
 */
function getSampleReviews($target_type, $target_id, $limit, $offset) {
    // Sample reviews data - in real implementation, this would query the reviews table
    $sample_reviews = [
        [
            'id' => 1,
            'user_id' => 1,
            'user_name' => 'Rajesh Kumar',
            'rating' => 5,
            'review_text' => 'Excellent property with great amenities. The location is perfect and the agent was very helpful throughout the process.',
            'created_at' => '2024-01-15 10:30:00',
            'helpful_votes' => 12,
            'verified_purchase' => true
        ],
        [
            'id' => 2,
            'user_id' => 2,
            'user_name' => 'Priya Sharma',
            'rating' => 4,
            'review_text' => 'Good property overall. Minor issues with maintenance but the agent resolved them quickly.',
            'created_at' => '2024-01-10 14:20:00',
            'helpful_votes' => 8,
            'verified_purchase' => true
        ],
        [
            'id' => 3,
            'user_id' => 3,
            'user_name' => 'Amit Patel',
            'rating' => 5,
            'review_text' => 'Outstanding experience! The property exceeded our expectations and the entire buying process was smooth.',
            'created_at' => '2024-01-05 09:15:00',
            'helpful_votes' => 15,
            'verified_purchase' => true
        ]
    ];

    // Simulate pagination
    return array_slice($sample_reviews, $offset, $limit);
}

/**
 * Get rating summary
 */
function getRatingSummary($target_type, $target_id) {
    // Sample rating summary - in real implementation, this would calculate from actual reviews
    return [
        'average_rating' => 4.3,
        'total_reviews' => 47,
        'rating_distribution' => [
            '5' => 28,
            '4' => 12,
            '3' => 5,
            '2' => 1,
            '1' => 1
        ],
        'recent_trend' => 'improving',
        'last_updated' => date('Y-m-d H:i:s')
    ];
}

/**
 * Calculate rating distribution
 */
function calculateRatingDistribution($reviews) {
    $distribution = [
        '5' => 0,
        '4' => 0,
        '3' => 0,
        '2' => 0,
        '1' => 0
    ];

    foreach ($reviews as $review) {
        $rating = (string)$review['rating'];
        if (isset($distribution[$rating])) {
            $distribution[$rating]++;
        }
    }

    return $distribution;
}

/**
 * Submit review (placeholder function)
 */
function submitReview($review_data) {
    // In real implementation, this would insert into the reviews table
    // For now, return a sample review ID
    return rand(1000, 9999);
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
