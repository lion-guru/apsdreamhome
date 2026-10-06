<?php
/**
 * Migration: Add soft delete columns (deleted_at) to key tables
 * Run: php scripts/migrate_soft_delete.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$db = Database::getInstance()->getPdo();

$tables = [
    'plots',
    'colonies',
    'land_records',
    'land_acquisitions',
    'colony_layouts',
    'colony_development_costs',
    'plot_bookings',
    'booking_payment_schedules',
    'leads',
    'leads',
    'users',
    'colonies',
    'plots',
];

echo "Adding soft delete columns (deleted_at)...\n";

foreach ($tables as $table) {
    try {
        $db->exec("ALTER TABLE `$table` ADD COLUMN `deleted_at` DATETIME NULL DEFAULT NULL AFTER `updated_at`");
        echo "✓ $table: added deleted_at\n";
    } catch (PDOException $e) {
        if ($e->getCode() === '42S21' || strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "- $table: deleted_at already exists\n";
        } else {
            echo "✗ $table: " . $e->getMessage() . "\n";
        }
    }
}

// Add indexes on deleted_at for filtered queries
$indexTables = ['plots', 'colonies', 'land_records', 'land_acquisitions', 'colony_layouts', 'leads', 'users'];
foreach ($indexTables as $table) {
    try {
        $db->exec("ALTER TABLE `$table` ADD INDEX idx_${table}_deleted_at (deleted_at)");
        echo "✓ ${table}: added idx_deleted_at\n";
    } catch (PDOException $e) {
        if ($e->getCode() === '42000' && strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "- $table: idx_deleted_at already exists\n";
        } else {
            echo "✗ $table: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nDone: soft delete columns added.\n";