<?php
// Log retention purge — prevents volatile log tables from growing unbounded.
// - ab_events:          DELETE older than 90 days (created_at)
// - visitor_page_views: DELETE older than 60 days (created_at)
// - csp_violations:     DELETE older than 30 days (received_at — table has no created_at)
// - notification_logs:  DELETE older than 180 days (created_at)
// - audit_logs:         DELETE older than 365 days (created_at)
// Runnable standalone: php scripts/cron_cleanup_logs.php           (deletes per retention rules)
//                      php scripts/cron_cleanup_logs.php --dry-run   (counts only, no deletes)
// Also wired as daily Task 10 in scripts/run_all_crons.php
// Returns ['deleted' => [...]] when included; echoes summary on CLI.
function cron_cleanup_logs(PDO $pdo, bool $dryRun = false): array
{
    $rules = [
        'ab_events'          => ['created_at', 90],
        'visitor_page_views' => ['created_at', 60],
        'csp_violations'     => ['received_at', 30],
        'notification_logs'  => ['created_at', 180],
        'audit_logs'         => ['created_at', 365],
    ];
    $deleted = [];
    foreach ($rules as $table => [$col, $days]) {
        try {
            if ($dryRun) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE `$col` < NOW() - INTERVAL " . (int)$days . " DAY");
                $stmt->execute();
                $deleted[$table] = (int)$stmt->fetchColumn(); // would-delete count (no rows deleted)
            } else {
                $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$col` < NOW() - INTERVAL " . (int)$days . " DAY");
                $stmt->execute();
                $deleted[$table] = $stmt->rowCount();
            }
        } catch (Throwable $e) {
            error_log("cron_cleanup_logs: $table failed: " . $e->getMessage());
            $deleted[$table] = -1; // overloaded sentinel; callers must treat -1 as error
        }
    }
    return ['deleted' => $deleted, 'dry_run' => $dryRun];
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__);
    $config = require $root . '/config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $dryRun = in_array('--dry-run', $argv ?? [], true);
    $result = cron_cleanup_logs($pdo, $dryRun);
    foreach ($result['deleted'] as $t => $n) {
        echo $n === -1
            ? "$t: FAILED (see error log)\n"
            : ($dryRun ? "$t: would delete $n rows\n" : "$t: $n rows purged\n");
    }
    echo $dryRun ? "DRY RUN DONE - no rows deleted\n" : "DONE cron_cleanup_logs\n";
}
