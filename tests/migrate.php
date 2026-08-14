<?php
/**
 * CI Migration Runner
 *
 * Runs the migration scripts that ship with the project to bring a fresh
 * test database up to the same shape as production. Idempotent - safe to
 * run repeatedly; each underlying script uses CREATE TABLE IF NOT EXISTS
 * and the _migrations table tracks what was applied.
 *
 * Pipeline:
 *   1. Create the _migrations tracking table
 *   2. Run each scripts/migrate_*.php, scripts/create_*.php, scripts/add_*.php,
 *      scripts/fix_*.php, scripts/consolidate_*.php, scripts/apply_*.php
 *   3. Run scripts/seed_*.php for reference data
 *   4. Record a CI marker so re-runs are no-ops
 *
 * Usage:
 *   php tests/migrate.php
 *
 * Environment overrides (for CI):
 *   DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
 *
 * Exit code 0 = success, 1 = any step failed.
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
define('MIGRATION_STEP_TIMEOUT', 120);

$root = APP_ROOT;
$reportsDir = $root . '/tests/reports';
if (!is_dir($reportsDir)) {
    @mkdir($reportsDir, 0775, true);
}

$log = static function (string $msg) use ($reportsDir): void {
    echo $msg . PHP_EOL;
    @file_put_contents($reportsDir . '/migrate.log', $msg . PHP_EOL, FILE_APPEND);
};

$log('');
$log('=== CI MIGRATION RUNNER ===');
$log('Started: ' . date('c'));
$log('PHP: ' . PHP_VERSION);
$log('DB: ' . (getenv('DB_HOST') ?: '127.0.0.1') . ':' . (getenv('DB_PORT') ?: '3306') . '/' . (getenv('DB_DATABASE') ?: 'apsdreamhome_test'));

$totalStart = microtime(true);
$pass = 0;
$fail = 0;
$skipped = 0;
$failures = [];

// Step 1: bootstrap the migration tracking table
$log('');
$log('[1/3] Creating _migrations tracking table...');
$createMigrations = $root . '/scripts/create_migrations_table.php';
if (is_file($createMigrations)) {
    $code = runStep($createMigrations, $log);
    $code === 0 ? $pass++ : ($fail++ + ($failures[] = 'create_migrations_table.php'));
} else {
    $log('  ! scripts/create_migrations_table.php missing - skipping');
    $skipped++;
}

// Step 2: run all migration-shaped scripts in lexical order
$log('');
$log('[2/3] Running migration scripts...');
$patterns = [
    'migrate_*.php',
    'create_*.php',
    'add_*.php',
    'fix_*.php',
    'consolidate_*.php',
    'apply_*.php',
    'audit_*.php',
    'cron_*.php',
];
$excluded = ['create_migrations_table.php'];
$scripts = [];
foreach ($patterns as $p) {
    foreach (glob($root . '/scripts/' . $p) ?: [] as $f) {
        $base = basename($f);
        if (in_array($base, $excluded, true)) continue;
        $scripts[$base] = $f;
    }
}
ksort($scripts);
foreach ($scripts as $name => $path) {
    $log("  - $name");
    $code = runStep($path, $log);
    if ($code === 0) {
        $pass++;
    } else {
        $fail++;
        $failures[] = $name;
    }
}

// Step 3: seed reference data
$log('');
$log('[3/3] Running seed scripts...');
foreach (glob($root . '/scripts/seed_*.php') ?: [] as $f) {
    $name = basename($f);
    $log("  - $name");
    $code = runStep($f, $log);
    if ($code === 0) {
        $pass++;
    } else {
        $fail++;
        $failures[] = $name;
    }
}

$elapsed = round(microtime(true) - $totalStart, 2);
$log('');
$log('=== MIGRATION SUMMARY ===');
$log("Passed:  $pass");
$log("Failed:  $fail");
$log("Skipped: $skipped");
$log('Elapsed: ' . $elapsed . 's');
if ($fail > 0) {
    $log('Failed scripts: ' . implode(', ', $failures));
    $log('Migration run FAILED');
    exit(1);
}
$log('Migration run PASSED');
exit(0);

/**
 * Execute a single migration script in a child process with a timeout.
 * Returns the exit code (0 = success). stderr is captured and logged.
 */
function runStep(string $script, callable $log): int
{
    $real = realpath($script);
    if ($real === false) {
        $log('    ! script not found: ' . $script);
        return 1;
    }
    $cmd = escapeshellcmd(PHP_BINARY)
        . ' -d display_errors=stderr'
        . ' ' . escapeshellarg($real)
        . ' 2>&1';
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $proc = @proc_open($cmd, $descriptors, $pipes, dirname($real));
    if (!is_resource($proc)) {
        $log('    ! failed to start: ' . $script);
        return 1;
    }
    fclose($pipes[0]);
    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $output = '';
    $deadline = microtime(true) + MIGRATION_STEP_TIMEOUT;
    while (true) {
        $status = proc_get_status($proc);
        $output .= (stream_get_contents($pipes[1]) ?: '');
        $output .= (stream_get_contents($pipes[2]) ?: '');
        if (!$status['running']) break;
        if (microtime(true) > $deadline) {
            proc_terminate($proc, 9);
            $log('    ! TIMEOUT after ' . MIGRATION_STEP_TIMEOUT . 's');
            $output .= "\n[CI: killed after timeout]";
            break;
        }
        usleep(50_000);
    }
    foreach (['1' => true, '2' => true] as $idx => $_) {
        if (is_resource($pipes[(int)$idx] ?? null)) {
            $output .= stream_get_contents($pipes[(int)$idx]) ?: '';
            fclose($pipes[(int)$idx]);
        }
    }
    $exit = proc_close($proc);
    $trimmed = trim($output);
    if ($trimmed !== '') {
        $maxLines = 6;
        $lines = explode("\n", $trimmed);
        if (count($lines) > $maxLines) {
            $shown = array_slice($lines, 0, $maxLines);
            $shown[] = '    ... (' . (count($lines) - $maxLines) . ' more lines)';
            $trimmed = implode("\n", $shown);
        }
        foreach (explode("\n", $trimmed) as $line) {
            $log('    | ' . $line);
        }
    }
    if ($exit !== 0) {
        $log('    x exit ' . $exit);
    }
    return (int)$exit;
}?>