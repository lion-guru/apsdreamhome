<?php
/**
 * Compatibility shim for admin pages expecting admin/includes/db_config.php
 * This file has been refactored to use the centralized ORM system.
 */

require_once __DIR__ . '/../../../../app/core/App.php';

try {
    $db = \App\Core\App::database();
    
    // Expose a mysqli-like connection as $con and $conn for legacy includes
    // Note: These now hold the PDO instance from the ORM
    if (!isset($con)) {
        $con = $db->getConnection();
    }
    if (!isset($conn)) {
        $conn = $con;
    }
    
    // Also expose a PDO connection as $pdo if needed
    if (!isset($pdo)) {
        $pdo = $con;
    }

    // Define legacy constants if not already defined
    if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
    if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');
    if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'apsdreamhome');

} catch (Exception $e) {
    error_log("Database connection failed in admin shim: " . $e->getMessage());
    // In admin context, we might want to be more explicit about failures
    if (defined('IN_ADMIN')) {
        die("Critical Error: Database connection could not be established.");
    }
}
?>
