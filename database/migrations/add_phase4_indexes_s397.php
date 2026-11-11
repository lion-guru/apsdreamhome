<?php
/**
 * Migration: Add Phase 4 Indexes — Session 397
 * Phase 4: Database Indexes + Identity Unification
 *
 * Adds the 7 missing indexes identified in the Phase 4 plan.
 *
 * Idempotent: Checks before creating each index.
 *
 * Run via: php database/migrations/add_phase4_indexes_s397.php
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

function addIndex(PDO $pdo, string $table, string $indexName, string $columns): bool {
    $exists = $pdo->query("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'")->fetch();
    if ($exists) {
        echo "⏭  SKIP: $indexName on $table already exists\n";
        return false;
    }
    try {
        $pdo->exec("CREATE INDEX `$indexName` ON `$table` ($columns)");
        echo "✅ CREATED: $indexName on $table ($columns)\n";
        return true;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating $indexName on $table: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "━━━ Phase 4 Critical Indexes ━━━\n\n";

// Leads indexes
addIndex($pdo, 'leads', 'idx_leads_status_created', 'status, created_at');
addIndex($pdo, 'leads', 'idx_leads_assigned_status', 'assigned_to, status');
addIndex($pdo, 'leads', 'idx_leads_source_created', 'source, created_at');

// Bookings indexes
addIndex($pdo, 'bookings', 'idx_bookings_customer_status', 'customer_id, status');
addIndex($pdo, 'bookings', 'idx_bookings_plot_status', 'plot_id, status');

// Notifications index
addIndex($pdo, 'notifications', 'idx_notifications_user_read', 'user_id, is_read, created_at');

// Lead activities index
addIndex($pdo, 'lead_activities', 'idx_lead_activities_lead_created', 'lead_id, created_at');

echo "\n=== Summary ===\n";
echo "Indexes created: $created\n";
echo "Errors: $errors\n";
echo "\n✅ Phase 4 missing indexes migration complete!\n";
