<?php
/**
 * Export MLM-related tables schema
 */

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'apsdreamhome',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
];

// MLM-related tables to export
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

try {
    // Connect to database
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Start output
    $output = "-- APS Dream Home - MLM Database Schema\n";
    $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $output .= "SET AUTOCOMMIT = 0;\n";
    $output .= "START TRANSACTION;\n";
    $output .= "SET time_zone = \"+00:00\";\n\n";

    // Disable foreign key checks temporarily
    $output .= "--\n-- Disable foreign key checks temporarily\n--\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

    // Get create table statements
    foreach ($mlmTables as $table) {
        $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
        $row = $stmt->fetch();
        
        if (isset($row['Create Table'])) {
            $output .= "--\n-- Table structure for table `$table`\n--\n";
            $output .= "DROP TABLE IF EXISTS `$table`;\n";
            $output .= $row['Create Table'] . ";\n\n";
            
            // Get triggers for this table
            $triggers = $pdo->query("
                SHOW TRIGGERS LIKE '$table'
            ")->fetchAll();
            
            if (!empty($triggers)) {
                $output .= "--\n-- Triggers for table `$table`\n--\n";
                foreach ($triggers as $trigger) {
                    $triggerName = $trigger['Trigger'];
                    $triggerStmt = $pdo->query("SHOW CREATE TRIGGER `$triggerName`");
                    $triggerRow = $triggerStmt->fetch();
                    $output .= "DROP TRIGGER IF EXISTS `$triggerName`;\n";
                    $output .= "DELIMITER //\n";
                    $output .= $triggerRow['SQL Original Statement'] . "//\n";
                    $output .= "DELIMITER ;\n\n";
                }
            }
        }
    }

    // Re-enable foreign key checks
    $output .= "--\n-- Enable foreign key checks\n--\nSET FOREIGN_KEY_CHECKS = 1;\n\n";

    // Get stored procedures and functions
    $routines = $pdo->query("
        SELECT 
            ROUTINE_NAME, 
            ROUTINE_TYPE,
            ROUTINE_DEFINITION
        FROM 
            INFORMATION_SCHEMA.ROUTINES 
        WHERE 
            ROUTINE_SCHEMA = '{$dbConfig['dbname']}'
            AND (
                ROUTINE_NAME LIKE '%mlm%' 
                OR ROUTINE_NAME LIKE '%commission%'
                OR ROUTINE_NAME LIKE '%payout%'
                OR ROUTINE_NAME LIKE '%associate%'
            )
        ORDER BY ROUTINE_TYPE, ROUTINE_NAME
    ")->fetchAll();

    if (!empty($routines)) {
        $output .= "--\n-- Routines (Stored Procedures & Functions)\n--\n";
        $currentType = '';
        
        foreach ($routines as $routine) {
            if ($currentType !== $routine['ROUTINE_TYPE']) {
                $currentType = $routine['ROUTINE_TYPE'];
                $output .= "\n-- $currentType\n--\n";
            }
            
            $output .= "DROP {$routine['ROUTINE_TYPE']} IF EXISTS `{$routine['ROUTINE_NAME']}`;\n";
            $output .= "DELIMITER //\n";
            $output .= "CREATE {$routine['ROUTINE_TYPE']} `{$routine['ROUTINE_NAME']}()\n";
            $output .= "{$routine['ROUTINE_DEFINITION']} //\n";
            $output .= "DELIMITER ;\n\n";
        }
    }

    // Get views
    $views = $pdo->query("
        SELECT 
            TABLE_NAME,
            VIEW_DEFINITION,
            CHECK_OPTION,
            IS_UPDATABLE,
            SECURITY_TYPE
        FROM 
            INFORMATION_SCHEMA.VIEWS 
        WHERE 
            TABLE_SCHEMA = '{$dbConfig['dbname']}'
            AND (
                TABLE_NAME LIKE '%mlm%' 
                OR TABLE_NAME LIKE '%commission%'
                OR TABLE_NAME LIKE '%payout%'
                OR TABLE_NAME LIKE '%associate%'
            )
        ORDER BY TABLE_NAME
    ")->fetchAll();

    if (!empty($views)) {
        $output .= "--\n-- Views\n--\n";
        
        foreach ($views as $view) {
            $output .= "--\n-- View: `{$view['TABLE_NAME']}`\n--\n";
            $output .= "DROP VIEW IF EXISTS `{$view['TABLE_NAME']}`;\n";
            $output .= "CREATE ";
            $output .= ($view['SECURITY_TYPE'] === 'INVOKER' ? 'SQL SECURITY INVOKER ' : 'SQL SECURITY DEFINER ');
            $output .= "VIEW `{$view['TABLE_NAME']}` AS {$view['VIEW_DEFINITION']};\n\n";
        }
    }

    // Commit transaction
    $output .= "--\n-- Commit the transaction\n--\nCOMMIT;\n";

    // Save to file
    $filename = 'mlm_schema_' . date('Ymd_His') . '.sql';
    file_put_contents($filename, $output);
    
    echo "Schema exported to: $filename\n";
    
    // Also update the main schema file
    if (file_put_contents('db_schema_updated.sql', $output) !== false) {
        echo "Updated schema also saved to: db_schema_updated.sql\n";
    } else {
        echo "Warning: Could not save to db_schema_updated.sql\n";
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
