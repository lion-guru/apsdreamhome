<?php
/**
 * APS Dream Home Demo Data Date Refresher
 * 
 * This script updates all date-based demo data to keep it current and relevant.
 * It shifts dates forward to ensure that:
 * - Recent transactions remain recent
 * - Upcoming visits remain in the future
 * - Historical data maintains proper time distribution
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

// HTML header
echo "<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home Demo Data Date Refresher</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #2c3e50; }
        h2 { color: #3498db; margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        .section { background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>APS Dream Home Demo Data Date Refresher</h1>
    <p>This tool updates date-based demo data to keep it current and relevant.</p>";

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhome";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo "<div class='section error'>
        <h2>Error</h2>
        <p>Connection failed: " . $conn->connect_error . "</p>
    </div>";
    exit;
}

// Check if refresh action is requested
$refresh = isset($_GET['refresh']) && $_GET['refresh'] == '1';

// Display current date information
echo "<div class='section'>
    <h2>Current Date Information</h2>
    <p>Server Date: " . date('Y-m-d') . "</p>
    <p>Server Time: " . date('H:i:s') . "</p>
</div>";

// Function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return ($result && $result->num_rows > 0);
}

// Function to check if a table exists
function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return ($result && $result->num_rows > 0);
}

// Tables and date columns to check
$dateTables = [
    'properties' => ['created_at', 'updated_at', 'listing_date', 'available_from'],
    'customers' => ['created_at', 'updated_at', 'registration_date'],
    'leads' => ['created_at', 'updated_at', 'converted_at', 'follow_up_date'],
    'bookings' => ['created_at', 'updated_at', 'booking_date', 'check_in_date', 'check_out_date'],
    'transactions' => ['created_at', 'updated_at', 'date', 'payment_date'],
    'property_visits' => ['created_at', 'updated_at', 'visit_date'],
    'visit_reminders' => ['created_at', 'updated_at', 'reminder_date'],
    'notifications' => ['created_at', 'updated_at'],
    'mlm_commissions' => ['created_at', 'updated_at', 'payment_date']
];

// Display date fields in tables
echo "<div class='section'>
    <h2>Date Fields in Database</h2>
    <table>
        <tr>
            <th>Table</th>
            <th>Date Column</th>
            <th>Oldest Date</th>
            <th>Newest Date</th>
            <th>Records</th>
        </tr>";

$updateNeeded = false;

foreach ($dateTables as $table => $columns) {
    if (tableExists($conn, $table)) {
        foreach ($columns as $column) {
            if (columnExists($conn, $table, $column)) {
                // Get date range
                $result = $conn->query("SELECT 
                    MIN(`$column`) as oldest,
                    MAX(`$column`) as newest,
                    COUNT(*) as count
                FROM `$table` 
                WHERE `$column` IS NOT NULL");
                
                if ($result && $row = $result->fetch_assoc()) {
                    $oldest = $row['oldest'] ? $row['oldest'] : 'N/A';
                    $newest = $row['newest'] ? $row['newest'] : 'N/A';
                    $count = $row['count'];
                    
                    // Check if dates are old (more than 30 days in the past)
                    $isOld = false;
                    if ($newest != 'N/A') {
                        $newestDate = new DateTime($newest);
                        $today = new DateTime();
                        $diff = $today->diff($newestDate);
                        
                        if ($diff->days > 30 && $diff->invert == 1) {
                            $isOld = true;
                            $updateNeeded = true;
                        }
                    }
                    
                    echo "<tr>
                        <td>$table</td>
                        <td>$column</td>
                        <td>$oldest</td>
                        <td" . ($isOld ? " class='warning'" : "") . ">$newest" . ($isOld ? " (outdated)" : "") . "</td>
                        <td>$count</td>
                    </tr>";
                }
            }
        }
    }
}

echo "</table>
</div>";

// Process refresh if requested
if ($refresh) {
    echo "<div class='section'>
        <h2>Refreshing Date Data</h2>
        <table>
            <tr>
                <th>Table</th>
                <th>Column</th>
                <th>Action</th>
                <th>Records Updated</th>
            </tr>";
    
    foreach ($dateTables as $table => $columns) {
        if (tableExists($conn, $table)) {
            foreach ($columns as $column) {
                if (columnExists($conn, $table, $column)) {
                    // Determine how to update based on column name
                    $updateType = '';
                    $updateSql = '';
                    
                    if (strpos($column, 'created_at') !== false) {
                        // Shift created_at dates forward, keeping relative time differences
                        $updateType = 'Shift forward';
                        $updateSql = "UPDATE `$table` 
                            SET `$column` = DATE_ADD(NOW(), INTERVAL DATEDIFF(`$column`, 
                                (SELECT MIN(`$column`) FROM (SELECT `$column` FROM `$table` WHERE `$column` IS NOT NULL) as temp)) DAY)
                            WHERE `$column` IS NOT NULL";
                    } 
                    elseif (strpos($column, 'updated_at') !== false) {
                        // Set updated_at to recent dates
                        $updateType = 'Set to recent';
                        $updateSql = "UPDATE `$table` 
                            SET `$column` = DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 14) DAY)
                            WHERE `$column` IS NOT NULL";
                    }
                    elseif (strpos($column, 'visit_date') !== false || strpos($column, 'reminder_date') !== false) {
                        // Set visit dates to future dates
                        $updateType = 'Set to future';
                        $updateSql = "UPDATE `$table` 
                            SET `$column` = DATE_ADD(NOW(), INTERVAL FLOOR(RAND() * 30) DAY)
                            WHERE `$column` IS NOT NULL";
                    }
                    elseif (strpos($column, 'booking_date') !== false) {
                        // Distribute booking dates over last 90 days
                        $updateType = 'Distribute recent';
                        $updateSql = "UPDATE `$table` 
                            SET `$column` = DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 90) DAY)
                            WHERE `$column` IS NOT NULL";
                    }
                    elseif (strpos($column, 'date') !== false) {
                        // General date fields - distribute over last 180 days
                        $updateType = 'Distribute';
                        $updateSql = "UPDATE `$table` 
                            SET `$column` = DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 180) DAY)
                            WHERE `$column` IS NOT NULL";
                    }
                    
                    // Execute update if SQL was defined
                    if ($updateSql) {
                        $conn->query($updateSql);
                        $rowsUpdated = $conn->affected_rows;
                        
                        echo "<tr>
                            <td>$table</td>
                            <td>$column</td>
                            <td>$updateType</td>
                            <td>$rowsUpdated</td>
                        </tr>";
                    }
                }
            }
        }
    }
    
    echo "</table>
    </div>";
    
    // Special handling for specific business logic
    echo "<div class='section'>
        <h2>Refreshing Business Logic</h2>";
    
    // Update leads conversion dates
    if (tableExists($conn, 'leads') && columnExists($conn, 'leads', 'status') && columnExists($conn, 'leads', 'converted_at')) {
        $conn->query("UPDATE `leads` 
            SET `converted_at` = DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 60) DAY)
            WHERE `status` = 'closed_won' OR `status` = 'converted'");
        
        echo "<p>Updated conversion dates for converted leads</p>";
    }
    
    // Update property visit statuses based on date
    if (tableExists($conn, 'property_visits') && columnExists($conn, 'property_visits', 'visit_date') && columnExists($conn, 'property_visits', 'status')) {
        $conn->query("UPDATE `property_visits` 
            SET `status` = CASE 
                WHEN `visit_date` < CURDATE() THEN 'completed'
                WHEN `visit_date` = CURDATE() THEN 'in_progress'
                ELSE 'scheduled'
            END");
        
        echo "<p>Updated property visit statuses based on dates</p>";
    }
    
    // Update reminder statuses based on date
    if (tableExists($conn, 'visit_reminders') && columnExists($conn, 'visit_reminders', 'reminder_date') && columnExists($conn, 'visit_reminders', 'status')) {
        $conn->query("UPDATE `visit_reminders` 
            SET `status` = CASE 
                WHEN `reminder_date` < CURDATE() THEN 'sent'
                ELSE 'pending'
            END");
        
        echo "<p>Updated reminder statuses based on dates</p>";
    }
    
    echo "</div>";
    
    // Show success message
    echo "<div class='section success'>
        <h2>Date Refresh Complete</h2>
        <p>All date-based demo data has been updated to reflect current dates.</p>
        <a href='refresh_demo_dates.php' class='btn'>View Updated Dates</a>
    </div>";
} else {
    // Show refresh button if needed
    echo "<div class='section" . ($updateNeeded ? " warning" : "") . "'>
        <h2>Date Status</h2>";
    
    if ($updateNeeded) {
        echo "<p>Some dates in your demo data are outdated and should be refreshed.</p>
        <p>Refreshing dates will:</p>
        <ul>
            <li>Update created_at and updated_at timestamps</li>
            <li>Move upcoming visits to future dates</li>
            <li>Distribute historical data appropriately</li>
            <li>Ensure dashboard widgets show relevant time-based data</li>
        </ul>
        <a href='refresh_demo_dates.php?refresh=1' class='btn'>Refresh All Dates</a>";
    } else {
        echo "<p class='success'>All dates in your demo data are current. No refresh needed at this time.</p>";
    }
    
    echo "</div>";
}

// Navigation
echo "<div class='section'>
    <a href='index.php' class='btn'>Return to Database Management Hub</a>
</div>
</body>
</html>";

// Close connection
$conn->close();
?>
