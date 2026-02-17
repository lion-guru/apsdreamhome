<?php
/**
 * Automated Response Management Interface - Updated with Session Management
 * Configure and monitor automated security responses
 */
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . "/../includes/security/auto_response.php";
require_once __DIR__ . "/../includes/middleware/rate_limit_middleware.php";
// Apply rate limiting
$rateLimitMiddleware->handle("admin");
// Initialize auto-response system
$autoResponseSystem = new AutoResponseSystem();
?>
