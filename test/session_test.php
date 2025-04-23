<?php
require_once __DIR__ . '/../includes/init.php';

// Test session security
if (!function_exists('check_session_security')) {
    die('Session security function not found');
}

try {
    if (check_session_security()) {
        echo "Session security check passed\n";
    } else {
        echo "Session security check failed\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>