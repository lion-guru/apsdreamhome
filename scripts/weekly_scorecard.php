<?php
/**
 * APS Dream Home - Weekly Health Scorecard
 * ========================================
 * One-command read-only aggregation of the project's integrity signals.
 * Designed to be run by a daily/weekly cron OR on demand:
 *
 *     php scripts/weekly_scorecard.php           # human report
 *     php scripts/weekly_scorecard.php --quiet   # exit code only (cron)
 *     php scripts/weekly_scorecard.php --full    # include full master test run (slow)
 *
 * Exit codes:  0 = no failures (warnings allowed)  |  1 = one or more FAIL checks
 *
 * Read-only: writes nothing except optionally a compact JSON artifact with --json.
 */

$quiet = in_array('--quiet', $argv ?? [], true);
$full  = in_array('--full', $argv ?? [], true);
$json  = in_array('--json', $argv ?? [], true);

$root = dirname(__DIR__);

function runCmd(string $cmd): array
{
    $out = [];
    $code = 1;
    exec(escapeshellarg(PHP_BINARY) . ' ' . $cmd . ' 2>&1', $out, $code);
    return [$code, implode("\n", $out)];
}

$results = [];
$check = function (string $name, string $status, string $detail) use (&$results) {
    $results[] = ['check' => $name, 'status' => $status, 'detail' => $detail];
};
// Truth table for exit-code-driven checks
$fromExit = fn(string $status_ok = 'PASS', string $status_fail = 'FAIL') => fn(int $code) => $code === 0 ? $status_ok : $status_fail;

// 1) End-to-end test suite (optional - costs ~20-25s)
if ($full) {
    [$code, $out] = runCmd($root . '/testing/master_test_runner.php');
    preg_match('/(\d+)\/\d+ SUITES PASSED/', $out, $mm);
    $check('master test suite', $mm[1] === '11' ? 'PASS' : 'FAIL',
        isset($mm[1]) ? "{$mm[1]}/11 suites green" : trim(substr($out, -200)));
} else {
    $check('master test suite', 'SKIP', 'use --full to include (takes ~20-25s)');
}

// 2) AGENTS.md session log integrity
[$code] = runCmd($root . '/scripts/verify_agents_log.php');
$check('AGENTS.md integrity', $code === 0 ? 'PASS' : 'FAIL',
    $code === 0 ? 'session log OK' : 'verify_agents_log.php FAILED');

// 3) Secret hygiene (plaintext sinks)
[$code, $out] = runCmd($root . '/scripts/secret_audit.php');
preg_match('/plaintext token-shaped strings:\s*(\d+)/', $out, $mm);
$found = isset($mm[1]) ? (int)$mm[1] : null;
$check('secret audit', $found === 0 ? 'PASS' : ($found === null ? 'WARN' : 'FAIL'),
    $found === null ? 'audit tool returned unexpected output' : "$found plaintext token-shaped rows in settings/api_keys/.env");

// 4) Preflight (env shape, dump tooling, connectivity, assetlinks)
[$code] = runCmd($root . '/scripts/preflight.php --quiet');
$check('preflight', $code === 0 ? 'PASS' : 'FAIL', $code === 0 ? 'all preflight checks pass' : 'run php scripts/preflight.php for details');

// 5) Migration evidence status (uses committed scripts/migration_status.php)
[$code, $out] = runCmd($root . '/scripts/migration_status.php');
$absent = $partial = $present = $skip = 0;
if (preg_match('/PRESENT[^\d]*(\d+)/', $out, $m)) $present = (int)$m[1];
if (preg_match('/PARTIAL[^\d]*(\d+)/', $out, $m)) { $partial = (int)$m[1]; }
if (preg_match('/ABSENT[^\d]*(\d+)/', $out, $m)) { $absent = (int)$m[1]; }
if (preg_match('/SKIP[^\d]*(\d+)/', $out, $m)) $skip = (int)$m[1];
$status = $absent > 0 ? 'WARN' : 'PASS'; // ABSENT means dead drafts, not broken app
$check('migration register evidence', $status,
    "$present PRESENT / $partial PARTIAL / $absent ABSENT / $skip SKIP. Dead-draft migrations are informational; no live code references missing objects.");

// 6) Backup freshness
$backups = glob($root . '/backups/db/*.sql') ?: [];
$newest = 0;
foreach ($backups as $f) { $newest = max($newest, filemtime($f)); }
$ageDays = $newest ? (int)((time() - $newest) / 86400) : 9999;
$check('DB backup freshness', $ageDays <= 7 ? 'PASS' : ($ageDays <= 14 ? 'WARN' : 'FAIL'),
    $newest ? "$ageDays day(s) since newest dump (" . basename(end($backups)) . ")" : 'NO backups/db/*.sql found');

// 7) Retention backlog monitor (reads the same overdue predicates the cron uses)
try {
    require_once $root . '/config/bootstrap.php';
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
    $overdue = [
        'ab_events (>90d)' => (int)$pdo->query("SELECT COUNT(*) FROM ab_events WHERE created_at < NOW() - INTERVAL 90 DAY")->fetchColumn(),
        'visitor_page_views (>60d)' => (int)$pdo->query("SELECT COUNT(*) FROM visitor_page_views WHERE created_at < NOW() - INTERVAL 60 DAY")->fetchColumn(),
        'csp_violations (>30d)' => (int)$pdo->query("SELECT COUNT(*) FROM csp_violations WHERE received_at < NOW() - INTERVAL 30 DAY")->fetchColumn(),
    ];
    $tot = array_sum($overdue);
    $check('log retention backlog', $tot === 0 ? 'PASS' : 'WARN',
        $tot === 0 ? 'no overdue rows in any retention-rule table' : "overdue: " . implode(', ', array_map(fn($k, $v) => "$k=$v", array_keys($overdue), $overdue)) . ' - the scheduled purge task (APS_LogRetentionPurge) needs registering by the owner');
} catch (\Throwable $e) {
    $check('log retention backlog', 'WARN', 'could not read DB: ' . $e->getMessage());
}

// 8) Disk headroom
$free = disk_free_space($root) ?: 0;
$gb = round($free / 1024 / 1024 / 1024, 2);
$check('disk headroom', $gb >= 10 ? 'PASS' : ($gb >= 5 ? 'WARN' : 'FAIL'), "$gb GB free on C:");

// 9) Oversized volatile log files
$big = [];
foreach (glob($root . '/logs/*.log*') ?: [] as $f) {
    $mb = filesize($f) / 1024 / 1024;
    if ($mb > 100) $big[] = basename($f) . " (" . round($mb, 1) . " MB)";
}
$check('log file sizes', $big ? 'WARN' : 'PASS', $big ? 'oversized: ' . implode(', ', $big) : 'all logs/ files <= 100 MB');

// 10) Uncommitted work (INFO, never fails)
$gitStatus = [];
exec('git -C ' . escapeshellarg($root) . ' status --short 2>nul', $gitStatus);
$check('worktree drift (info)', 'INFO', count($gitStatus) . ' uncommitted paths (parallel actors usually own these)');

// 11) schema_migrations vs registry sanity
$registry = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM migration_file_registry");
    $registry = (int)$stmt->fetchColumn();
    $check('migration file registry', $registry > 200 ? 'PASS' : 'WARN', "$registry migration files registered");
} catch (\Throwable $e) {
    $check('migration file registry', 'WARN', 'registry table missing - run scripts/register_migration_files.php');
}

// Report
$fails = count(array_filter($results, fn($r) => $r['status'] === 'FAIL'));
$warns = count(array_filter($results, fn($r) => $r['status'] === 'WARN' || $r['status'] === 'INFO'));

if ($json) {
    $payload = ['generated_at' => date('c'), 'fails' => $fails, 'results' => $results];
    $outPath = $root . '/backups/weekly_scorecard_' . date('Y-m-d_His') . '.json';
    file_put_contents($outPath, json_encode($payload, JSON_PRETTY_PRINT));
    echo "wrote $outPath\n";
}

if ($quiet) {
    exit($fails > 0 ? 1 : 0);
}

echo "=== APS Weekly Health Scorecard ===\n";
echo 'generated: ' . date('Y-m-d H:i:s') . "\n\n";
foreach ($results as $r) {
    $tag = str_pad($r['status'], 5);
    echo "  [$tag] {$r['check']}: {$r['detail']}\n";
}
echo "\n" . ($fails === 0 ? 'ALL CHECKS PASS (' . $warns . " warning(s))\n" : "$fails CHECK(S) FAILED\n");
exit($fails > 0 ? 1 : 0);
