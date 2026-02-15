<?php
/**
 * APS Dream Home - Test Email API
 * API endpoint for testing email functionality
 */

require_once '../includes/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Only POST requests allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

$email = $input['email'] ?? '';

if (empty($email)) {
    echo json_encode(['error' => 'Email address is required']);
    exit;
}

try {
    // Test email
    $email_system = new EmailSystem();
    $result = $email_system->sendNotification(
        $email,
        'APS Dream Home - System Test',
        'This is a test email from APS Dream Home management dashboard. If you received this, the email system is working correctly!'
    );

    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message'] ?? 'Email test completed',
        'error' => $result['error'] ?? null
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Email test failed: ' . $e->getMessage()]);
}
