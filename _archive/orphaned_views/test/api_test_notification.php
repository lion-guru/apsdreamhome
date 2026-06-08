<?php
header('Content-Type: application/json');

// Check if user is logged in as admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => h($mlSupport->translate('Unauthorized access'))]);
    exit();
}

// RBAC check: Only Super Admin and Manager can send test notifications
if (!hasRole('superadmin') && !hasRole('manager')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can send test notifications'))]);
    exit();
}

require_once ABSPATH . '/includes/email_service.php';
require_once ABSPATH . '/includes/notification_manager.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);

    // CSRF validation
    $csrf_token = $data['csrf_token'] ?? '';
    if (!verifyCSRFToken($csrf_token)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => h($mlSupport->translate('Security validation failed'))]);
        exit();
    }

    $type = $data['type'] ?? '';
    $recipient = $data['recipient'] ?? '';
    $message = $data['message'] ?? 'This is a test notification from APS Dream Home Management Dashboard.';

    if (empty($type) || empty($recipient)) {
        throw new Exception($mlSupport->translate('Missing required parameters: type and recipient'));
    }

    $db = \App\Core\App::database();
    $emailService = new EmailService();
    $notificationManager = new NotificationManager($db, $emailService);

    $params = [
        'data' => [
            'name' => 'Admin Test',
            'test_time' => date('Y-m-d H:i:s'),
            'message' => h($message)
        ],
        'channels' => [$type]
    ];

    if ($type === 'email') {
        $params['email'] = h($recipient);
        $params['template'] = 'TEST_NOTIFICATION';
    } elseif ($type === 'sms') {
        $params['phone'] = h($recipient);
        $params['template'] = 'TEST_NOTIFICATION';
    } else {
        throw new Exception($mlSupport->translate('Invalid notification type'));
    }

    $result = $notificationManager->send($params);

    echo json_encode(['success' => true, 'message' => h($mlSupport->translate(ucfirst($type) . ' test sent successfully'))]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => h($e->getMessage())]);
}
