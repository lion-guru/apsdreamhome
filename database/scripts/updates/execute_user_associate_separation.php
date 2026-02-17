<?php
/**
 * User and Associate Separation Migration Script
 * 
 * This script executes the SQL migration to properly separate user and associate data
 * in the APS Dream Homes database.
 */

// Include database configuration
require_once('config.php');

// Set maximum execution time to handle large datasets
ini_set('max_execution_time', 300); // 5 minutes

// Function to execute SQL from file
function executeSQLFromFile($conn, $filename) {
    try {
        // Read the SQL file
        $sql = file_get_contents($filename);
        
        if (!$sql) {
            throw new Exception("Error reading SQL file: $filename");
        }
        
        // Split SQL by delimiter statements
        $delimiter = ';';
        $delimiterKeyword = 'DELIMITER';
        $sqlPieces = [];
        $currentDelimiter = $delimiter;
        $currentPiece = '';
        
        // Process SQL line by line to handle DELIMITER changes
        foreach (explode("\n", $sql) as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || substr($line, 0, 2) == '--' || substr($line, 0, 1) == '#') {
                continue;
            }
            
            // Check for DELIMITER change
            if (strpos($line, $delimiterKeyword) === 0) {
                $parts = explode(' ', $line);
                if (isset($parts[1])) {
                    $currentDelimiter = trim($parts[1]);
                }
                continue;
            }
            
            $currentPiece .= $line . "\n";
            
            // Check if this line ends with the current delimiter
            if (substr(rtrim($line), -strlen($currentDelimiter)) == $currentDelimiter) {
                // Remove the delimiter from the end
                $currentPiece = substr(rtrim($currentPiece), 0, -strlen($currentDelimiter));
                $sqlPieces[] = $currentPiece;
                $currentPiece = '';
            }
        }
        
        // Execute each SQL statement
        $conn->begin_transaction();
        
        foreach ($sqlPieces as $piece) {
            if (!empty(trim($piece))) {
                if (!$conn->query($piece)) {
                    throw new Exception("Error executing SQL: " . $conn->error . "\nSQL: " . substr($piece, 0, 100) . "...");
                }
            }
        }
        
        // Execute the migration procedure
        if (!$conn->query("CALL migrate_user_associate_data()")) {
            throw new Exception("Error executing migration procedure: " . $conn->error);
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        throw $e;
    }
}

// Main execution
try {
    echo "<h1>User and Associate Separation Migration</h1>";
    echo "<p>Starting migration process...</p>";
    
    // Backup the database first
    echo "<p>Creating database backup...</p>";
    $backupFile = 'DATABASE FILE/backup_before_user_associate_separation_' . date('Y-m-d_H-i-s') . '.sql';
    $backupCmd = "mysqldump -u " . DB_USER . (DB_PASS ? " -p'" . DB_PASS . "'" : "") . " " . DB_NAME . " > " . $backupFile;
    
    // For security, we'll use PHP's built-in functions instead of system commands
    // This is just to show the command that would be executed
    echo "<p>Backup command would be (not executed for security): $backupCmd</p>";
    echo "<p>Please backup your database manually before proceeding.</p>";
    
    echo "<p>Executing migration script...</p>";
    $migrationFile = 'DATABASE FILE/user_associate_separation.sql';
    
    if (executeSQLFromFile($con, $migrationFile)) {
        echo "<div style='color: green; font-weight: bold;'>Migration completed successfully!</div>";
        echo "<p>The user and associate data has been properly separated in the database.</p>";
        echo "<p>Next steps:</p>";
        echo "<ol>";
        echo "<li>Verify that all associates can still log in</li>";
        echo "<li>Verify that the team hierarchy is correctly established</li>";
        echo "<li>Update any remaining code that might be using the old database structure</li>";
        echo "</ol>";
    }
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>Error during migration:</div>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>Please restore from your backup and try again after fixing the issues.</p>";
}
?>