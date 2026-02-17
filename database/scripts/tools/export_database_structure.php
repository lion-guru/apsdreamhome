<?php
// Script to export complete database structure to a single file

// Include database configuration
require_once '../config/bootstrap.php';
require_once '../config/database.php';

// Set error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers to download as a file
header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="database_structure.sql"');

// Function to get all tables
function getTables($con) {
    $tables = array();
    $result = $con->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
    return $tables;
}

// Function to get create table statement
function getTableCreate($con, $table) {
    $result = $con->query("SHOW CREATE TABLE `$table`");
    $row = $result->fetch_row();
    return $row[1] . ";\n\n";
}

// Function to get table data
function getTableData($con, $table) {
    $result = $con->query("SELECT * FROM `$table` LIMIT 1");
    if ($result->num_rows > 0) {
        return "-- Table `$table` contains data\n";
    }
    return "-- Table `$table` is empty\n";
}

// Function to get triggers
function getTriggers($con, $table) {
    $triggers = "";
    $result = $con->query("SHOW TRIGGERS LIKE '$table'");
    while ($row = $result->fetch_assoc()) {
        $triggers .= "DELIMITER //\n";
        $triggers .= "CREATE TRIGGER `{$row['Trigger']}` {$row['Timing']} {$row['Event']} ON `{$row['Table']}` FOR EACH ROW\n{$row['Statement']}//\n";
        $triggers .= "DELIMITER ;\n\n";
    }
    return $triggers;
}

// Function to get stored procedures and functions
function getRoutines($con) {
    $routines = "";
    
    // Get procedures
    $result = $con->query("SHOW PROCEDURE STATUS WHERE Db = '" . DB_NAME . "'");
    while ($row = $result->fetch_assoc()) {
        $procedureName = $row['Name'];
        $createProc = $con->query("SHOW CREATE PROCEDURE `$procedureName`")->fetch_assoc();
        $routines .= "DELIMITER //\n";
        $routines .= $createProc['Create Procedure'] . "//\n";
        $routines .= "DELIMITER ;\n\n";
    }
    
    // Get functions
    $result = $con->query("SHOW FUNCTION STATUS WHERE Db = '" . DB_NAME . "'");
    while ($row = $result->fetch_assoc()) {
        $functionName = $row['Name'];
        $createFunc = $con->query("SHOW CREATE FUNCTION `$functionName`")->fetch_assoc();
        $routines .= "DELIMITER //\n";
        $routines .= $createFunc['Create Function'] . "//\n";
        $routines .= "DELIMITER ;\n\n";
    }
    
    return $routines;
}

// Function to get views
function getViews($con) {
    $viewNames = [];
    $result = $con->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
    while ($row = $result->fetch_row()) {
        $viewNames[] = $row[0];
    }
    return $viewNames;
}

// Start output
echo "-- Database Structure Export for " . DB_NAME . "\n";
echo "-- Generated on " . date('Y-m-d H:i:s') . "\n\n";

echo "-- --------------------------------------------------------\n";
echo "-- Server version: " . $con->server_info . "\n\n";

echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
echo "START TRANSACTION;\n";
echo "SET time_zone = \"+00:00\";\n\n";

echo "--\n";
echo "-- Database: `" . DB_NAME . "`\n";
echo "--\n\n";

echo "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;\n";
echo "USE `" . DB_NAME . "`;\n\n";

// Get all tables
$tables = getTables($con);

// Get views and store their names
$views_array = getViews($con);
$views_raw = ""; // Initialize views_raw as empty string
foreach ($views_array as $viewName) {
    $createView = $con->query("SHOW CREATE VIEW `$viewName`")->fetch_assoc();
    $views_raw .= $createView['Create View'] . ";\n\n";
}

// First, drop all views
echo "-- --------------------------------------------------------\n";
echo "-- Drop existing views\n";
echo "-- --------------------------------------------------------\n\n";
foreach ($views_array as $view) {
    echo "DROP VIEW IF EXISTS `$view`;\n";
}
echo "\n";

// Then, drop all tables (for clean import)
echo "-- --------------------------------------------------------\n";
echo "-- Drop existing tables\n";
echo "-- --------------------------------------------------------\n\n";

echo "SET FOREIGN_KEY_CHECKS = 0;\n";
foreach ($tables as $table) {
    if (!in_array($table, $views_array)) { // Only drop tables, not views
        echo "DROP TABLE IF EXISTS `$table`;\n";
    }
}
echo "SET FOREIGN_KEY_CHECKS = 1;\n\n";

// Then create tables
echo "-- --------------------------------------------------------\n";
echo "-- Table structure\n";
echo "-- --------------------------------------------------------\n\n";

foreach ($tables as $table) {
    if (in_array($table, $views_array)) { // Skip views when creating table structure and data
        continue;
    }
    echo "-- --------------------------------------------------------\n";
    echo "-- Table structure for table `$table`\n";
    echo "-- --------------------------------------------------------\n\n";
    
    echo getTableCreate($con, $table);
    echo getTableData($con, $table);
    echo "\n";
    
    // Get triggers for this table
    $tableTriggers = getTriggers($con, $table);
    if (!empty($tableTriggers)) {
        echo "-- --------------------------------------------------------\n";
        echo "-- Triggers for table `$table`\n";
        echo "-- --------------------------------------------------------\n\n";
        echo $tableTriggers;
    }
}

// Get views
$views = getViews($con);
if (!empty($views)) {
    echo "-- --------------------------------------------------------\n";
    echo "-- Views\n";
    echo "-- --------------------------------------------------------\n\n";
    echo $views_raw;
}

// Get stored procedures and functions
$routines = getRoutines($con);
if (!empty($routines)) {
    echo "-- --------------------------------------------------------\n";
    echo "-- Stored procedures and functions\n";
    echo "-- --------------------------------------------------------\n\n";
    echo $routines;
}

echo "-- --------------------------------------------------------\n";
echo "-- End of database structure export\n";
echo "-- --------------------------------------------------------\n";

// Close connection
$con->close();