<?php
/**
 * Improved Database Migration Script
 * This script provides a comprehensive solution for database migration and fixes
 * for the APS Dream Homes website.
 */

// Include necessary files
require_once 'config.php';
require_once 'includes/src/Database/Database.php';
require_once 'includes/migration_helper.php';

// Set error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to HTML for better display
header('Content-Type: text/html; charset=utf-8');

// Define CSS for better UI
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Homes - Database Migration</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .success {
            color: #27ae60;
            background-color: #e8f5e9;
            padding: 10px;
            border-radius: 3px;
            margin: 10px 0;
        }
        .warning {
            color: #f39c12;
            background-color: #fef9e7;
            padding: 10px;
            border-radius: 3px;
            margin: 10px 0;
        }
        .error {
            color: #e74c3c;
            background-color: #fdedeb;
            padding: 10px;
            border-radius: 3px;
            margin: 10px 0;
        }
        .info {
            color: #3498db;
            background-color: #ebf5fb;
            padding: 10px;
            border-radius: 3px;
            margin: 10px 0;
        }
        pre {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>APS Dream Homes - Database Migration Tool</h1>
        <p>This tool helps fix database issues and perform migrations safely.</p>
        
        <?php
        // Check if form is submitted
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
            
            switch ($action) {
                case 'check_connection':
                    checkDatabaseConnection();
                    break;
                    
                case 'backup_database':
                    backupDatabase();
                    break;
                    
                case 'fix_encoding':
                    fixDatabaseEncoding();
                    break;
                    
                case 'run_migration':
                    runMigration();
                    break;
                    
                case 'fix_auto_increment':
                    fixAutoIncrementValues();
                    break;
                    
                case 'optimize_tables':
                    optimizeTables();
                    break;
                    
                default:
                    echo "<div class='error'>Invalid action specified.</div>";
            }
        }
        ?>
        
        <h2>Available Actions</h2>
        <form method="post" action="">
            <button type="submit" name="action" value="check_connection" class="btn">Check Database Connection</button>
            <button type="submit" name="action" value="backup_database" class="btn">Backup Database</button>
            <button type="submit" name="action" value="fix_encoding" class="btn">Fix Database Encoding</button>
            <button type="submit" name="action" value="run_

/**
 * Backup the database
 */
function backupDatabase() {
    echo "<div class='container'>";
    echo "<h2>Database Backup</h2>";
    
    $connection = getDbConnection();
    
    if (!$connection) {
        echo "<div class='error'>Failed to connect to the database.</div>";
        echo "</div>";
        return;
    }
    
    // Create backup directory if it doesn't exist
    $backupDir = __DIR__ . '/backups';
    if (!file_exists($backupDir)) {
        if (!mkdir($backupDir, 0755, true)) {
            echo "<div class='error'>Failed to create backup directory.</div>";
            echo "</div>";
            return;
        }
    }
    
    // Generate backup filename
    $backupFile = $backupDir . '/' . DB_NAME . '_backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Get all tables
    $tables = [];
    $result = $connection->query('SHOW TABLES');
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    
    if (empty($tables)) {
        echo "<div class='warning'>No tables found to backup.</div>";
        echo "</div>";
        return;
    }
    
    // Open backup file for writing
    $handle = fopen($backupFile, 'w');
    if (!$handle) {
        echo "<div class='error'>Failed to create backup file.</div>";
        echo "</div>";
        return;
    }
    
    // Write header
    fwrite($handle, "-- Database Backup for " . DB_NAME . "\n");
    fwrite($handle, "-- Generated on " . date('Y-m-d H:i:s') . "\n\n");
    fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
    fwrite($handle, "START TRANSACTION;\n");
    fwrite($handle, "SET time_zone = \"+00:00\";\n\n");
    
    // Write database creation
    fwrite($handle, "--\n");
    fwrite($handle, "-- Database: `" . DB_NAME . "`\n");
    fwrite($handle, "--\n\n");
    fwrite($handle, "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;\n");
    fwrite($handle, "USE `" . DB_NAME . "`;\n\n");
    
    // Disable foreign key checks
    fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n\n");
    
    // Process each table
    foreach ($tables as $table) {
        echo "<div class='info'>Backing up table: $table</div>";
        
        // Get create table statement
        $result = $connection->query("SHOW CREATE TABLE `$table`");
        $row = $result->fetch_row();
        $createTable = $row[1];
        
        // Write table structure
        fwrite($handle, "--\n");
        fwrite($handle, "-- Table structure for table `$table`\n");
        fwrite($handle, "--\n\n");
        fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");
        fwrite($handle, "$createTable;\n\n");
        
        // Get table data
        $result = $connection->query("SELECT * FROM `$table`");
        $numFields = $result->field_count;
        $numRows = $result->num_rows;
        
        if ($numRows > 0) {
            // Write table data
            fwrite($handle, "--\n");
            fwrite($handle, "-- Dumping data for table `$table`\n");
            fwrite($handle, "--\n\n");
            
            $fields = [];
            $fieldTypes = [];
            
            // Get field information
            $fieldsResult = $connection->query("SHOW COLUMNS FROM `$table`");
            while ($fieldRow = $fieldsResult->fetch_assoc()) {
                $fields[] = $fieldRow['Field'];
                $fieldTypes[$fieldRow['Field']] = $fieldRow['Type'];
            }
            
            // Write insert statements
            $counter = 0;
            $maxRowsPerInsert = 100;
            
            while ($row = $result->fetch_assoc()) {
                if ($counter % $maxRowsPerInsert === 0) {
                    if ($counter > 0) {
                        fwrite($handle, ";\n");
                    }
                    fwrite($handle, "INSERT INTO `$table` (`" . implode('`, `', $fields) . "`) VALUES\n");
                } else {
                    fwrite($handle, ",\n");
                }
                
                $values = [];
                foreach ($fields as $field) {
                    if (is_null($row[$field])) {
                        $values[] = "NULL";
                    } elseif (strpos($fieldTypes[$field], 'int') !== false || 
                              strpos($fieldTypes[$field], 'float') !== false || 
                              strpos($fieldTypes[$field], 'double') !== false || 
                              strpos($fieldTypes[$field], 'decimal') !== false) {