<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$tables = ['property_verifications', 'carbon_footprint', 'energy_metrics', 'green_technologies', 'sustainability_goals'];
foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$t'");
        echo $t . ': ' . ($stmt->rowCount() ? 'EXISTS' : 'MISSING') . "\n";
    } catch (Exception $e) {
        echo $t . ': ERROR - ' . $e->getMessage() . "\n";
    }
}