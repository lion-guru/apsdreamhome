<?php
/**
 * APS Dream Home - Purge Rotated Plaintext Tokens
 * ===============================================
 * The final stage of the credentials cleanup (docs/CREDENTIALS_RUNBOOK.md, Addendum 3):
 * run this AFTER you have rotated every flagged provider key at its console.
 *
 * What it does:
 *   1. (default) DRY-RUN: reports exactly which rows WOULD be deleted - 22 token-shaped rows (11 settings + 11 api_keys)
 *      plus optionally the 10 loose-prefix test/short rows with --include-loose.
 *   2. With --execute: takes a mysqldump safety dump first (refuses to continue without one),
 *      then deletes EXACTLY the listed IDs - never anything else - inside ONE transaction.
 *      Deletion only happens for rows whose values STILL match a token-shaped pattern
 *      (protects against deleting something the owner re-entered as legitimate new data).
 *
 * Usage:
 *     php scripts/secret_purge_rotated.php                     # dry-run (default, deletes nothing)
 *     php scripts/secret_purge_rotated.php --include-loose     # dry-run incl. the 13 test/short rows
 *     php scripts/secret_purge_rotated.php --execute           # perform the purge (takes dump first)
 *     php scripts/secret_purge_rotated.php --execute --dump="C:\xampp\htdocs\apsdreamhome\backups\db\pre-token-delete.sql"
 *
 * Exit codes: 0 = clean / purge completed as expected | 1 = one or more preconditions failed
 */

$execute = in_array('--execute', $argv ?? [], true);
$includeLoose = in_array('--include-loose', $argv ?? [], true);
$dumpPath = null;
foreach ($argv ?? [] as $a) {
    if (str_starts_with($a, '--dump=')) $dumpPath = substr($a, 7);
}

$root = dirname(__DIR__);

// Bootstrap must run before ANY output (it sends headers internally)
require_once $root . '/config/bootstrap.php';

echo "== secret purge (token rows) ==\n";
echo $execute ? "MODE: EXECUTE\n" : "MODE: DRY-RUN (no rows deleted)\n";
if ($includeLoose) echo "SCOPE: strict 22 + 10 loose-prefix rows\n"; else echo "SCOPE: strict 22 rows only\n";

// Exact IDs (see docs/CREDENTIALS_RUNBOOK.md Addendum 3)
// Token-shaped, strict (scripts/secret_audit.php flags these)
$settingsStrict = [423, 426, 429, 430, 431, 432, 434, 435, 440, 458, 459];
$apiKeysStrict  = [4, 7, 11, 19, 20, 22, 25, 29, 32, 33, 34];
// Loose prefix audit, NOT live tokens (test/short values - all confirmed NOT token-shaped)
$settingsLoose  = [425, 427, 428, 436];
$apiKeysLoose   = [10, 23, 26, 27, 28, 30];

$targetSettings = array_values(array_merge($settingsStrict, $includeLoose ? $settingsLoose : []));
$targetApiKeys  = array_values(array_merge($apiKeysStrict,  $includeLoose ? $apiKeysLoose  : []));

try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\Throwable $e) {
    echo "FAIL: cannot connect to DB: " . $e->getMessage() . "\n";
    exit(1);
}

$tokenPatterns = [
    '/^oc_sk_[A-Za-z0-9_-]{10,}/',
    '/^sk-[A-Za-z0-9]{20,}/',
    '/^gsk_[A-Za-z0-9]{20,}/',
    '/^AIzaSy[A-Za-z0-9_-]{33,}/',
    '/^AKIA[0-9A-Z]{16,}$/',
    '/^ghp_[A-Za-z0-9]{30,}/',
    '/^hf_[A-Za-z0-9]{30,}/',
    '/^xoxb-[0-9]+-/',
    '/^eyJ[A-Za-z0-9_-]{20,}\.[A-Za-z0-9_-]{10,}/', // JWT payloads (incl. api_supabase_auth_token)
];

$iso = static function (array $ids): string {
    return implode(',', array_map('intval', $ids));
};

echo "\n== inspecting candidate rows ==\n";
$report = ['settings' => [], 'api_keys' => []];

$q = $pdo->query(
    "SELECT id, `key` AS setting_key, LENGTH(`value`) len, `value` FROM settings WHERE id IN (" . $iso($targetSettings) . ")"
);
foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $tokenShaped = false;
    foreach ($tokenPatterns as $p) { if (preg_match($p, (string)$row['value'])) { $tokenShaped = true; break; } }
    $report['settings'][] = ['id' => (int)$row['id'], 'key' => $row['setting_key'], 'len' => (int)$row['len'], 'token_shaped' => $tokenShaped];
    printf("  settings id=%d %-40s len=%d %s\n", $row['id'], $row['setting_key'], $row['len'], $tokenShaped ? 'TOKEN' : 'NOT TOKEN-SHAPED (will be skipped)');
}

$q = $pdo->query(
    "SELECT id, service_name, key_name, LENGTH(key_value) len, key_value FROM api_keys WHERE id IN (" . $iso($targetApiKeys) . ")"
);
foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $tokenShaped = false;
    foreach ($tokenPatterns as $p) { if (preg_match($p, (string)$row['key_value'])) { $tokenShaped = true; break; } }
    $report['api_keys'][] = ['id' => (int)$row['id'], 'key_name' => $row['key_name'], 'token_shaped' => $tokenShaped];
    printf("  api_keys id=%d %-30s len=%d %s\n", $row['id'], $row['key_name'], $row['len'], $tokenShaped ? 'TOKEN' : 'NOT TOKEN-SHAPED (will be skipped)');
}

$settingsToDelete = array_column(array_filter($report['settings'], fn($r) => $r['token_shaped']), 'id');
$apiKeysToDelete  = array_column(array_filter($report['api_keys'],  fn($r) => $r['token_shaped']), 'id');

echo "\nrows token-shaped (would delete): settings=" . count($settingsToDelete) . " api_keys=" . count($apiKeysToDelete) . "\n";

if (!$execute) {
    echo "\nDRY-RUN complete. Nothing deleted.\n";
    echo "Owner step: rotate the provider keys, then re-run with --execute.\n";
    exit(0);
}

if (count($settingsToDelete) + count($apiKeysToDelete) === 0) {
    echo "\nNothing to delete - token-shaped set is empty (already purged or rotated?).\n";
    exit(0);
}

// Safety dump BEFORE deleting anything
if ($dumpPath === null) {
    $mysqldump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    if (!is_file($mysqldump)) {
        $candidates = ['/usr/bin/mysqldump', '/usr/local/bin/mysqldump', 'mysqldump'];
        foreach ($candidates as $c) { $which = trim((string)@shell_exec('command -v ' . escapeshellarg($c) . ' 2>/dev/null')); if ($which !== '') { $mysqldump = $which; break; } }
    }
    if (strpos($dumpPath ?? '', '/') === false && strpos($mysqldump ?? '', 'win') !== false) { /* noop */ }
    $dumpPath = $root . '/backups/db/pre-token-delete_' . date('Y-m-d_H-i-s') . '.sql';
    $cmd = escapeshellarg($mysqldump) . ' -u root apsdreamhome settings api_keys > ' . escapeshellarg($dumpPath);
    exec($cmd, $out, $code);
    if ($code !== 0 || !is_file($dumpPath) || filesize($dumpPath) < 100) {
        echo "FAIL: safety dump failed (cmd=$cmd, exit=$code). Refusing to delete anything.\n";
        exit(1);
    }
    echo "safety dump: $dumpPath (" . round(filesize($dumpPath) / 1024, 1) . " KB)\n";
} else {
    if (!is_file($dumpPath) || filesize($dumpPath) < 100) {
        echo "FAIL: --dump file missing or too small: $dumpPath. Refusing to delete anything.\n";
        exit(1);
    }
    echo "using provided dump: $dumpPath\n";
}

// Single-transaction purge, exact IDs, pattern re-verified in SQL is not needed (already checked in PHP)
try {
    $pdo->beginTransaction();
    $setPlaceholders = implode(',', array_fill(0, count($settingsToDelete), '?'));
    $apiPlaceholders = implode(',', array_fill(0, count($apiKeysToDelete), '?'));

    if ($settingsToDelete) {
        $pdo->prepare("DELETE FROM settings WHERE id IN ($setPlaceholders)")->execute(array_map('intval', $settingsToDelete));
    }
    if ($apiKeysToDelete) {
        $pdo->prepare("DELETE FROM api_keys WHERE id IN ($apiPlaceholders)")->execute(array_map('intval', $apiKeysToDelete));
    }
    $pdo->commit();
    echo "PURGED: settings=" . count($settingsToDelete) . " api_keys=" . count($apiKeysToDelete) . " rows (single transaction)\n";
} catch (\Throwable $e) {
    $pdo->rollBack();
    echo "FAIL: transaction rolled back - nothing deleted: " . $e->getMessage() . "\n";
    exit(1);
}

// Post-verify: re-run the committed secret audit
echo "\n== post-purge secret_audit ==\n";
[$auditCode, $auditOut] = [1, 'audit did not run'];
exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/scripts/secret_audit.php') . ' 2>&1', $auditLines, $auditCode);
echo implode("\n", $auditLines) . "\n";

exit($auditCode === 0 ? 0 : 1);
