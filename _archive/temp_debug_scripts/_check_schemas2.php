<?php
$c = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4", $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== mlm_rank_slabs schema ===\n";
$r = $pdo->query("SHOW CREATE TABLE mlm_rank_slabs");
$row = $r->fetch(PDO::FETCH_ASSOC);
echo $row['Create Table'] . "\n\n";

echo "=== mlm_rank_slabs data ===\n";
$rows = $pdo->query("SELECT * FROM mlm_rank_slabs ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
if (count($rows) > 0) {
    echo "Columns: " . implode(', ', array_keys($rows[0])) . "\n\n";
    foreach ($rows as $r) {
        echo "id={$r['id']} ";
        foreach ($r as $k => $v) {
            if ($k !== 'id' && $k !== 'created_at') echo "{$k}={$v} ";
        }
        echo "\n";
    }
} else {
    echo "TABLE IS EMPTY\n";
}
echo "Total rows: " . count($rows) . "\n";

echo "\n=== service_interests columns ===\n";
$r2 = $pdo->query("SHOW COLUMNS FROM service_interests");
$cols = $r2->fetchAll(PDO::FETCH_COLUMN);
echo implode(', ', $cols) . "\n";
