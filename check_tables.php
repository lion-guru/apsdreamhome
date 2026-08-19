<?php
require 'vendor/autoload.php';

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

// Check for required tables
$required_tables = ['bookings', 'properties', 'mlm_profiles', 'api_tokens'];
$stmt = $pdo->query("SHOW TABLES");
$existing = [];
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $existing[] = $row[0];
}

echo "Existing tables: " . count($existing) . "\n";
foreach ($required_tables as $table) {
    $exists = in_array($table, $existing);
    echo "$table: " . ($exists ? "EXISTS" : "MISSING") . "\n";
}

// Check bookings table structure
if (in_array('bookings', $existing)) {
    echo "\nBookings table structure:\n";
    $stmt = $pdo->query("DESCRIBE bookings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  " . $row['Field'] . " " . $row['Type'] . "\n";
    }
}
