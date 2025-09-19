<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/db_settings.php';
require_once '../includes/auth_check.php';

$response = [
    'success' => false,
    'message' => ''
];

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('You must be logged in to delete saved searches', 401);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $search_id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if (!$search_id) {
        throw new Exception('Invalid search ID', 400);
    }

    $conn = get_db_connection();
    
    // Delete the saved search for the current user
    $stmt = $conn->prepare("DELETE FROM saved_searches WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $search_id, $user_id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Saved search not found or you do not have permission to delete it', 404);
    }
    
    $response['success'] = true;
    $response['message'] = 'Search deleted successfully';
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
