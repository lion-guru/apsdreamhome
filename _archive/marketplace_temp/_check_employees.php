<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
echo "=== Employees table exists? ===\n";
$r = $pdo->query("SHOW TABLES LIKE 'employees'")->fetch();
echo $r ? "YES" : "NO";
echo "\n\n=== users with role=employee ===\n";
$s = $pdo->query("SELECT id, name, role FROM users WHERE role='employee'");
foreach ($s as $r) echo $r['id'] . ' ' . $r['name'] . ' ' . $r['role'] . "\n";
echo "\n=== existing sidebar for marketplace ===\n";
$s = $pdo->query("SELECT id, title, url FROM sidebar_items WHERE url LIKE '%market%' OR url LIKE '%listing%' OR title LIKE '%Market%'");
foreach ($s as $r) echo $r['id'] . ' ' . $r['title'] . ' ' . $r['url'] . "\n";?>