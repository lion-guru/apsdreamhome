<?php
/**
 * APS Dream Home Dashboard Data Manager
 * 
 * This script provides a simple interface to check and refresh demo data
 * for the APS Dream Home admin dashboard.
 */

// Check if running from command line or browser
$isCli = (php_sapi_name() === 'cli');

// Function to output messages based on environment
function output($message, $isError = false) {
    global $isCli;
    if ($isCli) {
        echo ($isError ? "ERROR: " : "") . $message . PHP_EOL;
    } else {
        echo ($isError ? "<div style='color: red; margin: 5px 0;'><strong>ERROR:</strong> " : "<div style='margin: 5px 0;'>") . $message . "</div>";
    }
}

// HTML header for browser view
if (!$isCli) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>APS Dream Home Dashboard Data Manager</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
            h1 { color: #2c3e50; }
            h2 { color: #3498db; margin-top: 20px; }
            table { border-collapse: collapse; width: 100%; margin: 15px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .success { color: green; }
            .warning { color: orange; }
            .error { color: red; }
            .button { display: inline-block; padding: 10px 15px; background-color: #3498db; color: white; 
                    text-decoration: none; border-radius: 4px; margin: 10px 0; }
            .button:hover { background-color: #2980b9; }
        </style>
    </head>
    <body>
        <h1>APS Dream Home Dashboard Data Manager</h1>";
}

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhome";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    output("Connection failed: " . $conn->connect_error, true);
    if (!$isCli) echo "</body></html>";
    exit;
}

output("Connected successfully to database");

// Function to check table count
function checkTableCount($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Check if we should refresh data
$refresh = isset($_GET['refresh']) || (isset($argv) && in_array('--refresh', $argv));

// Display dashboard data status
output("<h2>Dashboard Data Status</h2>");

// Core tables to check
$coreTables = [
    'users' => 'Users',
    'properties' => 'Properties',
    'customers' => 'Customers',
    'leads' => 'Leads/Inquiries',
    'bookings' => 'Bookings',
    'transactions' => 'Transactions'
];

// Output table with counts
if (!$isCli) {
    echo "<table>
        <tr>
            <th>Widget</th>
            <th>Table</th>
            <th>Record Count</th>
            <th>Status</th>
        </tr>";
}

foreach ($coreTables as $table => $widget) {
    $count = checkTableCount($conn, $table);
    $status = $count > 0 ? 'OK' : 'Empty';
    $statusClass = $count > 0 ? 'success' : 'error';
    
    if ($isCli) {
        output("$widget ($table): $count records - Status: $status");
    } else {
        echo "<tr>
            <td>$widget</td>
            <td>$table</td>
            <td>$count</td>
            <td class='$statusClass'>$status</td>
        </tr>";
    }
    
    // Refresh data if requested and table is empty
    if ($refresh && $count < 5) {
        output("Adding demo data to $table...");
        
        // Add minimal data based on table
        switch ($table) {
            case 'users':
                $conn->query("INSERT IGNORE INTO users (name, email, password, phone, type, status) VALUES
                    ('Admin User', 'admin@apsdreamhome.com', '\$2y\$10\$abcdefghijklmnopqrstuuWzAC6OdQrAUOL1CjRrYP5g/jVrFvXe', '9000000001', 'admin', 'active')");
                break;
                
            case 'properties':
                $conn->query("INSERT IGNORE INTO properties (id) VALUES (1), (2), (3), (4), (5)");
                break;
                
            case 'customers':
                $conn->query("INSERT IGNORE INTO customers (id) VALUES (1), (2), (3), (4), (5)");
                break;
                
            case 'leads':
                $conn->query("INSERT IGNORE INTO leads (id) VALUES (1), (2), (3), (4), (5)");
                break;
                
            case 'bookings':
                $conn->query("INSERT IGNORE INTO bookings (id) VALUES (1), (2), (3), (4), (5)");
                break;
                
            case 'transactions':
                $conn->query("INSERT IGNORE INTO transactions (id) VALUES (1), (2), (3), (4), (5)");
                break;
        }
        
        $newCount = checkTableCount($conn, $table);
        output("Updated $table: $newCount records");
    }
}

if (!$isCli) {
    echo "</table>";
}

// Check additional tables
$additionalTables = [
    'property_visits' => 'Visit Reminders',
    'notifications' => 'Notifications',
    'mlm_commissions' => 'MLM Commissions'
];

output("<h2>Additional Widgets Status</h2>");

if (!$isCli) {
    echo "<table>
        <tr>
            <th>Widget</th>
            <th>Table</th>
            <th>Exists</th>
            <th>Record Count</th>
            <th>Status</th>
        </tr>";
}

foreach ($additionalTables as $table => $widget) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $exists = ($result && $result->num_rows > 0);
    $count = $exists ? checkTableCount($conn, $table) : 0;
    $status = $count > 0 ? 'OK' : ($exists ? 'Empty' : 'Missing');
    $statusClass = $count > 0 ? 'success' : ($exists ? 'warning' : 'error');
    
    if ($isCli) {
        output("$widget ($table): " . ($exists ? "Exists" : "Missing") . ", $count records - Status: $status");
    } else {
        echo "<tr>
            <td>$widget</td>
            <td>$table</td>
            <td>" . ($exists ? "Yes" : "No") . "</td>
            <td>$count</td>
            <td class='$statusClass'>$status</td>
        </tr>";
    }
    
    // Refresh data if requested and table exists but is empty
    if ($refresh && $exists && $count < 5) {
        output("Adding demo data to $table...");
        
        // Add minimal data based on table
        switch ($table) {
            case 'property_visits':
                $conn->query("INSERT IGNORE INTO property_visits (id) VALUES (1), (2), (3), (4), (5)");
                break;
                
            case 'notifications':
                $conn->query("INSERT IGNORE INTO notifications (id) VALUES (1), (2), (3), (4), (5)");
                break;
                
            case 'mlm_commissions':
                $conn->query("INSERT IGNORE INTO mlm_commissions (id) VALUES (1), (2), (3), (4), (5)");
                break;
        }
        
        $newCount = checkTableCount($conn, $table);
        output("Updated $table: $newCount records");
    }
}

if (!$isCli) {
    echo "</table>";
}

// Show refresh button or instructions
if (!$isCli) {
    echo "<h2>Actions</h2>
        <p>Click the button below to refresh demo data for empty tables:</p>
        <a href='?refresh=1' class='button'>Refresh Demo Data</a>
        
        <h2>Advanced Options</h2>
        <p>For more comprehensive data seeding, run one of these scripts from the command line:</p>
        <pre>php database/complete_database_seed.php</pre>
        <pre>php database/structure_based_seed.php</pre>
        <pre>php database/final_dashboard_check.php</pre>";
}

// Close connection
$conn->close();

if (!$isCli) {
    echo "</body></html>";
}
?>
