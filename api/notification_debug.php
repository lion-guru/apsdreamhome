<?php
/**
 * Notification Debug API
 * Provides debugging data and tools for notification system
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/db_settings.php';
require_once __DIR__ . '/../includes/classes/NotificationTemplate.php';
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

    // Handle different actions
    $action = $_GET['action'] ?? 'status';

    switch ($action) {
        case 'status':
            echo json_encode([
                'queue' => getQueueStats($conn),
                'performance' => getPerformanceStats($conn),
                'logs' => getRecentLogs($conn),
                'anomalies' => detectAnomalies($conn)
            ]);
            break;

        case 'clear':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method");
            }
            clearLogs($conn);
            echo json_encode(['status' => 'success']);
            break;

        case 'export':
            exportLogs($conn);
            break;

        case 'test':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method");
            }
            $data = json_decode(file_get_contents('php://input'), true);
            sendTestNotification($conn, $data);
            echo json_encode(['status' => 'success', 'message' => 'Test notification sent']);
            break;

        default:
            throw new Exception("Invalid action");
    }

} catch (Exception $e) {
    error_log("Notification debug error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}

/**
 * Get queue statistics
 */
function getQueueStats($conn) {
    $query = "SELECT 
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
              FROM notification_queue
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";

    $result = $conn->query($query);
    return $result->fetch_assoc();
}

/**
 * Get performance statistics
 */
function getPerformanceStats($conn) {
    $query = "SELECT 
                AVG(TIMESTAMPDIFF(MICROSECOND, created_at, processed_at)) / 1000 as avg_processing_time,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as success_rate
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";

    $result = $conn->query($query);
    $stats = $result->fetch_assoc();

    return [
        'avgProcessingTime' => round($stats['avg_processing_time']),
        'successRate' => round($stats['success_rate'], 1)
    ];
}

/**
 * Get recent logs
 */
function getRecentLogs($conn) {
    $query = "SELECT 
                created_at as timestamp,
                CASE 
                    WHEN status = 'error' THEN 'error'
                    WHEN status = 'warning' THEN 'warning'
                    WHEN status = 'success' THEN 'success'
                    ELSE 'info'
                END as level,
                CONCAT(
                    '[', type, '] ',
                    CASE 
                        WHEN status = 'error' THEN CONCAT('Error: ', error_message)
                        WHEN status = 'warning' THEN 'Warning: ' || message
                        ELSE message
                    END
                ) as message
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
              ORDER BY created_at DESC
              LIMIT 100";

    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Detect anomalies in notification system
 */
function detectAnomalies($conn) {
    $anomalies = [];

    // Check for high failure rate
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";

    $result = $conn->query($query)->fetch_assoc();
    if ($result['total'] > 0) {
        $errorRate = ($result['errors'] / $result['total']) * 100;
        if ($errorRate > 20) {
            $anomalies[] = [
                'severity' => 'critical',
                'timestamp' => date('Y-m-d H:i:s'),
                'message' => 'High failure rate detected',
                'details' => "Error rate: {$errorRate}% in the last hour"
            ];
        }
    }

    // Check for queue backlog
    $query = "SELECT COUNT(*) as pending
              FROM notification_queue
              WHERE status = 'pending'
                AND created_at <= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";

    $result = $conn->query($query)->fetch_assoc();
    if ($result['pending'] > 100) {
        $anomalies[] = [
            'severity' => 'warning',
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => 'Large notification queue backlog',
            'details' => "{$result['pending']} notifications pending for >5 minutes"
        ];
    }

    // Check for slow processing
    $query = "SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, processed_at)) as avg_time
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                AND status = 'success'";

    $result = $conn->query($query)->fetch_assoc();
    if ($result['avg_time'] > 10) {
        $anomalies[] = [
            'severity' => 'warning',
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => 'Slow notification processing detected',
            'details' => "Average processing time: {$result['avg_time']} seconds"
        ];
    }

    // Check for repeated failures
    $query = "SELECT user_id, COUNT(*) as failures
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                AND status = 'error'
              GROUP BY user_id
              HAVING failures >= 3";

    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        $anomalies[] = [
            'severity' => 'warning',
            'timestamp' => date('Y-m-d H:i:s'),
            'message' => 'Repeated failures for user',
            'details' => "User ID {$row['user_id']} had {$row['failures']} failures in the last hour"
        ];
    }

    return $anomalies;
}

/**
 * Clear notification logs
 */
function clearLogs($conn) {
    // Only clear logs older than 1 hour
    $query = "DELETE FROM notification_logs 
              WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)";
    
    $conn->query($query);
}

/**
 * Export logs as CSV
 */
function exportLogs($conn) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="notification_logs_' . date('Y-m-d_H-i-s') . '.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Timestamp', 'Type', 'Status', 'Message', 'Error']);

    $query = "SELECT 
                created_at,
                type,
                status,
                message,
                error_message
              FROM notification_logs
              ORDER BY created_at DESC";

    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['created_at'],
            $row['type'],
            $row['status'],
            $row['message'],
            $row['error_message']
        ]);
    }

    fclose($output);
    exit;
}

/**
 * Send test notification
 */
function sendTestNotification($conn, $data) {
    $user = $conn->query("SELECT * FROM users WHERE id = " . (int)$data['recipient'])->fetch_assoc();
    if (!$user) {
        throw new Exception("User not found");
    }

    $templateManager = new NotificationTemplate($conn);
    $template = $templateManager->getTemplate($data['template']);
    if (!$template) {
        throw new Exception("Template not found");
    }

    $variables = [
        'site_name' => SITE_NAME,
        'timestamp' => date('Y-m-d H:i:s'),
        'test_id' => uniqid()
    ];

    // Send notifications based on type
    if (in_array($data['type'], ['email', 'both'])) {
        $subject = $templateManager->parseTemplate($template['email_subject'], $variables);
        $body = $templateManager->parseTemplate($template['email_body'], $variables);
        
        mail($user['email'], $subject, $body);
    }

    if (in_array($data['type'], ['sms', 'both']) && $user['phone']) {
        $smsBody = $templateManager->parseTemplate($template['sms_body'], $variables);
        $smsNotifier = new SmsNotifier();
        $smsNotifier->send($user['phone'], $smsBody);
    }

    // Log test notification
    $query = "INSERT INTO notification_logs (
                type,
                user_id,
                status,
                message,
                created_at
             ) VALUES (
                'test',
                ?,
                'success',
                'Test notification sent',
                NOW()
             )";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
}
?>
