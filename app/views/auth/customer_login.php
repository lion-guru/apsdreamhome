<?php
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = preg_replace('#/public$#', '', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    define('BASE_URL', $protocol . '://' . $host . $basePath);
}
// Redirect to unified login
header('Location: ' . BASE_URL . '/auth/login');
exit;
