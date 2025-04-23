<?php
// Database connection parameters
$host = 'localhost';
$user = 'root'; // Default XAMPP username
$password = ''; // Default XAMPP password is empty
$database = 'realestatephp';

// Connect to the database
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Database Connection Successful</h2>";

// Function to execute SQL file
function executeSqlFile($conn, $filename) {
    echo "<h3>Executing SQL file: $filename</h3>";
    
    // Read the SQL file
    $sql = file_get_contents($filename);
    
    // Remove comments and split into individual queries
    $sql = preg_replace('/--.*?\n|#.*?\n|\/\*.*?\*\//s', '', $sql);
    $queries = preg_split('/;\s*\n/', $sql);
    
    $success = true;
    $count = 0;
    
    // Execute each query
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        // Handle SOURCE command specially
        if (preg_match('/^SOURCE\s+(.+)$/i', $query, $matches)) {
            $sourceFile = dirname(__FILE__) . '/' . trim($matches[1]);
            if (file_exists($sourceFile)) {
                executeSqlFile($conn, $sourceFile);
            } else {
                echo "<p style='color:red'>Error: Source file not found: $sourceFile</p>";
                $success = false;
            }
            continue;
        }
        
        // Execute the query
        if ($conn->query($query)) {
            $count++;
        } else {
            echo "<p style='color:red'>Error executing query: " . $conn->error . "</p>";
            echo "<p>Query: $query</p>";
            $success = false;
        }
    }
    
    if ($success) {
        echo "<p style='color:green'>Successfully executed $count queries from $filename</p>";
    } else {
        echo "<p style='color:orange'>Completed with some errors from $filename</p>";
    }
    
    return $success;
}

// Execute the sample data SQL file
$sqlFile = dirname(__FILE__) . '/insert_sample_data.sql';

if (file_exists($sqlFile)) {
    executeSqlFile($conn, $sqlFile);
    echo "<h2>Sample data insertion process completed</h2>";
} else {
    echo "<p style='color:red'>Error: SQL file not found: $sqlFile</p>";
}

// Close the connection
$conn->close();

echo "<p><a href='../property-detail.php?pid=1'>View Property Detail</a></p>";
?>