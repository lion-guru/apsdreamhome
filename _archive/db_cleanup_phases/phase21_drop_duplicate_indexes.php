<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$dropped = 0;
$failed = 0;

foreach ($tables as $t) {
    $idxStmt = $pdo->query("SHOW INDEX FROM `$t`");
    $indexes = [];
    while ($row = $idxStmt->fetch(PDO::FETCH_ASSOC)) {
        $indexes[$row['Key_name']][$row['Seq_in_index']] = $row['Column_name'];
    }

    $names = array_keys($indexes);
    $toDrop = [];
    foreach ($names as $i => $name) {
        if ($name === 'PRIMARY') continue;
        foreach ($names as $j => $otherName) {
            if ($i >= $j || $otherName === 'PRIMARY') continue;
            if ($indexes[$name] === $indexes[$otherName]) {
                $toDrop[] = $name;
                break;
            }
        }
    }

    foreach ($toDrop as $idx) {
        try {
            $pdo->exec("ALTER TABLE `$t` DROP INDEX `$idx`");
            echo "DROPPED: $t.$idx\n";
            $dropped++;
        } catch (Exception $e) {
            echo "FAILED: $t.$idx - " . $e->getMessage() . "\n";
            $failed++;
        }
    }
}

echo "\nDropped $dropped indexes, $failed failures\n";?>