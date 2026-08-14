<?php
/**
 * APS Dream Home - Add E-Sign Columns to plot_bookings
 *
 * Adds esign_document_id, esign_status, esign_signed_at, esign_url
 * to the plot_bookings table for Leegality e-signature integration.
 *
 * Safe to re-run (idempotent).
 *
 * Run: php scripts/add_esign_columns.php
 */

$root = dirname(__DIR__);

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

$columns = [
    'esign_document_id' => "VARCHAR(100) NULL",
    'esign_status'      => "ENUM('pending','sent','signed','failed') DEFAULT 'pending'",
    'esign_signed_at'   => "DATETIME NULL",
    'esign_url'         => "VARCHAR(500) NULL",
];

$added = 0;
foreach ($columns as $col => $def) {
    try {
        $pdo->exec("ALTER TABLE plot_bookings ADD COLUMN `{$col}` {$def}");
        echo "  + Added column: {$col}\n";
        $added++;
    } catch (\Throwable $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "  - Column {$col} already exists â€” skipping.\n";
        } else {
            fwrite(STDERR, "  ! Error adding {$col}: " . $e->getMessage() . PHP_EOL);
        }
    }
}

echo "\nDone. {$added} column(s) added to plot_bookings.\n";?>