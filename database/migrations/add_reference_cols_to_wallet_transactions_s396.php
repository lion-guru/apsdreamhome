<?php
/**
 * Migration: Add reference_id and reference_type columns to wallet_transactions
 *
 * These columns are needed by WalletController::processWithdrawal() to link
 * wallet transactions to withdrawal requests.
 *
 * Idempotent: skips if columns already exist.
 *
 * Run via: php database/migrations/add_reference_cols_to_wallet_transactions_s396.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

function columnExists(PDO $pdo, string $table, string $column): bool {
    $row = $pdo->query("SHOW COLUMNS FROM {$table} LIKE '{$column}'")->fetch();
    return $row !== false;
}

// Add reference_id column
if (!columnExists($pdo, 'wallet_transactions', 'reference_id')) {
    try {
        $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN reference_id INT(11) NULL AFTER description");
        echo "✅ ADDED: wallet_transactions.reference_id\n";
    } catch (\Throwable $e) {
        echo "❌ ERROR adding reference_id: " . $e->getMessage() . "\n";
    }
} else {
    echo "⏭  SKIP: wallet_transactions.reference_id already exists\n";
}

// Add reference_type column
if (!columnExists($pdo, 'wallet_transactions', 'reference_type')) {
    try {
        $pdo->exec("ALTER TABLE wallet_transactions ADD COLUMN reference_type VARCHAR(50) NULL AFTER reference_id");
        echo "✅ ADDED: wallet_transactions.reference_type\n";
    } catch (\Throwable $e) {
        echo "❌ ERROR adding reference_type: " . $e->getMessage() . "\n";
    }
} else {
    echo "⏭  SKIP: wallet_transactions.reference_type already exists\n";
}

// Add index on reference_id for faster lookups
try {
    $pdo->exec("CREATE INDEX idx_wallet_transactions_reference ON wallet_transactions(reference_id, reference_type)");
    echo "✅ ADDED: index on wallet_transactions(reference_id, reference_type)\n";
} catch (\Throwable $e) {
    // Index might already exist
    echo "⏭  SKIP: index may already exist (" . $e->getMessage() . ")\n";
}

echo "\n✅ Migration complete\n";