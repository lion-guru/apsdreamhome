<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
echo "=== api_keys table columns ===\n";
$cols = $pdo->query("DESCRIBE api_keys");
while ($c = $cols->fetch(PDO::FETCH_ASSOC)) echo "{$c['Field']} ({$c['Type']})\n";
echo "\n=== All data ===\n";
$keys = $pdo->query("SELECT * FROM api_keys")->fetchAll(PDO::FETCH_ASSOC);
foreach ($keys as $k) { print_r($k); }
