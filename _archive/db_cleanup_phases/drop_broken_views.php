<?php
/**
 * Find and drop broken views (referencing non-existent tables)
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$before = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables before: $before\n\n";

$views = $pdo->query("SELECT TABLE_NAME FROM information_schema.VIEWS WHERE TABLE_SCHEMA = 'apsdreamhome'")->fetchAll(PDO::FETCH_COLUMN);
$broken = [];
foreach ($views as $v) {
    try {
        $pdo->query("SELECT * FROM `$v` LIMIT 1");
    } catch (Exception $e) {
        $broken[] = $v;
    }
}

echo "Broken views: " . count($broken) . "\n";
foreach ($broken as $v) echo "  - $v\n";

foreach ($broken as $v) {
    try {
        $pdo->exec("DROP VIEW IF EXISTS `$v`");
        echo "âœ“ DROPPED $v\n";
    } catch (Exception $e) {
        echo "âœ— $v: {$e->getMessage()}\n";
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\nTables: $before â†’ $after\n";?>