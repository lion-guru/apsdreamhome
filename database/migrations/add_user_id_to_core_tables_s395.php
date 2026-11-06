<?php
/**
 * Migration: Add user_id columns and FK constraints — Session 395
 * Phase 4/7: Identity Unification — Link core tables to users table
 *
 * Adds user_id BIGINT UNSIGNED column + FK → users(id) to:
 *   1. bookings (currently uses customer_id)
 *   2. leads (currently uses assigned_to/created_by)
 *   3. plot_bookings (currently uses customer_id/associate_id)
 *
 * Idempotent: Uses ALTER TABLE ... ADD COLUMN IF NOT EXISTS equivalent.
 *
 * Run via: php database/migrations/add_user_id_to_core_tables_s395.php
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

$created = 0;
$skipped = 0;
$errors = 0;

// Helper: column exists check
function columnExists(PDO $pdo, string $table, string $column): bool {
    $row = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'")->fetch();
    return $row !== false;
}

// Helper: add column if not exists
function addColumn(PDO $pdo, string $table, string $column, string $definition): bool {
    if (columnExists($pdo, $table, $column)) {
        echo "⏭  SKIP: $table.$column already exists\n";
        return false;
    }
    try {
        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        echo "✅ CREATED: $table.$column\n";
        return true;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating $table.$column: " . $e->getMessage() . "\n";
        return false;
    }
}

// Helper: add FK if not exists
function addFK(PDO $pdo, string $table, string $column, string $refTable, string $refColumn): bool {
    $fkName = "fk_{$table}_{$column}";
    try {
        // Check if FK already exists
        $existing = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = '$table' AND COLUMN_NAME = '$column' AND CONSTRAINT_NAME LIKE 'fk_%'")->fetch();
        if ($existing) {
            echo "⏭  SKIP: FK $fkName already exists\n";
            return false;
        }
        $pdo->exec("ALTER TABLE `$table` ADD CONSTRAINT `$fkName` FOREIGN KEY (`$column`) REFERENCES `$refTable`(`$refColumn`) ON DELETE CASCADE");
        echo "✅ CREATED: FK $fkName on $table.$column → $refTable.$refColumn\n";
        return true;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating FK $fkName: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "━━━ Add user_id columns ━━━\n\n";

// 1. Add user_id to bookings
addColumn($pdo, 'bookings', 'user_id', 'BIGINT UNSIGNED NULL AFTER id');

// 2. Add user_id to leads
addColumn($pdo, 'leads', 'user_id', 'BIGINT UNSIGNED NULL AFTER id');

// 3. Add user_id to plot_bookings
addColumn($pdo, 'plot_bookings', 'user_id', 'BIGINT UNSIGNED NULL AFTER id');

// ─────────────────────────────────────────────────────────
// Populate user_id from existing columns
// ─────────────────────────────────────────────────────────
echo "\n━━━ Populate user_id from legacy columns ━━━\n\n";

// bookings: user_id ← customer_id
try {
    $result = $pdo->exec("UPDATE bookings SET user_id = customer_id WHERE user_id IS NULL AND customer_id IS NOT NULL");
    echo "✅ Populated bookings.user_id from customer_id: $result rows\n";
} catch (\Throwable $e) {
    echo "❌ ERROR populating bookings.user_id: " . $e->getMessage() . "\n";
    $errors++;
}

// plot_bookings: user_id ← customer_id (fallback to associate_id)
try {
    $result = $pdo->exec("UPDATE plot_bookings SET user_id = COALESCE(customer_id, associate_id) WHERE user_id IS NULL AND (customer_id IS NOT NULL OR associate_id IS NOT NULL)");
    echo "✅ Populated plot_bookings.user_id from customer_id/associate_id: $result rows\n";
} catch (\Throwable $e) {
    echo "❌ ERROR populating plot_bookings.user_id: " . $e->getMessage() . "\n";
    $errors++;
}

// leads: user_id ← assigned_to (fallback to created_by)
try {
    $result = $pdo->exec("UPDATE leads SET user_id = COALESCE(assigned_to, created_by) WHERE user_id IS NULL AND (assigned_to IS NOT NULL OR created_by IS NOT NULL)");
    echo "✅ Populated leads.user_id from assigned_to/created_by: $result rows\n";
} catch (\Throwable $e) {
    echo "❌ ERROR populating leads.user_id: " . $e->getMessage() . "\n";
    $errors++;
}

// ─────────────────────────────────────────────────────────
// Add FK constraints
// ─────────────────────────────────────────────────────────
echo "\n━━━ Add FK constraints ━━━\n\n";

addFK($pdo, 'bookings', 'user_id', 'users', 'id');
addFK($pdo, 'leads', 'user_id', 'users', 'id');
addFK($pdo, 'plot_bookings', 'user_id', 'users', 'id');

// ─────────────────────────────────────────────────────────
// Summary
// ─────────────────────────────────────────────────────────
echo "\n=== Summary ===\n";
echo "Columns added: $created\n";
echo "Errors: $errors\n";

if ($errors > 0) {
    echo "\n⚠️  Migration completed with errors\n";
    exit(1);
}

echo "\n✅ user_id columns and FKs migration complete!\n";
