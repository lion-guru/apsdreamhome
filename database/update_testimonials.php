<?php
/**
 * Update script for testimonials database
 * 
 * This script will safely update the testimonials table structure
 * while preserving existing data.
 */

// Set error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set the content type to text/plain for CLI and web
header('Content-Type: text/plain');

// Check if running from command line
$isCli = php_sapi_name() === 'cli';

// Only allow access from localhost or CLI
if (!$isCli) {
    $allowedIps = ['127.0.0.1', '::1'];
    $clientIp = $_SERVER['REMOTE_ADDR'];
    
    if (!in_array($clientIp, $allowedIps)) {
        http_response_code(403);
        die('Access denied');
    }
}

// Output function that works in both CLI and web
function output($message) {
    if (php_sapi_name() === 'cli') {
        echo $message . PHP_EOL;
    } else {
        echo nl2br(htmlspecialchars($message)) . "<br>\n";
    }
}

try {
    // Include database connection
    require_once __DIR__ . '/../includes/db_connection.php';
    
    // Get database connection
    $conn = getDbConnection();
    
    // Start transaction
    $conn->begin_transaction();
    
    output("Starting database update...");
    
    // Read the update SQL file
    $updateSql = file_get_contents(__DIR__ . '/update_testimonials_table.sql');
    
    if (empty($updateSql)) {
        throw new Exception("Failed to read update SQL file");
    }
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $updateSql)));
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        output("Executing: " . substr($statement, 0, 100) . (strlen($statement) > 100 ? '...' : ''));
        
        if (!$conn->query($statement)) {
            // Check if this is a "duplicate column" error, which we can safely ignore
            if ($conn->errno != 1060) { // 1060 is ER_DUP_FIELDNAME
                throw new Exception("Error executing SQL: " . $conn->error . "\nStatement: " . $statement);
            }
            output("  - Column already exists, continuing...");
        }
    }
    
    // Commit the transaction
    $conn->commit();
    output("\nDatabase update completed successfully!");
    
    // Show current table structure
    output("\nCurrent Table Structure:");
    output(str_repeat("-", 80));
    
    $result = $conn->query("SHOW COLUMNS FROM `testimonials`");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            output(sprintf("%-15s %-30s %-10s %s", 
                $row['Field'], 
                $row['Type'], 
                $row['Null'], 
                $row['Key']
            ));
        }
    }
    
    // Show current data
    output("\nCurrent Testimonials:");
    output(str_repeat("-", 80));
    
    $result = $conn->query("SELECT `id`, `client_name`, `email`, `rating`, `status`, `created_at` FROM `testimonials` ORDER BY `created_at` DESC LIMIT 5");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            output(sprintf("#%d: %s (%s) - %d/5 - %s - %s",
                $row['id'],
                $row['client_name'],
                $row['email'] ?: 'no-email',
                $row['rating'],
                $row['status'],
                $row['created_at']
            ));
        }
        
        if ($result->num_rows >= 5) {
            output("... and more (showing 5 most recent)");
        }
    } else {
        output("No testimonials found in the database.");
    }
    
} catch (Exception $e) {
    // Rollback the transaction on error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    output("\nError: " . $e->getMessage());
    output("File: " . $e->getFile() . " (Line: " . $e->getLine() . ")");
    
    if (isset($conn) && $conn->error) {
        output("MySQL Error: " . $conn->error);
    }
    
    http_response_code(500);
    exit(1);
} finally {
    // Close the database connection
    if (isset($conn)) {
        $conn->close();
    }
}

exit(0);
