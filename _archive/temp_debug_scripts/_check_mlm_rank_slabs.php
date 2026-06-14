<?php
$c = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4", $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$rows = $pdo->query("SELECT id, rank_name, min_qualification, direct_commission_pct, team_override_pct, monthly_salary FROM mlm_rank_slabs ORDER BY min_qualification ASC")->fetchAll(PDO::FETCH_ASSOC);
echo "=== mlm_rank_slabs data ===\n";
echo str_pad('Rank', 20) . str_pad('Min Qualification', 20) . str_pad('Direct %', 10) . str_pad('Team %', 10) . str_pad('Monthly Salary', 15) . "\n";
echo str_repeat('-', 75) . "\n";
foreach ($rows as $r) {
    echo str_pad($r['rank_name'], 20) 
        . str_pad('₹' . number_format($r['min_qualification']), 20) 
        . str_pad($r['direct_commission_pct'] . '%', 10) 
        . str_pad($r['team_override_pct'] . '%', 10) 
        . str_pad('₹' . number_format($r['monthly_salary']), 15) 
        . PHP_EOL;
}
echo "\nTotal rows: " . count($rows) . "\n";
