<?php
/**
 * Find all CREATE TABLE IF NOT EXISTS in services and check if the table still exists
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$existingTables = array_flip($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
$found = 0;
foreach ($iter as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $content = file_get_contents($f->getPathname());
    if (preg_match_all('/CREATE TABLE IF NOT EXISTS\s+`?(\w+)`?/i', $content, $matches)) {
        foreach ($matches[1] as $table) {
            if (!isset($existingTables[$table])) {
                $relPath = str_replace('\\', '/', str_replace(__DIR__ . '/../', '', $f->getPathname()));
                echo "ORPHANED: $table in $relPath\n";
                $found++;
            }
        }
    }
}
echo "\nTotal orphaned CREATE TABLE statements: $found\n";?>