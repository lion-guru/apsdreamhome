<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database;

try {
    $db = Database::getInstance();

    $tables = [
        'users',
        'user',
        'associates',
        'agents',
        'customers',
        'mlm_profiles',
        'user_activity',
        'chart_of_accounts',
        'bank_accounts',
        'customers_ledger',
        'suppliers',
        'income_records',
        'expenses',
        'properties',
        'property',
        'property_images',
        'property_types',
        'city',
        'bookings',
        'employees'
    ];

    echo "Checking tables...\n";
    foreach ($tables as $table) {
        try {
            $count = $db->fetchColumn("SELECT COUNT(*) FROM $table");
            echo "Table '$table' exists. Rows: $count\n";

            // Show columns
            $columns = $db->fetchAll("DESCRIBE $table");
            echo "Columns: " . implode(', ', array_column($columns, 'Field')) . "\n\n";
        } catch (Exception $e) {
            echo "Table '$table' does not exist or error: " . $e->getMessage() . "\n\n";
        }
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
