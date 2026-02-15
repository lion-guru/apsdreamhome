<?php
// Associate Dashboard - Updated with Session Management
require_once __DIR__ . "/core/init.php";

// Authentication check
if (!isAuthenticated()) {
    header("Location: login.php");
    exit();
}

// include(__DIR__ . '/includes/core/functions.php'); // Check if this exists
error_reporting(E_ERROR | E_PARSE);

$user = getAuthRole();
$associate_id = getAuthUserId();
$msg = '';

// Check if the user is logged in
if ($user != 'assosiate') {
    // Debug information
    error_log("User type: " . $user);
    error_log("Associate ID: " . $associate_id);
    
    header("location:login.php");
    exit();
}
