<?php
// Script to execute the resell_plots table migration

// Include database connection
require_once('config.php');

// Check if connection is successful
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

echo "<h2>Executing Resell Plots Migration</h2>";

// Read the SQL file
$sql_file = file_get_contents('DATABASE FILE/resell_plots_migration.sql');

// Execute the SQL commands
if ($con->multi_query($sql_file)) {
    echo "<p>Migration executed successfully!</p>";
    echo "<p>The resell_plots table has been created.</p>";
    
    // Clear any remaining results
    while ($con->more_results() && $con->next_result()) {
        // Consume any remaining result sets
        if ($result = $con->store_result()) {
            $result->free();
        }
    }
} else {
    echo "<p>Error executing migration: " . $con->error . "</p>";
}

// Close the connection
$con->close();

echo "<p><a href='admin/resellplot.php'>Go to Resell Plots Management</a></p>";
?>