<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome', 'root', '');
    $stm = $pdo->query('SHOW TABLES');
    $tables = $stm->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: \n" . implode(", ", $tables) . "\n\n";
    
    if (in_array('leads', $tables)) {
        echo "Checking leads FKs:\n";
        $sql = "SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'apsdreamhome' AND TABLE_NAME = 'leads'";
        $stm = $pdo->query($sql);
        $fks = $stm->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fks as $fk) {
            echo "FK: {$fk['CONSTRAINT_NAME']} on {$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}\n";
        }
    } else {
        echo "Table 'leads' not found.\n";
    }

    if (in_array('users', $tables)) {
        echo "\nChecking users table count:\n";
        $stm = $pdo->query("SELECT count(*) FROM users");
        echo "Count: " . $stm->fetchColumn() . "\n";
    }

    if (in_array('associates', $tables)) {
        echo "\nChecking associates table count:\n";
        $stm = $pdo->query("SELECT count(*) FROM associates");
        echo "Count: " . $stm->fetchColumn() . "\n";
    }

} catch(Exception $e) { echo $e->getMessage(); }
