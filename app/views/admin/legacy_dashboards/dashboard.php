<?php
/**
 * Dashboard Entry Point - APS Dream Home
 * Redirects users to appropriate dashboard based on authentication and role
 */

require_once __DIR__ . '/../core/init.php';

// Set page title
$page_title = 'Dashboard - APS Dream Home';

// Check authentication and redirect accordingly
if (isAuthenticated()) {
    // Admin user - redirect to role-specific admin dashboard
    $admin_role = getAuthRole();

    // Get appropriate dashboard for role
    $dashboard_map = [
        'superadmin' => 'superadmin_dashboard.php',
        'admin' => 'admin_dashboard.php',
        'manager' => 'manager_dashboard.php',
        'director' => 'director_dashboard.php',
        'office_admin' => 'office_admin_dashboard.php',
        'ceo' => 'ceo_dashboard.php',
        'cfo' => 'cfo_dashboard.php',
        'coo' => 'coo_dashboard.php',
        'cto' => 'cto_dashboard.php',
        'cm' => 'cm_dashboard.php',
        'sales' => 'sales_dashboard.php',
        'employee' => 'employee_dashboard.php',
        'legal' => 'legal_dashboard.php',
        'marketing' => 'marketing_dashboard.php',
        'finance' => 'finance_dashboard.php',
        'hr' => 'hr_dashboard.php',
        'it' => 'it_dashboard.php',
        'operations' => 'operations_dashboard.php',
        'support' => 'support_dashboard.php',
        'builder' => 'builder_management_dashboard.php',
        'agent' => 'agent_dashboard.php',
        'associate' => 'associate_dashboard.php'
    ];

    $redirect_dashboard = $dashboard_map[$admin_role] ?? 'enhanced_dashboard.php';
    header('Location: ' . BASE_URL . 'admin/' . $redirect_dashboard);
    exit();
} elseif (isset($_SESSION['user_id'])) {
    // Regular user - redirect to user dashboard (MVC system)
    header('Location: ' . BASE_URL . 'dashboard/');
    exit();
} else {
    // Not authenticated - redirect to home page
    header('Location: ' . BASE_URL);
    exit();
}
?>
