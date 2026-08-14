<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

// Check mlm_settings schema
$r = $pdo->query('SHOW CREATE TABLE mlm_settings');
$row = $r->fetch(PDO::FETCH_ASSOC);
echo "=== mlm_settings ===\n";
echo $row['Create Table'] . "\n\n";

// Check mlm_network_tree schema
$r = $pdo->query('SHOW CREATE TABLE mlm_network_tree');
$row = $r->fetch(PDO::FETCH_ASSOC);
echo "=== mlm_network_tree ===\n";
echo $row['Create Table'] . "\n\n";

// Check mlm_commission_ledger columns
echo "=== mlm_commission_ledger columns ===\n";
$r = $pdo->query('SHOW COLUMNS FROM mlm_commission_ledger');
while ($c = $r->fetch(PDO::FETCH_ASSOC)) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | Null=' . $c['Null'] . ' | Default=' . $c['Default'] . "\n";
}?>