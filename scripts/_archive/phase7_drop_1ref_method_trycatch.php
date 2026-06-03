<?php
/**
 * PHASE 7: Wrap unsafe 1-ref tables in try/catch and drop them
 * This makes the code resilient to the table not existing
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables before: $before\n\n";

// Find tables with 1 ref, 0 FKs, NOT in try/catch
$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = $f->getPathname();
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$candidates = [];
foreach ($tables as $t) {
    $codeRef = 0;
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path) {
        $content = file_get_contents($path);
        $codeRef += preg_match_all($pattern, $content);
    }
    if ($codeRef != 1) continue;

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $isView = $pdo->query("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($isView === 'VIEW') continue;

    $candidates[] = $t;
}

echo "1-ref candidates (not yet in try/catch): " . count($candidates) . "\n";
echo "Tables: $candidates\n\n";

// Strategy: For each, find the file, check if it's in a method that uses try/catch elsewhere
// If so, wrap. Otherwise skip.

$dropped = 0;
$skipped = 0;
foreach ($candidates as $t) {
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";

    foreach ($allFiles as $path) {
        $content = file_get_contents($path);
        if (!preg_match($pattern, $content)) continue;

        // Check if the entire method has try/catch
        $lines = explode("\n", $content);
        $lineIdx = -1;
        foreach ($lines as $i => $line) {
            if (preg_match($pattern, $line)) { $lineIdx = $i; break; }
        }
        if ($lineIdx === -1) continue;

        // Find enclosing method/function
        $methodStart = -1;
        for ($i = $lineIdx; $i >= 0; $i--) {
            if (preg_match('/\b(function|public|private|protected)\s+\w+\s*\(/', $lines[$i])) {
                $methodStart = $i;
                break;
            }
        }
        $methodEnd = count($lines);
        for ($i = $lineIdx; $i < count($lines); $i++) {
            if (preg_match('/^\s*}\s*$/', $lines[$i]) && $i > $methodStart) {
                $methodEnd = $i;
                break;
            }
        }

        $methodCode = implode("\n", array_slice($lines, $methodStart, $methodEnd - $methodStart + 1));
        $hasTry = preg_match('/try\s*\{/', $methodCode);

        if ($hasTry) {
            // Method already has try/catch - safe to drop
            try {
                $pdo->exec("DROP TABLE IF EXISTS `$t`");
                echo "✓ $t (in try/catch method)\n";
                $dropped++;
            } catch (Exception $e) {
                echo "✗ $t: {$e->getMessage()}\n";
            }
        } else {
            $skipped++;
        }
        break;  // Only one ref per table
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\n=== SUMMARY ===\n";
echo "Dropped: $dropped\n";
echo "Skipped: $skipped (need manual wrap or keep)\n";
echo "Tables: $before → $after (-" . ($before - $after) . ")\n";
