<?php
/**
 * Migration: Add multi-currency columns to vendor_payments table
 * - currency VARCHAR(3) DEFAULT 'INR'
 * - exchange_rate DECIMAL(10,4) DEFAULT 1.0000
 * - amount_inr DECIMAL(12,2) â€” computed foreign-currency equivalent in INR
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== Multi-Currency Migration for vendor_payments ===\n\n";

    // 1. Check if vendor_payments table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'vendor_payments'")->fetch();
    if (!$tableCheck) {
        echo "[SKIP] vendor_payments table does not exist. Create it first.\n";
        exit(0);
    }

    // 2. Add currency column
    $colCheck = $pdo->query("SHOW COLUMNS FROM vendor_payments LIKE 'currency'")->fetch();
    if (!$colCheck) {
        $pdo->exec("ALTER TABLE vendor_payments ADD COLUMN currency VARCHAR(3) NOT NULL DEFAULT 'INR' AFTER paid_amount");
        echo "[OK] Added currency VARCHAR(3) DEFAULT 'INR'\n";
    } else {
        echo "[SKIP] currency column already exists\n";
    }

    // 3. Add exchange_rate column
    $colCheck = $pdo->query("SHOW COLUMNS FROM vendor_payments LIKE 'exchange_rate'")->fetch();
    if (!$colCheck) {
        $pdo->exec("ALTER TABLE vendor_payments ADD COLUMN exchange_rate DECIMAL(10,4) NOT NULL DEFAULT 1.0000 AFTER currency");
        echo "[OK] Added exchange_rate DECIMAL(10,4) DEFAULT 1.0000\n";
    } else {
        echo "[SKIP] exchange_rate column already exists\n";
    }

    // 4. Add amount_inr column
    $colCheck = $pdo->query("SHOW COLUMNS FROM vendor_payments LIKE 'amount_inr'")->fetch();
    if (!$colCheck) {
        $pdo->exec("ALTER TABLE vendor_payments ADD COLUMN amount_inr DECIMAL(12,2) DEFAULT NULL AFTER exchange_rate");
        echo "[OK] Added amount_inr DECIMAL(12,2)\n";
    } else {
        echo "[SKIP] amount_inr column already exists\n";
    }

    // 5. Verify
    $cols = $pdo->query("SHOW COLUMNS FROM vendor_payments WHERE Field IN ('currency','exchange_rate','amount_inr')")->fetchAll(PDO::FETCH_ASSOC);
    echo "\n=== Verification ===\n";
    foreach ($cols as $c) {
        echo "  {$c['Field']}: {$c['Type']} | Default: {$c['Default']} | Null: {$c['Null']}\n";
    }

    echo "\nMigration complete.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>