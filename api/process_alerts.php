<?php
/**
 * Alert Processing API
 * Processes system alerts and notifications
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/db_settings.php';
require_once __DIR__ . '/../includes/classes/AlertManager.php';

// Get database connection
$conn = get_db_connection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Initialize alert manager
$alertManager = new AlertManager($conn);

// Process request
$action = $_POST['action'] ?? '';
$alert_id = $_POST['alert_id'] ?? 0;
$user_id = $_POST['user_id'] ?? 0;

try {
    switch ($action) {
        case 'check':
            // Check system health
            $alertManager->checkSystemHealth();
            echo json_encode([
                'status' => 'success',
                'alerts' => $alertManager->getActiveAlerts()
            ]);
            break;

        case 'acknowledge':
            // Acknowledge alert
            if (!$alert_id || !$user_id) {
                throw new Exception('Missing required parameters');
            }
            
            $success = $alertManager->acknowledgeAlert($alert_id, $user_id);
            echo json_encode([
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Alert acknowledged' : 'Failed to acknowledge alert'
            ]);
            break;

        case 'resolve':
            // Resolve alert
            if (!$alert_id || !$user_id) {
                throw new Exception('Missing required parameters');
            }
            
            $notes = $_POST['notes'] ?? '';
            $success = $alertManager->resolveAlert($alert_id, $user_id, $notes);
            echo json_encode([
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Alert resolved' : 'Failed to resolve alert'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("Alert processing error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
