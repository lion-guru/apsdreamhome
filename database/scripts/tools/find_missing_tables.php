<?php
// Script to find missing tables by comparing current database with SQL dump

// Load DB config and fetch actual current tables from database
require_once __DIR__ . '/../includes/db_config.php';

$current_tables = [];

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('DB connection failed: ' . $conn->connect_error);
    }
    $result = $conn->query('SHOW TABLES');
    if ($result) {
        while ($row = $result->fetch_array()) {
            $current_tables[] = $row[0];
        }
        sort($current_tables);
    }
    $conn->close();
} catch (Exception $e) {
    echo "Warning: Could not fetch current tables from DB (" . $e->getMessage() . ")\n";
    // Fallback to previously known tables if DB not reachable
    $current_tables = [
        'associates',
        'bookings', 
        'booking_summary',
        'customers',
        'properties',
        'users'
    ];
}

// Read the main SQL file and extract table names
$sql_file = 'apsdreamhome.sql';
$all_tables = [];

if (file_exists($sql_file)) {
    $content = file_get_contents($sql_file);
    
    // Extract CREATE TABLE statements
    preg_match_all('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?[`]?([^`\s]+)[`]?\s*\(/i', $content, $matches);
    
    if (!empty($matches[1])) {
        $all_tables = array_unique($matches[1]);
        sort($all_tables);
    }
}

// Also check other SQL files in database directory
$other_sql_files = [
    'apsdreamhome copy.sql',
    'main_databases/apsdreamhomes.sql',
    'diff_output.txt'
];

foreach ($other_sql_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        preg_match_all('/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?[`]?([^`\s]+)[`]?\s*\(/i', $content, $matches);
        if (!empty($matches[1])) {
            $all_tables = array_merge($all_tables, $matches[1]);
        }
    }
}

$all_tables = array_unique($all_tables);
sort($all_tables);

echo "=== DATABASE TABLE ANALYSIS ===\n\n";

echo "Current tables in your database (" . count($current_tables) . "):\n";
foreach ($current_tables as $table) {
    echo "  - $table\n";
}

echo "\nTotal tables in SQL files (" . count($all_tables) . "):\n";
foreach ($all_tables as $table) {
    echo "  - $table\n";
}

// Find missing tables
$missing_tables = array_diff($all_tables, $current_tables);
$extra_tables = array_diff($current_tables, $all_tables);

echo "\n=== ANALYSIS RESULTS ===\n";
echo "Missing tables (need to be created): " . count($missing_tables) . "\n";
if (!empty($missing_tables)) {
    foreach ($missing_tables as $table) {
        echo "  - $table\n";
    }
} else {
    echo "  None - all tables are present!\n";
}

echo "\nExtra tables (in database but not in SQL): " . count($extra_tables) . "\n";
if (!empty($extra_tables)) {
    foreach ($extra_tables as $table) {
        echo "  - $table\n";
    }
} else {
    echo "  None\n";
}

// Generate SQL script to create missing tables
if (!empty($missing_tables)) {
    echo "\n=== GENERATING RESTORATION SCRIPT ===\n";
    
    $restoration_sql = "-- Restoration script for missing tables\n";
    $restoration_sql .= "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($missing_tables as $table) {
        // Try to find the CREATE TABLE statement for this table
        $pattern = '/CREATE TABLE\s+(?:IF NOT EXISTS\s+)?[`]?' . preg_quote($table, '/') . '[`]?\s*\([^;]+;/s';
        
        $found = false;
        foreach (array_merge(['apsdreamhome.sql'], $other_sql_files) as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (preg_match($pattern, $content, $table_match)) {
                    $restoration_sql .= $table_match[0] . "\n\n";
                    $found = true;
                    break;
                }
            }
        }
        
        if (!$found) {
            echo "Warning: Could not find CREATE TABLE statement for '$table'\n";
        }
    }
    
    // Save restoration script
    file_put_contents('restore_missing_tables.sql', $restoration_sql);
    echo "Restoration script saved to: restore_missing_tables.sql\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total tables in SQL files: " . count($all_tables) . "\n";
echo "Current tables in database: " . count($current_tables) . "\n";
echo "Missing tables: " . count($missing_tables) . "\n";
echo "Tables to restore: " . count($missing_tables) . "\n";

?>
