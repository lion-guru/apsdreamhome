<?php
/**
 * AI Schema Audit
 * Find which ai_* / voice_* tables are real vs duplicates
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$allTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$aiTables = array_filter($allTables, fn($t) => preg_match('/^(ai_|voice_|chat_)/i', $t));
sort($aiTables);

echo "=== AI TABLES (51 total) ===\n\n";

$real = [];
$drops = [];
foreach ($aiTables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    $fkTo = $pdo->query("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_NAME = '$t' AND TABLE_SCHEMA = 'apsdreamhome'
    ")->fetchColumn();

    $fkFrom = $pdo->query("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_NAME = '$t' AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_SCHEMA = 'apsdreamhome'
    ")->fetchColumn();

    $codeRef = 0;
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
    foreach ($iter as $f) {
        if ($f->isFile() && $f->getExtension() === 'php') {
            $content = file_get_contents($f->getPathname());
            if (preg_match("/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i", $content)) $codeRef++;
        }
    }

    $keep = ($count >= 10) || ($codeRef >= 3) || ($fkTo > 0);
    $droppable = ($count < 5) && ($codeRef <= 2) && ($fkTo == 0);

    $marker = $keep ? '✓' : ($droppable ? '✗' : '·');
    echo sprintf("%s %-42s %5d rows  FK→:%d FK←:%d  Code:%d\n",
        $marker, $t, $count, $fkFrom, $fkTo, $codeRef);

    if ($droppable) $drops[] = ['name' => $t, 'count' => $count, 'code' => $codeRef];
    else $real[] = ['name' => $t, 'count' => $count, 'code' => $codeRef, 'fkTo' => $fkTo];
}

echo "\n=== REAL KEEPERS (" . count($real) . ") ===\n";
foreach ($real as $r) {
    echo sprintf("  ✓ %-42s %5d rows  Code:%d\n", $r['name'], $r['count'], $r['code']);
}

echo "\n=== DROP CANDIDATES (" . count($drops) . ") ===\n";
foreach ($drops as $d) {
    echo sprintf("  ✗ %-42s %5d rows  Code:%d\n", $d['name'], $d['count'], $d['code']);
}
echo "\nTotal droppable: " . count($drops) . " of " . count($aiTables) . " AI tables\n";
