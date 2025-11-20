<?php
session_start();
require_once 'db_connection.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        throw new Exception('Please provide a valid email address');
    }

    $conn = $con;
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        // For security, don't reveal if email exists or not
        $response['message'] = 'If an account with that email exists, a password reset link has been sent.';
        $response['success'] = true;
        echo json_encode($response);
        exit();
    }
    
    // Generate a secure token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valid for 1 hour
    
    // Store token in database
    $stmt = $conn->prepare("INSERT INTO password_resets (email, token, created_at, expires_at) VALUES (?, ?, NOW(), ?)");
    $stmt->bind_param('sss', $email, $token, $expires);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create reset token');
    }
    
    // Create reset link
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
    $reset_link = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/admin/reset-password-form.php?token=' . $token;
    
    // Send email (in a real application, you would use a proper email library)
    $to = $user['email'];
    $subject = 'Password Reset Request';
    $message = "
        <h2>Password Reset Request</h2>
        <p>Hello " . htmlspecialchars($user['username']) . ",</p>
        <p>You have requested to reset your password. Please click the link below to set a new password:</p>
        <p><a href='$reset_link' style='background-color: #4CAF50; color: white; padding: 10px 20px; text-align: center; text-decoration: none; display: inline-block; border-radius: 4px;'>Reset Password</a></p>
        <p>Or copy and paste this link into your browser:</p>
        <p><code>$reset_link</code></p>
        <p>This link will expire in 1 hour.</p>
        <p>If you did not request a password reset, please ignore this email.</p>
    ";
    
    $headers = "From: no-reply@apsdreamhomes.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // In a real application, use a proper email library
    // For demo purposes, we'll just log the email
    error_log("Password reset email sent to: $to\nReset link: $reset_link");
    
    $response = [
        'success' => true,
        'message' => 'If an account with that email exists, a password reset link has been sent.'
    ];
    
} catch (Exception $e) {
    error_log('Password reset error: ' . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();
