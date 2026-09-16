<?php
// Log retention purge — prevents ab_events / visitor_page_views / csp_violations
// from growing unbounded (ab_events was 94k+ rows / 18.6MB).
// - ab_events: DELETE older than 90 days (created_at)
// - visitor_page_views: DELETE older than 60 days (created_at)
// - csp_violations: DELETE older than 30 days (received_at — table has no created_at)
// Runnable standalone: php scripts/cron_cleanup_logs.php
// Also wired as daily Task 10 in scripts/run_all_crons.php
// Returns ['deleted' => [...]] when included; echoes summary on CLI.
function cron_cleanup_logs(PDO $pdo): array
{
    $rules = [
        'ab_events'          => ['created_at', 90],
        'visitor_page_views' => ['created_at', 60],
        'csp_violations'     => ['received_at', 30],
    ];
    $deleted = [];
    foreach ($rules as $table => [$col, $days]) {
        try {
            $stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$col` < NOW() - INTERVAL " . (int)$days . " DAY");
            $stmt->execute();
            $deleted[$table] = $stmt->rowCount();
        } catch (Throwable $e) {
            error_log("cron_cleanup_logs: $table failed: " . $e->getMessage());
            $deleted[$table] = -1;
        }
    }
    return ['deleted' => $deleted];
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    $root = dirname(__DIR__);
    $config = require $root . '/config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $result = cron_cleanup_logs($pdo);
    foreach ($result['deleted'] as $t => $n) echo "$t: $n rows purged\n";
    echo "DONE cron_cleanup_logs\n";
}
