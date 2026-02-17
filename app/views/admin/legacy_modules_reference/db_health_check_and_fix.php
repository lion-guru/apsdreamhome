<?php
// Automated Database Health Check and Repair Script
// Scans all tables, checks for duplicates, table corruption, and fixes common issues
define('IN_ADMIN', true);
require_once __DIR__ . '/core/init.php';

function getAllTables($db) {
    $tables = [];
    $result = $db->query("SHOW TABLES");
    while ($row = $result->fetch(\PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    return $tables;
}

function checkAndRepairTable($db, $table) {
    $report = [];
    // Check table integrity
    $result = $db->query("CHECK TABLE `$table` text");
    if ($result) {
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $report[] = "CHECK: {$row['Msg_type']} - {$row['Msg_text']}";
        }
    }
    // Repair if needed
    $result = $db->query("REPAIR TABLE `$table` text");
    if ($result) {
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            $report[] = "REPAIR: {$row['Msg_type']} - {$row['Msg_text']}";
        }
    }
    return $report;
}

function checkDuplicates($db, $table) {
    $report = [];
    // Try to find primary key
    $pkResult = $db->query("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    $pk = $pkResult ? $pkResult->fetch(\PDO::FETCH_ASSOC) : null;
    if ($pk) {
        // Handle reserved keywords by wrapping column in backticks
        $pkCol = $pk['Column_name'];
        $dupResult = $db->query("SELECT `" . $pkCol . "`, COUNT(*) as cnt FROM `$table` GROUP BY `" . $pkCol . "` HAVING cnt > 1");
        if ($dupResult) {
            $duplicates = $dupResult->fetchAll(\PDO::FETCH_ASSOC);
            if (count($duplicates) > 0) {
                $report[] = "DUPLICATES FOUND in $table on $pkCol:";
                foreach ($duplicates as $row) {
                    $report[] = "  Value: {$row[$pkCol]} appears {$row['cnt']} times";
                }
            }
        }
    }
    return $report;
}

$db = \App\Core\App::database();
if (!$db) {
    die("Database connection failed. Please check your config.");
}

$tables = getAllTables($db);
$overall = [];
foreach ($tables as $table) {
    $overall[] = "--- $table ---";
    $overall = array_merge($overall, checkAndRepairTable($db, $table));
    $overall = array_merge($overall, checkDuplicates($db, $table));
}

// Output report
header('Content-Type: text/plain; charset=utf-8');
echo "Database Health Check Report\n";
echo str_repeat("=", 40) . "\n";
echo implode("\n", $overall);
echo "\nDone.";
