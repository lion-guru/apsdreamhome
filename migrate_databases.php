<?php
// Configuration
$config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'target_db' => 'apsdreamhomefinal',
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
        'sessions',
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

// Function to get table structure
function get_table_structure($conn, $db, $table) {
    $structure = [];
    $result = $conn->query("SHOW COLUMNS FROM `$db`.`$table`");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $structure[] = $row;
        }
    }
    
    return $structure;
}

// Function to check if table exists in target database
function table_exists($conn, $db, $table) {
    $result = $conn->query("SHOW TABLES FROM `$db` LIKE '$table'");
    return $result && $result->num_rows > 0;
}

// Function to migrate table data
function migrate_table($conn, $source_db, $target_db, $table, $skip_existing = true) {
    log_message("Migrating table: $source_db.$table to $target_db.$table");
    
    // Check if table exists in source
    if (!table_exists($conn, $source_db, $table)) {
        log_message("  - Table does not exist in source, skipping");
        return false;
    }
    
    // Check if table exists in target
    $table_exists = table_exists($conn, $target_db, $table);
    
    if ($table_exists) {
        log_message("  - Table exists in target, will only migrate data");
    } else {
        // Get table structure from source
        $create_table = $conn->query("SHOW CREATE TABLE `$source_db`.`$table`");
        if (!$create_table) {
            log_message("  - Error getting create table statement: " . $conn->error);
            return false;
        }
        
        $create_sql = $create_table->fetch_assoc()['Create Table'];
        
        // Replace the table name
        $create_sql = str_replace(
            "CREATE TABLE `$table`",
            "CREATE TABLE IF NOT EXISTS `$target_db`.`$table`",
            $create_sql
        );
        
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        
        if (!$conn->query($create_sql)) {
            log_message("  - Error creating table: " . $conn->error);
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
            return false;
        }
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        log_message("  - Table created in target database");
    }
    
    // Get table structure
    $structure = get_table_structure($conn, $source_db, $table);
    if (empty($structure)) {
        log_message("  - Error: Could not get table structure");
        return false;
    }
    
    // Create table in target if not exists
    $create_table = $conn->query("SHOW CREATE TABLE `$source_db`.`$table`");
    if ($create_table) {
        $create_sql = $create_table->fetch_assoc();
        $create_sql = $create_sql['Create Table'];
        
        // Replace the table name
        $create_sql = str_replace(
            "CREATE TABLE `$table`",
            "CREATE TABLE IF NOT EXISTS `$target_db`.`$table`",
            $create_sql
        );
        
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        
        if (!$conn->query($create_sql)) {
            log_message("  - Error creating table: " . $conn->error);
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
            return false;
        }
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
    } else {
        log_message("  - Error getting create table statement: " . $conn->error);
        return false;
    }
    
    // Get columns from source table
    $source_columns = [];
    $source_result = $conn->query("SHOW COLUMNS FROM `$source_db`.`$table`");
    while ($col = $source_result->fetch_assoc()) {
        $source_columns[] = $col['Field'];
    }
    
    // Get columns from target table
    $target_columns = [];
    $target_result = $conn->query("SHOW COLUMNS FROM `$target_db`.`$table`");
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
    $count_result = $conn->query("SELECT COUNT(*) as count FROM `$source_db`.`$table`");
    $row_count = $count_result->fetch_assoc()['count'];
    
    log_message("  - Found $row_count rows to migrate");
    
    if ($row_count > 0) {
        // Disable foreign key checks for data migration
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        
        // First, delete existing data if needed
        if (!$table_exists) {
            $conn->query("TRUNCATE TABLE `$target_db`.`$table`");
        }
        
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
log_message("Starting database migration process");
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
    $tables_result = $conn->query("SHOW TABLES FROM `$source_db`");
    $tables = [];
    
    if ($tables_result && $tables_result->num_rows > 0) {
        while ($row = $tables_result->fetch_row()) {
            $table = $row[0];
            
            // Skip system/special tables
            if (in_array($table, $config['skip_tables'])) {
                log_message("Skipping table (in skip list): $table");
                continue;
            }
            
            // Migrate the table
            migrate_table($conn, $source_db, $config['target_db'], $table);
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
