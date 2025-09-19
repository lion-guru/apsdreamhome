<?php
/**
 * Final Database Migration Script
 * 
 * This script will:
 * 1. Create a backup of the target database
 * 2. Migrate data from source databases handling duplicates
 */

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
        'telescope_entries',
        'telescope_entries_tags',
        'telescope_monitoring'
    ]
];

// Create connection
$conn = new mysqli($config['host'], $config['user'], $config['pass']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to log messages
function log_message($message) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
}

// Function to backup database
function backup_database($conn, $config) {
    $backup_file = __DIR__ . '/db_backups/backup_' . date('Y-m-d_His') . '.sql';
    
    log_message("Creating backup of {$config['target_db']}...");
    
    $command = sprintf(
        'C:/xampp/mysql/bin/mysqldump -u%s %s > "%s"',
        $config['user'],
        $config['target_db'],
        $backup_file
    );
    
    system($command, $return_var);
    
    if ($return_var !== 0) {
        log_message("Error creating backup");
        return false;
    }
    
    log_message("Backup created: $backup_file");
    return true;
}

// Function to get primary key of a table
function get_primary_key($conn, $db, $table) {
    $result = $conn->query("SHOW KEYS FROM `$db`.`$table` WHERE Key_name = 'PRIMARY'");
    if ($result && $row = $result->fetch_assoc()) {
        return $row['Column_name'];
    }
    return null;
}

// Function to migrate table data
function migrate_table_data($conn, $source_db, $target_db, $table) {
    log_message("Migrating data: $source_db.$table to $target_db.$table");
    
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
    $placeholders = rtrim(str_repeat('?,', count($common_columns)), ',');
    
    // Get primary key for conflict resolution
    $primary_key = get_primary_key($conn, $target_db, $table);
    
    // Count rows in source table
    $count_result = $conn->query("SELECT COUNT(*) as count FROM `$source_db`.`$table`");
    $row = $count_result->fetch_assoc();
    $row_count = $row ? $row['count'] : 0;
    
    log_message("  - Found $row_count rows to migrate");
    
    if ($row_count > 0) {
        // Disable foreign key checks for data migration
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        
        // Get data from source table
        $source_data = $conn->query("SELECT $columns_sql FROM `$source_db`.`$table`");
        
        if (!$source_data) {
            log_message("  - Error reading source data: " . $conn->error);
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
            return false;
        }
        
        $migrated = 0;
        $skipped = 0;
        
        // Prepare INSERT IGNORE statement for each row
        while ($row = $source_data->fetch_assoc()) {
            // Skip if primary key exists in target
            if ($primary_key && isset($row[$primary_key])) {
                $check = $conn->query("SELECT 1 FROM `$target_db`.`$table` WHERE `$primary_key` = '" . $conn->real_escape_string($row[$primary_key]) . "'");
                if ($check && $check->num_rows > 0) {
                    $skipped++;
                    continue;
                }
            }
            
            // Build and execute INSERT IGNORE
            $values = [];
            foreach ($common_columns as $col) {
                $values[] = $conn->real_escape_string($row[$col]);
            }
            
            $sql = "INSERT IGNORE INTO `$target_db`.`$table` ($columns_sql) VALUES ('" . implode("', '", $values) . "')";
            
            if ($conn->query($sql)) {
                $migrated += $conn->affected_rows;
            } else {
                log_message("  - Error inserting row: " . $conn->error);
            }
        }
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        log_message("  - Successfully migrated $migrated rows");
        if ($skipped > 0) {
            log_message("  - Skipped $skipped duplicate rows");
        }
    } else {
        log_message("  - No data to migrate");
    }
    
    return true;
}

// Main migration process
log_message("Starting final database migration process");
log_message("Target database: " . $config['target_db']);
log_message("Source databases: " . implode(', ', $config['source_dbs']));
log_message("=======================================\n");

// Create backup of target database
if (!backup_database($conn, $config)) {
    log_message("Backup failed, aborting migration");
    exit(1);
}

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
    
    if ($tables_result && $tables_result->num_rows > 0) {
        while ($row = $tables_result->fetch_row()) {
            $table = $row[0];
            
            // Skip system/special tables
            if (in_array($table, $config['skip_tables'])) {
                log_message("Skipping table (in skip list): $table");
                continue;
            }
            
            // Check if table exists in target
            $conn->select_db($config['target_db']);
            $table_exists = $conn->query("SHOW TABLES LIKE '$table'");
            if ($table_exists && $table_exists->num_rows > 0) {
                log_message("Migrating data to existing table: $table");
                migrate_table_data($conn, $source_db, $config['target_db'], $table);
            } else {
                log_message("Table $table does not exist in target, skipping...");
            }
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
