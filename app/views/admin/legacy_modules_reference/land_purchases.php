<?php
/**
 * Land Purchase Management - Updated with Unified Initialization
 */
require_once __DIR__ . '/core/init.php';

if (!hasRole("Admin") && !hasRole("SuperAdmin")) { 
    header("location:index.php?error=access_denied"); 
    exit(); 
}

$db = \App\Core\App::database();
?>
