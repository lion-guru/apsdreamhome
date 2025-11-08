<?php
/**
 * System Statistics API
 * Provides real-time system health and performance metrics
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
    // Get follow-up stats
    $followup_query = "SELECT 
                          COUNT(*) as count,
                          AVG(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_rate,
                          AVG(response_time) as avg_response
                      FROM system_logs 
                      WHERE system = 'followup'
                        AND timestamp >= DATE_SUB(NOW(), INTERVAL $interval)";
    
    $followup_result = $conn->query($followup_query)->fetch_assoc();
    
    // Get visit scheduling stats
    $visit_query = "SELECT 
                       COUNT(*) as count,
                       AVG(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as success_rate,
                       AVG(TIMESTAMPDIFF(SECOND, created_at, confirmed_at)) as avg_response
                   FROM bookings
                   WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)";
    
    $visit_result = $conn->query($visit_query)->fetch_assoc();
    
    // Get AI recommendation stats
    $ai_query = "SELECT 
                    COUNT(*) as count,
                    AVG(score) as avg_score,
                    AVG(processing_time) as avg_response
                 FROM ai_logs
                 WHERE type = 'property_recommendations'
                   AND timestamp >= DATE_SUB(NOW(), INTERVAL $interval)";
    
    $ai_result = $conn->query($ai_query)->fetch_assoc();
    
    // Get lead stats
    $lead_query = "SELECT 
                      COUNT(*) as count,
                      AVG(CASE WHEN status != 'new' THEN 1 ELSE 0 END) as processed_rate,
                      AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_response
                   FROM leads
                   WHERE created_at >= DATE_SUB(NOW(), INTERVAL $interval)";
    
    $lead_result = $conn->query($lead_query)->fetch_assoc();

    // Get timeline data
    $timeline_query = "SELECT 
                          $groupBy as label,
                          COUNT(CASE WHEN system = 'followup' THEN 1 END) as followups,
                          COUNT(CASE WHEN system = 'visit' THEN 1 END) as visits,
                          COUNT(CASE WHEN system = 'ai' THEN 1 END) as ai
                      FROM system_logs
                      WHERE timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
                      GROUP BY $groupBy
                      ORDER BY label ASC";
    
    $timeline_result = $conn->query($timeline_query);
    $timeline = [
        'labels' => [],
        'followups' => [],
        'visits' => [],
        'ai' => []
    ];
    
    while ($row = $timeline_result->fetch_assoc()) {
        $timeline['labels'][] = $row['label'];
        $timeline['followups'][] = (int)$row['followups'];
        $timeline['visits'][] = (int)$row['visits'];
        $timeline['ai'][] = (int)$row['ai'];
    }

    // Check system health
    $health_threshold = 0.95; // 95% success rate threshold
    
    echo json_encode([
        'followups' => [
            'count' => (int)$followup_result['count'],
            'status' => $followup_result['success_rate'] >= $health_threshold ? 'healthy' : 'issues',
            'performance' => round($followup_result['avg_response'])
        ],
        'visits' => [
            'count' => (int)$visit_result['count'],
            'status' => $visit_result['success_rate'] >= $health_threshold ? 'healthy' : 'issues',
            'performance' => round($visit_result['avg_response'])
        ],
        'ai' => [
            'count' => (int)$ai_result['count'],
            'status' => $ai_result['avg_score'] >= 0.7 ? 'healthy' : 'issues',
            'performance' => round($ai_result['avg_response'])
        ],
        'leads' => [
            'count' => (int)$lead_result['count'],
            'status' => $lead_result['processed_rate'] >= $health_threshold ? 'healthy' : 'issues',
            'performance' => round($lead_result['avg_response'])
        ],
        'timeline' => $timeline,
        'performance' => [
            'followups' => round($followup_result['avg_response']),
            'visits' => round($visit_result['avg_response']),
            'ai' => round($ai_result['avg_response']),
            'leads' => round($lead_result['avg_response'])
        ]
    ]);

} catch (Exception $e) {
    error_log("System stats error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch system statistics']);
}
?>
