<?php
/**
 * APS Dream Home - Test WhatsApp Template API
 * API for testing WhatsApp message templates
 */

require_once '../includes/config.php';
require_once '../includes/whatsapp_templates.php';

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

$template_name = $input['template_name'] ?? '';
$phone = $input['phone'] ?? '';
$variables = $input['variables'] ?? [];

if (empty($template_name) || empty($phone)) {
    echo json_encode(['error' => 'Template name and phone number are required']);
    exit;
}

try {
    // Test the template message
    $result = sendWhatsAppTemplateMessage($phone, $template_name, $variables);

    if ($result['success']) {
        // Log successful template test
        logWhatsAppActivity('TEMPLATE_TEST', "Template: {$template_name}, Phone: {$phone}, Variables: " . json_encode($variables));
    }

    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message'] ?? 'Template test completed',
        'error' => $result['error'] ?? null
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Template test failed: ' . $e->getMessage()]);
}

/**
 * Log WhatsApp activity
 */
function logWhatsAppActivity($type, $message) {
    $log_file = '../logs/whatsapp_activity.log';
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'message' => $message,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];

    file_put_contents($log_file, json_encode($log_entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}
