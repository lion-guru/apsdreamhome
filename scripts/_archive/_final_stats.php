<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
echo 'Total tables: ' . $pdo->query('SHOW TABLES')->rowCount() . PHP_EOL;
$rows = $pdo->query('SELECT SUM(TABLE_ROWS) FROM information_schema.TABLES WHERE TABLE_SCHEMA="apsdreamhome"')->fetchColumn();
echo 'Total rows: ' . number_format($rows) . PHP_EOL;
