<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/db_settings.php';
require_once '../includes/auth_check.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('You must be logged in to view saved searches', 401);
    }

    $search_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if (!$search_id) {
        throw new Exception('Invalid search ID', 400);
    }

    $conn = get_db_connection();
    
    // Get the saved search for the current user
    $stmt = $conn->prepare("SELECT id, name, search_params, created_at, updated_at 
                           FROM saved_searches 
                           WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $search_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Saved search not found', 404);
    }
    
    $search = $result->fetch_assoc();
    
    $response['success'] = true;
    $response['data'] = [
        'id' => $search['id'],
        'name' => $search['name'],
        'search_params' => json_decode($search['search_params'], true),
        'created_at' => $search['created_at'],
        'updated_at' => $search['updated_at']
    ];
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
