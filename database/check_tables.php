<?php
/**
 * APS Dream Home - Database Tables Check
 * 
 * This script checks for tables in the database and displays their structure.
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h1>Database Tables Check</h1>";
echo "<pre>";
echo "Connected successfully to database\n\n";

// Check for MLM-related tables
echo "=== MLM-Related Tables ===\n";
$result = $conn->query("SHOW TABLES LIKE '%mlm%'");
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found the following MLM tables:\n";
        while ($row = $result->fetch_row()) {
            echo "- " . $row[0] . "\n";
            
            // Show table structure
            $structure = $conn->query("DESCRIBE " . $row[0]);
            if ($structure && $structure->num_rows > 0) {
                echo "  Structure:\n";
                while ($field = $structure->fetch_assoc()) {
                    echo "    " . $field['Field'] . " - " . $field['Type'] . "\n";
                }
            }
            
            // Show record count
            $count = $conn->query("SELECT COUNT(*) as count FROM " . $row[0]);
            if ($count) {
                $countRow = $count->fetch_assoc();
                echo "  Records: " . $countRow['count'] . "\n\n";
            }
        }
    } else {
        echo "No MLM-related tables found.\n";
    }
}

// Check for associates table
echo "\n=== Associates Table ===\n";
$result = $conn->query("SHOW TABLES LIKE 'associates'");
if ($result && $result->num_rows > 0) {
    echo "Associates table exists.\n";
    
    // Show record count
    $count = $conn->query("SELECT COUNT(*) as count FROM associates");
    if ($count) {
        $countRow = $count->fetch_assoc();
        echo "Records: " . $countRow['count'] . "\n";
    }
    
    // Check for null names
    $nullNames = $conn->query("SELECT COUNT(*) as count FROM associates WHERE name IS NULL OR name = ''");
    if ($nullNames) {
        $nullRow = $nullNames->fetch_assoc();
        echo "Associates with null/empty names: " . $nullRow['count'] . "\n";
    }
} else {
    echo "Associates table does not exist.\n";
}

// Close connection
$conn->close();
echo "</pre>";
echo "<p><a href='index.php' class='btn' style='display: inline-block; background-color: #3498db; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none;'>Return to Database Management Hub</a></p>";
?>
