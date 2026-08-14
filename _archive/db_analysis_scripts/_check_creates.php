<?php
$config = require 'C:\xampp\htdocs\apsdreamhome\config\database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$existingTables = array_flip($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));

$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('C:\xampp\htdocs\apsdreamhome\app'));
$bad = [];
$checked = 0;

foreach ($iter as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $path = $f->getPathname();
    $content = file_get_contents($path);

    $pattern = '/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`?(\w+)`?/i';
    if (!preg_match_all($pattern, $content, $matches)) continue;

    foreach ($matches[1] as $tableName) {
        $checked++;
        if (!isset($existingTables[$tableName])) {
            $relPath = str_replace('C:\\xampp\\htdocs\\apsdreamhome\\', '', $path);
            $relPath = str_replace('\\', '/', $relPath);
            $bad[] = ['table' => $tableName, 'file' => $relPath];
        }
    }
}

echo "Checked $checked CREATE TABLE statements\n";
echo "Found " . count($bad) . " orphaned (table doesn't exist)\n\n";

if ($bad) {
    $byTable = [];
    foreach ($bad as $b) $byTable[$b['table']][] = $b['file'];
    foreach ($byTable as $table => $files) {
        echo "TABLE: $table\n";
        foreach (array_unique($files) as $f) echo "  $f\n";
    }
}?>