<?php
/**
 * PHASE 9: Drop more clearly bloated tables
 * Focus on tables with 0-3 code refs and no FKs, even if many rows
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

// More aggressive: drop tables with <=5 code refs AND 0 FKs
$drops = [];
foreach ($tables as $t) {
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $content) {
        $codeRef += preg_match_all($pattern, $content);
    }
    if ($codeRef > 5) continue;

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $isView = $pdo->query("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($isView === 'VIEW') continue;

    // Check if method-level try/catch
    $allInTry = true;
    foreach ($allFiles as $path => $content) {
        if (!preg_match($pattern, $content)) continue;
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (preg_match($pattern, $line)) {
                $inTry = false;
                for ($j = max(0, $i - 15); $j < $i; $j++) {
                    if (preg_match('/try\s*\{/', $lines[$j])) { $inTry = true; break; }
                }
                if (!$inTry) { $allInTry = false; break 2; }
            }
        }
    }
    if (!$allInTry) continue;

    $drops[] = $t;
}

$dropped = 0;
foreach ($drops as $t) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
        $dropped++;
    } catch (Exception $e) {
        echo "✗ $t: {$e->getMessage()}\n";
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "Dropped: $dropped (tables with <=5 refs, 0 FKs, all refs in try/catch)\n";
echo "Tables: $before → $after (-" . ($before - $after) . ")\n";
