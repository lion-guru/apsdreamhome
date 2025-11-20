<?php
/**
 * Enhanced Admin Index - APS Dream Home
 * Smart admin panel entry point with role-based redirection
 */

// Enhanced security and initialization
require_once __DIR__ . '/config.php';

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Role-based redirection to appropriate dashboard
$admin_role = $_SESSION['admin_role'] ?? 'admin';

switch ($admin_role) {
    case 'superadmin':
        // Super admin gets the enhanced dashboard
        header('Location: enhanced_dashboard.php');
        break;
    case 'admin':
        // Admin gets enhanced dashboard
        header('Location: enhanced_dashboard.php');
        break;
    case 'manager':
        // Manager gets management dashboard
        header('Location: manager_dashboard.php');
        break;
    case 'sales':
        // Sales gets sales dashboard
        header('Location: sales_dashboard.php');
        break;
    case 'hr':
        // HR gets HR dashboard
        header('Location: hr_dashboard.php');
        break;
    default:
        // Default to enhanced dashboard
        header('Location: enhanced_dashboard.php');
        break;
}

exit();
?>
