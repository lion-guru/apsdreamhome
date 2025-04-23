<?php
require_once __DIR__ . '/../src/bootstrap.php';

// Clear all session data
session_unset();
session_destroy();

// Redirect to home page
header('Location: /march2025apssite/');
exit;