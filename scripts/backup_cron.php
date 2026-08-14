<?php
/**
 * Cron job: Daily full MySQL backup
 *
 * Run daily at 02:00 (or whatever schedule you want):
 *   Windows Task Scheduler / Linux cron:
 *   C:\xampp\php\php.exe C:\xampp\htdocs\apsdreamhome\scripts\backup_cron.php
 *
 * - Calls BackupRestoreService::createFullBackup() to dump every table
 *   into <STORAGE_PATH>/backups/full_backup_YYYY-MM-DD_HH-MMSS.sql.gz
 * - Logs every step to logs/backup_cron.log
 * - Cleans up backup files older than 30 days
 * - On failure, emails the admin (uses the same EmailService as the rest
 *   of the app; falls back to error_log if SMTP is not configured)
 *
 * Exit code 0 = success, 1 = failure.
 */

use App\Services\BackupRestoreService;
use App\Services\EmailService;

$startTime   = microtime(true);
$logFile     = __DIR__ . '/../logs/backup_cron.log';
$projectRoot = dirname(__DIR__);

// Minimal constants needed by the services we call.
if (!defined('APP_ROOT'))     define('APP_ROOT', $projectRoot);
if (!defined('STORAGE_PATH')) define('STORAGE_PATH', $projectRoot . '/storage');
if (!defined('APP_PATH'))     define('APP_PATH', $projectRoot . '/app');

if (!is_dir(dirname($logFile))) {
    @mkdir(dirname($logFile), 0775, true);
}

// Manual autoloader for CLI mode (no framework bootstrap in cron).
// PSR-4 mapping: App\Foo\Bar => app/Foo/Bar.php
spl_autoload_register(function ($class) {
    $prefix  = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

function logMsg($msg) {
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND);
}

function notifyAdminFailure($subject, $body) {
    try {
        $adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@apsdreamhome.com';
        $mailer = new EmailService();
        $mailer->send($adminEmail, $subject, $body);
    } catch (\Throwable $e) {
        // Don't let mail failure mask the real error.
        logMsg("Admin notification email failed: " . $e->getMessage());
    }
}

logMsg('=== Daily backup cron started ===');

try {
    $service = new BackupRestoreService();
    $result  = $service->createFullBackup();

    if (!empty($result['success'])) {
        $elapsed = round(microtime(true) - $startTime, 2);
        logMsg("Backup OK: {$result['file']} ({$result['size']}, {$result['tables']} tables, sha256={$result['checksum']})");
        logMsg("=== Backup cron completed in {$elapsed}s ===");
        // Parse "4.23 MB" or "423 KB" into a numeric MB value
        $sizeMb = 0.0;
        if (!empty($result['size']) && preg_match('/^([\d.]+)\s*([KMGT]?B)$/i', trim($result['size']), $m)) {
            $unit = strtoupper($m[2]);
            $mult = 1.0;
            if ($unit === 'KB') {
                $mult = 1 / 1024;
            } elseif ($unit === 'GB') {
                $mult = 1024;
            } elseif ($unit === 'TB') {
                $mult = 1024 * 1024;
            }
            $sizeMb = round((float)$m[1] * $mult, 2);
        }
        $jsonOut = [
            'success'    => true,
            'backup_id'  => $result['backup_id'] ?? null,
            'file'       => $result['file'] ?? null,
            'size_mb'    => $sizeMb,
            'tables'     => $result['tables'] ?? null,
            'checksum'   => $result['checksum'] ?? null,
            'duration_s' => $elapsed,
            'log'        => 'logs/backup_cron.log',
        ];
        logMsg('Result: ' . json_encode($jsonOut));
        echo PHP_EOL . json_encode($jsonOut, JSON_PRETTY_PRINT) . PHP_EOL;
        exit(0);
    }

    $error = $result['error'] ?? 'Unknown error from BackupRestoreService';
    logMsg("Backup FAILED: {$error}");
    notifyAdminFailure(
        '[APS Backup] FAILED at ' . date('Y-m-d H:i:s'),
        "<h2>APS Dream Home Daily Backup FAILED</h2>" .
        "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>" .
        "<p><strong>Error:</strong> " . htmlspecialchars($error) . "</p>" .
        "<p>Check the full log: <code>logs/backup_cron.log</code></p>"
    );
    logMsg('=== Backup cron failed ===');
    $elapsed = round(microtime(true) - $startTime, 2);
    $jsonOut = [
        'success'    => false,
        'error'      => $error,
        'duration_s' => $elapsed,
        'log'        => 'logs/backup_cron.log',
    ];
    logMsg('Result: ' . json_encode($jsonOut));
    echo PHP_EOL . json_encode($jsonOut, JSON_PRETTY_PRINT) . PHP_EOL;
    exit(1);
} catch (\Throwable $e) {
    logMsg('FATAL: ' . $e->getMessage());
    logMsg('Trace: ' . $e->getTraceAsString());
    notifyAdminFailure(
        '[APS Backup] FATAL at ' . date('Y-m-d H:i:s'),
        "<h2>APS Dream Home Daily Backup CRASHED</h2>" .
        "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>" .
        "<p><strong>Exception:</strong> " . htmlspecialchars($e->getMessage()) . "</p>" .
        "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>"
    );
    $elapsed = round(microtime(true) - $startTime, 2);
    $jsonOut = [
        'success'    => false,
        'error'      => $e->getMessage(),
        'duration_s' => $elapsed,
        'log'        => 'logs/backup_cron.log',
    ];
    logMsg('Result: ' . json_encode($jsonOut));
    echo PHP_EOL . json_encode($jsonOut, JSON_PRETTY_PRINT) . PHP_EOL;
    exit(1);
}?>