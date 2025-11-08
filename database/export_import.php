<?php
/**
 * Database Export/Import Tool
 * 
 * This script exports and imports database structure and data
 * while handling foreign key constraints properly.
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

// Path to mysqldump (adjust if needed)
$mysqldump = 'C:/xampp/mysql/bin/mysqldump.exe';
$mysql = 'C:/xampp/mysql/bin/mysql.exe';

// Create temp directory
$temp_dir = __DIR__ . '/db_backups';
if (!file_exists($temp_dir)) {
    mkdir($temp_dir, 0777, true);
}

// Function to log messages
function log_message($message) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
}

// Function to run command
function run_command($command) {
    $output = [];
    $return_var = 0;
    
    log_message("Running: $command");
    
    exec($command . ' 2>&1', $output, $return_var);
    
    if ($return_var !== 0) {
        log_message("Command failed with code $return_var");
        log_message("Output: " . implode("\n", $output));
        return false;
    }
    
    return $output;
}

log_message("Starting database migration process");
log_message("Target database: " . $config['target_db']);
log_message("Source databases: " . implode(', ', $config['source_dbs']));
log_message("=======================================\n");

// Process each source database
foreach ($config['source_dbs'] as $source_db) {
    // Check if source database exists
    $check_db = run_command("$mysql -u{$config['user']} -e \"SHOW DATABASES LIKE '$source_db'\"");
    
    if (empty($check_db) || strpos(implode("\n", $check_db), $source_db) === false) {
        log_message("Source database '$source_db' does not exist, skipping...");
        continue;
    }
    
    log_message("\nProcessing database: $source_db");
    log_message("------------------------------");
    
    // Get list of tables
    $tables = [];
    $tables_result = run_command("$mysql -u{$config['user']} -e \"SHOW TABLES FROM `$source_db`\"");
    
    if ($tables_result) {
        foreach ($tables_result as $line) {
            $line = trim($line);
            if (!empty($line) && !in_array($line, $config['skip_tables'])) {
                $tables[] = $line;
            }
        }
    }
    
    if (empty($tables)) {
        log_message("No tables found in $source_db");
        continue;
    }
    
    log_message("Found " . count($tables) . " tables to process");
    
    // Export and import each table
    foreach ($tables as $table) {
        $dump_file = "$temp_dir/{$source_db}_{$table}.sql";
        
        // Export table structure without data
        $cmd = "$mysqldump --no-data -u{$config['user']} --skip-add-drop-table --skip-triggers $source_db $table > \"$dump_file\"";
        if (!run_command($cmd)) {
            log_message("  - Error exporting structure for $table");
            continue;
        }
        
        // Export table data
        $cmd = "$mysqldump --no-create-info --skip-triggers -u{$config['user']} $source_db $table >> \"$dump_file\"";
        if (!run_command($cmd)) {
            log_message("  - Error exporting data for $table");
            continue;
        }
        
        log_message("  - Exported $table");
        
        // Import into target database
        $cmd = "$mysql -u{$config['user']} {$config['target_db']} < \"$dump_file\"";
        if (run_command($cmd)) {
            log_message("  - Imported $table");
        } else {
            log_message("  - Error importing $table");
        }
        
        // Clean up
        @unlink($dump_file);
    }
    
    log_message("Finished processing database: $source_db\n");
}

log_message("\nDatabase migration process completed!");
?>
