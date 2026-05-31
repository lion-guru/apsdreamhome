<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $stmt = $db->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Total Tables: " . count($tables) . "\n";
    print_r($tables);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
