<?php
require_once 'db_connection.php';

// Perform database connection test
$testResults = testDatabaseConnection();

// Output results
header('Content-Type: application/json');
echo json_encode($testResults, JSON_PRETTY_PRINT);
exit();
