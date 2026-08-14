<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$triggers = $pdo->query('SHOW TRIGGERS')->fetchAll(PDO::FETCH_COLUMN);
echo "Triggers: " . count($triggers) . "\n";
foreach ($triggers as $t) echo "  $t\n";
$events = $pdo->query('SHOW EVENTS')->fetchAll(PDO::FETCH_COLUMN);
echo "Events: " . count($events) . "\n";
foreach ($events as $e) echo "  $e\n";?>