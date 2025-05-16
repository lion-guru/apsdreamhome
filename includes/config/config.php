<?php
// Site Configuration
define('SITE_NAME', 'APS Dream Home');
define('SITE_URL', 'http://localhost/apsdreamhomefinal');
define('SUPPORT_EMAIL', 'support@apsdreamhome.com');
// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Database configuration with secure connection handling
$db_pass = getenv('DB_PASS');
// If password is empty in .env file, use empty string
$con = mysqli_connect('localhost', 'root', $db_pass, 'apsdreamhomefinal');
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set to utf8mb4
$con->set_charset("utf8mb4");

// Error reporting configuration
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set SQL mode for stricter SQL syntax
$con->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");