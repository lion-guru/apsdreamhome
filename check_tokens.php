<?php
require 'vendor/autoload.php';
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$stmt = $pdo->query('DESCRIBE api_tokens');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . ' ' . $row['Type'] . PHP_EOL;
}