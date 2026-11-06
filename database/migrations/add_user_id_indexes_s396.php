<?php
/**
 * Migration: Add user_id indexes — Session 396
 * Phase 4/7: Identity Unification — Performance optimization
 *
 * Adds indexes on user_id columns to speed up queries that filter by user_id.
 *
 * Run via: php database/migrations/add_user_id_indexes_s396.php
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

// Helper: index exists check
function indexExists(PDO $pdo, string $table, string $indexName): bool {
    $row = $pdo->query("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'")->fetch();
    return $row !== false;
}

// Helper: add index if not exists
function addIndex(PDO $pdo, string $table, string $indexName, string $columns): bool {
    if (indexExists($pdo, $table, $indexName)) {
        echo "⏭  SKIP: $table.$indexName already exists\n";
        return false;
    }
    try {
        $pdo->exec("CREATE INDEX `$indexName` ON `$table` ($columns)");
        echo "✅ CREATED: $table.$indexName ($columns)\n";
        return true;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating $table.$indexName: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "━━━ Add user_id indexes ━━━\n\n";

// bookings.user_id
addIndex($pdo, 'bookings', 'idx_bookings_user_id', 'user_id');
// leads.user_id
addIndex($pdo, 'leads', 'idx_leads_user_id', 'user_id');
// plot_bookings.user_id
addIndex($pdo, 'plot_bookings', 'idx_plot_bookings_user_id', 'user_id');
// payments.user_id
addIndex($pdo, 'payments', 'idx_payments_user_id', 'user_id');

// Also add user_id to notifications if it exists
try {
    $pdo->exec("ALTER TABLE notifications ADD COLUMN IF NOT EXISTS user_id BIGINT UNSIGNED AFTER id");
    echo "✅ CREATED: notifications.user_id\n";
    addIndex($pdo, 'notifications', 'idx_notifications_user_id', 'user_id');
} catch (\Throwable $e) {
    // Column might already exist or table might not exist - skip
    echo "ℹ️  SKIP: notifications.user_id (" . $e->getMessage() . ")\n";
}

echo "\nDone.\n";
