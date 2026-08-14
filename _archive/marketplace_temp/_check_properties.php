<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
echo "=== user_properties columns ===\n";
foreach ($pdo->query('DESCRIBE user_properties') as $r) {
    echo $r['Field'] . ' ' . $r['Type'] . "\n";
}
echo "\n=== Row count ===\n";
echo 'Total: ' . $pdo->query('SELECT COUNT(*) FROM user_properties')->fetchColumn() . "\n";
echo "\n=== Statuses ===\n";
foreach ($pdo->query('SELECT status, COUNT(*) as c FROM user_properties GROUP BY status') as $r) {
    echo $r['status'] . ': ' . $r['c'] . "\n";
}
echo "\n=== Package tables ===\n";
foreach ($pdo->query("SHOW TABLES LIKE '%package%'") as $r) echo $r[0] . "\n";
echo "\n=== Premium tables ===\n";
foreach ($pdo->query("SHOW TABLES LIKE '%premium%'") as $r) echo $r[0] . "\n";
echo "\n=== User roles ===\n";
foreach ($pdo->query('SELECT DISTINCT role FROM users ORDER BY role') as $r) echo $r['role'] . "\n";
echo "\n=== Listing types in user_properties ===\n";
foreach ($pdo->query('SELECT DISTINCT listing_type FROM user_properties') as $r) echo $r['listing_type'] . "\n";
echo "\n=== Property types in user_properties ===\n";
foreach ($pdo->query('SELECT DISTINCT property_type FROM user_properties') as $r) echo $r['property_type'] . "\n";?>