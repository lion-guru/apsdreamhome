<?php
/**
 * Alert Analytics Export API
 * Exports alert analytics data in CSV format
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
        $groupBy = 'DATE(created_at)';
        break;
    case '30d':
        $interval = '30 DAY';
        $groupBy = 'DATE(created_at)';
        break;
    default: // 24h
        $interval = '24 HOUR';
        $groupBy = 'HOUR(created_at)';
        break;
}

try {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="alert_analytics_' . date('Y-m-d') . '.csv"');

    // Create output handle
    $output = fopen('php://output', 'w');

    // Export Alert Summary
    fputcsv($output, ['Alert Summary']);
    fputcsv($output, ['Metric', 'Value', 'Trend']);

    // Get summary data
    $summary_query = "SELECT 
                         COUNT(CASE WHEN level = 'critical' THEN 1 END) as critical,
                         COUNT(CASE WHEN level = 'warning' THEN 1 END) as warning,
                         AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(resolved_at, NOW()))) as avg_resolution,
                         COUNT(*) / 24 as hourly_rate
                     FROM system_alerts
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)";
    
    $summary = $conn->query($summary_query)->fetch_assoc();

    // Get previous period data for trends
    $prev_summary_query = str_replace(
        'WHERE created_at >=',
        'WHERE created_at BETWEEN DATE_SUB(NOW(), INTERVAL 2 * ' . $interval . ') AND',
        $summary_query
    );
    
    $prev_summary = $conn->query($prev_summary_query)->fetch_assoc();

    // Write summary data
    fputcsv($output, ['Critical Alerts', $summary['critical'], calculateTrend($summary['critical'], $prev_summary['critical']) . '%']);
    fputcsv($output, ['Warning Alerts', $summary['warning'], calculateTrend($summary['warning'], $prev_summary['warning']) . '%']);
    fputcsv($output, ['Avg Resolution Time', round($summary['avg_resolution']) . 'm', calculateTrend($prev_summary['avg_resolution'], $summary['avg_resolution']) . '%']);
    fputcsv($output, ['Alert Rate', round($summary['hourly_rate'], 1) . '/hr', calculateTrend($summary['hourly_rate'], $prev_summary['hourly_rate']) . '%']);
    fputcsv($output, []); // Empty line

    // Export Alert Timeline
    fputcsv($output, ['Alert Timeline']);
    fputcsv($output, ['Timestamp', 'Critical Alerts', 'Warning Alerts', 'Info Alerts', 'Total Alerts']);

    $timeline_query = "SELECT 
                          $groupBy as timestamp,
                          COUNT(CASE WHEN level = 'critical' THEN 1 END) as critical,
                          COUNT(CASE WHEN level = 'warning' THEN 1 END) as warning,
                          COUNT(CASE WHEN level = 'info' THEN 1 END) as info,
                          COUNT(*) as total
                      FROM system_alerts
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                      GROUP BY $groupBy
                      ORDER BY timestamp ASC";
    
    $timeline_result = $conn->query($timeline_query);
    while ($row = $timeline_result->fetch_assoc()) {
        fputcsv($output, [
            $row['timestamp'],
            $row['critical'],
            $row['warning'],
            $row['info'],
            $row['total']
        ]);
    }
    fputcsv($output, []); // Empty line

    // Export System Performance
    fputcsv($output, ['System Performance']);
    fputcsv($output, ['System', 'Total Alerts', 'Avg Response Time', 'Error Rate', 'Resolution Rate']);

    $performance_query = "SELECT 
                            a.system,
                            COUNT(*) as total_alerts,
                            AVG(l.response_time) as avg_response,
                            SUM(CASE WHEN l.status = 'error' THEN 1 ELSE 0 END) / COUNT(*) * 100 as error_rate,
                            SUM(CASE WHEN a.resolved_at IS NOT NULL THEN 1 ELSE 0 END) / COUNT(*) * 100 as resolution_rate
                         FROM system_alerts a
                         LEFT JOIN system_logs l ON a.system = l.system
                         WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                         GROUP BY a.system";
    
    $performance_result = $conn->query($performance_query);
    while ($row = $performance_result->fetch_assoc()) {
        fputcsv($output, [
            $row['system'],
            $row['total_alerts'],
            round($row['avg_response']) . 'ms',
            round($row['error_rate'], 1) . '%',
            round($row['resolution_rate'], 1) . '%'
        ]);
    }
    fputcsv($output, []); // Empty line

    // Export Top Issues
    fputcsv($output, ['Top Issues']);
    fputcsv($output, ['System', 'Issue', 'Occurrences', 'Avg Resolution Time', 'Last Occurrence', 'Trend']);

    $issues_query = "SELECT 
                        a.system,
                        a.message as issue,
                        COUNT(*) as occurrences,
                        AVG(TIMESTAMPDIFF(MINUTE, a.created_at, COALESCE(a.resolved_at, NOW()))) as avg_resolution,
                        MAX(a.created_at) as last_occurrence,
                        (
                            SELECT COUNT(*)
                            FROM system_alerts b
                            WHERE b.system = a.system
                              AND b.message = a.message
                              AND b.created_at BETWEEN 
                                  DATE_SUB(NOW(), INTERVAL 2 * $interval) AND
                                  DATE_SUB(NOW(), INTERVAL $interval)
                        ) as prev_occurrences
                    FROM system_alerts a
                    WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                    GROUP BY a.system, a.message
                    ORDER BY occurrences DESC
                    LIMIT 20";
    
    $issues_result = $conn->query($issues_query);
    while ($row = $issues_result->fetch_assoc()) {
        fputcsv($output, [
            $row['system'],
            $row['issue'],
            $row['occurrences'],
            round($row['avg_resolution']) . 'm',
            $row['last_occurrence'],
            calculateTrend($row['occurrences'], $row['prev_occurrences']) . '%'
        ]);
    }

    fclose($output);

} catch (Exception $e) {
    error_log("Alert analytics export error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to export alert analytics']);
}

/**
 * Calculate percentage trend between current and previous values
 */
function calculateTrend($current, $previous) {
    if ($previous == 0) {
        return $current > 0 ? 100 : 0;
    }
    return round((($current - $previous) / $previous) * 100, 1);
}
?>
