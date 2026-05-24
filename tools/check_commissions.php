<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$cols = $pdo->query("SHOW COLUMNS FROM commissions")->fetchAll(PDO::FETCH_ASSOC);
echo "=== commissions columns ===\n";
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";
