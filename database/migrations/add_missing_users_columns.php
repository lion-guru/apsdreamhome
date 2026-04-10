<?php

/**
 * Migration: Add Missing Columns to Users Table
 * This migration adds columns needed for RBAC, Wallet System, and Authentication
 */

// Load environment variables
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3307;dbname=apsdreamhome',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Adding missing columns to users table...\n";

    // Check if customer_id column exists
    $columnExists = $pdo->query("SHOW COLUMNS FROM users LIKE 'customer_id'")->rowCount() > 0;

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN customer_id VARCHAR(50) AFTER id");
        echo "✅ Added customer_id column\n";
    } else {
        echo "⏭️  customer_id column already exists\n";
    }

    // Check if referral_code column exists
    $columnExists = $pdo->query("SHOW COLUMNS FROM users LIKE 'referral_code'")->rowCount() > 0;

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN referral_code VARCHAR(50) AFTER referred_by");
        echo "✅ Added referral_code column\n";
    } else {
        echo "⏭️  referral_code column already exists\n";
    }

    // Check if referred_by column exists
    $columnExists = $pdo->query("SHOW COLUMNS FROM users LIKE 'referred_by'")->rowCount() > 0;

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN referred_by INT DEFAULT NULL AFTER referral_code");
        $pdo->exec("ALTER TABLE users ADD INDEX idx_referred_by (referred_by)");
        echo "✅ Added referred_by column\n";
    } else {
        echo "⏭️  referred_by column already exists\n";
    }

    // Check if user_type column exists
    $columnExists = $pdo->query("SHOW COLUMNS FROM users LIKE 'user_type'")->rowCount() > 0;

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN user_type ENUM('customer', 'associate', 'agent', 'admin', 'employee', 'builder', 'investor') DEFAULT 'customer' AFTER role");
        $pdo->exec("ALTER TABLE users ADD INDEX idx_user_type (user_type)");
        echo "✅ Added user_type column\n";
    } else {
        echo "⏭️  user_type column already exists\n";
    }

    // Check if email_verified_at column exists
    $columnExists = $pdo->query("SHOW COLUMNS FROM users LIKE 'email_verified_at'")->rowCount() > 0;

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL AFTER status");
        echo "✅ Added email_verified_at column\n";
    } else {
        echo "⏭️  email_verified_at column already exists\n";
    }

    // Check if remember_token column exists
    $columnExists = $pdo->query("SHOW COLUMNS FROM users LIKE 'remember_token'")->rowCount() > 0;

    if (!$columnExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN remember_token VARCHAR(100) DEFAULT NULL AFTER email_verified_at");
        echo "✅ Added remember_token column\n";
    } else {
        echo "⏭️  remember_token column already exists\n";
    }

    // Update role enum to include more roles
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'employee', 'associate', 'agent', 'builder', 'investor', 'super_admin', 'ceo', 'cfo', 'coo', 'cto', 'cmo', 'chro', 'director', 'manager') DEFAULT 'user'");
    echo "✅ Updated role enum with additional roles\n";

    // Add foreign key constraint for referred_by
    $fkExists = $pdo->query("
        SELECT COUNT(*) as count 
        FROM information_schema.table_constraints 
        WHERE constraint_schema = DATABASE() 
        AND table_name = 'users' 
        AND constraint_name = 'fk_users_referred_by'
    ")->fetchColumn();

    if (!$fkExists) {
        $pdo->exec("ALTER TABLE users ADD CONSTRAINT fk_users_referred_by FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL");
        echo "✅ Added foreign key constraint for referred_by\n";
    } else {
        echo "⏭️  Foreign key constraint already exists\n";
    }

    echo "\n✅ Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
