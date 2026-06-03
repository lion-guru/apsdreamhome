<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "Total: " . count($tables) . "\n\n";
$broken = [];
foreach ($tables as $t) {
    try {
        $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    } catch (Exception $e) {
        $broken[] = ['table' => $t, 'error' => $e->getMessage()];
    }
}
echo "Broken: " . count($broken) . "\n";
foreach ($broken as $b) {
    echo "- {$b['table']}: {$b['error']}\n";
}
