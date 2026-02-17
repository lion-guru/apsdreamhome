<?php
/**
 * Block Entity - Updated with Session Management
 * API to block an IP or user (admin only, POST)
 */
require_once __DIR__ . '/core/init.php';
require_once(__DIR__ . "/send_sms_twilio.php");

// Email notification config
$admin_email = "admin@apsdreamhomes.com"; // Updated to real admin email
if (!hasRole("admin")) { http_response_code(403); exit("Forbidden"); }
?>
