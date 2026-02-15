<?php
/**
 * Compliance Dashboard - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';

if (!hasRole("SuperAdmin")) { http_response_code(403); exit("Access denied."); }
?>
