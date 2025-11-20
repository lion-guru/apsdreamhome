<?php
// Sidebar partial for admin panel
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role = $_SESSION['admin_role'] ?? '';
?>
<aside class="d-flex flex-column flex-shrink-0 p-3 bg-dark text-white vh-100 position-fixed" style="width: 220px;">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4"><i class="fas fa-home me-2"></i>APS Admin</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item"><a href="index.php" class="nav-link text-white"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a href="adminlist.php" class="nav-link text-white"><i class="fas fa-user-shield me-2"></i>Admins</a></li>
        <li class="nav-item"><a href="userlist.php" class="nav-link text-white"><i class="fas fa-users me-2"></i>Users</a></li>
        <li class="nav-item"><a href="agent.php" class="nav-link text-white"><i class="fas fa-user-tie me-2"></i>Agent</a></li>
        <li class="nav-item"><a href="userbuilder.php" class="nav-link text-white"><i class="fas fa-hard-hat me-2"></i>Builder</a></li>
        <li class="nav-item"><a href="add_property_type.php" class="nav-link text-white"><i class="fas fa-plus-square me-2"></i>Add Property</a></li>
        <li class="nav-item"><a href="propertyview.php" class="nav-link text-white"><i class="fas fa-eye me-2"></i>View Property</a></li>
        <li class="nav-item"><a href="resellplot.php" class="nav-link text-white"><i class="fas fa-plus-square me-2"></i>Add Resell Plot</a></li>
        <li class="nav-item"><a href="viewresellplot.php" class="nav-link text-white"><i class="fas fa-eye me-2"></i>View Resell Plot</a></li>
        <li class="nav-item"><a href="contactview.php" class="nav-link text-white"><i class="fas fa-address-book me-2"></i>Contact</a></li>
        <li class="nav-item"><a href="feedbackview.php" class="nav-link text-white"><i class="fas fa-comments me-2"></i>Feedback</a></li>
        <li class="nav-item"><a href="aboutadd.php" class="nav-link text-white"><i class="fas fa-info-circle me-2"></i>Add About Content</a></li>
        <li class="nav-item"><a href="aboutview.php" class="nav-link text-white"><i class="fas fa-info-circle me-2"></i>View About</a></li>
        <li class="nav-item"><a href="addimage.php" class="nav-link text-white"><i class="fas fa-image me-2"></i>Add Image</a></li>
        <li class="nav-item"><a href="gallery_management.php" class="nav-link text-white"><i class="fas fa-images me-2"></i>View Images</a></li>
        <li class="nav-item"><a href="site_master.php" class="nav-link text-white"><i class="fas fa-sitemap me-2"></i>Add Site</a></li>
        <li class="nav-item"><a href="gata_master.php" class="nav-link text-white"><i class="fas fa-map me-2"></i>Add Gata</a></li>
        <li class="nav-item"><a href="plot_master.php" class="nav-link text-white"><i class="fas fa-th-large me-2"></i>Add Plot</a></li>
        <li class="nav-item"><a href="update_site.php" class="nav-link text-white"><i class="fas fa-edit me-2"></i>Update Site</a></li>
        <li class="nav-item"><a href="update_gata.php" class="nav-link text-white"><i class="fas fa-edit me-2"></i>Update Gata</a></li>
        <li class="nav-item"><a href="update_plot.php" class="nav-link text-white"><i class="fas fa-edit me-2"></i>Update Plot</a></li>
        <li class="nav-item"><a href="kissan_master.php" class="nav-link text-white"><i class="fas fa-tractor me-2"></i>Add Kissan</a></li>
        <li class="nav-item"><a href="view_kisaan.php" class="nav-link text-white"><i class="fas fa-tractor me-2"></i>View Kissan</a></li>
        <li class="nav-item"><a href="projects.php" class="nav-link text-white"><i class="fas fa-project-diagram me-2"></i>Projects</a></li>
        <li class="nav-item"><a href="property_inventory.php" class="nav-link text-white"><i class="fas fa-warehouse me-2"></i>Property Type & Plots Inventory</a></li>
        <li class="nav-item"><a href="booking.php" class="nav-link text-white"><i class="fas fa-calendar-check me-2"></i>Booking Form & Installment Management</a></li>
        <li class="nav-item"><a href="customer_management.php" class="nav-link text-white"><i class="fas fa-users me-2"></i>Customer Master, KYC, Docs</a></li>
        <li class="nav-item"><a href="ledger.php" class="nav-link text-white"><i class="fas fa-book me-2"></i>Customer Ledger & Outstanding</a></li>
        <li class="nav-item"><a href="reminders.php" class="nav-link text-white"><i class="fas fa-bell me-2"></i>Payment Reminders & Reports</a></li>
        <li class="nav-item"><a href="mlm_engagement.php" class="nav-link text-white"><i class="fas fa-chart-line me-2"></i>MLM Engagement</a></li>
        <li class="nav-item"><a href="add_income.php" class="nav-link text-white"><i class="fas fa-rupee-sign me-2"></i>Add Income</a></li>
        <li class="nav-item"><a href="add_expenses.php" class="nav-link text-white"><i class="fas fa-rupee-sign me-2"></i>Add Expenses</a></li>
        <li class="nav-item"><a href="admin_view_applicants.php" class="nav-link text-white"><i class="fas fa-briefcase me-2"></i>View Applicants</a></li>
        <li class="nav-item"><a href="add_task.php" class="nav-link text-white"><i class="fas fa-briefcase me-2"></i>Add Job</a></li>
        <li class="nav-item"><a href="assosiate_managment.php" class="nav-link text-white"><i class="fas fa-user-friends me-2"></i>Add Associate</a></li>
        <li class="nav-item"><a href="add_expenses.php" class="nav-link text-white"><i class="fas fa-rupee-sign me-2"></i>Add Expenses</a></li>
        <li class="nav-item"><a href="transactions.php" class="nav-link text-white"><i class="fas fa-exchange-alt me-2"></i>Transactions</a></li>
        <?php if ($role === 'superadmin'): ?>
            <li><a href="register.php" class="nav-link text-white"><i class="fas fa-user-plus me-2"></i>Register Admin</a></li>
            <li><a href="activity_log.php" class="nav-link text-white"><i class="fas fa-history me-2"></i>Activity Log</a></li>
        <?php endif; ?>
        <?php if (in_array($role, ['finance','superadmin'])): ?>
            <li><a href="transactions.php" class="nav-link text-white"><i class="fas fa-exchange-alt me-2"></i>Transactions</a></li>
        <?php endif; ?>
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
<script src="js/sidebar-ajax.js"></script>
<script src="includes/sidebar_slider.js"></script>
