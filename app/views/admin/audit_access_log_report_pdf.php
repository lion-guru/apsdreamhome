<?php
/**
 * Audit Access Log Report PDF - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';

if (!hasRole("superadmin")) { http_response_code(403); exit("Access denied."); }
?>
