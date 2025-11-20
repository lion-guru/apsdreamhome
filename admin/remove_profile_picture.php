<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin session
if (!isset($_SESSION['admin_session']) || $_SESSION['admin_session']['is_authenticated'] !== true) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include database connection
require_once __DIR__ . '/../includes/config/config.php';
global $con;

// Set content type to JSON
header('Content-Type: application/json');

// Get user ID from POST data
$user_id = $_POST['user_id'] ?? 0;

// Validate user ID
if (empty($user_id) || !is_numeric($user_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

try {
        global $con;
    $conn = $con;
    
    // Begin transaction
    $conn->begin_transaction();
    
    // Get current profile picture filename
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    $profile_picture = $user['profile_picture'];
    
    // If user has a profile picture, delete the file
    if (!empty($profile_picture)) {
        $upload_dir = __DIR__ . '/uploads/profile_pictures/';
        $file_path = $upload_dir . $profile_picture;
        
        // Delete the file if it exists
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        // Remove the profile picture reference from the database
        $stmt = $conn->prepare("UPDATE users SET profile_picture = NULL WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Update session if the current user is updating their own profile
    if ($_SESSION['admin_session']['user_id'] == $user_id) {
        $_SESSION['admin_session']['profile_picture'] = null;
    }
    
    echo json_encode(['success' => true, 'message' => 'Profile picture removed successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    error_log('Error removing profile picture: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while removing the profile picture']);
}
?>
