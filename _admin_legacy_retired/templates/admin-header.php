<?php
/**
 * Modern Admin Header Template
 * This file contains the header for all admin pages with modern UI
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Include necessary configuration files
require_once __DIR__ . '/../../includes/config/updated-config-paths.php';

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Get admin user information
$admin_id = $_SESSION['admin_id'];
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin User';
$admin_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'Administrator';
$admin_image = isset($_SESSION['admin_image']) ? $_SESSION['admin_image'] : 'default-avatar.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Panel | APS Dream Homes'; ?></title>
    <meta name="description" content="<?php echo isset($meta_description) ? $meta_description : 'Admin panel for APS Dream Homes property management system.'; ?>">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo get_asset_url('favicon.ico', 'images'); ?>">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('bootstrap/css/bootstrap.min.css', 'vendor'); ?>">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo get_asset_url('fontawesome/css/all.min.css', 'vendor'); ?>">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('css/admin-style.css'); ?>">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="<?php echo get_asset_url('datatables/css/dataTables.bootstrap5.min.css', 'vendor'); ?>">
    
    <!-- Additional CSS -->
    <?php if(isset($additional_css)) echo $additional_css; ?>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="d-flex align-items-center">
                    <img src="<?php echo get_asset_url('aps-logo.png', 'images'); ?>" alt="APS Dream Homes" class="logo-img">
                    <h3>APS Admin</h3>
                </div>
                <div class="sidebar-toggle-btn d-md-none" id="sidebarCollapseBtn">
                    <i class="fas fa-bars"></i>
                </div>
            </div>
            
            <div class="sidebar-user">
                <div class="user-info">
                    <img src="<?php echo get_asset_url($admin_image, 'images'); ?>" alt="User" class="user-img">
                    <div>
                        <h6 class="mb-0"><?php echo htmlspecialchars($admin_name); ?></h6>
                        <span class="user-role"><?php echo htmlspecialchars($admin_role); ?></span>
                    </div>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li class="menu-header">Main</li>
                <li class="menu-item <?php echo ($current_page == 'dashboard.php' || $current_page == 'dashboard-modern.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard-modern.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="menu-header">Property Management</li>
                <li class="menu-item <?php echo ($current_page == 'add_project.php' || $current_page == 'view_project.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/add_project.php">
                        <i class="fas fa-building"></i>
                        <span>Projects</span>
                    </a>
                </li>
                <li class="menu-item <?php echo ($current_page == 'submitproperty.php' || $current_page == 'property.php') ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/submitproperty.php">
                        <i class="fas fa-home"></i>
                        <span>Properties</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $current_page == 'add_property_type.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/add_property_type.php">
                        <i class="fas fa-tags"></i>
                        <span>Property Types</span>
                    </a>
                </li>
                
                <li class="menu-header">Customer Management</li>
                <li class="menu-item <?php echo $current_page == 'customer_management.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/customer_management.php">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $current_page == 'add_booking.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/add_booking.php">
                        <i class="fas fa-calendar-check"></i>
                        <span>Bookings</span>
                    </a>
                </li>
                
                <li class="menu-header">Financial</li>
                <li class="menu-item <?php echo $current_page == 'add_transaction.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/add_transaction.php">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Transactions</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $current_page == 'add_income.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/add_income.php">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Income</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $current_page == 'add_expenses.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/add_expenses.php">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Expenses</span>
                    </a>
                </li>
                
                <li class="menu-header">Marketing</li>
                <li class="menu-item <?php echo $current_page == 'contactview.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/contactview.php">
                        <i class="fas fa-envelope"></i>
                        <span>Inquiries</span>
                    </a>
                </li>
                <li class="menu-item <?php echo $current_page == 'admin_view_applicants.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/admin_view_applicants.php">
                        <i class="fas fa-user-tie"></i>
                        <span>Job Applicants</span>
                    </a>
                </li>
                
                <li class="menu-header">Content</li>
                <li class="menu-item <?php echo $current_page == 'aboutview.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/aboutview.php">
                        <i class="fas fa-info-circle"></i>
                        <span>About Us</span>
                    </a>
                </li>
                
                <li class="menu-header">System</li>
                <li class="menu-item <?php echo $current_page == 'adminlist.php' ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/adminlist.php">
                        <i class="fas fa-user-shield"></i>
                        <span>Admin Users</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?php echo BASE_URL; ?>/admin/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <!-- Top Navbar -->
            <nav class="top-navbar">
                <div class="navbar-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="page-title"><?php echo isset($content_title) ? $content_title : 'Dashboard'; ?></h4>
                </div>
                
                <div class="navbar-right">
                    <div class="nav-item">
                        <a href="<?php echo BASE_URL; ?>/admin/contactview.php" class="nav-link">
                            <i class="fas fa-bell"></i>
                            <?php 
                            // Get unread notifications count
                            $con = mysqli_connect("localhost", "root", "", "aps");
                            if ($con) {
                                $unread_query = "SELECT COUNT(*) as count FROM contact WHERE status = 'unread'";
                                $unread_result = mysqli_query($con, $unread_query);
                                $unread_count = mysqli_fetch_assoc($unread_result)["count"];
                                
                                if ($unread_count > 0) {
                                    echo "<span class='badge bg-danger'>$unread_count</span>";
                                }
                            }
                            ?>
                        </a>
                    </div>
                    
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="<?php echo get_asset_url($admin_image, 'images'); ?>" alt="User" class="user-avatar">
                            <span class="d-none d-md-inline"><?php echo htmlspecialchars($admin_name); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/admin/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>
            
            <!-- Content Container -->
            <div class="content-container">