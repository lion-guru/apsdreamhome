<?php
// APS Dream Homes — Database Table/Column Checklist
// This script connects to the current MySQL database and lists all tables and their columns.
// It helps you verify what is present in the new DB vs. what your code expects.

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'apsdreamhomefinal'; // Update if your DB name is different

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Get all tables using prepared statement
$tables = [];
$res = $conn->prepare("SHOW TABLES");
$res->execute();
$table_result = $res->get_result();
while ($row = $table_result->fetch_array()) {
    $tables[] = $row[0];
}
$res->close();

// Print table and column info
header('Content-Type: text/plain; charset=utf-8');
echo "Database: $database\n";
echo str_repeat('=', 40) . "\n";
foreach ($tables as $table) {
    echo "\nTable: $table\n";
    $res2 = $conn->prepare("SHOW COLUMNS FROM `$table`");
    $res2->execute();
    $column_result = $res2->get_result();
    while ($col = $column_result->fetch_assoc()) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
    $res2->close();
}

// Optionally, print table row counts

echo "\n" . str_repeat('-', 40) . "\nTable Row Counts:\n";
foreach ($tables as $table) {
    $res3 = $conn->prepare("SELECT COUNT(*) as cnt FROM `$table`");
    $res3->execute();
    $count_result = $res3->get_result();
    $cnt = $count_result->fetch_assoc()['cnt'];
    $res3->close();
    echo "$table: $cnt rows\n";
    echo "$table: $cnt rows\n";
}

$conn->close();
