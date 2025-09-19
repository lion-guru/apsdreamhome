<?php
/**
 * APS Dream Home - Leads Table Check
 * 
 * This script checks the structure and data of the leads table.
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

echo "<h1>Leads Table Check</h1>";
echo "<pre>";
echo "Connected successfully to database\n\n";

// Check leads table structure
echo "=== Leads Table Structure ===\n";
$result = $conn->query("DESCRIBE leads");
if ($result) {
    if ($result->num_rows > 0) {
        echo "Columns in leads table:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    } else {
        echo "No columns found in leads table.\n";
    }
} else {
    echo "Error describing leads table: " . $conn->error . "\n";
}

// Check for converted leads
echo "\n=== Converted Leads ===\n";
$result = $conn->query("SELECT id, status, converted_at, converted_amount FROM leads WHERE status = 'closed_won'");
if ($result) {
    if ($result->num_rows > 0) {
        echo "Found " . $result->num_rows . " converted leads:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- Lead ID: " . $row['id'] . "\n";
            echo "  Status: " . $row['status'] . "\n";
            echo "  Converted at: " . ($row['converted_at'] ?? 'NULL') . "\n";
            echo "  Amount: ₹" . number_format($row['converted_amount'] ?? 0, 2) . "\n\n";
        }
    } else {
        echo "No converted leads found.\n";
    }
} else {
    echo "Error querying converted leads: " . $conn->error . "\n";
}

// Check for any missing data in leads
echo "\n=== Leads Data Quality Check ===\n";

// Get the actual columns in the leads table
$columns = [];
$result = $conn->query("DESCRIBE leads");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }
}

// Check for missing names
if (in_array('name', $columns)) {
    $result = $conn->query("SELECT COUNT(*) as count FROM leads WHERE name IS NULL OR name = ''");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Leads with missing names: " . $row['count'] . "\n";
    }
}

// Check for missing contact information
if (in_array('contact', $columns)) {
    $result = $conn->query("SELECT COUNT(*) as count FROM leads WHERE contact IS NULL OR contact = ''");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Leads with missing contact info: " . $row['count'] . "\n";
    }
}

// Check for missing source
if (in_array('source', $columns)) {
    $result = $conn->query("SELECT COUNT(*) as count FROM leads WHERE source IS NULL OR source = ''");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Leads with missing source: " . $row['count'] . "\n";
    }
}

// Close connection
$conn->close();
echo "</pre>";
echo "<p><a href='index.php' class='btn' style='display: inline-block; background-color: #3498db; color: white; padding: 10px 15px; border-radius: 4px; text-decoration: none;'>Return to Database Management Hub</a></p>";
?>
