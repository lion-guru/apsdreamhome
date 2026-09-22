<?php
// Hunter v3: statement-level alias resolution. Joins continued string lines, resolves alias.col per statement.
require 'config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();
$schema = [];
foreach ($db->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE()") as $r) {
    $schema[strtolower($r['TABLE_NAME'])][strtolower($r['COLUMN_NAME'])] = true;
}
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
$files = [];
foreach ($rii as $f) { if ($f->isFile() && substr($f, -4) === '.php') $files[] = $f->getPathname(); }
$rii2 = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/views'));
foreach ($rii2 as $f) { if ($f->isFile() && substr($f, -4) === '.php') $files[] = $f->getPathname(); }
$hits = [];
foreach ($files as $file) {
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    // accumulate logical statements (lines without ; continue)
    $buf = ''; $start = 0;
    $stmts = [];
    foreach ($lines as $i => $ln) {
        if ($buf === '') $start = $i + 1;
        $buf .= ' ' . $ln;
        if (strpos($ln, ';') !== false || preg_match('/["\']\s*\)\s*;?\s*$/', $ln)) { $stmts[] = [$start, $buf]; $buf = ''; }
        if (strlen($buf) > 3000) { $stmts[] = [$start, $buf]; $buf = ''; }
    }
    if ($buf !== '') $stmts[] = [$start, $buf];
    foreach ($stmts as [$ln, $st]) {
        if (!preg_match('/\b(FROM|INTO|UPDATE)\s+`?(\w+)`?(?:\s+(?:AS\s+)?(\w+))?/i', $st, $m)) continue;
        $table = strtolower($m[2]);
        $alias = isset($m[3]) ? strtolower($m[3]) : null;
        if (!isset($schema[$table])) continue;
        if (in_array($alias, ['where', 'order', 'group', 'limit', 'left', 'inner', 'right', 'join', 'on', 'set', 'values', null], true)) $alias = null;
        // aliased refs
        if ($alias && preg_match_all('/(?<![\w])' . preg_quote($alias, '/') . '\.(\w+)/i', $st, $cm)) {
            foreach (array_unique($cm[1]) as $col) {
                if (in_array(strtolower($col), ['is_active', 'status', 'id']) && !isset($schema[$table][strtolower($col)])) {
                    $hits[] = str_replace('\\', '/', $file) . ":$ln table=$table.$col";
                }
            }
        }
        // bare WHERE col refs -> primary table (only when single-table statement)
        if (!preg_match('/\bJOIN\b/i', $st)) {
            foreach (['is_active', 'status'] as $col) {
                if (preg_match('/WHERE[^\'"]*\b' . $col . '\b/i', $st) && !isset($schema[$table][$col])) {
                    $hits[] = str_replace('\\', '/', $file) . ":$ln table=$table BARE-$col";
                }
            }
        }
    }
}
$hits = array_unique($hits);
sort($hits);
file_put_contents('C:\\Users\\abhay\\AppData\\Local\\Temp\\opencode\\hunt3.txt', implode("\n", $hits));
echo 'hits=' . count($hits) . "\n";
