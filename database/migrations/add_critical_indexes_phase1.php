<?php

/**
 * Migration: Add Critical Database Indexes (Phase 1)
 * 
 * These indexes support the most common query patterns identified from
 * slow query analysis and controller/service code review.
 * All indexes are idempotent - re-run will SKIP if index already exists.
 * 
 * Run: php database/migrations/add_critical_indexes_phase1.php
 */

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance()->getConnection();

$indexes = [
    // leads table - source + created_at for source-based time-series queries
    [
        'table' => 'leads',
        'name'  => 'idx_leads_source_created',
        'cols'  => ['source', 'created_at'],
    ],
    
    // plot_bookings - customer + status for "my bookings" filtered by status
    [
        'table' => 'plot_bookings',
        'name'  => 'idx_pb_customer_status',
        'cols'  => ['customer_id', 'status'],
    ],
    
    // plot_bookings - plot + status for "plot bookings" filtered by status
    [
        'table' => 'plot_bookings',
        'name'  => 'idx_pb_plot_status',
        'cols'  => ['plot_id', 'status'],
    ],
    
    // payments - customer + status for "my payments" filtered by status
    [
        'table' => 'payments',
        'name'  => 'idx_payments_customer_status',
        'cols'  => ['customer_id', 'status'],
    ],
    
    // payments - booking + status for "booking payments" filtered by status
    [
        'table' => 'payments',
        'name'  => 'idx_payments_booking_status',
        'cols'  => ['booking_id', 'status'],
    ],
    
    // site_visits - status + visit_date for "upcoming visits" queries
    [
        'table' => 'site_visits',
        'name'  => 'idx_sv_status_date',
        'cols'  => ['status', 'visit_date'],
    ],
    
    // site_visits - assigned_to + status for "my assigned visits" filtered by status
    [
        'table' => 'site_visits',
        'name'  => 'idx_sv_assigned_status',
        'cols'  => ['assigned_to', 'status'],
    ],
    
    // maintenance_dues - customer + status for "my dues" filtered by status
    [
        'table' => 'maintenance_dues',
        'name'  => 'idx_md_customer_status',
        'cols'  => ['customer_id', 'status'],
    ],
    
    // plot_bookings - status + created_at for "recent bookings" filtered by status
    [
        'table' => 'plot_bookings',
        'name'  => 'idx_pb_status_created',
        'cols'  => ['status', 'created_at'],
    ],
];

echo "=== Adding Critical Database Indexes (Phase 1) ===\n\n";

$created = 0;
$skipped = 0;
$errors = 0;

foreach ($indexes as $idx) {
    $table = $idx['table'];
    $name  = $idx['name'];
    $cols  = implode(', ', array_map(fn($c) => "`$c`", $idx['cols']));
    
    // Check if index already exists
    $check = $db->prepare("
        SELECT COUNT(*) FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?
    ");
    $check->execute([$table, $name]);
    $exists = (int)$check->fetchColumn() > 0;
    
    if ($exists) {
        echo "⏭  SKIP: $name on $table (already exists)\n";
        $skipped++;
        continue;
    }
    
    // Create the index
    $sql = "ALTER TABLE `$table` ADD INDEX `$name` ($cols)";
    
    try {
        $db->exec($sql);
        echo "✅  CREATED: $name on $table ($cols)\n";
        $created++;
    } catch (PDOException $e) {
        echo "❌  ERROR: $name on $table - " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== Summary ===\n";
echo "Created: $created\n";
echo "Skipped: $skipped\n";
echo "Errors:  $errors\n";

if ($errors === 0) {
    echo "\n✅ All indexes processed successfully!\n";
    exit(0);
} else {
    echo "\n❌ Some indexes failed. Check errors above.\n";
    exit(1);
}