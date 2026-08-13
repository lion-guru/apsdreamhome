<?php
require 'config/bootstrap.php';
try {
    $pdo = App\Core\Database\Database::getInstance()->getPdo();
    
    // Check tables
    $tables = ['districts', 'states', 'colonies'];
    foreach ($tables as $t) {
        $r = $pdo->query("SHOW TABLES LIKE '$t'");
        $exists = $r->rowCount() > 0;
        echo "$t: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    }
    
    // Test the NavigationHelper query
    $sql = "SELECT c.id, c.name, c.slug, d.name as district, s.name as state
            FROM colonies c
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE c.is_active = 1
            ORDER BY d.name, c.name";
    $result = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    echo "Query returned " . count($result) . " rows\n";
    foreach ($result as $row) {
        echo "  Colony: {$row['name']}, District: {$row['district']}, State: {$row['state']}\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
