<?php
/**
 * Enhanced Admin Index - APS Dream Home
 * Smart admin panel entry point with role-based redirection
 */

require_once __DIR__ . '/core/init.php';

// Check authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

// Role-based redirection to appropriate dashboard
$admin_role = getAuthSubRole();

switch ($admin_role) {
    case 'superadmin':
    case 'super_admin':
        // Superadmin gets command center
        header('Location: superadmin_dashboard.php');
        break;
    case 'admin':
        // Admin gets main dashboard
        header('Location: dashboard.php');
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
        // Default to main dashboard
        header('Location: dashboard.php');
        break;
}

exit();
?>
