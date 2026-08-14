<?php
/**
 * Drop 5 safe zero-row tables
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = ['data_change_log', 'import_jobs', 'mlm_earnings', 'workflow_actions', 'mlm_points'];

$allFiles = [];
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
foreach ($iter as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $allFiles[$f->getPathname()] = file_get_contents($f->getPathname());
    }
}

$dropped = 0;
foreach ($tables as $t) {
    $pattern = "/\b(FROM|JOIN|INTO|UPDATE)\s+`?$t`?/i";
    $allInTry = true;
    foreach ($allFiles as $content) {
        if (!preg_match($pattern, $content)) continue;
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (!preg_match($pattern, $line)) continue;
            $inTry = false;
            for ($j = max(0, $i - 15); $j < $i; $j++) {
                if (preg_match('/try\s*\{/', $lines[$j])) { $inTry = true; break; }
            }
            if (!$inTry) { $allInTry = false; break 2; }
        }
    }
    if (!$allInTry) { echo "SKIP $t (unprotected refs)\n"; continue; }

    try {
        $pdo->exec("DROP TABLE `$t`");
        echo "Dropped $t\n";
        $dropped++;
    } catch (Exception $e) {
        echo "Failed $t: {$e->getMessage()}\n";
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\nDropped: $dropped\nTables: $after\n";?>