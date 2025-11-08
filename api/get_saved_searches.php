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
    'data' => []
];

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('You must be logged in to view saved searches', 401);
    }

    $user_id = $_SESSION['user_id'];
    $conn = get_db_connection();
    
    // Get saved searches for the current user
    $stmt = $conn->prepare("SELECT id, name, search_params, created_at, updated_at FROM saved_searches WHERE user_id = ? ORDER BY updated_at DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $searches = [];
    while ($row = $result->fetch_assoc()) {
        $searches[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'params' => json_decode($row['search_params'], true),
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    $response['success'] = true;
    $response['data'] = $searches;
    $response['message'] = count($searches) . ' saved searches found';
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
