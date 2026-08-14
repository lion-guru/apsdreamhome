<?php
/**
 * Monitoring Cron
 *
 * Runs every 5 minutes via Windows Task Scheduler (see setup_monitoring_cron.ps1).
 *  - HealthAlertService::checkAll()  -> writes alerts to monitoring_alerts
 *  - Cleanup of old errors / alerts  -> retention 30 days
 *  - All output logged to storage/logs/monitoring_cron.log
 *
 * Usage:
 *   php scripts/monitoring_cron.php
 */

declare(strict_types=1);

// PSR-4 autoloader (App\* -> app/*)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    if (strpos($class, $prefix) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) require_once $file;
});

chdir(__DIR__ . '/..');

$logFile = __DIR__ . '/../storage/logs/monitoring_cron.log';
@mkdir(dirname($logFile), 0775, true);

$log = function (string $msg) use ($logFile) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    echo $line;
};

$log('=== monitoring_cron start ===');
$exitCode = 0;
$startedAt = microtime(true);

try {
    $health = new App\Services\Monitoring\HealthAlertService();
    $result = $health->checkAll();

    $log('Status: ' . ($result['status'] ?? 'unknown'));
    foreach (($result['checks'] ?? []) as $name => $check) {
        $log(sprintf(
            '  [%s] %-10s %s',
            strtoupper((string)($check['status'] ?? '?')),
            $name,
            (string)($check['message'] ?? '')
        ));
    }
} catch (Throwable $e) {
    $log('FATAL during checkAll(): ' . $e->getMessage());
    $exitCode = 1;
}

// Cleanup pass.
try {
    $errDel = App\Services\Monitoring\ErrorTrackerService::cleanup();
    $log("Cleanup: deleted {$errDel} old error rows");
} catch (Throwable $e) {
    $log('Error cleanup failed: ' . $e->getMessage());
}

try {
    $health2 = isset($health) ? $health : new App\Services\Monitoring\HealthAlertService();
    $alertDel = $health2->cleanup(30);
    $log("Cleanup: deleted {$alertDel} old alert rows");
} catch (Throwable $e) {
    $log('Alert cleanup failed: ' . $e->getMessage());
}

$elapsed = number_format((microtime(true) - $startedAt) * 1000, 1);
$log("Completed in {$elapsed}ms");
$log('=== monitoring_cron end ===');

exit($exitCode);?>