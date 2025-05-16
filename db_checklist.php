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

// Get all tables
$tables = [];
$res = $conn->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    $tables[] = $row[0];
}

// Print table and column info
header('Content-Type: text/plain; charset=utf-8');
echo "Database: $database\n";
echo str_repeat('=', 40) . "\n";
foreach ($tables as $table) {
    echo "\nTable: $table\n";
    $res2 = $conn->query("SHOW COLUMNS FROM `$table`");
    while ($col = $res2->fetch_assoc()) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
}

// Optionally, print table row counts

echo "\n" . str_repeat('-', 40) . "\nTable Row Counts:\n";
foreach ($tables as $table) {
    $res3 = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
    $cnt = $res3->fetch_assoc()['cnt'];
    echo "$table: $cnt rows\n";
}

$conn->close();
