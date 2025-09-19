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
    $mlmTables = [
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

    foreach ($mlmTables as $table) {
        // Check if table exists
        $tableExists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        
        if ($tableExists) {
            // Get table structure
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch()['Create Table'];
            
            // Count rows
            $count = $pdo->query("SELECT COUNT(*) as cnt FROM `$table`")->fetch()['cnt'];
            
            // Get columns
            $columns = [];
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
            while ($row = $stmt->fetch()) {
                $columns[] = $row['Field'] . ' ' . $row['Type'] . 
                           ($row['Null'] === 'NO' ? ' NOT NULL' : '') .
                           (!empty($row['Default']) ? " DEFAULT '{$row['Default']}'" : '') .
                           (!empty($row['Extra']) ? ' ' . $row['Extra'] : '');
            }
            
            // Get foreign keys
            $fks = $pdo->query("
                SELECT 
                    kcu.COLUMN_NAME, 
                    kcu.REFERENCED_TABLE_NAME, 
                    kcu.REFERENCED_COLUMN_NAME,
                    kcu.CONSTRAINT_NAME,
                    rc.UPDATE_RULE,
                    rc.DELETE_RULE
                FROM 
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
                LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
                    ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME 
                    AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
                WHERE 
                    kcu.TABLE_SCHEMA = 'apsdreamhomefinal' 
                    AND kcu.TABLE_NAME = '$table'
                    AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
            ")->fetchAll();

            // Output table info
            echo "\n=== Table: $table ($count rows) ===\n";
            echo "Columns:\n";
            foreach ($columns as $col) {
                echo "- $col\n";
            }
            
            if (!empty($fks)) {
                echo "\nForeign Keys:\n";
                foreach ($fks as $fk) {
                    echo "- {$fk['CONSTRAINT_NAME']}: {$fk['COLUMN_NAME']} → {$fk['REFERENCED_TABLE_NAME']}({$fk['REFERENCED_COLUMN_NAME']})\n";
                    echo "  ON UPDATE {$fk['UPDATE_RULE']} ON DELETE {$fk['DELETE_RULE']}\n";
                }
            }
            
            // Check if table exists in schema file
            $schemaContent = @file_get_contents(__DIR__ . '/db_schema.sql');
            $inSchema = $schemaContent && preg_match("/CREATE\s+TABLE\s+[`'\"]?" . preg_quote($table, '/') . "[`'\"]?/i", $schemaContent);
            echo "\nIn Schema File: " . ($inSchema ? '✓' : '❌') . "\n";
            
            echo "\n" . str_repeat("-", 50) . "\n";
        } else {
            echo "\n=== Table: $table ===\n";
            echo "❌ Table does not exist in database\n";
            
            // Check if table exists in schema file
            $schemaContent = @file_get_contents(__DIR__ . '/db_schema.sql');
            $inSchema = $schemaContent && preg_match("/CREATE\s+TABLE\s+[`'\"]?" . preg_quote($table, '/') . "[`'\"]?/i", $schemaContent);
            echo "In Schema File: " . ($inSchema ? '✓' : '❌') . "\n";
            
            echo "\n" . str_repeat("-", 50) . "\n";
        }
    }
    
    // Check for any MLM-related stored procedures or functions
    $routines = $pdo->query("
        SELECT 
            ROUTINE_NAME, 
            ROUTINE_TYPE,
            CREATED,
            LAST_ALTERED
        FROM 
            INFORMATION_SCHEMA.ROUTINES 
        WHERE 
            ROUTINE_SCHEMA = 'apsdreamhomefinal'
            AND (
                ROUTINE_NAME LIKE '%mlm%' 
                OR ROUTINE_NAME LIKE '%commission%'
                OR ROUTINE_NAME LIKE '%payout%'
                OR ROUTINE_NAME LIKE '%associate%'
            )
        ORDER BY ROUTINE_TYPE, ROUTINE_NAME
    ")->fetchAll();

    if (!empty($routines)) {
        echo "\n=== MLM Stored Procedures & Functions ===\n";
        $currentType = '';
        foreach ($routines as $routine) {
            if ($currentType !== $routine['ROUTINE_TYPE']) {
                $currentType = $routine['ROUTINE_TYPE'];
                echo "\n$currentType:\n";
            }
            echo "- {$routine['ROUTINE_NAME']} (Created: {$routine['CREATED']}, Last Modified: {$routine['LAST_ALTERED']})\n";
        }
    } else {
        echo "\nNo MLM-related stored procedures or functions found.\n";
    }
    
    // Check for MLM-related triggers
    $triggers = $pdo->query("
        SELECT 
            TRIGGER_NAME, 
            EVENT_OBJECT_TABLE,
            ACTION_TIMING,
            EVENT_MANIPULATION,
            CREATED,
            ACTION_STATEMENT
        FROM 
            INFORMATION_SCHEMA.TRIGGERS 
        WHERE 
            TRIGGER_SCHEMA = 'apsdreamhomefinal'
            AND (
                TRIGGER_NAME LIKE '%mlm%' 
                OR TRIGGER_NAME LIKE '%commission%'
                OR TRIGGER_NAME LIKE '%payout%'
                OR TRIGGER_NAME LIKE '%associate%'
            )
        ORDER BY EVENT_OBJECT_TABLE, ACTION_TIMING, EVENT_MANIPULATION
    ")->fetchAll();

    if (!empty($triggers)) {
        echo "\n\n=== MLM Triggers ===\n";
        $currentTable = '';
        foreach ($triggers as $trigger) {
            if ($currentTable !== $trigger['EVENT_OBJECT_TABLE']) {
                $currentTable = $trigger['EVENT_OBJECT_TABLE'];
                echo "\nOn table: $currentTable\n";
            }
            echo "- {$trigger['TRIGGER_NAME']} ({$trigger['ACTION_TIMING']} {$trigger['EVENT_MANIPULATION']})\n";
            echo "  Created: {$trigger['CREATED']}\n";
            echo "  Action: " . substr($trigger['ACTION_STATEMENT'], 0, 100) . (strlen($trigger['ACTION_STATEMENT']) > 100 ? '...' : '') . "\n";
        }
    } else {
        echo "\nNo MLM-related triggers found.\n";
    }
    
    // Check for any MLM-related views
    $views = $pdo->query("
        SELECT 
            TABLE_NAME,
            VIEW_DEFINITION,
            IS_UPDATABLE,
            CHECK_OPTION,
            IS_UPDATABLE,
            SECURITY_TYPE
        FROM 
            INFORMATION_SCHEMA.VIEWS 
        WHERE 
            TABLE_SCHEMA = 'apsdreamhomefinal'
            AND (
                TABLE_NAME LIKE '%mlm%' 
                OR TABLE_NAME LIKE '%commission%'
                OR TABLE_NAME LIKE '%payout%'
                OR TABLE_NAME LIKE '%associate%'
            )
        ORDER BY TABLE_NAME
    ")->fetchAll();

    if (!empty($views)) {
        echo "\n\n=== MLM Views ===\n";
        foreach ($views as $view) {
            echo "\nView: {$view['TABLE_NAME']}\n";
            echo "Updatable: " . ($view['IS_UPDATABLE'] === 'YES' ? 'Yes' : 'No') . "\n";
            echo "Security: {$view['SECURITY_TYPE']}\n";
            echo "Definition: " . substr($view['VIEW_DEFINITION'], 0, 150) . 
                 (strlen($view['VIEW_DEFINITION']) > 150 ? '...' : '') . "\n";
        }
    } else {
        echo "\nNo MLM-related views found.\n";
    }
    
    echo "\n=== End of Report ===\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
