<?php
// Parse backup_20260525_nofk.sql - matches "Table structure for table X" comment
$file = $argv[1] ?? 'database/backup_20260525_nofk.sql';
$content = file_get_contents($file);
echo "File: $file, Size: " . strlen($content) . " bytes\n";

// Split by "Table structure for table `xxx`"
preg_match_all('/Table structure for table `(\w+)`.*?CREATE TABLE `(\w+)` \((.*?)\) ENGINE/s', $content, $matches, PREG_SET_ORDER);

echo "Found " . count($matches) . " CREATE TABLE blocks\n";

$tables = [];
foreach ($matches as $m) {
    $name = $m[2];
    $cols = $m[3];
    preg_match_all('/`(\w+)`\s+([^,\n]+)/', $cols, $colMatches);
    $columns = [];
    foreach ($colMatches[1] as $i => $col) {
        $columns[] = $col . '|' . trim($colMatches[2][$i]);
    }
    $tables[$name] = [
        'columns' => $columns,
        'col_count' => count($columns)
    ];
}

// Also get row count from INSERT INTO `xxx` VALUES
foreach ($tables as $name => &$t) {
    // Find the INSERT block for this table
    if (preg_match('/LOCK TABLES `' . preg_quote($name, '/') . '` WRITE;.*?INSERT INTO `' . preg_quote($name, '/') . '` VALUES (.*?);.*?UNLOCK TABLES;/s', $content, $insertMatch)) {
        $values = $insertMatch[1];
        // Count value groups: rough count by counting opening ( at start of each row
        // Actually count ",(" patterns but be careful with nested values
        $rowCount = substr_count($values, '),(') + 1;
        if (strpos($values, '(') === 0 && substr_count($values, '),(') === 0) {
            $rowCount = 1;
        }
        $t['rows'] = $rowCount;
    } else {
        $t['rows'] = 0;
    }
}
unset($t);

echo "Saved: " . count($tables) . " tables\n";
ksort($tables);
file_put_contents('_old_db_schema.json', json_encode($tables, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Written to _old_db_schema.json\n";

// Show first 20 tables
echo "\n=== First 20 tables (sorted) ===\n";
$i = 0;
foreach ($tables as $name => $t) {
    echo sprintf("  %-50s %3d cols  %4d rows\n", $name, $t['col_count'], $t['rows']);
    if (++$i >= 20) break;
}
echo "...\n";

// Show tables with 0 rows
$empty = array_filter($tables, fn($t) => $t['rows'] === 0);
echo "\n=== Empty tables (0 rows): " . count($empty) . " ===\n";
$i = 0;
foreach ($empty as $name => $t) {
    echo sprintf("  %-50s %3d cols\n", $name, $t['col_count']);
    if (++$i >= 30) {
        echo "  ... +" . (count($empty) - 30) . " more\n";
        break;
    }
}?>