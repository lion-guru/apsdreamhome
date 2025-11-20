<?php
/**
 * Updated Admin Sidebar for APS Dream Homes
 * Contains sidebar navigation elements shared across all admin pages
 */

// Include admin functions if not already included
if (!function_exists('is_admin_logged_in')) {
    require_once(__DIR__ . '/admin-functions.php');
}

// Check if admin is logged in
if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <a href="dashboard.php"><i class="fe fe-home"></i> <span>Dashboard</span></a>
                </li>
                
                <li class="submenu">
                    <a href="#"><i class="fe fe-user"></i> <span> Users</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo basename($_SERVER['PHP_SELF']) == 'adminlist.php' ? 'active' : ''; ?>" href="adminlist.php">Admin</a></li>
                        <li><a class="<?php echo basename($_SERVER['PHP_SELF']) == 'userlist.php' ? 'active' : ''; ?>" href="userlist.php">Users</a></li>
                    </ul>
                </li>
                
                <li class="submenu">
                    <a href="#"><i class="fe fe-location"></i> <span> Property</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo basename($_SERVER['PHP_SELF']) == 'propertyview.php' ? 'active' : ''; ?>" href="propertyview.php">Property</a></li>
                        <li><a class="<?php echo basename($_SERVER['PHP_SELF']) == 'propertyadd.php' ? 'active' : ''; ?>" href="propertyadd.php">Add Property</a></li>
                    </ul>
                </li>
                
                <li class="submenu">
                    <a href="#"><i class="fe fe-map"></i> <span> Projects</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo basename($_SERVER['PHP_SELF']) == 'projectview.php' ? 'active' : ''; ?>" href="projectview.php">Projects</a></li>
                        <li><a class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_project.php' ? 'active' : ''; ?>" href="add_project.php">Add Project</a></li>
                    </ul>
                </li>
                
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'aboutview.php' ? 'active' : ''; ?>">
                    <a href="aboutview.php"><i class="fe fe-info"></i> <span>About</span></a>
                </li>
                
                <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'contactview.php' ? 'active' : ''; ?>">
                    <a href="contactview.php"><i class="fe fe-phone"></i> <span>Contact</span></a>
                </li>
                
                <li class="submenu">
                    <a href="#"><i class="fe fe-users"></i> <span> CRM</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo basename($_SERVER['PHP_SELF']) == 'customer_management.php' ? 'active' : ''; ?>" href="customer_management.php">Customers</a></li>
                        <li><a class="<?php echo basename($_SERVER['PHP_SELF']) == 'booking.php' ? 'active' : ''; ?>" href="booking.php">Bookings</a></li>
                        <li><a class="<?php echo basename($_SERVER['PHP_SELF']) == 'aps_custom_report.php' ? 'active' : ''; ?>" href="aps_custom_report.php">Reports</a></li>
                        <li><a class="<?php echo basename($_SERVER['PHP_SELF']) == 'mlm_engagement.php' ? 'active' : ''; ?>" href="mlm_engagement.php">MLM Engagement</a></li>
                    </ul>
                </li>
                
                <li>
                    <a href="logout.php"><i class="fe fe-power"></i> <span>Logout</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->