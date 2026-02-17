<?php
/**
 * Upload Document - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';

$csrf_token = generateCSRFToken();
$msg = "";
$msg_type = "";
?>
