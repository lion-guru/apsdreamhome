<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$stmt = $pdo->query('DESC ai_api_logs');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo implode(' | ', $row) . "\n";
}