<?php
require_once __DIR__ . '/vendor/autoload.php';

$pdo = \App\Core\Database\Database::getInstance()->getPdo();

echo "=== mlm_rank_history Table Structure ===\n\n";
$rows = $pdo->query("SHOW CREATE TABLE mlm_rank_history")->fetchAll(PDO::FETCH_ASSOC);
echo $rows[0]['Create Table'] . "\n\n";

echo "=== Current Data ===\n";
$data = $pdo->query("SELECT * FROM mlm_rank_history LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($data as $row) {
    print_r($row);
}?>