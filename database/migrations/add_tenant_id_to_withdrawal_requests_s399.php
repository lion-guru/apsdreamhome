<?php
/**
 * Migration: Add tenant_id column to withdrawal_requests table
 * 
 * The WalletController::processWithdrawal method inserts into withdrawal_requests
 * with tenant_id, but the table was created without it.
 * 
 * Idempotent: Checks if column exists before adding.
 * 
 * Run via: php database/migrations/add_tenant_id_to_withdrawal_requests_s399.php
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

// Check if withdrawal_requests table exists
$tableExists = $pdo->query("SHOW TABLES LIKE 'withdrawal_requests'")->fetch();
if (!$tableExists) {
    echo "❌ withdrawal_requests table does not exist\n";
    exit(1);
}

// Check if tenant_id column exists
$cols = $pdo->query("SHOW COLUMNS FROM withdrawal_requests LIKE 'tenant_id'")->fetch();
if ($cols) {
    echo "⏭  SKIP: tenant_id column already exists\n";
} else {
    try {
        $pdo->exec("ALTER TABLE withdrawal_requests ADD COLUMN tenant_id INT UNSIGNED DEFAULT 1 AFTER id");
        echo "✅ Added tenant_id column to withdrawal_requests\n";
    } catch (\Throwable $e) {
        echo "❌ ERROR adding tenant_id: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Backfill existing rows
try {
    $pdo->exec("UPDATE withdrawal_requests SET tenant_id = 1 WHERE tenant_id IS NULL OR tenant_id = 0");
    echo "✅ Backfilled tenant_id for existing rows\n";
} catch (\Throwable $e) {
    echo "⚠️  Could not backfill tenant_id: " . $e->getMessage() . "\n";
}

echo "\n✅ withdrawal_requests tenant_id migration complete!\n";