<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
echo "=== users table columns ===\n";
$cols = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
foreach ($cols as $c) echo "  $c\n";
echo "\n=== associates table columns ===\n";
$cols = $db->query("DESCRIBE associates")->fetchAll(PDO::FETCH_COLUMN);
foreach ($cols as $c) echo "  $c\n";
echo "\n=== agents table columns ===\n";
$cols = $db->query("DESCRIBE agents")->fetchAll(PDO::FETCH_COLUMN);
foreach ($cols as $c) echo "  $c\n";
echo "\n=== employees table columns ===\n";
$cols = $db->query("DESCRIBE employees")->fetchAll(PDO::FETCH_COLUMN);
foreach ($cols as $c) echo "  $c\n";
echo "\n=== users sample rows ===\n";
$rows = $db->query("SELECT * FROM users LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    foreach ($r as $k => $v) echo "  $k: " . ($r[$k] ?? '') . "\n";
    echo "  ---\n";
}