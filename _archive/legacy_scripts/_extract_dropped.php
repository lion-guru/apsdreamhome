<?php
// Extract all dropped tables from archived scripts
$archive = __DIR__ . '/_archive';
$tables = [];
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($archive));
foreach ($rii as $file) {
    if ($file->getExtension() !== 'php') continue;
    $content = file_get_contents($file->getPathname());
    // Match DROP TABLE patterns
    preg_match_all('/DROP\s+TABLE(?:\s+IF\s+EXISTS)?\s+[`]?(\w+)[`]?/i', $content, $m1);
    foreach ($m1[1] as $t) $tables[$t][] = $file->getFilename();
    // Match "table_name" => 'drop' patterns
    preg_match_all("/['\"`](\w+)['\"`]\s*=>\s*['\"]drop/i", $content, $m2);
    foreach ($m2[1] as $t) $tables[$t][] = $file->getFilename();
    // Match "drop" => "table" patterns
    preg_match_all("/['\"]drop['\"]\s*=>\s*['\"`]?(\w+)/i", $content, $m3);
    foreach ($m3[1] as $t) $tables[$t][] = $file->getFilename();
}
ksort($tables);
$out = fopen(__DIR__ . '/_dropped_tables.csv', 'w');
fputcsv($out, ['table_name', 'drop_count', 'scripts']);
foreach ($tables as $name => $scripts) {
    fputcsv($out, [$name, count(array_unique($scripts)), implode(';', array_unique($scripts))]);
}
fclose($out);
echo "Total unique tables dropped: " . count($tables) . PHP_EOL;
echo "Written to _dropped_tables.csv" . PHP_EOL;?>