<?php
/**
 * APS Dream Home - Centralized Path Configuration
 */

// Base directory path
define('BASE_PATH', dirname(__DIR__));

// Dynamic BASE_URL calculation
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = '/apsdreamhome'; // Fixed path for XAMPP
    define('BASE_URL', $protocol . '://' . $host . $path);
}

// Additional path constants
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');
define('CSS_URL', BASE_URL . '/assets/css');
define('JS_URL', BASE_URL . '/assets/js');
define('IMAGES_URL', BASE_URL . '/assets/images');

// File system paths
define('ASSETS_PATH', BASE_PATH . '/public/assets');
define('UPLOADS_PATH', BASE_PATH . '/public/uploads');
define('VIEWS_PATH', BASE_PATH . '/app/views');
define('CONTROLLERS_PATH', BASE_PATH . '/app/Http/Controllers');
define('MODELS_PATH', BASE_PATH . '/app/Models');
?>
