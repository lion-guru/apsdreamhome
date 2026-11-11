<?php
/**
 * Migration: Add tenant_id column to wallet_transactions table
 * 
 * The WalletController inserts into wallet_transactions with tenant_id column,
 * but the table was created without it.
 * 
 * Idempotent: Checks if column exists before adding.
 * 
 * Run via: php database/migrations/add_tenant_id_to_wallet_transactions_s398.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if wallet_transactions table exists
$tableExists = $pdo->query("SHOW TABLES LIKE 'wallet_transactions'")->fetch();
if (!$tableExists) {
    echo "❌ wallet_transactions table does not exist\n";
    exit(1);
}

// Check if tenant_id column exists
$cols = $pdo->query("SHOW COLUMNS FROM wallet_transactions LIKE 'tenant_id'")->fetch();
if ($cols) {
    echo "⏭  SKIP: tenant_id column already exists\n";
} else {
    try {
        $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN tenant_id INT UNSIGNED DEFAULT 1 AFTER id");
        echo "✅ Added tenant_id column to wallet_transactions\n";
    } catch (\Throwable $e) {
        echo "❌ ERROR adding tenant_id: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Backfill existing rows with tenant_id = 1
try {
    $pdo->exec("UPDATE wallet_transactions SET tenant_id = 1 WHERE tenant_id IS NULL OR tenant_id = 0");
    echo "✅ Backfilled tenant_id for existing rows\n";
} catch (\Throwable $e) {
    echo "⚠️  Could not backfill tenant_id: " . $e->getMessage() . "\n";
}

echo "\n✅ wallet_transactions tenant_id migration complete!\n";