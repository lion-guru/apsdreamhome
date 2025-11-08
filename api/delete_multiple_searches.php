<?php
/**
 * Delete Multiple Saved Searches API Endpoint
 * Handles deletion of multiple saved searches in a single request
 */

// Set headers for CORS and JSON response
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Include necessary files
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/../includes/session.php';

// Start session and check if user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'You must be logged in to perform this action']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST method is allowed']);
    exit;
}

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate input
if (empty($data['search_ids']) || !is_array($data['search_ids'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'No search IDs provided or invalid format']);
    exit;
}

try {
    // Begin transaction
    $conn->beginTransaction();
    
    // Prepare the SQL statement
    $placeholders = rtrim(str_repeat('?,', count($data['search_ids'])), ',');
    $sql = "DELETE FROM saved_searches WHERE id IN ($placeholders) AND user_id = ?";
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    $params = $data['search_ids'];
    $params[] = $_SESSION['user_id'];
    $stmt->execute($params);
    
    $deletedCount = $stmt->rowCount();
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'deleted_count' => $deletedCount,
        'message' => "Successfully deleted $deletedCount searches"
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    
    // Log the error
    error_log("Error deleting saved searches: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the searches',
        'error' => $e->getMessage()
    ]);
}
