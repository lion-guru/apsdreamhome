<?php
/**
 * Notification Analytics API
 * Provides analytics data for the notification dashboard
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
            $groupBy = 'DATE(created_at)';
            break;
        case '30d':
            $interval = '30 DAY';
            $groupBy = 'DATE(created_at)';
            break;
        default:
            $interval = '24 HOUR';
            $groupBy = 'HOUR(created_at)';
    }

    // Get summary statistics
    $summary = getSummaryStats($conn, $interval);
    
    // Get trends
    $trends = getTrends($conn, $interval);
    
    // Get volume data
    $volumeData = getVolumeData($conn, $interval, $groupBy);
    
    // Get notification types distribution
    $typesData = getTypesData($conn, $interval);
    
    // Get delivery statistics
    $deliveryStats = getDeliveryStats($conn, $interval);
    
    // Get recent failures
    $recentFailures = getRecentFailures($conn);

    echo json_encode([
        'summary' => $summary,
        'trends' => $trends,
        'charts' => [
            'volume' => $volumeData,
            'types' => $typesData
        ],
        'deliveryStats' => $deliveryStats,
        'recentFailures' => $recentFailures
    ]);

} catch (Exception $e) {
    error_log("Notification analytics error: " . $e->getMessage());
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
                    SUM(CASE WHEN type LIKE '%sms%' THEN 1 ELSE 0 END) as sms_rate
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)";

    $stats = $conn->query($query)->fetch_assoc();

    // Get queue health
    $queueQuery = "SELECT 
                     COUNT(*) as pending,
                     MAX(TIMESTAMPDIFF(MINUTE, created_at, NOW())) as max_age
                   FROM notification_queue 
                   WHERE status = 'pending'";
    
    $queueStats = $conn->query($queueQuery)->fetch_assoc();
    
    $queueHealth = 'Healthy';
    if ($queueStats['max_age'] > 30) {
        $queueHealth = 'Degraded';
    } elseif ($queueStats['max_age'] > 60) {
        $queueHealth = 'Critical';
    }

    return [
        'total' => (int)$stats['total'],
        'successRate' => round($stats['success_rate'], 1),
        'smsRate' => round($stats['sms_rate'], 1),
        'queueHealth' => $queueHealth,
        'queuePending' => (int)$queueStats['pending']
    ];
}

/**
 * Get trends compared to previous period
 */
function getTrends($conn, $interval) {
    $query = "SELECT 
                period,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) * 100.0 / COUNT(*) as success_rate,
                SUM(CASE WHEN type LIKE '%sms%' AND status = 'success' THEN 1 ELSE 0 END) * 100.0 / 
                    NULLIF(SUM(CASE WHEN type LIKE '%sms%' THEN 1 ELSE 0 END), 0) as sms_rate
              FROM (
                  SELECT 
                      CASE 
                          WHEN created_at >= DATE_SUB(NOW(), INTERVAL $interval) THEN 'current'
                          ELSE 'previous'
                      END as period,
                      *
                  FROM notification_logs
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval * 2)
              ) t
              GROUP BY period";

    $result = $conn->query($query);
    $periods = [];
    while ($row = $result->fetch_assoc()) {
        $periods[$row['period']] = $row;
    }

    $calculateTrend = function($current, $previous) {
        if (!$previous) return 0;
        return round((($current - $previous) / $previous) * 100, 1);
    };

    return [
        'total' => $calculateTrend(
            $periods['current']['total'] ?? 0,
            $periods['previous']['total'] ?? 0
        ),
        'success' => $calculateTrend(
            $periods['current']['success_rate'] ?? 0,
            $periods['previous']['success_rate'] ?? 0
        ),
        'sms' => $calculateTrend(
            $periods['current']['sms_rate'] ?? 0,
            $periods['previous']['sms_rate'] ?? 0
        )
    ];
}

/**
 * Get volume data for chart
 */
function getVolumeData($conn, $interval, $groupBy) {
    $query = "SELECT 
                $groupBy as label,
                COUNT(*) as count
              FROM notification_logs
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
              GROUP BY $groupBy
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
              ORDER BY count DESC
              LIMIT 5";

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
 * Get delivery statistics by type
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
 * Get recent failures
 */
function getRecentFailures($conn) {
    $query = "SELECT 
                created_at,
                type,
                recipient_id,
                error_message,
                attempts,
                status
              FROM notification_queue
              WHERE status = 'failed'
              ORDER BY created_at DESC
              LIMIT 10";

    $result = $conn->query($query);
    $failures = [];

    while ($row = $result->fetch_assoc()) {
        // Get recipient details
        $recipientQuery = "SELECT email, phone FROM users WHERE id = " . $row['recipient_id'];
        $recipient = $conn->query($recipientQuery)->fetch_assoc();

        $failures[] = [
            'time' => date('Y-m-d H:i:s', strtotime($row['created_at'])),
            'type' => $row['type'],
            'recipient' => $recipient['email'] . ($recipient['phone'] ? ' / ' . $recipient['phone'] : ''),
            'error' => $row['error_message'],
            'attempts' => (int)$row['attempts'],
            'status' => $row['status']
        ];
    }

    return $failures;
}
?>
