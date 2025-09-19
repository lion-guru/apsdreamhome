<?php
/**
 * APS Dream Home Dashboard Verification Report
 * 
 * This script checks all dashboard widgets and generates a comprehensive report
 * on the status of demo data in the database.
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

// HTML header
echo "<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home Dashboard Verification Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #2c3e50; }
        h2 { color: #3498db; margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        .section { background-color: #f9f9f9; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .chart { height: 20px; background-color: #3498db; margin-top: 5px; }
    </style>
</head>
<body>
    <h1>APS Dream Home Dashboard Verification Report</h1>
    <p>Generated on: " . date('Y-m-d H:i:s') . "</p>
    <div class='section'>
        <h2>Executive Summary</h2>
        <p>This report verifies the status of all dashboard widgets and their underlying data in the APS Dream Home system.</p>
    </div>";

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<div class='error'>Connection failed: " . $conn->connect_error . "</div></body></html>");
}

// Function to check table count
function checkTableCount($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Function to check if table exists
function tableExists($conn, $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    return ($result && $result->num_rows > 0);
}

// Function to check column in table
function columnExists($conn, $table, $column) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return ($result && $result->num_rows > 0);
}

// Check core dashboard widgets
echo "<div class='section'>
    <h2>Core Dashboard Widgets</h2>
    <table>
        <tr>
            <th>Widget</th>
            <th>Table</th>
            <th>Record Count</th>
            <th>Status</th>
            <th>Data Distribution</th>
        </tr>";

$totalRecords = 0;
$coreWidgets = [
    'Properties' => 'properties',
    'Customers' => 'customers',
    'Bookings' => 'bookings',
    'Inquiries/Leads' => 'leads',
    'Transactions' => 'transactions',
    'Users' => 'users'
];

foreach ($coreWidgets as $widget => $table) {
    $count = checkTableCount($conn, $table);
    $totalRecords += $count;
    $status = $count > 0 ? 'OK' : 'Empty';
    $statusClass = $count > 0 ? 'success' : 'error';
    $percentage = $totalRecords > 0 ? round(($count / $totalRecords) * 100) : 0;
    
    echo "<tr>
        <td>$widget</td>
        <td>$table</td>
        <td>$count</td>
        <td class='$statusClass'>$status</td>
        <td>
            <div class='chart' style='width: $percentage%;'></div>
            $percentage%
        </td>
    </tr>";
}

echo "</table>
</div>";

// Check recent data widgets
echo "<div class='section'>
    <h2>Recent Data Widgets</h2>
    <table>
        <tr>
            <th>Widget</th>
            <th>Table</th>
            <th>Recent Records (Last 30 Days)</th>
            <th>Status</th>
        </tr>";

$recentWidgets = [
    'Recent Bookings' => 'bookings',
    'Recent Transactions' => 'transactions',
    'Recent Inquiries' => 'leads'
];

foreach ($recentWidgets as $widget => $table) {
    // Check if date column exists
    $dateColumn = 'created_at';
    if ($table == 'bookings' && columnExists($conn, $table, 'booking_date')) {
        $dateColumn = 'booking_date';
    } elseif ($table == 'transactions' && columnExists($conn, $table, 'date')) {
        $dateColumn = 'date';
    }
    
    // Check if date column exists in the table
    if (columnExists($conn, $table, $dateColumn)) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table WHERE $dateColumn >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        if ($result) {
            $row = $result->fetch_assoc();
            $count = $row['count'];
        } else {
            $count = 0;
        }
    } else {
        $count = 0;
    }
    
    $status = $count > 0 ? 'OK' : 'No Recent Data';
    $statusClass = $count > 0 ? 'success' : 'warning';
    
    echo "<tr>
        <td>$widget</td>
        <td>$table</td>
        <td>$count</td>
        <td class='$statusClass'>$status</td>
    </tr>";
}

echo "</table>
</div>";

// Check additional widgets
echo "<div class='section'>
    <h2>Additional Widgets</h2>
    <table>
        <tr>
            <th>Widget</th>
            <th>Table</th>
            <th>Exists</th>
            <th>Record Count</th>
            <th>Status</th>
        </tr>";

$additionalWidgets = [
    'Visit Reminders' => ['property_visits', 'visit_reminders'],
    'Notifications' => ['notifications'],
    'MLM Commission' => ['mlm_commissions']
];

foreach ($additionalWidgets as $widget => $tables) {
    $allExist = true;
    $totalCount = 0;
    
    foreach ($tables as $table) {
        $exists = tableExists($conn, $table);
        $count = $exists ? checkTableCount($conn, $table) : 0;
        $totalCount += $count;
        
        if (!$exists) {
            $allExist = false;
        }
    }
    
    $status = $allExist ? ($totalCount > 0 ? 'OK' : 'Empty') : 'Missing Table';
    $statusClass = $allExist ? ($totalCount > 0 ? 'success' : 'warning') : 'error';
    
    echo "<tr>
        <td>$widget</td>
        <td>" . implode(', ', $tables) . "</td>
        <td>" . ($allExist ? 'Yes' : 'No') . "</td>
        <td>$totalCount</td>
        <td class='$statusClass'>$status</td>
    </tr>";
}

echo "</table>
</div>";

// Check for data quality issues
echo "<div class='section'>
    <h2>Data Quality Check</h2>
    <table>
        <tr>
            <th>Check Type</th>
            <th>Status</th>
            <th>Details</th>
        </tr>";

// Check for orphaned records in leads
$orphanedLeads = 0;
if (tableExists($conn, 'leads') && columnExists($conn, 'leads', 'customer_id')) {
    $result = $conn->query("SELECT COUNT(*) as count FROM leads l LEFT JOIN customers c ON l.customer_id = c.id WHERE l.customer_id IS NOT NULL AND c.id IS NULL");
    if ($result) {
        $row = $result->fetch_assoc();
        $orphanedLeads = $row['count'];
    }
}

echo "<tr>
    <td>Orphaned Leads</td>
    <td class='" . ($orphanedLeads > 0 ? 'warning' : 'success') . "'>" . ($orphanedLeads > 0 ? 'Found' : 'None') . "</td>
    <td>" . ($orphanedLeads > 0 ? "$orphanedLeads leads with missing customer references" : "All leads have valid customer references") . "</td>
</tr>";

// Check for orphaned bookings
$orphanedBookings = 0;
if (tableExists($conn, 'bookings') && columnExists($conn, 'bookings', 'property_id')) {
    $result = $conn->query("SELECT COUNT(*) as count FROM bookings b LEFT JOIN properties p ON b.property_id = p.id WHERE b.property_id IS NOT NULL AND p.id IS NULL");
    if ($result) {
        $row = $result->fetch_assoc();
        $orphanedBookings = $row['count'];
    }
}

echo "<tr>
    <td>Orphaned Bookings</td>
    <td class='" . ($orphanedBookings > 0 ? 'warning' : 'success') . "'>" . ($orphanedBookings > 0 ? 'Found' : 'None') . "</td>
    <td>" . ($orphanedBookings > 0 ? "$orphanedBookings bookings with missing property references" : "All bookings have valid property references") . "</td>
</tr>";

echo "</table>
</div>";

// Dashboard widget coverage
echo "<div class='section'>
    <h2>Dashboard Widget Coverage</h2>
    <p>This section shows which dashboard widgets are fully supported by the current data.</p>
    <table>
        <tr>
            <th>Widget</th>
            <th>Coverage</th>
            <th>Status</th>
        </tr>";

$dashboardWidgets = [
    'Properties Count' => checkTableCount($conn, 'properties') > 0,
    'Customers Count' => checkTableCount($conn, 'customers') > 0,
    'Bookings Count' => checkTableCount($conn, 'bookings') > 0,
    'Inquiries Count' => checkTableCount($conn, 'leads') > 0,
    'Recent Bookings' => checkTableCount($conn, 'bookings') > 0,
    'Recent Transactions' => checkTableCount($conn, 'transactions') > 0,
    'Recent Inquiries' => checkTableCount($conn, 'leads') > 0,
    'Visit Reminders' => tableExists($conn, 'property_visits') && checkTableCount($conn, 'property_visits') > 0,
    'Notifications' => tableExists($conn, 'notifications') && checkTableCount($conn, 'notifications') > 0,
    'MLM Commission' => tableExists($conn, 'mlm_commissions') && checkTableCount($conn, 'mlm_commissions') > 0,
    'Leads Converted' => tableExists($conn, 'leads') && columnExists($conn, 'leads', 'status') && columnExists($conn, 'leads', 'converted_at')
];

foreach ($dashboardWidgets as $widget => $supported) {
    echo "<tr>
        <td>$widget</td>
        <td>" . ($supported ? '100%' : '0%') . "</td>
        <td class='" . ($supported ? 'success' : 'error') . "'>" . ($supported ? 'Supported' : 'Not Supported') . "</td>
    </tr>";
}

echo "</table>
</div>";

// Recommendations
echo "<div class='section'>
    <h2>Recommendations</h2>
    <ul>";

$recommendations = [];

// Check for empty tables
foreach ($coreWidgets as $widget => $table) {
    if (checkTableCount($conn, $table) == 0) {
        $recommendations[] = "Add demo data to the <strong>$table</strong> table to support the <strong>$widget</strong> widget.";
    }
}

// Check for missing tables
foreach ($additionalWidgets as $widget => $tables) {
    foreach ($tables as $table) {
        if (!tableExists($conn, $table)) {
            $recommendations[] = "Create the <strong>$table</strong> table to support the <strong>$widget</strong> widget.";
        } elseif (checkTableCount($conn, $table) == 0) {
            $recommendations[] = "Add demo data to the <strong>$table</strong> table to support the <strong>$widget</strong> widget.";
        }
    }
}

// Check for data quality issues
if ($orphanedLeads > 0) {
    $recommendations[] = "Fix orphaned leads by adding missing customer records or updating customer_id references.";
}

if ($orphanedBookings > 0) {
    $recommendations[] = "Fix orphaned bookings by adding missing property records or updating property_id references.";
}

// Display recommendations
if (empty($recommendations)) {
    echo "<li class='success'>All dashboard widgets are fully supported by the current data. No recommendations needed.</li>";
} else {
    foreach ($recommendations as $recommendation) {
        echo "<li>$recommendation</li>";
    }
    
    echo "<li>Run <code>php database/final_dashboard_check.php</code> to automatically fix these issues.</li>";
}

echo "</ul>
</div>";

// Close connection
$conn->close();

// Footer
echo "<div class='section'>
    <h2>Next Steps</h2>
    <p>To ensure all dashboard widgets display properly:</p>
    <ol>
        <li>Address any recommendations listed above</li>
        <li>Refresh your admin dashboard</li>
        <li>Verify that all widgets display data correctly</li>
        <li>For any remaining issues, run the comprehensive data seeding script</li>
    </ol>
    <p><a href='dashboard_data_manager.php'>Go to Dashboard Data Manager</a> to refresh demo data if needed.</p>
</div>
</body>
</html>";
?>
