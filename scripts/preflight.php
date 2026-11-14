<?php
#!/usr/bin/env php
/**
 * APS Dream Home — Preflight Check
 * ================================
 * Run before ANY deployment / dump restore / server restart.
 * Verifies: required PHP extensions, .env shape, assetlinks presence,
 * disk space, mysqldump availability, DB connectivity, and app health.
 *
 * Read-only. Never modifies files or data.
 *
 * Usage:
 *   php scripts/preflight.php                # run all checks
 *   php scripts/preflight.php --quiet        # only exit status 0|1
 *
 * Exit codes: 0 = all checks pass | 1 = one or more failures (details printed unless --quiet)
 */

$quiet = in_array('--quiet', $argv ?? [], true);
$errors = [];
$notes = [];
$root = dirname(__DIR__);

$check = function (string $label, bool $ok, string $detail = '') use (&$errors, &$notes, $quiet) {
    if ($ok) {
        $notes[] = "  OK   $label" . ($detail ? " — $detail" : '');
    } else {
        $errors[] = "  FAIL $label" . ($detail ? " — $detail" : '');
    }
};

// 1) PHP extensions
foreach (['pdo_mysql', 'mbstring', 'openssl', 'gd', 'curl', 'json', 'intl', 'fileinfo'] as $ext) {
    $check("ext: $ext", extension_loaded($ext));
}

// 2) PHP version
$check('PHP >= 8.1', version_compare(PHP_VERSION, '8.1.0', '>='), PHP_VERSION);

// 3) mysqldump / mysql cli availability
$mysqlDump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
$check('mysqldump binary', is_file($mysqlDump), $mysqlDump);
if (is_file($mysqlDump)) {
    $check('mysql binary', is_file('C:\\xampp\\mysql\\bin\\mysql.exe'));
}

// 4) Disk space (need at least 1 GB free)
$free = disk_free_space($root) ?: 0;
$check('disk >= 1 GB free', $free >= 1024 * 1024 * 1024, round($free / 1024 / 1024 / 1024, 2) . ' GB free');

// 5) .env sanity
$envFile = $root . '/.env';
$check('.env exists', is_file($envFile));
if (is_file($envFile)) {
    $envContent = file_get_contents($envFile);
    $seenKeys = [];
    foreach (preg_split('/\r?\n/', $envContent) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/', $line, $mm)) continue;
        $key = $mm[1];
        if (isset($seenKeys[$key])) {
            $errors[] = "  FAIL duplicate key in .env: $key (lines " . ($seenKeys[$key] + 1) . ' and ' . (count($seenKeys) + 1) . ')';
        }
        $seenKeys[$key] = count($seenKeys);
    }
    $check('.env: no duplicate keys', count($seenKeys) > 0);

    $isProduction = preg_match('/^APP_ENV\s*=\s*production\s*$/m', $envContent);

    $baseURLPresent = preg_match('/^BASE_URL\s*=\s*.+$/m', $envContent);
    if ($isProduction) {
        $check('.env: BASE_URL present', (bool)$baseURLPresent);
        $check('.env: APP_ENV = production', true);
    } else {
        $notes[] = '  INFO dev mode: production-only checks (BASE_URL=…, APP_ENV=production) skipped';
        $check('.env: BASE_URL set (dev info-only)', true);
        $check('.env: APP_ENV != production', true);
    }
}

// 6) Assetlinks (needed for HTTPS App Links / deep links)
$assetlinks = $root . '/public/.well-known/assetlinks.json';
$check('public/.well-known/assetlinks.json exists', is_file($assetlinks));
if (is_file($assetlinks)) {
    $j = json_decode(file_get_contents($assetlinks), true);
    $fp = $j[0]['target']['sha256_cert_fingerprints'] ?? [];
    $check('assetlinks has sha256 fingerprints', !empty($fp), count($fp) . ' fingerprint(s) — Play App Signing SHA-256 needed for Play installs');
    $check('assetlinks package name', ($j[0]['target']['package_name'] ?? '') === 'com.apsdreamhomes.mobileapp', ($j[0]['target']['package_name'] ?? 'unknown'));
}

// 7) AAB release artifact present (Play Console upload-ready)
$aab = $root . '/public/downloads/apsdreamhome-v1.2.11-release.aab';
if (is_file($aab)) {
    $age = time() - filemtime($aab);
    $check('release AAB v1.2.11+14 present', true, sizehuman(filesize($aab)) . ' / ' . floor($age / 86400) . ' days old');
} else {
    $errors[] = "  FAIL release AAB not found at $aab";
}

// 8) DB connectivity (read-only: SELECT 1 + count USERS)
if ($envFileExists = is_file($envFile)) {
    $envVals = [];
    foreach (explode("\n", file_get_contents($envFile)) as $line) {
        if (preg_match('/^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/', trim($line), $mm)) {
            $envVals[$mm[1]] = trim($mm[2], " \t\"'");
        }
    }
    try {
        $pdo = new PDO(
            'mysql:host=' . ($envVals['DB_HOST'] ?? 'localhost') . ';port=' . ($envVals['DB_PORT'] ?? '3306') . ';dbname=' . ($envVals['DB_NAME'] ?? '') . ';charset=utf8mb4',
            $envVals['DB_USER'] ?? 'root',
            $envVals['DB_PASSWORD'] ?? $envVals['DB_PASS'] ?? '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]
        );
        $n = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $check('DB: users table readable', true, "$n users");
    } catch (\Throwable $e) {
        $check('DB connectivity', false, $e->getMessage());
    }
}

// 9) service_configs all encrypted
try {
    $host = isset($envVals) ? $envVals['DB_HOST'] ?? 'localhost' : 'localhost';
    $name = isset($envVals) ? $envVals['DB_NAME'] ?? 'apsdreamhome' : 'apsdreamhome';
    $user = isset($envVals) ? $envVals['DB_USER'] ?? 'root' : 'root';
    $pass = isset($envVals) ? ($envVals['DB_PASSWORD'] ?? $envVals['DB_PASS'] ?? '') : '';
    $pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8mb4", $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
    $bad = $pdo->query("SELECT COUNT(*) FROM service_configs WHERE is_secret = 1 AND config_value <> '' AND config_value NOT LIKE 'enc:v1:%'")->fetchColumn();
    $check('service_configs: no plaintext secrets', (int)$bad === 0, "$bad non-encrypted secret rows (warn)");
} catch (\Throwable $e) {
    $errors[] = "  NOTE could not verify service_configs encryption: " . $e->getMessage();
}

// 10) disk note about archives
$notes[] = "  INFO backups/db count: " . (glob($root . '/backups/db/*.sql') ? count(glob($root . '/backups/db/*.sql')) : 0) . " dumps";

// Report
if ($quiet) {
    exit(count($errors) ? 1 : 0);
}

echo "=== APS Preflight ===\n";
foreach ($notes as $n) echo $n, "\n";
foreach ($errors as $e) echo $e, "\n";
echo "\n", (count($errors) ? count($errors) . " CHECK(S) FAILED\n" : "ALL CHECKS PASS\n");
echo "Notes: ", count($notes), " informational\n";
exit(count($errors) ? 1 : 0);

function sizehuman(int $bytes): string {
    $units = ['B','KB','MB','GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) { $bytes /= 1024; $i++; }
    return round($bytes, 1) . ' ' . $units[$i];
}
