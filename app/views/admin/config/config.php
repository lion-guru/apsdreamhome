<?php
/**
 * Database Configuration File
 * Establishes PDO connection for AI tools
 */

require_once __DIR__ . '/../core/init.php';

// Establish connection using centralized config
try {
    $db = \App\Core\App::database();
} catch (Exception $e) {
    if (function_exists('handleDatabaseError')) {
        handleDatabaseError($e);
    } else {
        error_log("Database connection failed: " . $e->getMessage());
        die("Database connection failed. Please check the configuration.");
    }
}
