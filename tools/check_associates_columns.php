<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$cols = $pdo->query("SHOW COLUMNS FROM associates")->fetchAll(PDO::FETCH_ASSOC);
echo "=== associates columns ===\n";
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";

// Check network_tree
$cols2 = $pdo->query("SHOW COLUMNS FROM network_tree")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== network_tree columns ===\n";
foreach ($cols2 as $c) echo "  {$c['Field']} ({$c['Type']})\n";
