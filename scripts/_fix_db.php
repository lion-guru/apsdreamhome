<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fix field_collections - add id PK
try {
    $desc = $pdo->query("DESCRIBE field_collections")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('id', $desc)) {
        $pdo->exec("ALTER TABLE field_collections ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
        echo "Added id PK to field_collections\n";
    } else {
        echo "field_collections already has id column\n";
    }
} catch (Throwable $e) {
    echo "field_collections error: " . $e->getMessage() . "\n";
}

// Fix land_acquisitions - add id PK
try {
    $desc = $pdo->query("DESCRIBE land_acquisitions")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('id', $desc)) {
        $pdo->exec("ALTER TABLE land_acquisitions ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST");
        echo "Added id PK to land_acquisitions\n";
    } else {
        echo "land_acquisitions already has id column\n";
    }
} catch (Throwable $e) {
    echo "land_acquisitions error: " . $e->getMessage() . "\n";
}

// Fix duplicate FKs
echo "\n=== Checking for duplicate FKs ===\n";
try {
    $fks = $pdo->query("
        SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = 'apsdreamhome' AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    $seen = [];
    $dupes = [];
    foreach ($fks as $fk) {
        $key = "{$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']}";
        if (isset($seen[$key])) {
            $dupes[] = $fk;
        }
        $seen[$key] = true;
    }
    
    echo "Duplicate FK constraints: " . count($dupes) . "\n";
    foreach ($dupes as $d) {
        echo "  Dropping: {$d['CONSTRAINT_NAME']} on {$d['TABLE_NAME']}.{$d['COLUMN_NAME']}\n";
        try {
            $pdo->exec("ALTER TABLE `{$d['TABLE_NAME']}` DROP FOREIGN KEY `{$d['CONSTRAINT_NAME']}`");
            echo "    -> Dropped successfully\n";
        } catch (Throwable $e) {
            echo "    -> Error dropping: " . $e->getMessage() . "\n";
        }
    }
} catch (Throwable $e) {
    echo "FK check error: " . $e->getMessage() . "\n";
}
