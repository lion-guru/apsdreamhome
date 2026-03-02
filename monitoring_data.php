<?php
/**
 * APS Dream Home - Monitoring Data API
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config/paths.php';
require_once APP_PATH . '/Monitoring/APM.php';

$apm = App\Monitoring\APM::getInstance();
$metrics = $apm->getMetrics();

// Calculate additional metrics
$requestsPerMinute = calculateRequestsPerMinute();
$errorRate = calculateErrorRate();
$responseTimeHistory = getResponseTimeHistory();

$data = [
    'requests_per_minute' => $requestsPerMinute,
    'avg_response_time' => round($metrics['request']['avg_time'], 2),
    'memory_usage' => formatBytes($metrics['memory']['current_usage']),
    'error_rate' => $errorRate,
    'response_time_history' => $responseTimeHistory,
    'system_resources' => [
        'cpu' => round($metrics['performance']['cpu_usage'], 2),
        'memory' => round(($metrics['memory']['current_usage'] / (1024 * 1024 * 1024)) * 100, 2),
        'disk' => round($metrics['performance']['disk_usage'], 2)
    ],
    'database_metrics' => [
        'queries' => $metrics['database']['queries'],
        'avg_query_time' => round($metrics['database']['avg_time'], 2),
        'slow_queries' => $metrics['database']['slow_queries']
    ],
    'error_metrics' => [
        'total_errors' => $metrics['errors']['count'],
        'error_types' => $metrics['errors']['types']
    ]
];

echo json_encode($data);

function calculateRequestsPerMinute()
{
    $logFile = BASE_PATH . '/logs/apm_requests.log';
    if (!file_exists($logFile)) return 0;
    
    $oneMinuteAgo = time() - 60;
    $count = 0;
    
    $handle = fopen($logFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $entry = json_decode($line, true);
            if ($entry && strtotime($entry['timestamp']) >= $oneMinuteAgo) {
                $count++;
            }
        }
        fclose($handle);
    }
    
    return $count;
}

function calculateErrorRate()
{
    $logFile = BASE_PATH . '/logs/apm_errors.log';
    if (!file_exists($logFile)) return 0;
    
    $oneMinuteAgo = time() - 60;
    $count = 0;
    
    $handle = fopen($logFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $entry = json_decode($line, true);
            if ($entry && strtotime($entry['timestamp']) >= $oneMinuteAgo) {
                $count++;
            }
        }
        fclose($handle);
    }
    
    return $count;
}

function getResponseTimeHistory()
{
    $logFile = BASE_PATH . '/logs/apm_requests.log';
    if (!file_exists($logFile)) return [];
    
    $fiveMinutesAgo = time() - 300;
    $times = [];
    
    $handle = fopen($logFile, 'r');
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            $entry = json_decode($line, true);
            if ($entry && strtotime($entry['timestamp']) >= $fiveMinutesAgo) {
                $times[] = $entry['duration'];
            }
        }
        fclose($handle);
    }
    
    // Return last 60 data points (5 minutes with 5-second intervals)
    return array_slice(array_reverse($times), 0, 60);
}

function formatBytes($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
