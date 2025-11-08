<?php
// DEPRECATED: This file is an unused template variation
// Unified header implementation not referenced anywhere in codebase
// Use includes/universal_template.php instead
?>
<?php
/**
 * APS Dream Home - Unified Header
 * This file consolidates all header functionality into a single, clean component
 */

// Ensure BASE_URL is defined
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_name = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    $base_path = str_replace('\\', '/', $script_name);
    $base_path = rtrim($base_path, '/') . '/';
    define('BASE_URL', $protocol . $host . $base_path);
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_type = $_SESSION['user_type'] ?? 'guest';
$user_name = $_SESSION['user_name'] ?? 'User';

// Helper function for settings
if (!function_exists('get_setting')) {
    function get_setting($key, $default = '') {
        static $settings = null;

        if ($settings === null) {
            $settings = [
                'site_title' => 'APS Dream Home',
                'site_description' => 'Your trusted real estate partner',
                'contact_phone' => '+91 95540 00001',
                'contact_email' => 'info@apsdreamhome.com',
                'company_address' => '123 Dream Avenue, Gorakhpur, UP, India',
                'business_hours' => 'Mon - Sat: 9:00 AM - 8:00 PM'
            ];
        }

        return $settings[$key] ?? $default;
    }
}

// Helper function to check current page
if (!function_exists('is_current_page')) {
    function is_current_page($path) {
        $current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $check_path = trim($path, '/');
        return $current_path === $check_path;
    }
}

// Helper function to check active path
if (!function_exists('is_active_path')) {
    function is_active_path($path) {
        $current_path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $check_path = trim($path, '/');
        return strpos($current_path, $check_path) === 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= get_setting('site_title', 'APS Dream Home') ?></title>
    <meta name="description" content="<?= get_setting('site_description', 'Your trusted real estate partner') ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/images/favicon.png">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>
    <?php if ($is_logged_in && in_array($user_type, ['employee', 'customer', 'associate'])): ?>
    <!-- Sidebar for Authenticated Users -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4>
                <?php if ($user_type === 'employee'): ?>
                    <i class="fas fa-user-tie me-2"></i>Employee Panel
                <?php elseif ($user_type === 'customer'): ?>
                    <i class="fas fa-user me-2"></i>Customer Panel
                <?php elseif ($user_type === 'associate'): ?>
                    <i class="fas fa-users me-2"></i>Associate Panel
                <?php endif; ?>
            </h4>
        </div>
        <div class="sidebar-menu">
            <ul>
                <?php if ($user_type === 'employee'): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>employee/dashboard" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/employee/dashboard') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>employee/profile" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/employee/profile') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-user"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>employee/tasks" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/employee/tasks') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-tasks"></i>Tasks
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>employee/attendance" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/employee/attendance') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-clock"></i>Attendance
                        </a>
                    </li>
                    <li class="mt-4">
                        <a href="<?php echo BASE_URL; ?>employee/logout" class="text-danger">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </li>
                <?php elseif ($user_type === 'customer'): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>customer/dashboard" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/customer/dashboard') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>customer/properties" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/customer/properties') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-search"></i>Property Search
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>customer/favorites" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/customer/favorites') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-heart"></i>Favorites
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>customer/bookings" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/customer/bookings') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-calendar-check"></i>Bookings
                        </a>
                    </li>
                    <li class="mt-4">
                        <a href="<?php echo BASE_URL; ?>customer/logout" class="text-danger">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </li>
                <?php elseif ($user_type === 'associate'): ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>associate/dashboard" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/associate/dashboard') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>associate/team" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/associate/team') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-users"></i>Team Management
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>associate/business" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/associate/business') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-chart-bar"></i>Business Overview
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>associate/earnings" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/associate/earnings') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-money-bill-wave"></i>Earnings
                        </a>
                    </li>
                    <li class="mt-4">
                        <a href="<?php echo BASE_URL; ?>associate/logout" class="text-danger">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Top Navbar for Authenticated Users -->
    <nav class="top-navbar">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>

            <div class="ms-auto d-flex align-items-center">
                <!-- Notifications -->
                <div class="dropdown me-3">
                    <button class="btn btn-link text-muted position-relative" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge badge-danger badge-pill position-absolute" style="top: -5px; right: -5px;">3</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#">New notification</a>
                        <a class="dropdown-item" href="#">Another notification</a>
                        <a class="dropdown-item" href="#">Third notification</a>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown">
                    <button class="btn btn-link text-muted" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-lg me-2"></i>
                        <?php
                        if ($user_type === 'employee') {
                            echo $_SESSION['employee_name'] ?? 'Employee';
                        } elseif ($user_type === 'customer') {
                            echo $_SESSION['customer_name'] ?? 'Customer';
                        } elseif ($user_type === 'associate') {
                            echo $_SESSION['associate_name'] ?? 'Associate';
                        }
                        ?>
                        <i class="fas fa-chevron-down ms-2"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#logout">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content for Authenticated Users -->
    <div class="main-content" id="mainContent">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

    <?php else: ?>
    <!-- Top Bar for Public Site -->
    <div class="top-bar bg-primary text-white py-2 d-none d-lg-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="d-flex gap-3">
                        <span><i class="fas fa-phone-alt me-2"></i> <?php echo get_setting('contact_phone'); ?></span>
                        <span><i class="fas fa-envelope me-2"></i> <?php echo get_setting('contact_email'); ?></span>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="social-links">
                        <a href="#" class="text-white me-2" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-2" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-2" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Header for Public Site -->
    <header class="header sticky-top">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <!-- Logo -->
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                    <img src="<?php echo BASE_URL; ?>assets/images/logo.png" alt="APS Dream Home" class="img-fluid" style="max-height: 50px;">
                </a>

                <!-- Mobile Toggle -->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Main Navigation -->
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_current_page('') ? 'active' : ''; ?>"
                               href="<?php echo BASE_URL; ?>">
                                <i class="fas fa-home d-lg-none me-2"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_path('properties') ? 'active' : ''; ?>"
                               href="<?php echo BASE_URL; ?>properties">
                                <i class="fas fa-building d-lg-none me-2"></i> Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_path('projects') ? 'active' : ''; ?>"
                               href="<?php echo BASE_URL; ?>projects">
                                <i class="fas fa-project-diagram d-lg-none me-2"></i> Projects
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_path('about') ? 'active' : ''; ?>"
                               href="<?php echo BASE_URL; ?>about">
                                <i class="fas fa-info-circle d-lg-none me-2"></i> About Us
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_path('contact') ? 'active' : ''; ?>"
                               href="<?php echo BASE_URL; ?>contact">
                                <i class="fas fa-envelope d-lg-none me-2"></i> Contact
                            </a>
                        </li>
                    </ul>

                    <!-- User Actions -->
                    <div class="d-flex align-items-center gap-3">
                        <a href="<?php echo BASE_URL; ?>login" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                        <a href="<?php echo BASE_URL; ?>register" class="btn btn-primary">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay"></div>

    <!-- Page Content Wrapper for Public Site -->
    <main class="main-content">
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_messages'])): ?>
            <div class="container mt-3">
                <?php foreach ($_SESSION['flash_messages'] as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?> alert-dismissible fade show">
                        <?php echo htmlspecialchars($message['text']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
                <?php unset($_SESSION['flash_messages']); ?>
            </div>
        <?php endif; ?>

        <!-- Main Content will be inserted here -->
        <div class="content-wrapper">
    <?php endif; ?>

    <!-- Sidebar Styles for Authenticated Users -->
    <style>
        /* Sidebar Styles */
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0d6efd 0%, #6c757d 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            margin-left: -260px;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: white;
        }

        .sidebar-menu i {
            width: 20px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            transition: all 0.3s ease;
            min-height: calc(100vh - 80px);
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Top Navbar */
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0.5rem 0;
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            z-index: 999;
        }

        .top-navbar .navbar-toggler {
            border: none;
            color: #0d6efd;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -260px;
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.expanded {
                margin-left: 260px;
            }

            .top-navbar {
                left: 0;
            }
        }

        /* Alert Styles */
        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 1rem;
        }

        /* Cards */
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
            border-radius: 10px;
        }

        .card-header {
            background: linear-gradient(135deg, #0d6efd 0%, #6c757d 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            border-bottom: none;
        }

        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, #0d6efd 0%, #6c757d 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        /* Tables */
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-top: none;
            color: #495057;
        }

        /* Form Controls */
        .form-control {
            border-radius: 8px;
            border: 2px solid #e3e6f0;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Custom Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            if (sidebar && mainContent) {
                sidebar.classList.toggle('show');
                mainContent.classList.toggle('expanded');
            }
        }

        // Auto-hide sidebar on mobile
        if (window.innerWidth <= 768) {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            if (sidebar && mainContent) {
                sidebar.classList.remove('show');
                mainContent.classList.add('expanded');
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    </script>
