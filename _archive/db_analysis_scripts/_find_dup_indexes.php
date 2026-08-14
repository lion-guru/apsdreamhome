<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$dupCount = 0;
$dropList = [];

foreach ($tables as $t) {
    $idxStmt = $pdo->query("SHOW INDEX FROM `$t`");
    $indexes = [];
    while ($row = $idxStmt->fetch(PDO::FETCH_ASSOC)) {
        $indexes[$row['Key_name']][$row['Seq_in_index']] = $row['Column_name'];
    }

    // Compare each pair of indexes
    $names = array_keys($indexes);
    foreach ($names as $i => $name) {
        if ($name === 'PRIMARY') continue;
        // Find a duplicate
        foreach ($names as $j => $otherName) {
            if ($i >= $j || $otherName === 'PRIMARY') continue;
            if ($indexes[$name] === $indexes[$otherName]) {
                $dropList[] = "DROP INDEX `$name` ON `$t` -- dup of `$otherName`";
                $dupCount++;
                break;
            }
        }
    }
}

echo "Found $dupCount duplicate indexes:\n";
foreach ($dropList as $d) echo "  $d\n";?>