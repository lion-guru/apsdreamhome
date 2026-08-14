<?php
/**
 * Find 2-ref tables: wrap both refs in try/catch, then drop
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $path = str_replace('\\', '/', $f->getPathname());
        $allFiles[$path] = file_get_contents($f->getPathname(), FILE_IGNORE_NEW_LINES);
    }
}

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$twoRef = [];

foreach ($tables as $t) {
    $codeRef = 0;
    $refLocations = [];
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    foreach ($allFiles as $path => $content) {
        $matches = preg_match_all($pattern, $content);
        if ($matches > 0) {
            $codeRef += $matches;
            $refLocations[] = basename($path);
        }
    }
    if ($codeRef !== 2) continue;

    $fkTo = $pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME='$t' AND TABLE_SCHEMA='apsdreamhome'")->fetchColumn();
    if ($fkTo > 0) continue;

    $rows = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    // Check how many refs are unprotected
    $unprotected = 0;
    $protected = 0;
    foreach ($allFiles as $path => $content) {
        if (!preg_match($pattern, $content)) continue;
        $lines = $content;
        foreach ($lines as $lineNum => $line) {
            if (!preg_match($pattern, $line)) continue;
            $inTry = false;
            for ($i = max(0, $lineNum - 20); $i < $lineNum; $i++) {
                if (preg_match('/\btry\s*\{/', $lines[$i])) { $inTry = true; break; }
                if (preg_match('/\}\s*catch/', $lines[$i])) break;
            }
            if ($inTry) $protected++; else $unprotected++;
        }
    }

    $twoRef[] = ['name' => $t, 'rows' => $rows, 'files' => implode(', ', $refLocations), 'unprotected' => $unprotected, 'protected' => $protected];
}

usort($twoRef, fn($a,$b) => $b['rows'] <=> $a['rows']);

echo "2-REF TABLES:\n";
echo sprintf("%-40s %5s  %-5s %-5s %s\n", "TABLE", "ROWS", "UNP", "PROT", "FILES");
echo str_repeat("-", 110) . "\n";
foreach ($twoRef as $t) {
    echo sprintf("%-40s %5d  %-5d %-5d %s\n", $t['name'], $t['rows'], $t['unprotected'], $t['protected'], $t['files']);
}
echo "\nTotal: " . count($twoRef) . "\n";
$allUnprotected = count(array_filter($twoRef, fn($t) => $t['unprotected'] == 2));
echo "All unprotected (safe to wrap+drop): $allUnprotected\n";?>