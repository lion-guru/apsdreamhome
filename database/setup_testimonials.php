<?php
/**
 * Setup script for testimonials database
 * 
 * This script will create the testimonials table and populate it with sample data.
 * It's safe to run multiple times as it uses CREATE TABLE IF NOT EXISTS.
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
    
    // Check if testimonials table exists
    $tableExists = $conn->query("SHOW TABLES LIKE 'testimonials'");
    
    if ($tableExists->num_rows === 0) {
        // Table doesn't exist, create it
        output("Creating testimonials table...");
        $createTableSql = file_get_contents(__DIR__ . '/create_testimonials_table.sql');
        
        if ($conn->multi_query($createTableSql)) {
            // Clear any remaining results
            while ($conn->more_results() && $conn->next_result()) {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            }
            output("Testimonials table created successfully.");
        } else {
            throw new Exception("Error creating testimonials table: " . $conn->error);
        }
    } else {
        output("Testimonials table already exists.");
    }
    
    // Check if we already have testimonials
    $result = $conn->query("SELECT COUNT(*) as count FROM `testimonials`");
    $count = $result ? $result->fetch_assoc()['count'] : 0;
    
    if ($count == 0) {
        // No testimonials found, insert sample data
        output("Inserting sample testimonials...");
        $insertSql = file_get_contents(__DIR__ . '/seed_testimonials.sql');
        
        if ($conn->multi_query($insertSql)) {
            // Clear any remaining results
            while ($conn->more_results() && $conn->next_result()) {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            }
            output("Sample testimonials inserted successfully.");
        } else {
            throw new Exception("Error inserting sample testimonials: " . $conn->error);
        }
    } else {
        output("Found $count testimonials in the database.");
    }
    
    // Show current testimonials
    output("\nCurrent Testimonials:");
    output(str_repeat("-", 80));
    
    $result = $conn->query("SELECT `name`, `rating`, `status`, `created_at` FROM `testimonials` ORDER BY `created_at` DESC");
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            output(sprintf(
                "%s - %d/5 (%s) - %s",
                $row['name'],
                $row['rating'],
                $row['status'],
                $row['created_at']
            ));
        }
    } else {
        output("No testimonials found in the database.");
    }
    
    output("\nSetup completed successfully!");
    
} catch (Exception $e) {
    output("\nError: " . $e->getMessage());
    output("File: " . $e->getFile() . " (Line: " . $e->getLine() . ")");
    output("Trace: " . $e->getTraceAsString());
    
    if (isset($conn) && $conn->error) {
        output("MySQL Error: " . $conn->error);
    }
    
    http_response_code(500);
    exit(1);
}

// Close the database connection
if (isset($conn)) {
    $conn->close();
}

exit(0);
