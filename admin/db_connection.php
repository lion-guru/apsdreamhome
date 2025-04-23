<?php
/**
 * Database Connection Handler for Admin Panel
 * This file provides a database connection with improved error handling
 * for the APS Dream Homes admin panel.
 *
 * This file has been updated to use the centralized database configuration
 * to avoid duplication and ensure consistent database connections.
 */

// Include the centralized database configuration
require_once __DIR__ . '/../includes/db_config.php';

// For backward compatibility, make sure $con is available
if (!isset($con) || !$con) {
    $con = getDbConnection();
}

// Log successful connection for debugging
if ($con) {
    error_log('Admin panel database connection established successfully');
}