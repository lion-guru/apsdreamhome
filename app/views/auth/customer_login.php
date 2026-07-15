<?php
require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('BASE_URL', $protocol . '://' . $host . '/apsdreamhome');
}
// Redirect to unified login
header('Location: ' . BASE_URL . '/auth/login');
exit;
