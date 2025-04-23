<?php
// Sidebar partial for admin panel
session_start();
$role = $_SESSION['admin_role'] ?? '';
?>
<aside class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white vh-100 position-fixed" style="width: 220px;">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4"><i class="fas fa-home me-2"></i>APS Admin</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item"><a href="index.php" class="nav-link text-white"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
        <li><a href="projects.php" class="nav-link text-white"><i class="fas fa-building me-2"></i>Projects</a></li>
        <li><a href="add_project.php" class="nav-link text-white"><i class="fas fa-plus me-2"></i>Add Project</a></li>
        <li><a href="customer_management.php" class="nav-link text-white"><i class="fas fa-users me-2"></i>Customers</a></li>
        <li><a href="booking.php" class="nav-link text-white"><i class="fas fa-calendar-check me-2"></i>Bookings</a></li>
        <li><a href="property_inventory.php" class="nav-link text-white"><i class="fas fa-warehouse me-2"></i>Inventory</a></li>
        <li><a href="leads.php" class="nav-link text-white"><i class="fas fa-bullhorn me-2"></i>Leads</a></li>
        <li><a href="opportunities.php" class="nav-link text-white"><i class="fas fa-lightbulb me-2"></i>Opportunities</a></li>
        <?php if ($role === 'superadmin'): ?>
            <li><a href="adminlist.php" class="nav-link text-white"><i class="fas fa-user-shield me-2"></i>Admins</a></li>
            <li><a href="register.php" class="nav-link text-white"><i class="fas fa-user-plus me-2"></i>Register Admin</a></li>
            <li><a href="activity_log.php" class="nav-link text-white"><i class="fas fa-history me-2"></i>Activity Log</a></li>
        <?php endif; ?>
        <?php if (in_array($role, ['finance','superadmin'])): ?>
            <li><a href="transactions.php" class="nav-link text-white"><i class="fas fa-exchange-alt me-2"></i>Transactions</a></li>
        <?php endif; ?>
        <li><a href="gallery_management.php" class="nav-link text-white"><i class="fas fa-images me-2"></i>Gallery</a></li>
        <li><a href="assosiate_managment.php" class="nav-link text-white"><i class="fas fa-user-friends me-2"></i>Associates</a></li>
        <li><a href="reminders.php" class="nav-link text-white"><i class="fas fa-bell me-2"></i>Reminders</a></li>
        <li><a href="aps_custom_report.php" class="nav-link text-white"><i class="fas fa-chart-line me-2"></i>Reports</a></li>
        <?php if ($role === 'superadmin'): ?>
            <li><a href="log_viewer.php" class="nav-link text-white"><i class="fas fa-clipboard-list me-2"></i>Logs</a></li>
            <li><a href="backup_manager.php" class="nav-link text-white"><i class="fas fa-database me-2"></i>Backup</a></li>
            <li><a href="header_footer_settings.php" class="nav-link text-white"><i class="fas fa-cog me-2"></i>Settings</a></li>
            <li><a href="ai_admin_insights.php" class="nav-link text-white"><i class="fas fa-robot me-2"></i>AI Admin</a></li>
        <?php endif; ?>
        <li><a href="analytics_dashboard.php" class="nav-link text-white"><i class="fas fa-chart-pie me-2"></i>Analytics</a></li>
        <li><a href="2fa_setup.php" class="nav-link text-white"><i class="fas fa-shield-alt me-2"></i>2FA Setup</a></li>
        <li><a href="account_lockout.php" class="nav-link text-white"><i class="fas fa-user-lock me-2"></i>Account Lockout</a></li>
        <li><a href="logout.php" class="nav-link text-white"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
    </ul>
    <?php if ($role !== 'superadmin'): ?>
        <style>
            a[href="log_viewer.php"],
            a[href="backup_manager.php"],
            a[href="header_footer_settings.php"],
            a[href="ai_admin_insights.php"] {
                display: none !important;
            }
        </style>
    <?php endif; ?>
    <hr>
    <div class="text-center small">&copy; 2025 APS Dream Homes</div>
</aside>
