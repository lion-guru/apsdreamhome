<?php
require_once __DIR__.'/includes/admin_header.php';
require_once __DIR__.'/includes/session_manager.php';
require_once __DIR__.'/../includes/csrf_protection.php';

// Initialize and validate session
initAdminSession();
if (!validateAdminSession()) {
    header('Location: login.php');
    exit();
}

// Verify CSRF token for POST requests
verifyCSRFToken();

// Your page content starts here