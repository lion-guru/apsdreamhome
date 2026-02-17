<?php
/**
 * Compare db_schema.sql with live database
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhome',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
];

// Connect to database
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Get all tables from database
    $tablesInDb = [];
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tablesInDb[] = $row[0];
    }
    sort($tablesInDb);

    // Get tables from schema file
    $schemaFile = __DIR__ . '/db_schema.sql';
    $schemaContent = file_get_contents($schemaFile);
    
    preg_match_all('/CREATE\s+TABLE\s+[`"]([^`"]+)[`"]/i', $schemaContent, $matches);
    $tablesInSchema = $matches[1] ?? [];
    sort($tablesInSchema);

    // Find differences
    $onlyInDb = array_diff($tablesInDb, $tablesInSchema);
    $onlyInSchema = array_diff($tablesInSchema, $tablesInDb);
    $commonTables = array_intersect($tablesInDb, $tablesInSchema);

    // Start output
    $output = "# Database Schema Comparison\n";
    $output .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";

    // 1. Tables only in database
    $output .= "## Tables only in database (missing from schema):\n\n";
    if (!empty($onlyInDb)) {
        foreach ($onlyInDb as $table) {
            $output .= "- `$table`\n";
        }
    } else {
        $output .= "None\n";
    }

    // 2. Tables only in schema (not in database)
    $output .= "\n## Tables only in schema (not in database):\n\n";
    if (!empty($onlyInSchema)) {
        foreach ($onlyInSchema as $table) {
            $output .= "- `$table`\n";
        }
    } else {
        $output .= "None\n";
    }

    // 3. Compare common tables
    $output .= "\n## Table structure differences:\n\n";
    foreach ($commonTables as $table) {
        // Get schema from database
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $dbCreate = $stmt->fetch()['Create Table'];
        
        // Get schema from file
        preg_match("/CREATE\s+TABLE\s+[`\"]" . preg_quote($table, '/') . "[`\"].*?;\s*/is", $schemaContent, $matches);
        $fileCreate = $matches[0] ?? '';
        
        // Normalize both for comparison
        $dbCreate = preg_replace('/\s+/', ' ', trim($dbCreate));
        $fileCreate = preg_replace('/\s+/', ' ', trim($fileCreate));
        
        if ($dbCreate !== $fileCreate) {
            $output .= "### Table: `$table`\n";
            $output .= "- **Structure differs** between database and schema file\n";
            
            // Get columns from database
            $dbColumns = [];
            $stmt = $pdo->query("DESCRIBE `$table`");
            while ($row = $stmt->fetch()) {
                $dbColumns[$row['Field']] = $row;
            }
            
            // Get columns from schema file
            $fileColumns = [];
            if (preg_match('/\(\s*((?:[^()]|\((?1)\))*+)\s*\)/s', $fileCreate, $matches)) {
                $columnDefs = $matches[1];
                if (preg_match_all('/`([^`]+)`\s+([^,]+)(?:,|$)/', $columnDefs, $cols, PREG_SET_ORDER)) {
                    foreach ($cols as $col) {
                        $fileColumns[trim($col[1])] = trim($col[2]);
                    }
                }
            }
            
            // Compare columns
            $onlyInDb = array_diff(array_keys($dbColumns), array_keys($fileColumns));
            $onlyInFile = array_diff(array_keys($fileColumns), array_keys($dbColumns));
            $commonColumns = array_intersect(array_keys($dbColumns), array_keys($fileColumns));
            
            // Column differences
            $diffColumns = [];
            foreach ($commonColumns as $col) {
                $dbType = strtoupper(preg_replace('/\s+/', ' ', $dbColumns[$col]['Type']));
                $fileType = strtoupper($fileColumns[$col]);
                
                if ($dbType !== $fileType) {
                    $diffColumns[] = "  - `$col`: DB has `$dbType`, File has `$fileType`";
                }
            }
            
            // Output column differences
            if (!empty($onlyInDb)) {
                $output .= "  - **Columns only in database**: " . implode(', ', array_map(function($c) { return "`$c`"; }, $onlyInDb)) . "\n";
            }
            
            if (!empty($onlyInFile)) {
                $output .= "  - **Columns only in schema file**: " . implode(', ', array_map(function($c) { return "`$c`"; }, $onlyInFile)) . "\n";
            }
            
            if (!empty($diffColumns)) {
                $output .= "  - **Column type differences**:\n" . implode("\n", $diffColumns) . "\n";
            }
            
            $output .= "\n";
        }
    }
    
    // 4. Check for missing foreign keys
    $output .= "## Foreign Key Differences:\n\n";
    foreach ($tablesInDb as $table) {
        // Get foreign keys from database
        $stmt = $pdo->query("
            SELECT 
                kcu.COLUMN_NAME,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME,
                rc.UPDATE_RULE,
                rc.DELETE_RULE
            FROM 
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
            LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
            WHERE 
                kcu.TABLE_SCHEMA = '{$dbConfig['dbname']}'
                AND kcu.TABLE_NAME = '$table'
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        $dbFks = [];
        while ($fk = $stmt->fetch()) {
            $dbFks[] = $fk;
        }
        
        // Get foreign keys from schema file
        $fileFks = [];
        if (preg_match("/CREATE\s+TABLE\s+[`\"]" . preg_quote($table, '/') . "[`\"].*?;\s*/is", $schemaContent, $matches)) {
            $tableDef = $matches[0];
            if (preg_match_all('/CONSTRAINT\s+[`"]([^`"]+)[`"]\s*FOREIGN\s+KEY\s*\([^)]+\)\s*REFERENCES\s+[`"]([^`"]+)[`"]\s*\([^)]+\)(?:\s+ON\s+UPDATE\s+([^\s,)]+))?(?:\s+ON\s+DELETE\s+([^\s,)]+))?/i', $tableDef, $fks, PREG_SET_ORDER)) {
                foreach ($fks as $fk) {
                    $fileFks[] = [
                        'CONSTRAINT_NAME' => $fk[1],
                        'REFERENCED_TABLE_NAME' => $fk[2],
                        'UPDATE_RULE' => !empty($fk[3]) ? $fk[3] : 'RESTRICT',
                        'DELETE_RULE' => !empty($fk[4]) ? $fk[4] : 'RESTRICT',
                    ];
                }
            }
        }
        
        // Compare foreign keys
        $fkDifferences = [];
        
        // Check FKs in DB but not in file
        foreach ($dbFks as $dbFk) {
            $found = false;
            foreach ($fileFks as $fileFk) {
                if ($fileFk['REFERENCED_TABLE_NAME'] === $dbFk['REFERENCED_TABLE_NAME'] &&
                    $fileFk['UPDATE_RULE'] === $dbFk['UPDATE_RULE'] &&
                    $fileFk['DELETE_RULE'] === $dbFk['DELETE_RULE']) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $fkDifferences[] = "  - Missing in schema: `{$dbFk['COLUMN_NAME']}` → `{$dbFk['REFERENCED_TABLE_NAME']}({$dbFk['REFERENCED_COLUMN_NAME']})` " .
                                 "ON UPDATE {$dbFk['UPDATE_RULE']} ON DELETE {$dbFk['DELETE_RULE']}";
            }
        }
        
        // Check FKs in file but not in DB
        foreach ($fileFks as $fileFk) {
            $found = false;
            foreach ($dbFks as $dbFk) {
                if ($fileFk['REFERENCED_TABLE_NAME'] === $dbFk['REFERENCED_TABLE_NAME'] &&
                    $fileFk['UPDATE_RULE'] === $dbFk['UPDATE_RULE'] &&
                    $fileFk['DELETE_RULE'] === $dbFk['DELETE_RULE']) {
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $fkDifferences[] = "  - Extra in schema: `{$fileFk['CONSTRAINT_NAME']}` → `{$fileFk['REFERENCED_TABLE_NAME']}` " .
                                 "ON UPDATE {$fileFk['UPDATE_RULE']} ON DELETE {$fileFk['DELETE_RULE']}";
            }
        }
        
        if (!empty($fkDifferences)) {
            $output .= "### Table: `$table`\n" . implode("\n", $fkDifferences) . "\n\n";
        }
    }
    
    // Save the comparison report
    $reportFile = 'schema_comparison_' . date('Ymd_His') . '.md';
    file_put_contents($reportFile, $output);
    
    echo "Comparison complete! Report saved to: $reportFile\n";
    
    // Generate SQL to update schema
    $updateSql = "-- SQL to update schema\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    // Drop tables that shouldn't be in the schema
    if (!empty($onlyInSchema)) {
        $updateSql .= "-- Tables to remove from schema (exist in schema but not in database)\n";
        foreach ($onlyInSchema as $table) {
            $updateSql .= "-- DROP TABLE IF EXISTS `$table`;\n";
        }
        $updateSql .= "\n";
    }
    
    // Add tables that are missing from schema
    if (!empty($onlyInDb)) {
        $updateSql .= "-- Tables to add to schema (exist in database but not in schema)\n";
        foreach ($onlyInDb as $table) {
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch()['Create Table'];
            $updateSql .= "$createTable;\n\n";
        }
    }
    
    // Save the update SQL
    $updateFile = 'schema_update_' . date('Ymd_His') . '.sql';
    file_put_contents($updateFile, $updateSql);
    
    echo "Update SQL generated: $updateFile\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
