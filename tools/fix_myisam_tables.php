<?php
$host = '127.0.0.1';
$port = 3307;
$db   = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "Connected to MySQL ($host:$port, db=$db)\n\n";

    $stmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema='apsdreamhome' AND ENGINE='MyISAM' AND table_type='BASE TABLE'");
    $myisamTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($myisamTables)) {
        echo "No MyISAM tables found. All tables are already InnoDB.\n";
        exit(0);
    }

    echo "Found " . count($myisamTables) . " MyISAM table(s):\n";
    foreach ($myisamTables as $t) {
        echo "  - $t\n";
    }
    echo "\nConverting to InnoDB...\n\n";

    $success = 0;
    $failure = 0;

    foreach ($myisamTables as $table) {
        try {
            $pdo->exec("ALTER TABLE `$table` ENGINE=InnoDB");
            echo "  [OK] $table\n";
            $success++;
        } catch (Exception $e) {
            echo "  [FAIL] $table - " . $e->getMessage() . "\n";
            $failure++;
        }
    }

    echo "\nDone. $success converted, $failure failed.\n";

    // Final verification
    $stmt = $pdo->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema='apsdreamhome' AND ENGINE='MyISAM' AND table_type='BASE TABLE'");
    $remaining = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!empty($remaining)) {
        echo "\nWARNING: " . count($remaining) . " table(s) still MyISAM:\n";
        foreach ($remaining as $t) {
            echo "  - $t\n";
        }
    } else {
        echo "\nAll tables are now InnoDB.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
