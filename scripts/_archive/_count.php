<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
echo 'Total: ' . $pdo->query('SHOW TABLES')->rowCount() . PHP_EOL;
