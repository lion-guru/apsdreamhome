<?php
/**
 * Add tenant_id to profile tables that lack it (associate_profiles, agent_profiles, customer_profiles).
 *
 * employee_profiles already has tenant_id (added during initial migration).
 * The other 3 profile tables need tenant_id for consistent tenant scoping
 * when services are migrated from legacy tables (associates, agents, customers)
 * to profile tables.
 *
 * Safe: idempotent (checks column existence before ALTER), populates from users table.
 * Zero data loss: tenant_id defaults to 1 (single-tenant default).
 *
 * @session 399
 */
require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

$tables = ['associate_profiles', 'agent_profiles', 'customer_profiles'];

foreach ($tables as $table) {
    // Check if table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    if (!$tableExists) {
        echo "SKIP: $table does not exist\n";
        continue;
    }

    // Check if tenant_id column already exists
    $colExists = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'tenant_id'")->fetch();
    if ($colExists) {
        echo "SKIP: $table already has tenant_id\n";
        continue;
    }

    // Add tenant_id column (same type as users.tenant_id: int unsigned, default 1)
    $pdo->exec("ALTER TABLE `$table` ADD COLUMN `tenant_id` int(10) unsigned NOT NULL DEFAULT 1 AFTER `user_id`");
    echo "ADDED: tenant_id to $table\n";

    // Populate tenant_id from users table
    $updated = $pdo->exec("
        UPDATE `$table` p
        JOIN users u ON u.id = p.user_id
        SET p.tenant_id = COALESCE(u.tenant_id, 1)
    ");
    echo "  POPULATED: $updated rows updated in $table\n";

    // Add index for tenant scoping performance
    try {
        $pdo->exec("CREATE INDEX idx_{$table}_tenant ON `$table` (tenant_id)");
        echo "  INDEX: idx_{$table}_tenant created\n";
    } catch (\Throwable $e) {
        echo "  INDEX: " . $e->getMessage() . "\n";
    }
}

// Verify
echo "\n=== Verification ===\n";
foreach ($tables as $table) {
    $tableExists = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    if (!$tableExists) continue;
    $col = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'tenant_id'")->fetch();
    if ($col) {
        $nullCount = $pdo->query("SELECT COUNT(*) as c FROM `$table` WHERE tenant_id IS NULL")->fetch()['c'];
        $distinct = $pdo->query("SELECT COUNT(DISTINCT tenant_id) as c FROM `$table`")->fetch()['c'];
        $total = $pdo->query("SELECT COUNT(*) as c FROM `$table`")->fetch()['c'];
        echo "  $table: tenant_id=YES, rows=$total, null=$nullCount, distinct_tenants=$distinct\n";
    } else {
        echo "  $table: tenant_id=NO (ERROR!)\n";
    }
}

echo "\nDone. All profile tables now have tenant_id for consistent tenant scoping.\n";
