<?php
/**
 * Fix all corrupted InnoDB tables in apsdreamhome database
 * Usage: Start MySQL with innodb_force_recovery=3 first, then run this script
 */

$host = '127.0.0.1';
$port = '3307';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "Connected to MySQL\n";

    // Get all InnoDB tables
    $tables = $pdo->query("SELECT TABLE_SCHEMA, TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'apsdreamhome' AND ENGINE = 'InnoDB'")->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($tables) . " InnoDB tables\n";

    $corrupted = [];
    $clean = 0;

    foreach ($tables as $t) {
        $full = $t['TABLE_SCHEMA'] . '.' . $t['TABLE_NAME'];
        try {
            $result = $pdo->query("CHECK TABLE `$full` EXTENDED")->fetch(PDO::FETCH_ASSOC);
            $status = $result['Msg_text'] ?? 'Unknown';
            if (stripos($status, 'OK') === false && stripos($status, 'Table is already up to date') === false) {
                $corrupted[] = ['table' => $full, 'status' => $status];
                echo "CORRUPTED: $full - $status\n";
            } else {
                $clean++;
            }
        } catch (Exception $e) {
            $corrupted[] = ['table' => $full, 'status' => $e->getMessage()];
            echo "CORRUPTED (exception): $full\n";
        }
    }

    echo "\nClean: $clean | Corrupted: " . count($corrupted) . "\n";

    if (count($corrupted) > 0) {
        echo "\n--- Dropping corrupted tables ---\n";
        foreach ($corrupted as $c) {
            try {
                $pdo->exec("DROP TABLE IF EXISTS `{$c['table']}`");
                echo "DROPPED: {$c['table']}\n";
            } catch (Exception $e) {
                echo "FAILED to drop {$c['table']}: " . $e->getMessage() . "\n";
            }
        }
        echo "\nAll corrupted tables dropped!\n";
    } else {
        echo "\nNo corrupted tables found!\n";
    }

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
