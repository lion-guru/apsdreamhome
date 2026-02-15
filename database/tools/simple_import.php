<?php
// Simple Database Import Script
echo "<h1>🔄 Importing Database...</h1>";

// Database config
$db = new mysqli('localhost', 'root', '', 'apsdreamhome');
if ($db->connect_error) die("Connection failed: " . $db->connect_error);

// Recreate database
$db->query("DROP DATABASE IF EXISTS apsdreamhome");
$db->query("CREATE DATABASE apsdreamhome CHARACTER SET utf8mb4");
$db->select_db('apsdreamhome');

// Import SQL file
$sql = file_get_contents('database/apsdreamhomes.sql');
$queries = array_filter(explode(';', $sql), 'strlen');

echo "<p>Executing " . count($queries) . " queries...</p><pre>";

foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        if ($db->query($query)) {
            echo ".";
        } else {
            echo "<span style='color:red'>E</span>";
        }
        flush();
    }
}

// Show results
$tables = $db->query("SHOW TABLES")->num_rows;
echo "</pre><p>✅ Import complete! $tables tables created.</p>";
?>
