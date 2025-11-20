<?php
/**
 * Configuration Constants Check and Loader
 * Prevents constant redefinition warnings
 */

// Prevent multiple inclusions
if (defined('CONFIG_CONSTANTS_LOADED')) {
    return;
}
define('CONFIG_CONSTANTS_LOADED', true);

// Database configuration - Check before defining
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', ''); // Default XAMPP has no password
}
if (!defined('DB_PASS')) {
    define('DB_PASS', DB_PASSWORD);
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'apsdreamhome');
}

// Contact information constants
if (!defined('CONTACT_PHONE')) {
    define('CONTACT_PHONE', '+91-70074-44842');
}
if (!defined('CONTACT_EMAIL')) {
    define('CONTACT_EMAIL', 'support@apsdreamhome.com');
}
if (!defined('CONTACT_ADDRESS')) {
    define('CONTACT_ADDRESS', 'Gorakhpur, Uttar Pradesh, India');
}

// Base URL configuration
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}
?>
