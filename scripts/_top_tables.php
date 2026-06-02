<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

$ranked = [];
foreach ($tables as $t) {
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $ranked[$t] = $rows;
}
arsort($ranked);
echo "=== TOP 30 TABLES BY ROW COUNT (500+ tables remain) ===\n\n";
$i = 0;
foreach ($ranked as $t => $rows) {
    if ($i++ >= 30) break;
    echo sprintf("%4d  %s\n", $rows, $t);
}
