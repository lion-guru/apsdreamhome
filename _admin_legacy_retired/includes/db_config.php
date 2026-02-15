<?php
// Compatibility shim for admin pages expecting admin/includes/db_config.php
// Reuse the central database connection and helpers.

// Load the global DB connection (defines DB_* constants and getMysqliConnection())
require_once __DIR__ . '/../../includes/db_connection.php';

// Expose a mysqli connection as $con for legacy includes
if (!isset($con) && function_exists('getMysqliConnection')) {
    $con = getMysqliConnection();
}

// Also expose a PDO connection as $pdo if needed
if (!isset($pdo) && function_exists('getPdoConnection')) {
    $pdo = getPdoConnection();
}

?>
