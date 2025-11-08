<?php
/**
 * Database Migration Tool - Handles migration without foreign key constraints
 */

// Configuration
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'target_db' => 'apsdreamhome',
    'source_dbs' => ['aps_dream_home', 'apsdreamhome', 'apsdreamhomes'],
    'skip_tables' => [
        'migrations',
        'migration_errors',
        'password_reset_temp',
        'sessions',
        'cache',
        'job_batches',
        'failed_jobs',
        'jobs',
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring'
    ]
];

// Create connection without selecting a database
$conn = new mysqli($config['host'], $config['user'], $config['pass']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to log messages
function log_message($message) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
}

// Function to get create table statement without foreign keys using prepared statement
function get_create_table_without_fk($conn, $db, $table) {
    global $config;
    $stmt = $conn->prepare("SHOW CREATE TABLE `$db`.`$table`");
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        log_message("  - Error getting create table statement: " . $conn->error);
        $stmt->close();
        return false;
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    $createTable = $row['Create Table'];

    $row = $result->fetch_assoc();
    $create_sql = $row['Create Table'];

    // Remove foreign key constraints
    $create_sql = preg_replace('/CONSTRAINT `[^`]+` FOREIGN KEY \(`[^`]+`\) REFERENCES `[^`]+` \(`[^`]+`\).*?(?:,|$)/', '', $create_sql);
    $create_sql = str_replace(',\n  KEY', '\n  KEY', $create_sql);
    $create_sql = str_replace(',\n  PRIMARY', '\n  PRIMARY', $create_sql);
    $create_sql = str_replace(',\n  UNIQUE', '\n  UNIQUE', $create_sql);
    
    // Remove trailing comma if exists
    if (substr(trim($create_sql), -1) === ',') {
        $create_sql = substr(trim($create_sql), 0, -1) . ';';
    }
    
    // Replace the table name
    $create_sql = str_replace(
        "CREATE TABLE `$table`",
        "CREATE TABLE IF NOT EXISTS `{$config['target_db']}`.`$table`",
        $create_sql
    );
    
    return $create_sql;
}

// Function to get foreign key constraints for a table
function get_foreign_keys($conn, $db, $table) {
    $fks = [];
    $result = $conn->query("SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                           WHERE TABLE_SCHEMA = '$db' 
                           AND TABLE_NAME = '$table'
                           AND REFERENCED_TABLE_SCHEMA IS NOT NULL");
    
    while ($row = $result->fetch_assoc()) {
        $fks[] = [
            'column' => $row['COLUMN_NAME'],
            'ref_table' => $row['REFERENCED_TABLE_NAME'],
            'ref_column' => $row['REFERENCED_COLUMN_NAME'],
            'constraint_name' => $row['CONSTRAINT_NAME']
        ];
    }
    
    return $fks;
}

// Function to migrate table data
function migrate_table_data($conn, $source_db, $target_db, $table) {
    log_message("Migrating data: $source_db.$table to $target_db.$table");
    
    // Get columns from source table
    $source_columns = [];
    $source_sql = "SHOW COLUMNS FROM `" . $conn->real_escape_string($source_db) . "`.`" . $conn->real_escape_string($table) . "`";
    $source_result = $conn->query($source_sql);
    
    if ($source_result === false) {
        log_message("  - Error getting source columns: " . $conn->error);
        return false;
    }
    
    while ($col = $source_result->fetch_assoc()) {
        $source_columns[] = $col['Field'];
    }
    
    // Get columns from target table
    $target_columns = [];
    $target_sql = "SHOW COLUMNS FROM `" . $conn->real_escape_string($target_db) . "`.`" . $conn->real_escape_string($table) . "`";
    $target_result = $conn->query($target_sql);
    
    if ($target_result === false) {
        log_message("  - Error getting target columns: " . $conn->error);
        return false;
    }
    
    while ($col = $target_result->fetch_assoc()) {
        $target_columns[] = $col['Field'];
    }
    
    // Find common columns
    $common_columns = array_intersect($source_columns, $target_columns);
    
    if (empty($common_columns)) {
        log_message("  - No common columns found between source and target tables");
        return false;
    }
    
    $columns_sql = '`' . implode('`, `', $common_columns) . '`';
    
    // Count rows in source table
    $count_sql = "SELECT COUNT(*) as count FROM `" . $conn->real_escape_string($source_db) . "`.`" . $conn->real_escape_string($table) . "`";
    $count_result = $conn->query($count_sql);
    
    if ($count_result === false) {
        log_message("  - Error counting rows: " . $conn->error);
        return false;
    }
    $row = $count_result->fetch_assoc();
    $row_count = $row ? $row['count'] : 0;
    
    log_message("  - Found $row_count rows to migrate");
    
    if ($row_count > 0) {
        // Disable foreign key checks for data migration
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        
        // Insert data in chunks to avoid timeouts
        $chunk_size = 1000;
        $offset = 0;
        $migrated = 0;
        
        while ($offset < $row_count) {
            $insert_sql = "INSERT IGNORE INTO `$target_db`.`$table` ($columns_sql) 
                         SELECT $columns_sql FROM `$source_db`.`$table` 
                         LIMIT $offset, $chunk_size";
            
            if ($conn->query($insert_sql)) {
                $affected = $conn->affected_rows;
                $migrated += $affected;
                $offset += $chunk_size;
                log_message("  - Migrated $migrated/$row_count rows");
            } else {
                log_message("  - Error migrating data chunk: " . $conn->error);
                $conn->query("SET FOREIGN_KEY_CHECKS=1");
                return false;
            }
        }
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        log_message("  - Successfully migrated $migrated rows");
    } else {
        log_message("  - No data to migrate");
    }
    
    return true;
}

// Main migration process
log_message("Starting database migration process (without foreign keys)");
log_message("Target database: " . $config['target_db']);
log_message("Source databases: " . implode(', ', $config['source_dbs']));
log_message("=======================================\n");

// Process each source database
foreach ($config['source_dbs'] as $source_db) {
    // Check if source database exists
    $result = $conn->query("SHOW DATABASES LIKE '$source_db'");
    if (!$result || $result->num_rows === 0) {
        log_message("Source database '$source_db' does not exist, skipping...");
        continue;
    }
    
    log_message("\nProcessing database: $source_db");
    log_message("------------------------------");
    
    // Get all tables in source database
    $tables_result = $conn->query("SHOW TABLES FROM `$" . $source_db . "`");
    $tables = [];
    
    if ($tables_result && $tables_result->num_rows > 0) {
        // First pass: create all tables without foreign keys
        $tables_result->data_seek(0); // Reset pointer
        while ($row = $tables_result->fetch_row()) {
            $table = $row[0];
            
            // Skip system/special tables
            if (in_array($table, $config['skip_tables'])) {
                log_message("Skipping table (in skip list): $table");
                continue;
            }
            
            log_message("Creating table: $table");
            
            // Get create table statement without foreign keys
            $create_sql = get_create_table_without_fk($conn, $source_db, $table);
            if (!$create_sql) {
                log_message("  - Error getting table structure, skipping...");
                continue;
            }
            
            // Create the table
            $conn->query("SET FOREIGN_KEY_CHECKS=0");
            if ($conn->query($create_sql)) {
                log_message("  - Table created successfully");
            } else {
                log_message("  - Error creating table: " . $conn->error);
            }
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
        }
        
        // Second pass: migrate data
        $tables_result->data_seek(0); // Reset pointer again
        while ($row = $tables_result->fetch_row()) {
            $table = $row[0];
            
            // Skip system/special tables
            if (in_array($table, $config['skip_tables'])) {
                continue;
            }
            
            // Migrate data
            migrate_table_data($conn, $source_db, $config['target_db'], $table);
        }
    } else {
        log_message("No tables found in $source_db");
    }
    
    log_message("Finished processing database: $source_db\n");
}

log_message("\nDatabase migration process completed!");

// Close connection
$conn->close();
?>
