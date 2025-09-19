<?php
try {
    // Database connection
    $pdo = new PDO(
        'mysql:host=localhost;dbname=apsdreamhomefinal;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // MLM-related tables to check
    $tables = [
        'associates',
        'mlm_tree',
        'mlm_commissions',
        'mlm_commission_ledger',
        'commission_payouts',
        'commission_transactions',
        'associate_levels',
        'team_hierarchy'
    ];

    echo "=== MLM Table Structure Report ===\n\n";

    foreach ($tables as $table) {
        // Check if table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        
        echo "\n=== Table: $table ===\n";
        
        if ($tableExists) {
            // Count rows
            $count = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`")->fetch()['cnt'];
            echo "Exists in database with $count rows.\n";
            
            // Show columns
            echo "Columns:\n";
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
            while ($row = $stmt->fetch()) {
                echo "- {$row['Field']} ({$row['Type']})\n";
            }
        } else {
            echo "❌ Table does not exist in database\n";
        }
        
        // Check if table exists in schema file
        $schemaFile = __DIR__ . '/db_schema.sql';
        if (file_exists($schemaFile)) {
            $schemaContent = file_get_contents($schemaFile);
            $inSchema = preg_match("/CREATE\s+TABLE\s+[`'\"]?" . preg_quote($table, '/') . "[`'\"]?/i", $schemaContent);
            echo "In schema file: " . ($inSchema ? '✓' : '❌') . "\n";
        } else {
            echo "Schema file not found.\n";
        }
        
        echo "\n" . str_repeat("-", 50) . "\n";
    }
    
    echo "\n=== End of Report ===\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
