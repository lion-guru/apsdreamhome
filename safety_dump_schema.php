<?php
/**
 * Safety dump - schema only (data dump via mysqldump if needed)
 * This creates a schema-only SQL dump of key tables before FK changes.
 */
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');

$tables = ['bookings', 'leads', 'plot_bookings', 'payments', 'users'];

$output = "-- Safety dump: apsdreamhome schema only\n";
$output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= "-- Tables: " . implode(', ', $tables) . "\n";
$output .= "-- SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    // Check if table exists
    $exists = $db->query("SHOW TABLES LIKE '$table'")->fetchColumn();
    if (!$exists) {
        $output .= "-- Table: $table (DOES NOT EXIST, skipping)\n\n";
        echo "  $table: DOES NOT EXIST\n";
        continue;
    }
    try {
        $row = $db->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_ASSOC);
        $output .= "-- Table: $table\n";
        $output .= $row['Create Table'] . ";\n\n";
    } catch (\Throwable $e) {
        $output .= "-- Table: $table (ERROR: " . $e->getMessage() . ")\n\n";
        echo "  $table: ERROR - " . $e->getMessage() . "\n";
        continue;
    }
}

$output .= "-- SET FOREIGN_KEY_CHECKS=1;\n";

file_put_contents('backups/db/apsdreamhome_pre-identity-unification-fk-updates_2026-11-06_schema_only.sql', $output);
echo "Schema dump saved. Size: " . strlen($output) . " bytes\n";

// Also get row counts for verification
echo "\nRow counts:\n";
foreach ($tables as $table) {
    $exists = $db->query("SHOW TABLES LIKE '$table'")->fetchColumn();
    if (!$exists) {
        echo "  $table: DOES NOT EXIST\n";
        continue;
    }
    $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
    echo "  $table: $count rows\n";
}