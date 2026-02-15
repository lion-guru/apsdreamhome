<?php
/**
 * APS Dream Home - Test WhatsApp API
 * API endpoint for testing WhatsApp functionality
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

$phone = $input['phone'] ?? '';
$message = $input['message'] ?? '';

if (empty($phone) || empty($message)) {
    echo json_encode(['error' => 'Phone number and message are required']);
    exit;
}

try {
    // Test WhatsApp message
    $result = sendWhatsAppMessage($phone, $message);

    echo json_encode([
        'success' => $result['success'],
        'message' => $result['message'] ?? 'WhatsApp test completed',
        'error' => $result['error'] ?? null
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'WhatsApp test failed: ' . $e->getMessage()]);
}

// Helper function to send WhatsApp message
function sendWhatsAppMessage($phone, $message) {
    try {
        $whatsapp = new WhatsAppIntegration();
        return $whatsapp->sendMessage($phone, $message);
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
