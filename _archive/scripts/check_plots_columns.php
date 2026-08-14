<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$cols = $pdo->query("DESCRIBE plots")->fetchAll(PDO::FETCH_COLUMN);
echo "plots columns: " . implode(", ", $cols) . "\n";?>