<?php
/**
 * AI Feedback Analytics - Updated with Session Management
 * Admin Analytics Dashboard for AI Feedback Trends
 */
require_once __DIR__ . '/core/init.php';

if (!hasRole("admin")) { header("location:index.php?error=access_denied"); exit(); }
require_once(__DIR__ . "/send_sms_twilio.php");
?>
