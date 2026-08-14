<?php
// Compare what existed in audit (756 tables) vs what exists now (213 tables)
$root = dirname(__DIR__);
$audit = $root . '/_audit_full.txt';
$content = file_get_contents($audit);

// Parse all tables from audit
preg_match_all('/^\s+(\w+)\s+(\d+)\s+rows\s+Code:(\d+)/m', $content, $m);
$before = [];
foreach ($m[1] as $i => $name) {
    $before[$name] = ['rows' => (int)$m[2][$i], 'code' => (int)$m[3][$i]];
}

// Get current tables from DB
$config = require $root . '/config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$current = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

$now = [];
foreach ($current as $t) {
    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $now[$t] = $rows;
}

$dropped = array_diff(array_keys($before), $current);
$survived = array_intersect(array_keys($before), $current);
$added = array_diff($current, array_keys($before));

echo "=== AUDIT vs CURRENT COMPARISON ===" . PHP_EOL;
echo "Tables in audit (2da572a8b): " . count($before) . PHP_EOL;
echo "Tables in DB now: " . count($current) . PHP_EOL;
echo "Dropped since audit: " . count($dropped) . PHP_EOL;
echo "Survived: " . count($survived) . PHP_EOL;
echo "Added since audit: " . count($added) . PHP_EOL;
echo PHP_EOL;

// Save dropped table analysis to CSV
$out = fopen($root . '/_dropped_vs_survived.csv', 'w');
fputcsv($out, ['table', 'before_rows', 'before_code_refs', 'now_status', 'now_rows', 'note']);
foreach ($dropped as $t) {
    fputcsv($out, [$t, $before[$t]['rows'], $before[$t]['code'], 'DROPPED', 0, '']);
}
foreach ($survived as $t) {
    $note = '';
    if (!isset($before[$t])) {
        $note = 'ADDED';
    } else {
        $code = $before[$t]['code'];
        if ($code === 0) $note = 'survived-0refs';
        elseif ($code < 5) $note = 'survived-lowrefs';
    }
    fputcsv($out, [$t, $before[$t]['rows'] ?? 0, $before[$t]['code'] ?? 0, 'SURVIVED', $now[$t] ?? 0, $note]);
}
fclose($out);
echo "Written to _dropped_vs_survived.csv" . PHP_EOL;
echo PHP_EOL;
echo "=== Sample DROPPED tables (first 30) ===" . PHP_EOL;
$i = 0;
foreach ($dropped as $t) {
    echo sprintf("  %-45s %4d rows  Code:%d\n", $t, $before[$t]['rows'], $before[$t]['code']);
    if (++$i >= 30) break;
}?>