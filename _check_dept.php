<?php
require 'config/bootstrap.php';
$db = db_connect();

// Check departments table
$r = $db->query("SHOW TABLES LIKE 'departments'");
$found = count($r->fetchAll());
echo "departments table: " . ($found > 0 ? "EXISTS" : "MISSING") . PHP_EOL;

if ($found > 0) {
    $r2 = $db->query("DESCRIBE departments");
    echo "--- Schema ---" . PHP_EOL;
    foreach ($r2->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")" . PHP_EOL;
    }
    
    $r3 = $db->query("SELECT COUNT(*) as cnt FROM departments");
    echo "Row count: " . $r3->fetch()['cnt'] . PHP_EOL;
}

// Check designations table
$r = $db->query("SHOW TABLES LIKE 'designations'");
$found = count($r->fetchAll());
echo PHP_EOL . "designations table: " . ($found > 0 ? "EXISTS" : "MISSING") . PHP_EOL;

if ($found > 0) {
    $r2 = $db->query("DESCRIBE designations");
    echo "--- Schema ---" . PHP_EOL;
    foreach ($r2->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")" . PHP_EOL;
    }
    
    $r3 = $db->query("SELECT COUNT(*) as cnt FROM designations");
    echo "Row count: " . $r3->fetch()['cnt'] . PHP_EOL;
}
