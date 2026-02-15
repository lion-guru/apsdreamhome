<?php
/**
 * APS Dream Home - Legacy Initialization
 */

namespace App\Services\Legacy;

require_once __DIR__ . '/../../helpers.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db_connection.php';

// Define legacy constants if not defined
if (!defined('BASE_URL')) {
    // Attempt to determine BASE_URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome/');
}

if (!defined('ASSETS_URL')) {
    define('ASSETS_URL', BASE_URL . 'public/assets/');
}
