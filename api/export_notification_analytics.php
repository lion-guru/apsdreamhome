<?php
/**
 * Export Notification Analytics API
 * Exports notification analytics data in CSV format
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/db_settings.php';

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

    // Get time range
    $range = $_GET['range'] ?? '24h';
    switch ($range) {
        case '7d':
            $interval = '7 DAY';
            break;
        case '30d':
            $interval = '30 DAY';
            break;
        default:
            $interval = '24 HOUR';
    }

    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="notification_analytics_' . date('Y-m-d') . '.csv"');

    // Create output handle
    $output = fopen('php://output', 'w');

    // Export Summary
    fputcsv($output, ['Summary Statistics']);
    fputcsv($output, ['Metric', 'Value']);

    $summary = getSummaryStats($conn, $interval);
    foreach ($summary as $key => $value) {
        fputcsv($output, [ucfirst(str_replace('_', ' ', $key)), $value]);
    }

    fputcsv($output, []); // Empty line for separation

    // Export Delivery Stats
    fputcsv($output, ['Delivery Statistics']);
    fputcsv($output, ['Type', 'Total Sent', 'Success Rate (%)', 'Avg Delivery Time (ms)', 'Failure Rate (%)', 'Top Error']);

    $deliveryStats = getDeliveryStats($conn, $interval);
    foreach ($deliveryStats as $stat) {
        fputcsv($output, [
            $stat['type'],
            $stat['total'],
            $stat['successRate'],
            $stat['avgDeliveryTime'],
            $stat['failureRate'],
            $stat['topError']
        ]);
    }

    fputcsv($output, []);

    // Export Volume Data
    fputcsv($output, ['Notification Volume']);
    fputcsv($output, ['Time', 'Count']);

    $volumeData = getVolumeData($conn, $interval);
    foreach ($volumeData['labels'] as $i => $label) {
        fputcsv($output, [$label, $volumeData['data'][$i]]);
    }

    fputcsv($output, []);

    // Export Type Distribution
    fputcsv($output, ['Notification Types Distribution']);
    fputcsv($output, ['Type', 'Count']);

    $typesData = getTypesData($conn, $interval);
    foreach ($typesData['labels'] as $i => $label) {
        fputcsv($output, [$label, $typesData['data'][$i]]);
    }

    fputcsv($output, []);

    // Export Recent Failures
    fputcsv($output, ['Recent Failures']);
    fputcsv($output, ['Time', 'Type', 'Recipient', 'Error', 'Attempts', 'Status']);

    $failures = getRecentFailures($conn);
    foreach ($failures as $failure) {
        fputcsv($output, [
            $failure['time'],
            $failure['type'],
            $failure['recipient'],
            $failure['error'],
            $failure['attempts'],
            $failure['status']
        ]);
    }

    fclose($output);

} catch (Exception $e) {
    error_log("Export notification analytics error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}

/**
 * Get summary statistics
 */
function getSummaryStats($conn, $interval) {
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as success_rate,
                SUM(CASE WHEN type LIKE '%sms%' AND status = 'success' THEN 1 ELSE 0 END) * 100.0 / 
                    NULLIF(SUM(CASE WHEN type LIKE '%sms%' THEN 1 ELSE 0 END), 0) as sms_rate
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)";

    $stats = $conn->query($query)->fetch_assoc();

    // Get queue stats
    $queueQuery = "SELECT 
                     COUNT(*) as pending,
                     MAX(TIMESTAMPDIFF(MINUTE, created_at, NOW())) as max_age
                   FROM notification_queue 
                   WHERE status = 'pending'";
    
    $queueStats = $conn->query($queueQuery)->fetch_assoc();

    return [
        'Total Notifications' => (int)$stats['total'],
        'Success Rate' => round($stats['success_rate'], 1) . '%',
        'SMS Delivery Rate' => round($stats['sms_rate'], 1) . '%',
        'Pending in Queue' => (int)$queueStats['pending'],
        'Max Queue Age' => $queueStats['max_age'] . ' minutes'
    ];
}

/**
 * Get delivery statistics
 */
function getDeliveryStats($conn, $interval) {
    $query = "SELECT 
                type,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as success_rate,
                AVG(CASE 
                    WHEN status = 'success' 
                    THEN TIMESTAMPDIFF(MILLISECOND, created_at, processed_at)
                    ELSE NULL 
                END) as avg_delivery_time,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as failure_rate,
                MAX(error_message) as top_error
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
              GROUP BY type";

    $result = $conn->query($query);
    $stats = [];

    while ($row = $result->fetch_assoc()) {
        $stats[] = [
            'type' => $row['type'],
            'total' => (int)$row['total'],
            'successRate' => round($row['success_rate'], 1),
            'avgDeliveryTime' => round($row['avg_delivery_time']),
            'failureRate' => round($row['failure_rate'], 1),
            'topError' => $row['top_error'] ?? 'None'
        ];
    }

    return $stats;
}

/**
 * Get volume data
 */
function getVolumeData($conn, $interval) {
    $query = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as label,
                COUNT(*) as count
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
              GROUP BY label
              ORDER BY label";

    $result = $conn->query($query);
    $labels = [];
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['label'];
        $data[] = (int)$row['count'];
    }

    return [
        'labels' => $labels,
        'data' => $data
    ];
}

/**
 * Get notification types distribution
 */
function getTypesData($conn, $interval) {
    $query = "SELECT 
                type,
                COUNT(*) as count
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
              GROUP BY type
              ORDER BY count DESC";

    $result = $conn->query($query);
    $labels = [];
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['type'];
        $data[] = (int)$row['count'];
    }

    return [
        'labels' => $labels,
        'data' => $data
    ];
}

/**
 * Get recent failures
 */
function getRecentFailures($conn) {
    $query = "SELECT 
                nl.created_at,
                nl.type,
                u.email,
                u.phone,
                nl.error_message,
                nq.attempts,
                nq.status
              FROM notification_logs nl
              LEFT JOIN users u ON nl.user_id = u.id
              LEFT JOIN notification_queue nq ON nl.id = nq.notification_id
              WHERE nl.status = 'error'
              ORDER BY nl.created_at DESC
              LIMIT 50";

    $result = $conn->query($query);
    $failures = [];

    while ($row = $result->fetch_assoc()) {
        $failures[] = [
            'time' => $row['created_at'],
            'type' => $row['type'],
            'recipient' => $row['email'] . ($row['phone'] ? ' / ' . $row['phone'] : ''),
            'error' => $row['error_message'],
            'attempts' => (int)$row['attempts'],
            'status' => $row['status'] ?? 'failed'
        ];
    }

    return $failures;
}
?>
