<?php
/**
 * Cron job: Send email alerts for saved searches
 *
 * Run daily at 9:00 AM (or whatever schedule you want):
 *   Windows Task Scheduler / Linux cron:
 *   C:\xampp\php\php.exe C:\xampp\htdocs\apsdreamhome\scripts\daily_alerts_cron.php
 *
 * Or via the HTTP endpoint with the secret:
 *   GET /user/saved-searches/cron-alerts?key=YOUR_CRON_SECRET
 *
 * Tracks sent alerts in `search_alert_log` to prevent duplicates.
 */

use App\Services\SavedSearchService;

// Manual autoloader for CLI mode (no framework bootstrap in cron).
// PSR-4 mapping: App\Foo\Bar => app/Foo/Bar.php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    if (strpos($class, $prefix) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) require_once $file;
});

chdir(__DIR__ . '/..');

$startTime = microtime(true);
$logFile = __DIR__ . '/../logs/alerts_cron.log';

function logMsg($msg) {
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND);
}

logMsg('=== Daily search alerts cron started ===');

try {
    $service = new SavedSearchService();
    $stats = $service->sendAlerts();

    logMsg("Searches processed: {$stats['searches_processed']}");
    logMsg("Alerts sent:        {$stats['alerts_sent']}");
    logMsg("Alerts skipped:     {$stats['alerts_skipped_duplicate']} (duplicate)");
    logMsg("Alerts failed:      {$stats['alerts_failed']}");

    if (!empty($stats['errors'])) {
        logMsg("Errors:");
        foreach ($stats['errors'] as $err) logMsg("  - $err");
    }

    // Run cleanup: delete old unused saved searches (90+ days idle)
    $deleted = $service->cleanup(90);
    logMsg("Cleaned up $deleted stale saved search(es)");

    $elapsed = round(microtime(true) - $startTime, 2);
    logMsg("=== Cron completed in {$elapsed}s ===");
} catch (\Throwable $e) {
    logMsg("FATAL: " . $e->getMessage());
    logMsg("Trace: " . $e->getTraceAsString());
    exit(1);
}

exit(0);?>