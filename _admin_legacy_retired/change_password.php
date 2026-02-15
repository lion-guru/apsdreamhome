<?php
session_start();
require_once '../includes/config.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $user_id = $_SESSION['admin_session']['user_id'] ?? 0;
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        throw new Exception('All fields are required');
    }
    
    if (strlen($new_password) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }
    
    if ($new_password !== $confirm_password) {
        throw new Exception('New password and confirm password do not match');
    }
    
    global $con;
    $conn = $con;
    
    // Get current user data with all password fields
    $stmt = $conn->prepare("SELECT id, username, password, apass, upass FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Check current password against all possible password fields
    $valid_password = false;
    $password_fields = ['password', 'apass', 'upass'];
    
    foreach ($password_fields as $field) {
        if (!empty($user[$field])) {
            if (password_verify($current_password, $user[$field])) {
                $valid_password = true;
                break;
            } elseif (sha1($current_password) === $user[$field]) { // For legacy SHA1 hashes
                $valid_password = true;
                break;
            }
        }
    }
    
    if (!$valid_password) {
        throw new Exception('Current password is incorrect');
    }
    
    // Hash the new password using the default algorithm (Argon2id)
    $hashed_password = password_hash(
        $new_password,
        PASSWORD_ARGON2ID,
        [
            'memory_cost' => 65536,  // 64MB
            'time_cost' => 4,        // 4 iterations
            'threads' => 2           // 2 threads
        ]
    );
    
    // Update all password fields for backward compatibility
    $stmt = $conn->prepare("UPDATE users SET password = ?, apass = ?, upass = ? WHERE id = ?");
    $stmt->bind_param('sssi', $hashed_password, $hashed_password, $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        // Log the password change
        error_log("User {$user['username']} (ID: {$user['id']}) changed their password.");
        
        $response = [
            'success' => true,
            'message' => 'Password changed successfully. You will be logged out in 3 seconds...',
            'redirect' => 'logout.php?password_changed=1'
        ];
    } else {
        throw new Exception('Failed to update password: ' . $conn->error);
    }
    
} catch (Exception $e) {
    error_log('Password change error: ' . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();
