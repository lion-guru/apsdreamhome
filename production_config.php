<?php
/**
 * Production Configuration for APS Dream Home
 * Security and performance settings for live environment
 */

// Production error handling
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// Enhanced logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/production_errors.log');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

// Performance optimizations
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 30);

// Database optimizations
ini_set('mysql.connect_timeout', 10);
ini_set('default_socket_timeout', 10);
?>
