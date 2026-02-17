<?php
/**
 * Database Connection Wrapper for Legacy Code
 * Bridges legacy getMysqliConnection() calls to the modern App::database() singleton.
 */

require_once __DIR__ . '/../app/core/autoload.php';

use App\Core\App;
use App\Core\Database;

if (!function_exists('getMysqliConnection')) {
    function getMysqliConnection() {
        try {
            // Use the modern database singleton
            return App::database()->getConnection();
        } catch (Exception $e) {
            error_log("Legacy DB Connection Error: " . $e->getMessage());
            // Fallback or rethrow depending on needs
            throw $e;
        }
    }
}

// Ensure the App is initialized if not already
if (!defined('APP_INIT')) {
    // Basic initialization if needed
    // App::getInstance(); 
    define('APP_INIT', true);
}
