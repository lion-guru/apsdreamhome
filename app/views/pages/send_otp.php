<?php
/**
 * Send OTP Service - APS Dream Homes
 * Migrated from resources/views/Views/send_otp.php
 * Uses EmailService for sending password reset links/OTPs
 */

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/../../../includes/email_service.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email is required']);
    exit();
}

// Generate a random 6-digit OTP
$otp = \App\Helpers\SecurityHelper::secureRandomInt(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['reset_email'] = $email;

// For password reset link (legacy compatibility)
$key = md5(2418 * 2 + $email);
$expDate = date("Y-m-d H:i:s", strtotime('+1 hour'));

try {
    $db = \App\Core\App::database();
    // Update or insert password reset temp
    $db->execute(
        "INSERT INTO password_reset_temp (email, `key`, expDate) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `key` = ?, expDate = ?",
        [$email, $key, $expDate, $key, $expDate]
    );

    // Send email using EmailService
    $emailService = new EmailService();

    $subject = "Password Reset OTP - APS Dream Homes";
    $body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
            <h2 style='color: #1e3c72;'>APS Dream Homes</h2>
            <p>Hello,</p>
            <p>You requested a password reset. Your One-Time Password (OTP) is:</p>
            <div style='background: #f4f4f4; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #333;'>
                $otp
            </div>
            <p style='margin-top: 20px;'>This OTP is valid for 1 hour. If you did not request this, please ignore this email.</p>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='font-size: 12px; color: #777;'>&copy; " . date('Y') . " APS Dream Homes. All rights reserved.</p>
        </div>
    ";

    $result = $emailService->send($email, $subject, $body);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'OTP has been sent to your email address.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send email. Please try again later.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
