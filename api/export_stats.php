<?php
/**
 * System Statistics Export API
 * Exports system statistics in CSV format
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
        $groupBy = 'DATE(timestamp)';
        break;
    case '30d':
        $interval = '30 DAY';
        $groupBy = 'DATE(timestamp)';
        break;
    default: // 24h
        $interval = '24 HOUR';
        $groupBy = 'HOUR(timestamp)';
        break;
}

try {
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="system_stats_' . date('Y-m-d') . '.csv"');

    // Create output handle
    $output = fopen('php://output', 'w');

    // Write headers
    fputcsv($output, [
        'Timestamp',
        'System',
        'Total Events',
        'Success Rate',
        'Avg Response Time (s)',
        'Details'
    ]);

    // Get stats for each system
    $systems = ['followup', 'visit', 'ai', 'lead'];
    
    foreach ($systems as $system) {
        $query = "SELECT 
                     $groupBy as timestamp,
                     COUNT(*) as total,
                     AVG(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_rate,
                     AVG(response_time) as avg_response,
                     GROUP_CONCAT(DISTINCT event) as events
                 FROM system_logs
                 WHERE system = ?
                   AND timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
                 GROUP BY $groupBy";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $system);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['timestamp'],
                ucfirst($system),
                $row['total'],
                number_format($row['success_rate'] * 100, 2) . '%',
                round($row['avg_response'], 2),
                $row['events']
            ]);
        }
    }

    // Get performance metrics
    fputcsv($output, []); // Empty line
    fputcsv($output, ['Performance Metrics']);
    fputcsv($output, ['System', 'Avg Response Time', 'Success Rate', 'Total Events']);

    $perf_query = "SELECT 
                      system,
                      AVG(response_time) as avg_response,
                      AVG(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_rate,
                      COUNT(*) as total
                   FROM system_logs
                   WHERE timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
                   GROUP BY system";
    
    $result = $conn->query($perf_query);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            ucfirst($row['system']),
            round($row['avg_response'], 2) . 's',
            number_format($row['success_rate'] * 100, 2) . '%',
            $row['total']
        ]);
    }

    // Get error summary
    fputcsv($output, []); // Empty line
    fputcsv($output, ['Error Summary']);
    fputcsv($output, ['System', 'Error Type', 'Count', 'Last Occurrence']);

    $error_query = "SELECT 
                       system,
                       event as error_type,
                       COUNT(*) as count,
                       MAX(timestamp) as last_occurrence
                    FROM system_logs
                    WHERE status = 'error'
                      AND timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
                    GROUP BY system, event
                    ORDER BY count DESC";
    
    $result = $conn->query($error_query);
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            ucfirst($row['system']),
            $row['error_type'],
            $row['count'],
            $row['last_occurrence']
        ]);
    }

    fclose($output);

} catch (Exception $e) {
    error_log("Stats export error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to export statistics']);
}
?>
