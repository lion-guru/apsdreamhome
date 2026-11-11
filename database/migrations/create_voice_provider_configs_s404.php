<?php
/**
 * Migration: Create voice_provider_configs table (fix typo in previous migration)
 * 
 * Run via: php database/migrations/create_voice_provider_configs_s404.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';
use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if table already exists
$exists = $pdo->query("SHOW TABLES LIKE 'voice_provider_configs'")->fetch();
if ($exists) {
    echo "⏭️  voice_provider_configs already exists\n";
    exit(0);
}

try {
    $pdo->exec("
    CREATE TABLE voice_provider_configs (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        provider_name VARCHAR(50) NOT NULL UNIQUE,
        display_name VARCHAR(100) NOT NULL,
        api_key VARCHAR(255),
        api_secret VARCHAR(255),
        account_sid VARCHAR(255),
        auth_token VARCHAR(255),
        from_number VARCHAR(50),
        webhook_url VARCHAR(500),
        callback_url VARCHAR(500),
        is_active TINYINT(1) DEFAULT 1,
        is_default TINYINT(1) DEFAULT 0,
        priority INT DEFAULT 0,
        cost_per_minute DECIMAL(10,4) DEFAULT 0,
        tenant_id INT UNSIGNED DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_provider_active (provider_name, is_active),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✅ Created: voice_provider_configs\n";
} catch (\Throwable $e) {
    echo "❌ ERROR creating voice_provider_configs: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n✅ Done\n";