<?php
/**
 * Test Notification API
 * Sends test notifications to verify user settings
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/db_settings.php';
require_once __DIR__ . '/../includes/classes/SmsNotifier.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $conn = get_db_connection();
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Get user details
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        throw new Exception("User not found");
    }

    // Send test email
    $subject = "Test Notification - " . SITE_NAME;
    $body = "This is a test notification from " . SITE_NAME . ".\n\n";
    $body .= "If you received this message, your email notifications are working correctly.\n";
    $body .= "Time: " . date('Y-m-d H:i:s') . "\n";

    $headers = [
        'From: ' . SITE_NAME . ' <' . SUPPORT_EMAIL . '>',
        'Reply-To: ' . SUPPORT_EMAIL,
        'X-Mailer: PHP/' . phpversion()
    ];

    $email_sent = mail($user['email'], $subject, $body, implode("\r\n", $headers));

    // Send test SMS if enabled
    $sms_sent = false;
    if ($user['sms_enabled'] && !empty($user['phone'])) {
        $smsNotifier = new SmsNotifier();
        $message = SITE_NAME . " Test: Your SMS notifications are working correctly.";
        $sms_sent = $smsNotifier->send($user['phone'], $message);
    }

    // Log test notifications
    $query = "INSERT INTO system_logs (
                 system,
                 event,
                 status,
                 details,
                 timestamp
             ) VALUES (?, 'test_notification', ?, ?, NOW())";

    $system = 'notification';
    $status = ($email_sent && (!$user['sms_enabled'] || $sms_sent)) ? 'success' : 'error';
    $details = json_encode([
        'email_sent' => $email_sent,
        'sms_enabled' => $user['sms_enabled'],
        'sms_sent' => $sms_sent
    ]);

    $stmt = $conn->prepare($query);
    $stmt->bind_param('sss', $system, $status, $details);
    $stmt->execute();

    echo json_encode([
        'status' => 'success',
        'message' => 'Test notifications sent successfully',
        'details' => [
            'email_sent' => $email_sent,
            'sms_sent' => $sms_sent
        ]
    ]);

} catch (Exception $e) {
    error_log("Test notification error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
