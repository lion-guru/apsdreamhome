<?php
require_once __DIR__ . '/../../app/core/App.php';
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "Connecting to database via App::database()...\n";
try {
    $db = \App\Core\App::database();
    echo "Connected successfully!\n";
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

function show_columns($db, $table) {
    echo "--- $table columns ---\n";
    try {
        $rows = $db->fetchAll("DESCRIBE `$table` ");
        foreach ($rows as $row) {
            echo "- " . ($row['Field'] ?? 'Field') . " (" . ($row['Type'] ?? 'Type') . ")\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

function count_rows($db, $table) {
    try {
        $row = $db->fetchOne("SELECT COUNT(*) as count FROM `$table` ");
        return $row['count'] ?? 0;
    } catch (Exception $e) {
        return "Error: " . $e->getMessage();
    }
}

$tables = [
    'farmers',
    'farmer_profiles',
    'land_acquisitions',
    'land_purchases',
    'farmer_land_holdings'
];

foreach ($tables as $table) {
    echo "$table count: " . count_rows($db, $table) . "\n";
}

// Example migration/cleanup logic (commented out for safety unless intended)
/*
echo "Updating land_acquisitions foreign key...\n";
$db->execute("ALTER TABLE land_acquisitions DROP FOREIGN KEY IF EXISTS land_acquisitions_ibfk_1");
$db->execute("ALTER TABLE land_acquisitions ADD CONSTRAINT land_acquisitions_ibfk_1 FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(id) ON DELETE SET NULL");

echo "Dropping empty farmers table...\n";
$db->execute("DROP TABLE IF EXISTS farmers");
*/

echo "Checking for remaining farmer tables...\n";
$rows = $db->fetchAll("SHOW TABLES LIKE '%farmer%'");
foreach ($rows as $row) {
    echo "- " . array_values($row)[0] . "\n";
}
