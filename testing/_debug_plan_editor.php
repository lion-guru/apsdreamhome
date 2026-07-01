<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== mlm_rank_benefits schema ===\n";
$rows = $pdo->query("SHOW COLUMNS FROM mlm_rank_benefits")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  {$r['Field']} ({$r['Type']})\n";

echo "\n=== mlm_rank_benefits data ===\n";
$rows = $pdo->query("SELECT * FROM mlm_rank_benefits ORDER BY rank_order")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  " . json_encode($r) . "\n";

echo "\n=== mlm_settings data ===\n";
$rows = $pdo->query("SELECT * FROM mlm_settings")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  " . json_encode($r) . "\n";

echo "\n=== mlm_levels data ===\n";
$rows = $pdo->query("SELECT * FROM mlm_levels ORDER BY level_number")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  " . json_encode($r) . "\n";

echo "\n=== Does mlm_rank_benefits have color_code column? ===\n";
$cols = array_column($rows, 'Field');
$rbCols = $pdo->query("SHOW COLUMNS FROM mlm_rank_benefits")->fetchAll(PDO::FETCH_COLUMN);
echo "  " . (in_array('color_code', $rbCols) ? 'YES' : 'NO') . "\n";
echo "  " . (in_array('badge_icon', $rbCols) ? 'YES' : 'NO') . "\n";
echo "  " . (in_array('rank_order', $rbCols) ? 'YES' : 'NO') . "\n";
