<?php
/**
 * Cron: Backup health check
 *
 * Verifies that a successful backup was completed within the last 24h.
 * If missing, writes a CRITICAL log line so monitoring / alert systems can
 * pick it up. Emits a JSON status line as the final output.
 *
 * Run hourly (or every few hours) via Windows Task Scheduler:
 *   C:\xampp\php\php.exe C:\xampp\htdocs\apsdreamhome\scripts\backup_health_check.php
 *
 * Exit codes:
 *   0 = healthy (backup in last 24h)
 *   1 = stale (no successful backup in last 24h)
 *   2 = config / DB error
 */

declare(strict_types=1);

use App\Core\Database\Database;

require_once __DIR__ . '/../config/bootstrap.php';

$logFile     = LOG_PATH . '/backup_health.log';
$maxAgeHours = (int) (getenv('BACKUP_MAX_AGE_HOURS') ?: 24);
$adminEmail  = getenv('ADMIN_EMAIL') ?: 'admin@apsdreamhome.com';
$fromEmail   = getenv('BACKUP_FROM_EMAIL') ?: 'noreply@apsdreamhome.com';

if (!is_dir(dirname($logFile))) {
    @mkdir(dirname($logFile), 0755, true);
}

function logHealth(string $msg, string $logFile): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND);
}

logHealth("=== Backup health check started (max age: {$maxAgeHours}h) ===", $logFile);

try {
    $db = Database::getInstance()->getConnection();

    // Try the main table; fall back to counting files in the backup dir
    $lastSuccess = null;
    try {
        $stmt = $db->prepare("SELECT id, file_path, file_size, started_at, completed_at FROM system_backups WHERE status = 'completed' ORDER BY completed_at DESC LIMIT 1");
        $stmt->execute();
        $lastSuccess = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        logHealth('system_backups query failed: ' . $e->getMessage(), $logFile);
    }

    $status         = 'ok';
    $lastBackup     = null;
    $ageHours       = null;
    $backupId       = null;
    $fileSizeBytes  = null;

    if ($lastSuccess) {
        $lastTs        = strtotime($lastSuccess['completed_at'] ?: $lastSuccess['started_at']);
        $lastBackup    = $lastSuccess['completed_at'] ?: $lastSuccess['started_at'];
        $ageHours      = $lastTs ? round((time() - $lastTs) / 3600, 2) : null;
        $backupId      = (int) $lastSuccess['id'];
        $fileSizeBytes = (int) ($lastSuccess['file_size'] ?? 0);

        if ($ageHours !== null && $ageHours > $maxAgeHours) {
            $status = 'stale';
        }
    } else {
        $status = 'stale';
    }

    // Also verify file still exists on disk
    $fileExists = false;
    if ($lastSuccess && !empty($lastSuccess['file_path'])) {
        $fileExists = file_exists($lastSuccess['file_path']);
        if (!$fileExists) {
            logHealth('WARNING: Last backup record points to a missing file: ' . $lastSuccess['file_path'], $logFile);
        }
    }

    $output = [
        'success'        => $status === 'ok',
        'status'         => $status,
        'last_backup'    => $lastBackup,
        'age_hours'      => $ageHours,
        'max_age_hours'  => $maxAgeHours,
        'backup_id'      => $backupId,
        'file_size'      => $fileSizeBytes,
        'file_exists'    => $fileExists,
        'checked_at'     => date('Y-m-d H:i:s'),
    ];

    if ($status === 'stale') {
        $reason = $lastSuccess
            ? "Last successful backup was {$ageHours}h ago (max {$maxAgeHours}h)"
            : 'No successful backup found in system_backups table';
        logHealth("CRITICAL: Backup is STALE. {$reason}", $logFile);

        // Email admin
        $headers  = "From: {$fromEmail}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $subject  = '[APS Dream Home] Backup STALE - ' . date('Y-m-d');
        $body     = "The backup health check found NO recent successful backup.\n\n"
                  . "Reason: {$reason}\n"
                  . "Last backup timestamp: " . ($lastBackup ?? 'NONE') . "\n"
                  . "Max allowed age: {$maxAgeHours} hours\n"
                  . "Checked at: " . date('Y-m-d H:i:s') . "\n\n"
                  . "Please verify storage/logs/backup_cron.log and run:\n"
                  . "  C:\\xampp\\php\\php.exe C:\\xampp\\htdocs\\apsdreamhome\\scripts\\backup_cron.php\n";
        $sent = @mail($adminEmail, $subject, $body, $headers);
        logHealth('Admin notify ' . ($sent ? 'sent' : 'FAILED (mail() unavailable)') . ": {$adminEmail}", $logFile);
    } else {
        logHealth("OK: last backup #{$backupId} {$ageHours}h ago, file " . ($fileExists ? 'exists' : 'MISSING'), $logFile);
    }

    logHealth('Result: ' . json_encode($output), $logFile);
    logHealth('=== Health check finished (' . strtoupper($status) . ") ===", $logFile);
    echo PHP_EOL . json_encode($output, JSON_PRETTY_PRINT) . PHP_EOL;
    exit($status === 'ok' ? 0 : 1);

} catch (Throwable $e) {
    logHealth('FATAL: ' . $e->getMessage(), $logFile);
    logHealth('Trace: ' . $e->getTraceAsString(), $logFile);
    $output = [
        'success' => false,
        'status'  => 'error',
        'error'   => $e->getMessage(),
    ];
    logHealth('Result: ' . json_encode($output), $logFile);
    exit(2);
}?>