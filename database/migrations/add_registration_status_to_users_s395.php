<?php
/**
 * Migration: Add registration_status column to users table
 * 
 * The findUser() method in AuthController queries registration_status = 'approved',
 * but this column was never added to the users table. This causes login to fail
 * silently (SQL error caught, redirect back to login), but probes see 302 and
 * assume success.
 * 
 * Idempotent: Uses ALTER TABLE ADD COLUMN IF NOT EXISTS pattern.
 * 
 * Run via: php database/migrations/add_registration_status_to_users_s395.php
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

// Check if column already exists
$cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'registration_status'")->fetch();
if ($cols) {
    echo "⏭  SKIP: registration_status column already exists\n";
    exit(0);
}

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN registration_status ENUM('pending','approved','rejected') DEFAULT 'approved' AFTER status");
    echo "✅ CREATED: users.registration_status column\n";
} catch (\Throwable $e) {
    echo "❌ ERROR adding registration_status: " . $e->getMessage() . "\n";
    exit(1);
}

// Update all existing users to have 'approved' registration_status
try {
    $affected = $pdo->exec("UPDATE users SET registration_status = 'approved' WHERE registration_status IS NULL OR registration_status = ''");
    echo "✅ Updated $affected existing users to registration_status='approved'\n";
} catch (\Throwable $e) {
    echo "⚠️  Could not update existing users: " . $e->getMessage() . "\n";
}

echo "\n✅ Migration complete!\n";