<?php
// Email Configuration File
// This file contains sensitive email credentials - ensure proper file permissions

$email_config = [
    'host' => 'smtp.gmail.com',
    'username' => 'your-email@gmail.com', // Replace with your actual email
    'password' => 'your-app-password',   // Replace with your app-specific password
    'port' => 587,
    'secure' => 'tls'
];

// Security check - prevent direct access
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is not allowed.');
}
