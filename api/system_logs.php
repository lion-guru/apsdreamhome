<?php
/**
 * System Logs API
 * Retrieves system logs for monitoring
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/db_settings.php';

// Get database connection
$conn = get_db_connection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get time range
$range = $_GET['range'] ?? '24h';
switch ($range) {
    case '7d':
        $interval = '7 DAY';
        break;
    case '30d':
        $interval = '30 DAY';
        break;
    default: // 24h
        $interval = '24 HOUR';
        break;
}

try {
    // Get system logs
    $query = "SELECT 
                  timestamp,
                  system,
                  event,
                  status,
                  details
              FROM system_logs
              WHERE timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
              ORDER BY timestamp DESC
              LIMIT 100";
    
    $result = $conn->query($query);
    $logs = [];
    
    while ($row = $result->fetch_assoc()) {
        $logs[] = [
            'timestamp' => date('Y-m-d H:i:s', strtotime($row['timestamp'])),
            'system' => ucfirst($row['system']),
            'event' => $row['event'],
            'status' => $row['status'],
            'details' => $row['details']
        ];
    }

    echo json_encode([
        'status' => 'success',
        'logs' => $logs
    ]);

} catch (Exception $e) {
    error_log("System logs error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch system logs']);
}
?>
