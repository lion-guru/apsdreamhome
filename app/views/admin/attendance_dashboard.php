<?php
/**
 * Attendance Dashboard - Updated with Session Management
 */
require_once __DIR__ . '/core/init.php';

if (!hasRole("HR")) { header("location:index.php?error=access_denied"); exit(); }
?>
