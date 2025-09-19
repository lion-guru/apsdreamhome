<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? '';
$admin_role = $_SESSION['admin_role'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Home - Admin Dashboard</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/img/favicon.ico">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style-guide.css">
    
    <!-- Custom styles for this template -->
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 65px;
        }
        
        body {
            display: flex;
            min-height: 100vh;
            background-color: var(--gray-100);
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: #fff;
            box-shadow: var(--shadow);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--primary);
            color: white;
        }
        
        .sidebar-logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        .sidebar-menu {
            padding: 1rem 0;
        }
        
        .menu-title {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--gray-700);
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            border-left: 3px solid transparent;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--primary);
            background-color: var(--primary-light);
            border-left-color: var(--primary);
        }
        
        .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .has-submenu .nav-link:after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-left: auto;
            transition: transform 0.3s;
        }
        
        .has-submenu.show > .nav-link:after {
            transform: rotate(180deg);
        }
        
        .submenu {
            padding-left: 2.5rem;
            background-color: var(--gray-50);
            display: none;
        }
        
        .submenu.show {
            display: block;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header Styles */
        .topbar {
            height: var(--header-height);
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .topbar-left {
            display: flex;
            align-items: center;
        }
        
        .menu-toggle {
            font-size: 1.25rem;
            color: var(--gray-600);
            margin-right: 1rem;
            cursor: pointer;
            display: none;
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
        }
        
        .user-dropdown {
            position: relative;
        }
        
        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.5rem;
        }
        
        .user-name {
            font-weight: 500;
            color: var(--gray-800);
            margin-right: 0.5rem;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: var(--rounded-md);
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            padding: 0.5rem 0;
            margin-top: 0.5rem;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.2s;
            z-index: 1000;
        }
        
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: block;
            padding: 0.5rem 1.5rem;
            color: var(--gray-700);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: var(--gray-100);
            color: var(--primary);
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: var(--gray-200);
            margin: 0.5rem 0;
        }
        
        /* Main Content Area */
        .page-content {
            flex: 1;
            padding: 1.5rem;
        }
        
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }
        
        .page-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-900);
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .page-content {
                padding: 1rem;
            }
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--gray-300);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--gray-400);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="index.php" class="sidebar-logo">
                APS Dream Home
            </a>
        </div>
        
        <nav class="sidebar-menu">
            <div class="menu-title">Main</div>
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            
            <div class="menu-title mt-3">Management</div>
            
            <div class="has-submenu">
                <a href="#" class="nav-link">
                    <i class="fas fa-building"></i>
                    <span>Properties</span>
                </a>
                <div class="submenu">
                    <a href="properties.php" class="nav-link">All Properties</a>
                    <a href="property-add.php" class="nav-link">Add New</a>
                    <a href="property-categories.php" class="nav-link">Categories</a>
                </div>
            </div>
            
            <a href="bookings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
            </a>
            
            <a href="customers.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
            
            <a href="leads.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'leads.php' ? 'active' : ''; ?>">
                <i class="fas fa-bullseye"></i>
                <span>Leads</span>
            </a>
            
            <div class="menu-title mt-3">Finance</div>
            
            <a href="transactions.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">
                <i class="fas fa-money-bill-wave"></i>
                <span>Transactions</span>
            </a>
            
            <a href="invoices.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice"></i>
                <span>Invoices</span>
            </a>
            
            <div class="menu-title mt-3">System</div>
            
            <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-cog"></i>
                <span>Users</span>
            </a>
            
            <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-left">
                <div class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </div>
                <h4 class="page-title">
                    <?php 
                    $page_titles = [
                        'dashboard.php' => 'Dashboard',
                        'properties.php' => 'Properties',
                        'customers.php' => 'Customers',
                        'bookings.php' => 'Bookings',
                        'leads.php' => 'Leads',
                        'transactions.php' => 'Transactions',
                        'users.php' => 'Users',
                        'settings.php' => 'Settings'
                    ];
                    echo $page_titles[basename($_SERVER['PHP_SELF'])] ?? 'Dashboard';
                    ?>
                </h4>
            </div>
            
            <div class="topbar-right">
                <div class="user-dropdown" id="userDropdown">
                    <div class="user-dropdown-toggle">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($admin_name); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-content">
            <!-- Page content will be loaded here -->
