<?php
$dbHost = getenv('DB_HOST') ?: '127.0.0.1';
$dbPort = getenv('DB_PORT') ?: '3306';
$dbName = getenv('DB_DATABASE') ?: 'apsdreamhome';
$dbUser = getenv('DB_USERNAME') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);

// Find duplicate indexes (same table, same leftmost columns)
$indexes = $pdo->query("
    SELECT TABLE_NAME, INDEX_NAME, NON_UNIQUE, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as cols
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA='apsdreamhome' AND INDEX_NAME != 'PRIMARY'
    GROUP BY TABLE_NAME, INDEX_NAME, NON_UNIQUE
")->fetchAll(PDO::FETCH_ASSOC);

$tableIndexes = [];
foreach ($indexes as $idx) {
    $tableIndexes[$idx['TABLE_NAME']][$idx['INDEX_NAME']] = $idx['cols'];
}

$duplicateIndexes = [];
foreach ($tableIndexes as $table => $idxList) {
    $seen = [];
    foreach ($idxList as $name => $cols) {
        if (isset($seen[$cols])) {
            $duplicateIndexes[$table][] = [
                'existing' => $seen[$cols],
                'duplicate' => $name,
                'columns' => $cols
            ];
        } else {
            $seen[$cols] = $name;
        }
    }
}

echo "=== EXACT DUPLICATE INDEXES: " . count($duplicateIndexes) . " tables affected ===\n";
foreach ($duplicateIndexes as $tbl => $dups) {
    echo "Table `$tbl`:\n";
    foreach ($dups as $d) {
        echo "  - Index `{$d['duplicate']}` is an exact duplicate of `{$d['existing']}` on ({$d['columns']})\n";
    }
}
