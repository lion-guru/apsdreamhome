<?php
/**
 * Database Migration Helper
 * This file provides functions to assist with database migrations and schema updates
 * for the APS Dream Homes website.
 */

// Include the database connection handler
require_once 'src/Database/Database.php';

/**
 * Check if a table exists in the database
 * @param string $tableName The name of the table to check
 * @return bool True if the table exists, false otherwise
 */
function tableExists($tableName) {
    $db = \App\Core\App::database();
    $result = $db->fetch("SHOW TABLES LIKE :tableName", ['tableName' => $tableName]);
    return !empty($result);
}

/**
 * Check if a column exists in a table
 * @param string $tableName The name of the table
 * @param string $columnName The name of the column to check
 * @return bool True if the column exists, false otherwise
 */
function columnExists($tableName, $columnName) {
    if (!tableExists($tableName)) {
        return false;
    }
    
    $db = \App\Core\App::database();
    $result = $db->fetch("SHOW COLUMNS FROM `$tableName` LIKE :columnName", ['columnName' => $columnName]);
    return !empty($result);
}

/**
 * Add a column to a table if it doesn't exist
 * @param string $tableName The name of the table
 * @param string $columnName The name of the column to add
 * @param string $columnDefinition The SQL definition of the column (e.g., "VARCHAR(255) NOT NULL")
 * @return bool True if the column was added or already exists, false on error
 */
function addColumnIfNotExists($tableName, $columnName, $columnDefinition) {
    if (columnExists($tableName, $columnName)) {
        return true;
    }
    
    try {
        $db = \App\Core\App::database();
        $db->query("ALTER TABLE `$tableName` ADD COLUMN `$columnName` $columnDefinition");
        return true;
    } catch (Exception $e) {
        error_log("Error adding column $columnName to $tableName: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a table if it doesn't exist
 * @param string $tableName The name of the table to create
 * @param string $tableDefinition The SQL definition of the table columns and constraints
 * @return bool True if the table was created or already exists, false on error
 */
function createTableIfNotExists($tableName, $tableDefinition) {
    if (tableExists($tableName)) {
        return true;
    }
    
    try {
        $db = \App\Core\App::database();
        $db->query("CREATE TABLE `$tableName` ($tableDefinition)");
        return true;
    } catch (Exception $e) {
        error_log("Error creating table $tableName: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a backup of a table
 * @param string $tableName The name of the table to backup
 * @param string $backupTableName The name for the backup table
 * @return bool True if the backup was created successfully, false on error
 */
function backupTable($tableName, $backupTableName = null) {
    if (!tableExists($tableName)) {
        return false;
    }
    
    if ($backupTableName === null) {
        $backupTableName = $tableName . '_backup_' . date('Ymd_His');
    }
    
    try {
        $db = \App\Core\App::database();
        // Drop the backup table if it already exists
        $db->query("DROP TABLE IF EXISTS `$backupTableName` text");
        $db->query("CREATE TABLE `$backupTableName` AS SELECT * FROM `$tableName` text");
        return true;
    } catch (Exception $e) {
        error_log("Error backing up table $tableName to $backupTableName: " . $e->getMessage());
        return false;
    }
}

/**
 * Execute a SQL file
 * @param string $filePath The path to the SQL file
 * @return bool True if the SQL file was executed successfully, false on error
 */
function executeSqlFile($filePath) {
    if (!file_exists($filePath)) {
        error_log("SQL file not found: $filePath");
        return false;
    }
    
    $sql = file_get_contents($filePath);
    if (!$sql) {
        error_log("Could not read SQL file: $filePath");
        return false;
    }
    
    // Split the SQL file by delimiter statements
    $delimiter = ';';
    $sqlPieces = [];
    $currentDelimiter = ';';
    
    // Split the SQL into statements based on delimiter
    $lines = explode("\n", $sql);
    $statement = '';
    $inProcedure = false;
    
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        
        // Skip comments and empty lines
        if (empty($trimmedLine) || strpos($trimmedLine, '--') === 0 || strpos($trimmedLine, '/*') === 0) {
            continue;
        }
        
        // Check for DELIMITER statements
        if (strpos($trimmedLine, 'DELIMITER') === 0) {
            if ($statement !== '') {
                $sqlPieces[] = $statement;
                $statement = '';
            }
            
            // Extract new delimiter
            $newDelimiter = str_replace('DELIMITER ', '', $trimmedLine);
            $currentDelimiter = trim($newDelimiter);
            $inProcedure = ($currentDelimiter !== ';');
            continue;
        }
        
        // Add the line to the current statement
        $statement .= $line . "\n";
        
        // Check if the line ends with the current delimiter
        if (!$inProcedure && substr(rtrim($line), -strlen($currentDelimiter)) === $currentDelimiter) {
            // Remove the delimiter from the end of the statement
            $statement = substr($statement, 0, strrpos($statement, $currentDelimiter));
            $sqlPieces[] = $statement;
            $statement = '';
        } else if ($inProcedure && strpos($trimmedLine, $currentDelimiter) !== false) {
            // For procedures/functions/triggers with custom delimiters
            $sqlPieces[] = $statement;
            $statement = '';
            $inProcedure = false;
            $currentDelimiter = ';';
        }
    }
    
    // Add any remaining statement
    if (trim($statement) !== '') {
        $sqlPieces[] = $statement;
    }
    
    // Execute each statement
    try {
        $db = \App\Core\App::database();
        $success = true;
        foreach ($sqlPieces as $piece) {
            $piece = trim($piece);
            if (empty($piece)) {
                continue;
            }
            
            $db->query($piece);
        }
        return true;
    } catch (Exception $e) {
        error_log("Error executing SQL statement: " . $e->getMessage());
        return false;
    }
}

/**
 * Check and fix database encoding
 * @param string $tableName The name of the table to check/fix
 * @return bool True if the encoding was fixed or already correct, false on error
 */
function fixDatabaseEncoding($tableName) {
    if (!tableExists($tableName)) {
        return false;
    }
    
    $query = "ALTER TABLE `$tableName` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    $result = executeQuery($query);
    
    return $result !== null && $result !== false;
}

/**
 * Add indexes to a table for better performance
 * @param string $tableName The name of the table
 * @param array $indexes Associative array of index definitions
 *                      [index_name => [columns => [col1, col2], type => 'INDEX|UNIQUE|FULLTEXT']]
 * @return bool True if all indexes were added successfully, false on error
 */
function addIndexes($tableName, $indexes) {
    if (!tableExists($tableName)) {
        return false;
    }
    
    $success = true;
    
    foreach ($indexes as $indexName => $indexDef) {
        $columns = $indexDef['columns'];
        $type = isset($indexDef['type']) ? $indexDef['type'] : 'INDEX';
        
        // Check if index already exists
        $checkQuery = "SHOW INDEX FROM `$tableName` WHERE Key_name = ?";
        $result = executeQuery($checkQuery, [$indexName]);
        
        if ($result && $result->num_rows > 0) {
            continue; // Index already exists
        }
        
        // Create the index
        $columnList = '`' . implode('`, `', $columns) . '`';
        $query = "ALTER TABLE `$tableName` ADD $type `$indexName` ($columnList)";
        $result = executeQuery($query);
        
        if ($result === null || $result === false) {
            $success = false;
        }
    }
    
    return $success;
}

/**
 * Fix auto-increment values for a table
 * @param string $tableName The name of the table
 * @param string $primaryKeyColumn The name of the primary key column
 * @return bool True if the auto-increment was fixed, false on error
 */
function fixAutoIncrement($tableName, $primaryKeyColumn) {
    if (!tableExists($tableName)) {
        return false;
    }
    
    // Get the maximum value of the primary key
    $query = "SELECT MAX(`$primaryKeyColumn`) as max_id FROM `$tableName`";
    $result = executeQuery($query);
    
    if (!$result || !($result instanceof mysqli_result)) {
        return false;
    }
    
    $row = $result->fetch_assoc();
    $maxId = $row['max_id'] ?? 0;
    
    // Set the auto-increment value to max_id + 1
    $query = "ALTER TABLE `$tableName` AUTO_INCREMENT = " . ($maxId + 1);
    $result = executeQuery($query);
    
    return $result !== null && $result !== false;
}