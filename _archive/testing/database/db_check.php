<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$stmt = $pdo->query('SHOW TABLES');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Total Tables: " . count($tables) . "\n\n";
echo "Tables:\n";
foreach($tables as $t) { echo "- $t\n"; }
