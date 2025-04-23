



<?php
// Include the main database connection handler
require_once __DIR__ . '/db_connection.php';

// Get database connection
$conn = getDbConnection();
if (!$conn) {
    die("Failed to establish database connection");
}

// For backward compatibility
$con = $conn;
?>




