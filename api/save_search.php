<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
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
        throw new Exception('You must be logged in to save searches', 401);
    }

    $user_id = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (empty($input['name']) || empty($input['params'])) {
        throw new Exception('Name and search parameters are required', 400);
    }

    $name = trim($input['name']);
    $search_params = json_encode($input['params']);

    // Insert into database
    $conn = get_db_connection();
    $stmt = $conn->prepare("INSERT INTO saved_searches (user_id, name, search_params) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $user_id, $name, $search_params);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Search saved successfully';
        $response['data'] = [
            'id' => $stmt->insert_id,
            'name' => $name,
            'created_at' => date('Y-m-d H:i:s')
        ];
    } else {
        throw new Exception('Failed to save search', 500);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
