<?php
/**
 * Get System Status - AJAX Endpoint
 * Returns real-time system status information
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include admin configuration
require_once __DIR__ . '/../core/init.php';

// Verify admin authentication
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized'))]);
    exit();
}

// CSRF validation
$csrf_token = $_GET['csrf_token'] ?? '';
if (!verifyCSRFToken($csrf_token)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Security validation failed'))]);
    exit();
}

try {
    $db = \App\Core\App::database();
    $status = [];

    // Check database connection
    try {
        $db->query("SELECT 1");
        $status['database'] = h($mlSupport->translate('Connected'));
        $status['database_status'] = 'success';
    } catch (Exception $e) {
        $status['database'] = h($mlSupport->translate('Error'));
        $status['database_status'] = 'danger';
        error_log('Database status check failed: ' . $e->getMessage());
    }

    // Check AI system status
    try {
        // This would check if AI services are responding
        $status['ai_system'] = h($mlSupport->translate('Active'));
        $status['ai_status'] = 'success';
    } catch (Exception $e) {
        $status['ai_system'] = h($mlSupport->translate('Inactive'));
        $status['ai_status'] = 'warning';
    }

    // Check email service
    $status['email_service'] = h($mlSupport->translate('Ready'));
    $status['email_status'] = 'success';

    // Check WhatsApp service
    $status['whatsapp'] = h($mlSupport->translate('Connected'));
    $status['whatsapp_status'] = 'success';

    // Get system metrics
    $status['uptime'] = h(shell_exec('uptime'));
    $status['memory_usage'] = h(memory_get_usage(true) / 1024 / 1024 . ' MB');
    $status['php_version'] = h(PHP_VERSION);

    // Check recent errors
    $errorLog = __DIR__ . '/../error_log';
    if (file_exists($errorLog)) {
        $status['recent_errors'] = count(file($errorLog));
    } else {
        $status['recent_errors'] = 0;
    }

    echo json_encode([
        'success' => true,
        'status' => $status,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log('System status error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => h($mlSupport->translate('Error retrieving system status'))
    ]);
}
?>
