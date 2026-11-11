<?php
/**
 * Migration: Add setting_key/setting_value columns to wallet_configuration
 * 
 * The wallet_configuration table has config_key/config_value columns, but
 * WalletController queries setting_key/setting_value. Add both as aliases.
 * 
 * Idempotent: Checks if columns exist before adding.
 * 
 * Run via: php database/migrations/add_setting_columns_to_wallet_config_s397.php
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

// Check if wallet_configuration table exists
$tableExists = $pdo->query("SHOW TABLES LIKE 'wallet_configuration'")->fetch();
if (!$tableExists) {
    echo "❌ wallet_configuration table does not exist\n";
    exit(1);
}

// Check if setting_key column exists
$cols = $pdo->query("SHOW COLUMNS FROM wallet_configuration LIKE 'setting_key'")->fetch();
if ($cols) {
    echo "⏭  SKIP: setting_key column already exists\n";
} else {
    try {
        $pdo->exec("ALTER TABLE wallet_configuration ADD COLUMN setting_key VARCHAR(100) NOT NULL DEFAULT '' AFTER config_key");
        echo "✅ Added setting_key column to wallet_configuration\n";
    } catch (\Throwable $e) {
        echo "❌ ERROR adding setting_key: " . $e->getMessage() . "\n";
    }
}

// Check if setting_value column exists
$cols = $pdo->query("SHOW COLUMNS FROM wallet_configuration LIKE 'setting_value'")->fetch();
if ($cols) {
    echo "⏭  SKIP: setting_value column already exists\n";
} else {
    try {
        $pdo->exec("ALTER TABLE wallet_configuration ADD COLUMN setting_value TEXT NOT NULL DEFAULT '' AFTER config_value");
        echo "✅ Added setting_value column to wallet_configuration\n";
    } catch (\Throwable $e) {
        echo "❌ ERROR adding setting_value: " . $e->getMessage() . "\n";
    }
}

// Copy existing config_key/config_value data to setting_key/setting_value
try {
    $pdo->exec("UPDATE wallet_configuration SET setting_key = config_key WHERE setting_key = '' OR setting_key IS NULL");
    $pdo->exec("UPDATE wallet_configuration SET setting_value = config_value WHERE setting_value = '' OR setting_value IS NULL");
    echo "✅ Synced config_key/config_value to setting_key/setting_value\n";
} catch (\Throwable $e) {
    echo "⚠️  Could not sync columns: " . $e->getMessage() . "\n";
}

echo "\n✅ wallet_configuration migration complete!\n";