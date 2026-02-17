<?php
/**
 * Integration Settings Redirect
 * This file is now redundant and redirects to the modern Integration Dashboard.
 */
require_once __DIR__ . '/core/init.php';

// Redirect to the modern integration dashboard
header('Location: integration_dashboard.php');
exit();

