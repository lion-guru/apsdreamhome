<?php

/**
 * Legacy Environment Helper Wrapper
 * 
 * All functions have been moved to app/helpers.php for consolidation.
 * This file remains for backward compatibility with manual includes.
 */

// Ensure core helpers are loaded
$helpersPath = dirname(__DIR__) . '/helpers.php';
if (file_exists($helpersPath)) {
    require_once $helpersPath;
}
