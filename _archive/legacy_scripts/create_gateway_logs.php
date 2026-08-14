<?php
/**
 * APS Dream Home - Gateway Logs Table
 * Creates (or migrates) the `gateway_logs` table used by TwilioService + future gateways.
 *
 * Safe to re-run. Adds missing columns if the table already exists with an older schema.
 * Run: `php scripts/create_gateway_logs.php`
 */

require_once __DIR__ . '/../vendor/autoload.php';
$root = dirname(__DIR__);
if (!defined('APP_ROOT')) define('APP_ROOT', $root);

try {
    $envFile = $root . '/.env';
    if (file_exists($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            if (getenv($k) === false) putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
} catch (\Throwable $e) { /* ignore */ }

try {
    $pdo = new \PDO(
        'mysql:host=' . (getenv('DB_HOST') ?: '127.0.0.1')
        . ';port=' . (getenv('DB_PORT') ?: '3307')
        . ';dbname=' . (getenv('DB_NAME') ?: 'apsdreamhome')
        . ';charset=utf8mb4',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASS') ?: '',
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
    );
} catch (\Throwable $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

/* ---------------------------------------------------------------
   Step 1: create the table if it doesn't exist
   --------------------------------------------------------------- */
$createSql = <<<SQL
CREATE TABLE IF NOT EXISTS gateway_logs (
    id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gateway VARCHAR(50) NOT NULL,
    action VARCHAR(100) NULL,
    method VARCHAR(10) NULL,
    endpoint VARCHAR(255) NULL,
    recipient VARCHAR(100) NULL,
    request_payload LONGTEXT NULL,
    response_payload LONGTEXT NULL,
    http_code INT NULL,
    response_code SMALLINT(5) UNSIGNED NULL,
    status VARCHAR(20) DEFAULT 'pending',
    amount_paise BIGINT(20) UNSIGNED NULL,
    cost DECIMAL(10,4) DEFAULT 0,
    transaction_id VARCHAR(80) NULL,
    duration_ms INT(10) UNSIGNED DEFAULT 0,
    retry_count TINYINT(3) UNSIGNED DEFAULT 0,
    error_message TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gateway (gateway),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

try {
    $pdo->exec($createSql);
    echo "OK: gateway_logs table is ready.\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Migration failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

/* ---------------------------------------------------------------
   Step 2: add missing columns if table pre-existed with older schema
   --------------------------------------------------------------- */
$required = [
    'action'    => "VARCHAR(100) NULL AFTER gateway",
    'recipient' => "VARCHAR(100) NULL AFTER endpoint",
    'http_code' => "INT NULL AFTER response_payload",
    'cost'      => "DECIMAL(10,4) DEFAULT 0 AFTER amount_paise",
];

$existing = [];
foreach ($pdo->query('DESCRIBE gateway_logs') as $r) {
    $existing[strtolower($r['Field'])] = true;
}

$added = 0;
foreach ($required as $col => $def) {
    if (!isset($existing[strtolower($col)])) {
        try {
            $pdo->exec("ALTER TABLE gateway_logs ADD COLUMN {$col} {$def}");
            echo "  + ADD COLUMN {$col}\n";
            $added++;
        } catch (\Throwable $e) {
            fwrite(STDERR, "  ! Could not add column {$col}: " . $e->getMessage() . "\n");
        }
    }
}
echo $added === 0 ? "OK: all required columns already present.\n" : "OK: added $added column(s).\n";

/* ---------------------------------------------------------------
   Step 3: summary
   --------------------------------------------------------------- */
try {
    $count = (int)$pdo->query('SELECT COUNT(*) FROM gateway_logs')->fetchColumn();
    echo "INFO: gateway_logs has $count existing rows.\n";
} catch (\Throwable $e) { /* ignore */ }?>