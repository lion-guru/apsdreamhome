<?php
/**
 * Commission Plan Calculator - Updated with Session Management
 * Advanced calculator for testing different commission scenarios
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../includes/associate_permissions.php';

// Check if user is admin
if (!isset($_SESSION['associate_logged_in']) || $_SESSION['associate_logged_in'] !== true) {
    header("Location: associate_login.php");
    exit();
}

$associate_id = $_SESSION['associate_id'];
if (!isAssociateAdmin($associate_id)) {
    $_SESSION['error_message'] = "You don't have permission to access plan calculator.";
    header("Location: associate_dashboard.php");
    exit();
}

$associate_name = $_SESSION['associate_name'];
