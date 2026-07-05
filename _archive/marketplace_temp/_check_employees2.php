<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
echo "=== employees table cols ===\n";
foreach ($pdo->query('DESCRIBE employees') as $r) echo $r['Field'] . ' ' . $r['Type'] . "\n";
echo "\n=== Employee department options ===\n";
$s = $pdo->query("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND department != ''");
foreach ($s as $r) echo $r['department'] . "\n";
echo "\n=== Admin sidebar file ===\n";
