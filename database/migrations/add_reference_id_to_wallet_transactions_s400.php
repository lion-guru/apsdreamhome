<?php
/**
 * Migration: Add reference_id column to wallet_transactions table
 * 
 * The WalletController inserts into wallet_transactions with reference_id column,
 * but the table was created without it.
 * 
 * Idempotent: Checks if column exists before adding.
 * 
 * Run via: php database/migrations/add_reference_id_to_wallet_transactions_s400.php
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

// Check if reference_id column exists
$cols = $pdo->query("SHOW COLUMNS FROM wallet_transactions LIKE 'reference_id'")->fetch();
if ($cols) {
    echo "⏭  SKIP: reference_id column already exists\n";
} else {
    try {
        $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN reference_id INT NULL AFTER amount");
        echo "✅ Added reference_id column to wallet_transactions\n";
    } catch (\Throwable $e) {
        echo "❌ ERROR adding reference_id: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\n✅ wallet_transactions reference_id migration complete!\n";