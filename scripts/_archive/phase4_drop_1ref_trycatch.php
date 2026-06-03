<?php
/**
 * PHASE 4: Drop 1-ref + 0-FK tables
 * Following the proven pattern: progressively more conservative
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables before: $before\n\n";

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$drops = [];
foreach ($tables as $t) {
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) {
        $codeRef += preg_match_all($pattern, $content);
    }
    if ($codeRef != 1) continue;

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $isView = $pdo->query("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($isView === 'VIEW') continue;

    // Check if the 1 ref is in try/catch
    $refFile = '';
    $refLine = 0;
    $inTryCatch = false;
    foreach ($allFiles as $path => $content) {
        if (preg_match($pattern, $content)) {
            $refFile = $path;
            $lines = explode("\n", $content);
            foreach ($lines as $i => $line) {
                if (preg_match($pattern, $line)) {
                    $refLine = $i;
                    // Check if previous 5 lines have try {
                    for ($j = max(0, $i - 5); $j < $i; $j++) {
                        if (preg_match('/try\s*\{/', $lines[$j])) { $inTryCatch = true; break; }
                    }
                    break;
                }
            }
            break;
        }
    }

    $drops[] = [
        'name' => $t,
        'file' => $refFile,
        'line' => $refLine,
        'inTry' => $inTryCatch,
    ];
}

echo "Candidates (1 code ref, 0 FKs, not view): " . count($drops) . "\n";
$inTry = count(array_filter($drops, fn($d) => $d['inTry']));
$notInTry = count(array_filter($drops, fn($d) => !$d['inTry']));
echo "  In try/catch: $inTry\n";
echo "  NOT in try/catch: $notInTry\n\n";

// First batch: drop ONLY those in try/catch (safer)
$dropped = 0;
$skipped = 0;
foreach ($drops as $d) {
    if (!$d['inTry']) {
        $skipped++;
        continue;
    }
    try {
        $pdo->exec("DROP TABLE IF EXISTS `" . $d['name'] . "`");
        $dropped++;
    } catch (Exception $e) {
        echo "✗ {$d['name']}: {$e->getMessage()}\n";
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\n=== SUMMARY ===\n";
echo "Dropped (in try/catch): $dropped\n";
echo "Skipped (not in try/catch, for manual review): $skipped\n";
echo "Tables: $before → $after (-" . ($before - $after) . ")\n";
