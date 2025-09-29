<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/db_config.php';

$conn = getDbConnection();
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// List all tables in the current database
echo "<h3>Database Tables:</h3>";
$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "<ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . htmlspecialchars($row[0]) . "</li>";
    }
    echo "</ul>";
} else {
    echo "Could not retrieve tables.";
}

$conn->close();
?>
