#!/usr/bin/env php
<?php
/**
 * DAILY METRICS REFRESH - Analytics v2 fact-table seeder
 * ======================================================
 * Records honest daily business metrics into `daily_metrics_summary` so the
 * Analytics v2 desk (/admin/features/analytics) and the forecast API
 * (/api/v2/analytics/forecast) have a real history instead of empty charts.
 *
 * Every number is computed from live tables - no estimates, no placeholders,
 * nothing invented. Point-in-time snapshot metrics (available plots) are only
 * recorded for *today*; backfilled dates get flow metrics only (counts of rows
 * created on that date), because the past snapshot of a mutable column is not
 * recoverable honestly.
 *
 * Safe to re-run: UNIQUE KEY uniq_metric_date(metric_name, metric_date) +
 * `ON DUPLICATE KEY UPDATE` rewrites the row in place - never duplicates.
 *
 * Usage:
 *   php scripts/daily_metrics_refresh.php --dry-run          (print, no writes)
 *   php scripts/daily_metrics_refresh.php                    (record today)
 *   php scripts/daily_metrics_refresh.php --backfill=30      (flow metrics for last 30 days; today incl.)
 */

$root   = dirname(__DIR__);
$config = require $root . '/config/database.php';

$args     = $argv ?? [];
$dryRun   = in_array('--dry-run', $args, true);
$backfill = 0;
foreach ($args as $a) {
    if (preg_match('/^--backfill=(\d+)$/', $a, $m)) {
        $backfill = max(1, (int)$m[1]);
    }
}

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Throwable $e) {
    echo "ERROR: cannot connect to database: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

// tenant_id is baked to 1: the app runs single-tenant in production today and
// every metric source below is unscoped. If the app ever goes multi-tenant,
// this script must be re-scoped - do NOT add tenantSql() blindly.
const TENANT_ID = 1;
const CATEGORY  = 'aps';

// metric => [sql (? date placeholder), is_snapshot]
// is_snapshot=true  -> only record for today (backfill skips it, by honesty)
// is_snapshot=false -> record for any requested date
$flowMetrics = [
    'leads_created'         => ["SELECT COUNT(*) FROM leads WHERE DATE(created_at) = ?", false],
    'bookings_created'      => ["SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = ?", false],
    'plot_bookings_created' => ["SELECT COUNT(*) FROM plot_bookings WHERE DATE(created_at) = ?", false],
    'transactions_value'    => ["SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE date = ?", false],
    'wallet_credits_value'  => ["SELECT COALESCE(SUM(amount), 0) FROM wallet_transactions WHERE transaction_type = 'credit' AND DATE(created_at) = ?", false],
    'users_created'         => ["SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?", false],
    'open_enquiries'        => ["SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) = ?", false],
];
$snapshotMetrics = [
    'available_plots' => ["SELECT COUNT(*) FROM plots WHERE status = 'available'", true],
];

function metricInsert(PDO $pdo, string $name, float $value, string $date, bool $dryRun): string {
    $dimensions = json_encode(['scope' => 'aps', 'high_water' => 'daily_metrics_summary'], JSON_UNESCAPED_UNICODE);
    if ($dryRun) {
        return sprintf('%-28s = %s', $name, $value);
    }
    $stmt = $pdo->prepare(
        "INSERT INTO daily_metrics_summary (tenant_id, metric_name, category, value, dimensions, metric_date, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
         ON DUPLICATE KEY UPDATE value = VALUES(value), dimensions = VALUES(dimensions), updated_at = NOW()"
    );
    $stmt->execute([TENANT_ID, $name, CATEGORY, $value, $dimensions, $date]);
    return sprintf('%-28s = %s', $name, $value);
}

$today = date('Y-m-d');
$dates = [];
if ($backfill > 0) {
    for ($i = $backfill - 1; $i >= 0; $i--) {
        $dates[] = date('Y-m-d', strtotime("-{$i} days"));
    }
} else {
    $dates[] = $today;
}

echo "=== DAILY METRICS REFRESH " . ($dryRun ? '(DRY RUN - no writes)' : '') . " ===" . PHP_EOL;
$failures = 0;
foreach ($dates as $date) {
    echo "--- {$date} ---" . PHP_EOL;
    $isToday = ($date === $today);

    // Flow metrics: computable for any date from live tables
    foreach ($flowMetrics as $name => [$sql, $isSnapshot]) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$date]);
            $row  = $stmt->fetch(PDO::FETCH_NUM);
            $value = $row !== false ? (float)$row[0] : 0.0;
            if ($name === 'transactions_value' || $name === 'wallet_credits_value') {
                $value = round($value, 2);
            }
            echo metricInsert($pdo, $name, $value, $date, $dryRun) . PHP_EOL;
        } catch (Throwable $e) {
            $failures++;
            echo "ERROR {$name}: " . $e->getMessage() . PHP_EOL;
        }
    }

    // Snapshot metrics: honest only for today
    if ($isToday) {
        foreach ($snapshotMetrics as $name => [$sql, $isSnapshot]) {
            try {
                $value = (float)$pdo->query($sql)->fetchColumn();
                echo metricInsert($pdo, $name, $value, $date, $dryRun) . PHP_EOL;
            } catch (Throwable $e) {
                $failures++;
                echo "ERROR {$name}: " . $e->getMessage() . PHP_EOL;
            }
        }
    } else {
        echo "  (available_plots skipped for {$date} - snapshot honesty)" . PHP_EOL;
    }
    echo PHP_EOL;
}

try {
    $st = $pdo->query("SELECT COUNT(*) c, COUNT(DISTINCT metric_name) m, MIN(metric_date) mn, MAX(metric_date) mx FROM daily_metrics_summary");
    $r = $st->fetch(PDO::FETCH_ASSOC);
    if (!$dryRun) {
        echo "Table now holds {$r['c']} rows across {$r['m']} metrics ({$r['mn']} .. {$r['mx']})" . PHP_EOL;
    }
} catch (Throwable $e) {
    echo "Summary probe failed (non-fatal): " . $e->getMessage() . PHP_EOL;
}

echo $failures === 0 ? "OK - metrics refreshed." . PHP_EOL : "COMPLETED WITH {$failures} ERRORS (see above)." . PHP_EOL;
exit($failures === 0 ? 0 : 1);
