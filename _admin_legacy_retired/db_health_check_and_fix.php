<?php
// Automated Database Health Check and Repair Script
// Scans all tables, checks for duplicates, table corruption, and fixes common issues
define('IN_ADMIN', true);
require_once __DIR__ . '/../includes/config.php';

function getAllTables($conn) {
    $tables = [];
    $stmt = $conn->prepare("SHOW TABLES");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_array()) {
        $tables[] = $row[0];
    }
    $stmt->close();
    return $tables;
}

function checkAndRepairTable($conn, $table) {
    $report = [];
    // Validate table name to prevent injection
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
        $report[] = "ERROR: Invalid table name format: $table";
        return $report;
    }

    // Check table integrity
    $check = $conn->query("CHECK TABLE `$table`");

    if ($check) {
        while ($row = $check->fetch_assoc()) {
            $report[] = "CHECK: {$row['Msg_type']} - {$row['Msg_text']}";
        }
    }
    // Repair if needed
    $repair = $conn->query("REPAIR TABLE `$table`");
    if ($repair) {
        while ($row = $repair->fetch_assoc()) {
            $report[] = "REPAIR: {$row['Msg_type']} - {$row['Msg_text']}";
        }
    }
    return $report;
}

function checkDuplicates($conn, $table) {
    $report = [];
    // Validate table name
    if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table)) {
        $report[] = "ERROR: Invalid table name format: $table";
        return $report;
    }

    // Try to find primary key
    $stmt = $conn->prepare("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    $stmt->execute();
    $pkResult = $stmt->get_result();
    if ($pkResult && $pkResult->num_rows > 0) {
        // Handle reserved keywords by wrapping column in backticks
        $pk = $pkResult->fetch_assoc();
        $pkCol = $pk['Column_name'];
        $stmt = $conn->prepare("SELECT `$pkCol`, COUNT(*) as cnt FROM `$table` GROUP BY `$pkCol` HAVING cnt > 1");
        $stmt->execute();
        $dupResult = $stmt->get_result();
        if ($dupResult && $dupResult->num_rows > 0) {
            $report[] = "DUPLICATES FOUND in $table on $pkCol:";
            while ($row = $dupResult->fetch_assoc()) {
                $report[] = "  Value: {$row[$pkCol]} appears {$row['cnt']} times";
            }
        }
        $stmt->close();
    }
    $stmt->close();
    return $report;
}

global $con;
$conn = $con;
if (!$conn) {
    die("Database connection failed. Please check your config.");
}

$tables = getAllTables($conn);
$overall = [];
foreach ($tables as $table) {
    $overall[] = "--- $table ---";
    $overall = array_merge($overall, checkAndRepairTable($conn, $table));
    $overall = array_merge($overall, checkDuplicates($conn, $table));
}
$conn->close();

// Output report
header('Content-Type: text/plain; charset=utf-8');
echo "Database Health Check Report\n";
echo str_repeat("=", 40) . "\n";
echo implode("\n", $overall);
echo "\nDone.";
