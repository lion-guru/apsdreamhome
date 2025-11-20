<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/db_config.php';

global $con;
$conn = $con;
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "<h3>users Table Columns:</h3><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($row['Field']) . " (" . htmlspecialchars($row['Type']) . ")</li>";
    }
    echo "</ul>";
} else {
    echo "Could not describe users table.";
}
$conn->close();
?>
