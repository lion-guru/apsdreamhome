<?php
/**
 * Forgot Password - Updated with Session Management
 */
require_once __DIR__ . "/core/init.php";
$csrf_token = generateCSRFToken();
$success = $error = "";
?>
