<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "2jcePXuNaOfEyo6I5wJVkG");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get all foreign keys
$fks = $pdo->query("
    SELECT 
        TABLE_NAME,
        COLUMN_NAME,
        CONSTRAINT_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'apsdreamhome'
      AND REFERENCED_TABLE_NAME IS NOT NULL
      AND CONSTRAINT_NAME != 'PRIMARY'
    ORDER BY TABLE_NAME, COLUMN_NAME
")->fetchAll(PDO::FETCH_ASSOC);

echo "Total foreign keys: " . count($fks) . "\n\n";

// Check which FK columns don't have indexes
$missingIndexes = [];
foreach ($fks as $fk) {
    $table = $fk['TABLE_NAME'];
    $column = $fk['COLUMN_NAME'];
    
    // Check if this column has an index
    $indexes = $pdo->query("
        SELECT INDEX_NAME, COLUMN_NAME 
        FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE TABLE_SCHEMA = 'apsdreamhome' 
          AND TABLE_NAME = '$table' 
          AND COLUMN_NAME = '$column'
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($indexes)) {
        $missingIndexes[] = $fk;
    }
}

echo "Foreign keys WITHOUT indexes: " . count($missingIndexes) . "\n\n";
foreach ($missingIndexes as $mi) {
    echo "  {$mi['TABLE_NAME']}.{$mi['COLUMN_NAME']} -> {$mi['REFERENCED_TABLE_NAME']}.{$mi['REFERENCED_COLUMN_NAME']} (constraint: {$mi['CONSTRAINT_NAME']})\n";
}