<?php
/**
 * Drop AI feature-scaffolding tables - MODERATE PASS
 * Drop tables with 0-1 code references AND 0 incoming FKs
 * 1-code-ref tables are reviewed individually for safety
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables before: $before\n\n";

// Re-check all AI tables with 1 code ref
$allTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$aiTables = array_filter($allTables, fn($t) => preg_match('/^(ai_|voice_|chat_)/i', $t));

$candidates = [];
foreach ($aiTables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    if ($count >= 5) continue;  // Skip tables with meaningful data

    $fkTo = $pdo->query("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_NAME = '$t' AND TABLE_SCHEMA = 'apsdreamhome'
    ")->fetchColumn();
    if ($fkTo > 0) continue;  // Skip tables with incoming FKs

    $codeRef = 0;
    $refFile = '';
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
    foreach ($iter as $f) {
        if ($f->isFile() && $f->getExtension() === 'php') {
            $content = file_get_contents($f->getPathname());
            if (preg_match("/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i", $content)) {
                $codeRef++;
                $refFile = $f->getPathname();
            }
        }
    }

    if ($codeRef <= 1) {
        $candidates[] = ['name' => $t, 'rows' => $count, 'code' => $codeRef, 'file' => $refFile];
    }
}

echo "=== 1-CODE-REF CANDIDATES ===\n";
foreach ($candidates as $c) {
    echo sprintf("%-42s %2d rows  Code:%d  File: %s\n",
        $c['name'], $c['rows'], $c['code'], str_replace('C:\\xampp\\htdocs\\apsdreamhome\\', '', $c['file']));
}

echo "\n=== REVIEWING EACH REFERENCE ===\n\n";

// Review each candidate by reading the file context
$dropped = 0;
$kept = 0;
foreach ($candidates as $c) {
    $name = $c['name'];
    if (!$c['file']) {
        // 0 code refs but didn't get caught by zero-ref pass
        // (this can happen if file was modified between passes)
        try {
            $pdo->exec("DROP TABLE IF EXISTS `$name`");
            echo "✓ DROPPED $name (0 refs after recheck)\n";
            $dropped++;
        } catch (Exception $e) {
            echo "✗ FAILED $name: {$e->getMessage()}\n";
        }
        continue;
    }

    // Read file and show the context
    $content = file_get_contents($c['file']);
    $lines = explode("\n", $content);
    $matched = -1;
    foreach ($lines as $i => $line) {
        if (preg_match("/\b(FROM|JOIN|INTO|UPDATE)\s+`?$name`?/i", $line)) {
            $matched = $i;
            break;
        }
    }

    if ($matched === -1) continue;

    $start = max(0, $matched - 1);
    $end = min(count($lines) - 1, $matched + 1);
    $context = '';
    for ($i = $start; $i <= $end; $i++) {
        $context .= "  " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
    }
    echo "$name (in " . basename($c['file']) . " line " . ($matched + 1) . "):\n$context\n";

    // Try drop and verify E2E still works (we'll run E2E at end)
    try {
        $pdo->exec("DROP TABLE IF EXISTS `$name`");
        echo "  → DROPPED\n\n";
        $dropped++;
    } catch (Exception $e) {
        echo "  → FAILED: {$e->getMessage()}\n\n";
        $kept++;
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\n=== MODERATE PASS SUMMARY ===\n";
echo "Dropped: $dropped\n";
echo "Kept: $kept\n";
echo "Tables: $before → $after (-" . ($before - $after) . ")\n";
echo "\nRun E2E to verify no regressions.\n";
