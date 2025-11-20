<?php
/**
 * Database Connection Handler for Admin Panel
 * This file provides a database connection with improved error handling
 * for the APS Dream Homes admin panel.
 *
 * This file has been updated to use the centralized database configuration
 * to avoid duplication and ensure consistent database connections.
 */

// Include the centralized database connection
require_once __DIR__ . '/../includes/db_connection.php';

// For backward compatibility, make sure $con is available
try {
    global $con;
    // Log successful connection for debugging
    error_log('Admin panel database connection established successfully');
} catch (Exception $e) {
    // Log detailed connection error
    error_log('Admin panel database connection failed: ' . $e->getMessage());
    
    // Handle connection failure (you might want to display a user-friendly error or redirect)
    die('Database connection error. Please contact support.');
}