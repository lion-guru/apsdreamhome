<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/email_service.php';
require_once __DIR__ . '/../includes/logger.php';

use App\Core\Database;

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // CSRF Protection
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Invalid CSRF token. Please refresh the page and try again.');
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        throw new Exception('Please provide a valid email address');
    }

    $db = \App\Core\App::database();

    // Check if user exists in the admin table
    $user = $db->fetch("SELECT id, auser as username, email FROM admin WHERE email = :email", ['email' => $email]);

    if (!$user) {
        // For security, don't reveal if email exists or not
        $response['message'] = 'If an account with that email exists, a password reset link has been sent.';
        $response['success'] = true;
        echo json_encode($response);
        exit();
    }

    // Generate a secure token
    $token = \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32));
    $expires = \date('Y-m-d H:i:s', \strtotime('+1 hour')); // Token valid for 1 hour

    // Store token in database
    if (!$db->execute("INSERT INTO password_resets (email, token, created_at, expires_at) VALUES (:email, :token, NOW(), :expires)", [
        'email' => $email,
        'token' => $token,
        'expires' => $expires
    ])) {
        throw new Exception('Failed to create reset token');
    }

    // Create reset link
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
    $reset_link = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/admin/reset-password-form.php?token=' . $token;

    // Send email using EmailService
    $logger = new Logger();
    $emailService = new EmailService($logger, $db->getConnection());

    $subject = 'Password Reset Request - APS Dream Homes';
    $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;'>
            <h2 style='color: #0d6efd;'>Password Reset Request</h2>
            <p>Hello " . h($user['username']) . ",</p>
            <p>You have requested to reset your password. Please click the link below to set a new password:</p>
            <p style='text-align: center; margin: 30px 0;'>
                <a href='$reset_link' style='background-color: #0d6efd; color: white; padding: 12px 25px; text-align: center; text-decoration: none; display: inline-block; border-radius: 4px; font-weight: bold;'>Reset Password</a>
            </p>
            <p>Or copy and paste this link into your browser:</p>
            <p style='background: #f8f9fa; padding: 10px; border-radius: 4px;'><code>$reset_link</code></p>
            <p style='color: #666; font-size: 0.9em;'>This link will expire in 1 hour.</p>
            <p style='color: #666; font-size: 0.9em;'>If you did not request a password reset, please ignore this email.</p>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='font-size: 0.8em; color: #999;'>&copy; " . date('Y') . " APS Dream Homes. All rights reserved.</p>
        </div>
    ";

    if ($emailService->send($user['email'], $subject, $message)) {
        $response = [
            'success' => true,
            'message' => 'If an account with that email exists, a password reset link has been sent.'
        ];
    } else {
        throw new Exception('Failed to send reset email. Please try again later.');
    }
} catch (Exception $e) {
    error_log('Password reset error: ' . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit();
