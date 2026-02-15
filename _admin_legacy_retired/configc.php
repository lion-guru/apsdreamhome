



<?php
// Include the main database connection handler
require_once __DIR__ . '/src/Database/Database.php';

// Get database connection
global $con;
$conn = $con;
if (!$conn) {
    die("Failed to establish database connection");
}

// For backward compatibility
$con = $conn;
?>




