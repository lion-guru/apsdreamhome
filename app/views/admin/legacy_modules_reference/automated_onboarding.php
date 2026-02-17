<?php
/**
 * Automated Onboarding - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';

if (!hasRole("Admin")) { header("location:index.php?error=access_denied"); exit(); }
?>
