<?php
/**
 * Alert Analytics API
 * Provides analytics data for system alerts
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
    // Get alert summary
    $summary_query = "SELECT 
                         COUNT(CASE WHEN level = 'critical' THEN 1 END) as critical,
                         COUNT(CASE WHEN level = 'warning' THEN 1 END) as warning,
                         AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(resolved_at, NOW()))) as avg_resolution,
                         COUNT(*) / 24 as hourly_rate
                     FROM system_alerts
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)";
    
    $summary = $conn->query($summary_query)->fetch_assoc();

    // Get trends (compare with previous period)
    $prev_summary_query = "SELECT 
                             COUNT(CASE WHEN level = 'critical' THEN 1 END) as critical,
                             COUNT(CASE WHEN level = 'warning' THEN 1 END) as warning,
                             AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(resolved_at, NOW()))) as avg_resolution,
                             COUNT(*) / 24 as hourly_rate
                         FROM system_alerts
                         WHERE created_at BETWEEN 
                             DATE_SUB(NOW(), INTERVAL 2 * $interval) AND
                             DATE_SUB(NOW(), INTERVAL $interval)";
    
    $prev_summary = $conn->query($prev_summary_query)->fetch_assoc();

    // Calculate trends
    $trends = [
        'critical' => calculateTrend($summary['critical'], $prev_summary['critical']),
        'warning' => calculateTrend($summary['warning'], $prev_summary['warning']),
        'resolution' => calculateTrend($prev_summary['avg_resolution'], $summary['avg_resolution']),
        'alertRate' => calculateTrend($summary['hourly_rate'], $prev_summary['hourly_rate'])
    ];

    // Get alert trends data
    $trends_query = "SELECT 
                        $groupBy as label,
                        COUNT(CASE WHEN level = 'critical' THEN 1 END) as critical,
                        COUNT(CASE WHEN level = 'warning' THEN 1 END) as warning
                    FROM system_alerts
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                    GROUP BY $groupBy
                    ORDER BY label ASC";
    
    $trends_result = $conn->query($trends_query);
    $chart_trends = [
        'labels' => [],
        'critical' => [],
        'warning' => []
    ];
    
    while ($row = $trends_result->fetch_assoc()) {
        $chart_trends['labels'][] = $row['label'];
        $chart_trends['critical'][] = (int)$row['critical'];
        $chart_trends['warning'][] = (int)$row['warning'];
    }

    // Get alert distribution
    $distribution_query = "SELECT 
                             level,
                             COUNT(*) as count
                         FROM system_alerts
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)
                         GROUP BY level";
    
    $distribution_result = $conn->query($distribution_query);
    $distribution = [
        'critical' => 0,
        'warning' => 0,
        'info' => 0
    ];
    
    while ($row = $distribution_result->fetch_assoc()) {
        $distribution[$row['level']] = (int)$row['count'];
    }

    // Get response times by system
    $response_query = "SELECT 
                          system,
                          AVG(response_time) as avg_response
                      FROM system_logs
                      WHERE timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
                      GROUP BY system";
    
    $response_result = $conn->query($response_query);
    $response_times = [
        'labels' => [],
        'values' => []
    ];
    
    while ($row = $response_result->fetch_assoc()) {
        $response_times['labels'][] = $row['system'];
        $response_times['values'][] = round($row['avg_response']);
    }

    // Get error rates by system
    $error_query = "SELECT 
                       system,
                       COUNT(*) as total,
                       SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors
                    FROM system_logs
                    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
                    GROUP BY system";
    
    $error_result = $conn->query($error_query);
    $error_rates = [
        'labels' => [],
        'values' => []
    ];
    
    while ($row = $error_result->fetch_assoc()) {
        $error_rates['labels'][] = $row['system'];
        $error_rates['values'][] = round(($row['errors'] / $row['total']) * 100, 2);
    }

    // Get top issues
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
                    LIMIT 10";
    
    $issues_result = $conn->query($issues_query);
    $top_issues = [];
    
    while ($row = $issues_result->fetch_assoc()) {
        $top_issues[] = [
            'system' => $row['system'],
            'issue' => $row['issue'],
            'occurrences' => (int)$row['occurrences'],
            'avgResolutionTime' => round($row['avg_resolution']) . 'm',
            'lastOccurrence' => date('Y-m-d H:i:s', strtotime($row['last_occurrence'])),
            'trend' => calculateTrend($row['occurrences'], $row['prev_occurrences'])
        ];
    }

    echo json_encode([
        'summary' => [
            'critical' => (int)$summary['critical'],
            'warning' => (int)$summary['warning'],
            'avgResolutionTime' => round($summary['avg_resolution']) . 'm',
            'alertRate' => round($summary['hourly_rate'], 1) . '/hr'
        ],
        'trends' => $trends,
        'charts' => [
            'trends' => $chart_trends,
            'distribution' => $distribution,
            'responseTimes' => $response_times,
            'errorRates' => $error_rates
        ],
        'topIssues' => $top_issues
    ]);

} catch (Exception $e) {
    error_log("Alert analytics error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch alert analytics']);
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
