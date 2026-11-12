<?php
/**
 * Migration: Add missing foreign key & lookup performance indexes (S454)
 *
 * Adds indexes on:
 * - plot_bookings: colony_id, associate_id, sales_manager_id
 * - properties: category_id, land_id
 * - payments: transaction_id
 *
 * Idempotent: checks if index already exists on the table before creating.
 *
 * Run via: php database/migrations/add_performance_indexes_s454.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "OK  Database connected\n\n";
} catch (\Throwable $e) {
    echo "ERR Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$indexes = [
    'plot_bookings' => [
        'idx_plot_bookings_colony_id' => 'colony_id',
        'idx_plot_bookings_associate_id' => 'associate_id',
        'idx_plot_bookings_sales_mgr_id' => 'sales_manager_id',
    ],
    'properties' => [
        'idx_properties_category_id' => 'category_id',
        'idx_properties_land_id' => 'land_id',
    ],
    'payments' => [
        'idx_payments_transaction_id' => 'transaction_id',
    ],
];

foreach ($indexes as $table => $tableIndexes) {
    $tableExists = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    if (!$tableExists) {
        echo "SKIP Table '$table' does not exist\n";
        continue;
    }

    // Get existing index names
    $existingIndexes = [];
    $stmt = $pdo->query("SHOW INDEX FROM `$table`");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $existingIndexes[$row['Key_name']] = true;
    }

    foreach ($tableIndexes as $indexName => $columnName) {
        if (isset($existingIndexes[$indexName])) {
            echo "SKIP Index '$indexName' already exists on `$table`\n";
            continue;
        }

        // Verify column exists
        $colStmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$columnName'");
        if (!$colStmt->fetch()) {
            echo "SKIP Column '$columnName' does not exist in `$table`\n";
            continue;
        }

        try {
            $pdo->exec("CREATE INDEX `$indexName` ON `$table` (`$columnName`)");
            echo "OK   Created index '$indexName' on `$table` (`$columnName`)\n";
        } catch (\Throwable $e) {
            echo "ERR  Failed to create index '$indexName' on `$table`: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nMigration S454 completed successfully.\n";

