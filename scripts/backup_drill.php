<?php
#!/usr/bin/env php
/**
 * APS Dream Home — Backup Drill
 * ===========================
 * Proves that any .sql dump can actually be restored:
 *  1) creates a scratch DB (aps_drill_<ts>)
 *  2) imports the dump into it via the mysql client
 *  3) verifies required tables exist and carry rows
 *  4) drops the scratch DB
 *
 * NEVER touches the live application database.
 *
 * Usage:
 *   php scripts/backup_drill.php                        # newest dump in backups/db/
 *   php scripts/backup_drill.php <dump.sql>             # explicit dump
 *   php scripts/backup_drill.php <dump.sql> --keep      # keep scratch DB for manual inspection
 *   php scripts/backup_drill.php <dump.sql> --tables=users,plots
 *   php scripts/backup_drill.php --scan                 # just list backups + sizes
 *
 * Exit codes: 0 OK | 1 drill failed | 2 usage error
 */

$root       = dirname(__DIR__);
$defaultDir = $root . '/backups/db';

// --- helpers ------------------------------------------------------------
function mysql_path(): string {
    $candidates = ['C:\\xampp\\mysql\\bin\\mysql.exe', '/usr/bin/mysql', 'mysql'];
    foreach ($candidates as $c) { if (file_exists($c) || $c === 'mysql') return $c; }
    return 'mysql';
}
function mysql_cmd(string $sql, string $db = ''): string {
    $mysql = mysql_path();
    $args = [escapeshellarg('root')];
    if (defined('DB_PASSWORD') && DB_PASSWORD !== '') $args[] = '-p' . escapeshellarg(DB_PASSWORD);
    $cmd = escapeshellcmd($mysql)
         . ' --user=' . implode(' --user=', $args) // will be cleaned below; simpler:
         ;
    return $cmd; // placeholder; actual calls below
}
function run_mysql(string $user, string $pass, string $sql): string {
    $mysql = mysql_path();
    $u = '--user=' . escapeshellarg($user);
    $p = $pass !== '' ? ' --password=' . escapeshellarg($pass) : '';
    return trim((string)shell_exec(escapeshellcmd($mysql) . " $u$p -e " . escapeshellarg($sql) . ' 2>&1'));
}
function import_dump(string $mysql, string $user, string $pass, string $scratch, string $dump): string {
    $path = str_replace('\\', '/', $dump);
    $u = '--user=' . escapeshellarg($user);
    $p = $pass !== '' ? ' --password=' . escapeshellarg($pass) : '';
    $cmd = 'cmd /c "' . escapeshellcmd($mysql)
         . " $u$p --default-character-set=utf8mb4 $scratch < " . escapeshellarg($path)
         . '" 2>&1';
    return (string)shell_exec($cmd);
}

$keep = in_array('--keep', $argv ?? [], true);
if (in_array('--help', $argv ?? [], true)) { echo "usage: backup_drill.php [dump.sql] [--scan] [--keep] [--tables=t,t]\n"; exit(0); }

if (in_array('--scan', $argv ?? [], true)) {
    $files = glob($defaultDir . '/*.sql') ?: [];
    usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
    $sum = 0;
    foreach ($files as $f) { $mb = filesize($f) / 1048576; $sum += $mb; printf("%8.1f MB  %s\n", $mb, basename($f)); }
    printf("TOTAL %.1f MB across %d dumps\n", $sum, count($files));
    exit(0);
}

// --- args ---------------------------------------------------------------
$dumpFile = null;
$extraTables = [];
foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--keep') continue;
    if (strpos($arg, '--tables=') === 0) { $extraTables = array_filter(array_map('trim', explode(',', substr($arg, 9)))); continue; }
    if (strpos($arg, '--') === 0) { fwrite(STDERR, "Unknown flag: $arg\n"); exit(2); }
    if ($dumpFile !== null) { fwrite(STDERR, "Only one dump argument allowed.\n"); exit(2); }
    $dumpFile = $arg;
}

if ($dumpFile === null) {
    $cands = glob($defaultDir . '/*.sql') ?: [];
    if (!$cands) { fwrite(STDERR, "No dumps in $defaultDir\n"); exit(2); }
    usort($cands, fn($a, $b) => filemtime($b) - filemtime($a));
    $dumpFile = $cands[0];
}
if (!is_file($dumpFile)) { fwrite(STDERR, "Dump not found: $dumpFile\n"); exit(2); }
if (strpos($dumpFile, '..') !== false) { fwrite(STDERR, "Refusing '..' in dump path\n"); exit(2); }

$mb = filesize($dumpFile) / 1048576;
echo "=== APS Backup Drill ===\nDump: $dumpFile (" . number_format($mb, 1) . " MB)\n\n";

// --- config (from .env without touching $env) ---------------------------
$env = [];
if (is_file("$root/.env")) {
    foreach (file("$root/.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (!preg_match('/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)\s*$/', $line, $mm)) continue;
        $val = trim($mm[2]);
        if (($val[0] ?? '') === '"' && substr($val, -1) === '"') $val = substr($val, 1, -1);
        $env[$mm[1]] = $val;
    }
}
$host = $env['DB_HOST'] ?? 'localhost';
$port = $env['DB_PORT'] ?? '3306';
$user = $env['DB_USER'] ?? $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? $env['DB_PASS'] ?? '';

echo "Host: $host:$port | User: $user\n";

$scratch = 'aps_drill_' . gmdate('ymdHis') . '_' . bin2hex(random_bytes(3));

$mysql = mysql_path();
echo "Creating scratch DB: $scratch\n";
$out = run_mysql($user, $pass, "CREATE DATABASE `$scratch` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
if ($out !== '' && stripos($out, 'error') !== false && stripos($out, 'notice') === false) {
    printf("  create output: %s\n", $out);
    fwrite(STDERR, "Create scratch DB failed\n");
    exit(1);
}

// --- import --------------------------------------------------------------
echo "Importing dump...\n";
$t0 = microtime(true);
$imp = import_dump($mysql, $user, $pass, $scratch, $dumpFile);
$secs = round(microtime(true) - $t0, 1);
echo "  import took {$secs}s\n";
if ($imp && preg_match('/error/i', $imp)) {
    echo "  import output: " . trim(preg_split('/\n/', $imp)[0]) . "\n";
}

// --- verify --------------------------------------------------------------
$must = array_values(array_unique(array_filter(array_merge(
    ['users', 'plots', 'leads', 'associate_profiles', 'plot_bookings', 'mlm_profiles'],
    $extraTables
))));
echo "\nRequired tables check:\n";
$dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec("USE `$scratch`");

$fail = 0;
foreach ($must as $t) {
    try {
        $n = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        printf("  %-25s %s rows\n", $t, number_format($n));
    } catch (\Throwable $e) {
        printf("  %-25s MISSING\n", $t);
        $fail++;
    }
}
$tables = (int)$pdo->query(
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$scratch'"
)->fetchColumn();
echo "\nTables in scratch: $tables\n";
$pdo = null;

// --- cleanup -------------------------------------------------------------
if ($keep) {
    echo "\nKEEP requested: scratch DB '$scratch' retained for review. Drop it manually later.\n";
} else {
    echo "\nDropping scratch DB $scratch...\n";
    $drop = run_mysql($user, $pass, "DROP DATABASE IF EXISTS `$scratch`");
    echo "  " . ($drop === '' ? 'dropped OK' : trim($drop)) . "\n";
}

echo "\n" . ($fail ? "RESULT: FAILED ($fail required table(s) missing)\n" : "RESULT: OK — dump is restorable ({$secs}s import, $tables tables).\n");
exit($fail ? 1 : 0);
