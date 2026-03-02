<?php
/**
 * APS Dream Home - Error Data API
 */

header('Content-Type: application/json');

require_once __DIR__ . '/config/paths.php';
require_once APP_PATH . '/Monitoring/ErrorTracker.php';
require_once APP_PATH . '/Monitoring/AlertingSystem.php';

$errorTracker = App\Monitoring\ErrorTracker::getInstance();
$alertingSystem = App\Monitoring\AlertingSystem::getInstance();

$errorStats = $errorTracker->getErrorStats();
$recentErrors = $errorTracker->getRecentErrors(20);
$errorTrends = $errorTracker->getErrorTrends(24);
$activeAlerts = $alertingSystem->getActiveAlerts();

$data = [
    'critical_errors' => $errorStats['by_severity']['critical'] ?? 0,
    'high_errors' => $errorStats['by_severity']['high'] ?? 0,
    'medium_errors' => $errorStats['by_severity']['medium'] ?? 0,
    'low_errors' => $errorStats['by_severity']['low'] ?? 0,
    'total_errors' => $errorStats['total'],
    'error_types' => $errorStats['by_type'],
    'error_trends' => $errorTrends,
    'recent_errors' => $recentErrors,
    'active_alerts' => count($activeAlerts),
    'error_rate' => calculateErrorRate(),
    'most_common_error' => getMostCommonError($errorStats['by_type'])
];

echo json_encode($data);

function calculateErrorRate()
{
    $logFile = BASE_PATH . '/logs/error_tracking.log';
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

function getMostCommonError($errorTypes)
{
    if (empty($errorTypes)) return null;
    
    $maxCount = 0;
    $mostCommon = null;
    
    foreach ($errorTypes as $type => $count) {
        if ($count > $maxCount) {
            $maxCount = $count;
            $mostCommon = $type;
        }
    }
    
    return $mostCommon;
}
