<?php
/**
 * APS Dream Home - Public Pages Initialization
 * Standardizes database, session, and security for all public view pages.
 *
 * This file replaces the fragmented includes in individual page files.
 */

// 1. Include the root bootstrap for core system initialization
require_once __DIR__ . '/../../../bootstrap.php';

// 2. Ensure session is started securely and helpers are loaded
require_once __DIR__ . '/../../../includes/session_helpers.php';
ensureSessionStarted();

// 3. Initialize Database and provide legacy compatibility variables
try {
    $db = \App\Core\App::database();
    $conn = $db; // Use our enhanced database wrapper
    $con = $db;  // Legacy alias
} catch (Exception $e) {
    error_log("Public Page Initialization Error: " . $e->getMessage());
    if (defined('APP_DEBUG') && APP_DEBUG) {
        die("System Initialization Error: " . $e->getMessage());
    } else {
        die("A system error occurred. Please try again later.");
    }
}

// 4. Define common constants if not already defined
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . $domainName . '/apsdreamhome/');
}

// 5. Global Helpers for Pages
if (!function_exists('get_page_title')) {
    function get_page_title($title = '') {
        $base = 'APS Dream Home';
        return $title ? $title . ' | ' . $base : $base;
    }
}
