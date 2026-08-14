<?php
$config = include 'config/database.php';
$dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'];
$pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

foreach (['departments', 'designations', 'employees'] as $table) {
    $r = $pdo->query("SHOW TABLES LIKE '$table'");
    $exists = count($r->fetchAll()) > 0;
    echo "$table: " . ($exists ? 'EXISTS' : 'MISSING') . PHP_EOL;
    if ($exists) {
        $r2 = $pdo->query("DESCRIBE $table");
        foreach ($r2->fetchAll(PDO::FETCH_ASSOC) as $col) {
            echo "  {$col['Field']} ({$col['Type']})" . PHP_EOL;
        }
        $r3 = $pdo->query("SELECT COUNT(*) as cnt FROM $table");
        echo "  Rows: " . $r3->fetch()['cnt'] . PHP_EOL;
    }
}?>