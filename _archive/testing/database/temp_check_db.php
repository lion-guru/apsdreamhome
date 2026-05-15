<?php
require 'c:/xampp/htdocs/apsdreamhome/vendor/autoload.php';
use App\Core\Database\Database;

$db = Database::getInstance();
$tables = ['lead_deals', 'properties', 'leads', 'users', 'deals', 'bookings', 'property_listings'];

foreach ($tables as $table) {
    echo "=== $table ===\n";
    try {
        $cols = $db->query("DESCRIBE $table")->fetchAll();
        foreach ($cols as $col) {
            echo $col['Field'] . " - " . $col['Type'] . "\n";
        }
    } catch (Exception $e) {
        echo "Table not found or error\n";
    }
    echo "\n";
}
