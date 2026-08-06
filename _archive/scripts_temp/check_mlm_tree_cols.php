<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "=== mlm_network_tree columns ===\n";
$cols = $pdo->query("SHOW COLUMNS FROM mlm_network_tree")->fetchAll(PDO::FETCH_COLUMN, 0);
print_r($cols);

echo "\n=== network_tree columns ===\n";
$cols2 = $pdo->query("SHOW COLUMNS FROM network_tree")->fetchAll(PDO::FETCH_COLUMN, 0);
print_r($cols2);

echo "\n=== mlm_network_tree sample (first 5) ===\n";
$rows = $pdo->query("SELECT * FROM mlm_network_tree LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

echo "\n=== network_tree sample (first 5) ===\n";
$rows2 = $pdo->query("SELECT * FROM network_tree LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows2);

echo "\n=== ReferralService column check ===\n";
foreach (['ancestor_user_id', 'descendant_user_id'] as $col) {
    $exists = in_array($col, $cols);
    echo "$col in mlm_network_tree: " . ($exists ? "YES" : "NO") . "\n";
}
