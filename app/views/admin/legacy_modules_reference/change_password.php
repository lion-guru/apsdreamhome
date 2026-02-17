<?php
require_once __DIR__ . '/core/init.php';

use App\Core\Database;

if (PHP_SAPI !== 'cli') {
    header('Content-Type: application/json');
}

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Security validation failed');
    }

    $user_id = getAuthUserId();
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

    $db = \App\Core\App::database();

    // Get current user data with all password fields
    $user = $db->fetch("SELECT id, username, password, apass FROM admin WHERE id = :id", ['id' => $user_id]);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Check current password against all possible password fields
    $valid_password = false;
    $password_fields = ['password', 'apass'];

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
    if ($db->execute("UPDATE admin SET password = :password, apass = :apass WHERE id = :id", [
        'password' => $hashed_password,
        'apass' => $hashed_password,
        'id' => $user_id
    ])) {
        // Log the password change
        error_log("User {$user['username']} (ID: {$user['id']}) changed their password.");

        $response = [
            'success' => true,
            'message' => 'Password changed successfully. You will be logged out in 3 seconds...',
            'redirect' => 'logout.php?password_changed=1'
        ];
    } else {
        throw new Exception('Failed to update password.');
    }
} catch (Exception $e) {
    error_log('Password change error: ' . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();
