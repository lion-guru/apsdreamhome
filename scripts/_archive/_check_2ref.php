<?php
/**
 * Check which 2-code-ref AI tables can be dropped
 * These have 2 code references - we need to verify they're all wrapped in try/catch
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$allTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$aiTables = array_filter($allTables, fn($t) => preg_match('/^(ai_|voice_|chat_)/i', $t));

$twoRef = [];
foreach ($aiTables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    if ($count >= 5) continue;

    $fkTo = $pdo->query("
        SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
        WHERE REFERENCED_TABLE_NAME = '$t' AND TABLE_SCHEMA = 'apsdreamhome'
    ")->fetchColumn();
    if ($fkTo > 0) continue;

    $refs = [];
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
    foreach ($iter as $f) {
        if ($f->isFile() && $f->getExtension() === 'php') {
            $content = file_get_contents($f->getPathname());
            if (preg_match("/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i", $content)) {
                $refs[] = $f->getPathname();
            }
        }
    }

    if (count($refs) == 2) {
        $twoRef[$t] = $refs;
    }
}

echo "=== 2-CODE-REF CANDIDATES ===\n\n";
foreach ($twoRef as $name => $files) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$name`")->fetchColumn();
    echo "$name ($count rows):\n";
    foreach ($files as $f) {
        echo "  - " . str_replace('C:\\xampp\\htdocs\\apsdreamhome\\', '', $f) . "\n";
    }
    // Check if any reference is NOT in try/catch
    $unprotected = 0;
    foreach ($files as $f) {
        $lines = file($f);
        foreach ($lines as $i => $line) {
            if (preg_match("/\b(FROM|JOIN|INTO|UPDATE)\s+`?$name`?/i", $line)) {
                // Check if previous 10 lines have try {
                $start = max(0, $i - 10);
                $hasTry = false;
                for ($j = $start; $j < $i; $j++) {
                    if (preg_match('/try\s*\{/', $lines[$j])) { $hasTry = true; break; }
                }
                if (!$hasTry) $unprotected++;
            }
        }
    }
    echo "  → Unprotected refs: $unprotected / 2\n";
    echo $unprotected > 0 ? "  → RISKY TO DROP\n" : "  → Safe (all in try/catch)\n";
    echo "\n";
}
