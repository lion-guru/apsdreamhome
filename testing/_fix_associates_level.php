<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Check current actual values
echo "=== CURRENT associates.level VALUES ===\n";
$rows = $pdo->query("SELECT id, user_id, level, HEX(level) as hex_val FROM associates ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$counts = [];
foreach ($rows as $r) {
    $lv = $r['level'];
    $counts[$lv] = ($counts[$lv] ?? 0) + 1;
}
foreach ($counts as $k => $v) {
    echo "  '" . $k . "' => {$v} rows (hex: " . ($rows[0]['hex_val'] ?? '') . ")\n";
}
echo "  Total: " . count($rows) . " rows\n";

// Force all empty/invalid to 'associate' using raw SQL
echo "\n=== FORCE FIX ===\n";
$affected = $pdo->exec("UPDATE associates SET level = 'associate' WHERE level != 'associate'");
echo "  Updated {$affected} rows to 'associate'\n";

// Verify
$rows2 = $pdo->query("SELECT level, COUNT(*) as cnt FROM associates GROUP BY level ORDER BY cnt DESC")->fetchAll();
echo "\n=== AFTER FIX ===\n";
foreach ($rows2 as $r) {
    echo "  '{$r['level']}' => {$r['cnt']}\n";
}
