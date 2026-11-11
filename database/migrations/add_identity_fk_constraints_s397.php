<?php
/**
 * Migration: Add FK constraints for Identity Unification
 *
 * Adds foreign key constraints on booking/lead/payment tables to reference
 * users(id) instead of legacy associates/agents/employees tables.
 *
 * All existing data already references valid users.id values (verified: 0 orphans).
 * Tables are empty except users (6 rows), so this is safe.
 *
 * Idempotent: checks if FK constraint already exists before adding.
 *
 * Run via: php database/migrations/add_identity_fk_constraints_s397.php
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

function fkExists(PDO $pdo, string $table, string $fkName): bool {
    $row = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = '$table' 
        AND CONSTRAINT_NAME = '$fkName'
        AND CONSTRAINT_NAME != 'PRIMARY'
    ")->fetch();
    return $row !== false;
}

function addFk(PDO $pdo, string $table, string $fkName, string $column, string $refTable, string $refColumn, string $onDelete): void {
    if (fkExists($pdo, $table, $fkName)) {
        echo "⏭  SKIP: $fkName on $table.$column already exists\n";
        return;
    }
    try {
        $pdo->exec("
            ALTER TABLE $table 
            ADD CONSTRAINT $fkName 
            FOREIGN KEY ($column) REFERENCES $refTable($refColumn) 
            ON DELETE $onDelete
        ");
        echo "✅ ADDED: $fkName on $table.$column → $refTable.$refColumn ($onDelete)\n";
    } catch (\Throwable $e) {
        echo "❌ ERROR adding $fkName: " . $e->getMessage() . "\n";
    }
}

echo "━━━ Adding FK constraints for Identity Unification ━━━\n\n";

// bookings.customer_id → users(id) ON DELETE CASCADE (booking belongs to customer)
addFk($pdo, 'bookings', 'fk_bookings_customer_id_users', 'customer_id', 'users', 'id', 'CASCADE');

// leads.assigned_to → users(id) ON DELETE SET NULL (assignment can be cleared)
addFk($pdo, 'leads', 'fk_leads_assigned_to_users', 'assigned_to', 'users', 'id', 'SET NULL');

// plot_bookings.customer_id → users(id) ON DELETE CASCADE (booking belongs to customer)
addFk($pdo, 'plot_bookings', 'fk_plot_bookings_customer_id_users', 'customer_id', 'users', 'id', 'CASCADE');

// plot_bookings.associate_id → users(id) ON DELETE SET NULL (associate can be unassigned)
addFk($pdo, 'plot_bookings', 'fk_plot_bookings_associate_id_users', 'associate_id', 'users', 'id', 'SET NULL');

// payments.user_id → users(id) ON DELETE SET NULL (payment can exist without user)
addFk($pdo, 'payments', 'fk_payments_user_id_users', 'user_id', 'users', 'id', 'SET NULL');

echo "\n✅ Migration complete\n";