<?php
require 'config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$schema = [];
foreach ($db->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()") as $r) {
    $schema[strtolower($r['TABLE_NAME'])][strtolower($r['COLUMN_NAME'])] = true;
}
echo 'schema=' . count($schema) . " tables\n";
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
$files = [];
foreach ($rii as $f) { if ($f->isFile() && substr($f, -4) === '.php') $files[] = $f->getPathname(); }
echo 'files=' . count($files) . "\n";
$out = [];
foreach ($files as $file) {
    $c = file_get_contents($file);
    if (preg_match_all('/FROM\s+`?(\w+)`?/i', $c, $fm, PREG_OFFSET_CAPTURE)) {
        foreach ($fm[1] as $x) {
            $table = strtolower($x[0]);
            $window = substr($c, $x[1], 400);
            foreach (['is_active', 'status'] as $col) {
                if (preg_match('/\b' . $col . '\b/i', $window) && isset($schema[$table]) && !isset($schema[$table][$col])) {
                    $line = substr_count($c, "\n", 0, $x[1]) + 1;
                    $out[] = "table=$table col=$col " . str_replace('\\', '/', $file) . ":$line";
                }
            }
        }
    }
}
$out = array_unique($out);
sort($out);
file_put_contents('C:\\Users\\abhay\\AppData\\Local\\Temp\\opencode\\huntfull3.txt', implode("\n", $out));
echo 'hits=' . count($out) . "\n";
