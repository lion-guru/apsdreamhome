<?php
/**
 * Execute Consolidated Migration Script
 * This script executes the consolidated migration SQL file with proper error handling
 */

// Include database configuration
require_once dirname(__DIR__) . '/includes/DatabaseConfig.php';

// Set execution time limit to 5 minutes for large migrations
set_time_limit(300);

// Initialize database connection
$db = DatabaseConfig::getConnection();
if (!$db) {
    die("Database connection failed. Cannot proceed with migration.\n");
}

// Path to consolidated migration file
$migrationFile = dirname(__DIR__) . '/DATABASE FILE/consolidated_migration.sql';

if (!file_exists($migrationFile)) {
    die("Migration file not found: $migrationFile\n");
}

echo "Starting database migration...\n";
echo "Using migration file: $migrationFile\n\n";

// Read migration file
$sql = file_get_contents($migrationFile);
if (!$sql) {
    die("Failed to read migration file.\n");
}

// Split SQL by delimiter statements
function splitSqlByDelimiter($sql) {
    $delimiter = ';';
    $delimiterPattern = '/DELIMITER\s+([^\s]+)/i';
    $tokens = [];
    $currentToken = '';
    
    // Split by lines to handle DELIMITER statements
    $lines = explode("\n", $sql);
    
    foreach ($lines as $line) {
        // Check for DELIMITER statement
        if (preg_match($delimiterPattern, $line, $matches)) {
            // Add current token if not empty
            if (trim($currentToken) !== '') {
                $tokens[] = $currentToken;
                $currentToken = '';
            }
            
            // Set new delimiter
            $delimiter = $matches[1];
        } 
        // Check if line ends with current delimiter
        elseif (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
            $currentToken .= $line . "\n";
            $tokens[] = $currentToken;
            $currentToken = '';
        } 
        // Add line to current token
        else {
            $currentToken .= $line . "\n";
        }
    }
    
    // Add final token if not empty
    if (trim($currentToken) !== '') {
        $tokens[] = $currentToken;
    }
    
    return $tokens;
}

// Execute migration with transaction support
try {
    // Disable foreign key checks temporarily
    $db->query('SET FOREIGN_KEY_CHECKS = 0');
    
    // Split SQL by delimiter
    $statements = splitSqlByDelimiter($sql);
    
    // Execute each statement
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        echo "Executing statement...\n";
        $result = $db->multi_query($statement);
        
        if (!$result) {
            throw new Exception("Error executing SQL: " . $db->error);
        }
        
        // Clear results to prepare for next statement
        while ($db->more_results() && $db->next_result()) {
            $result = $db->use_result();
            if ($result instanceof mysqli_result) {
                $result->free();
            }
        }
    }
    
    // Re-enable foreign key checks
    $db->query('SET FOREIGN_KEY_CHECKS = 1');
    
    echo "\nMigration completed successfully!\n";
    echo "The database has been updated to the latest version.\n";
} catch (Exception $e) {
    echo "\nMigration failed: " . $e->getMessage() . "\n";
    echo "Rolling back changes...\n";
    
    // Attempt to rollback if possible
    $db->query('ROLLBACK');
    $db->query('SET FOREIGN_KEY_CHECKS = 1');
    
    echo "Please check the migration file and try again.\n";
}