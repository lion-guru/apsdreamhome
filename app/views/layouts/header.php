<?php

/**
 * APS Dream Home - Header Component
 * This file contains the navigation and header logic without HTML/HEAD tags
 * It is designed to be included inside the BODY tag of the layout
 */

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_type = $_SESSION['user_type'] ?? 'guest';
$user_name = $_SESSION['user_name'] ?? 'User';

// Helper function for settings
if (!function_exists('get_setting')) {
    function get_setting($key, $default = '')
    {
        static $settings = null;

        if ($settings === null) {
            $settings = [
                'site_title' => 'APS Dream Home',
                'site_description' => 'Your trusted real estate partner',
                'contact_phone' => '+91 7007444842',
                'contact_email' => 'info@apsdreamhome.com',
                'company_address' => '1st floor singhariya, kunraghat, Gorakhpur, UP, India',
                'business_hours' => 'Mon - Sat: 9:00 AM - 8:00 PM'
            ];
        }

        if (function_exists('getSiteSetting')) {
            $value = getSiteSetting($key, null);
            if ($value !== null) {
                return $value;
            }
        }

        return $settings[$key] ?? $default;
    }
}

// Helper function to check current page
if (!function_exists('is_current_page')) {
    function is_current_page($path)
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $current_path = trim(parse_url($uri, PHP_URL_PATH), '/');
        $check_path = trim($path, '/');
        return $current_path === $check_path;
    }
}

// Helper function to check active path
if (!function_exists('is_active_path')) {
    function is_active_path($path)
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $current_path = trim(parse_url($uri, PHP_URL_PATH), '/');
        $check_path = trim($path, '/');
        return strpos($current_path, $check_path) === 0;
    }
}
?>

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
                        <a href="<?php echo BASE_URL; ?>employee/dashboard" class="<?php echo (strpos($_SERVER['REQUEST_URI'] ?? '/', '/employee/dashboard') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>employee/profile" class="<?php echo (strpos($_SERVER['REQUEST_URI'] ?? '/', '/employee/profile') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-user"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>employee/tasks" class="<?php echo (strpos($_SERVER['REQUEST_URI'] ?? '/', '/employee/tasks') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-tasks"></i>Tasks
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>employee/attendance" class="<?php echo (strpos($_SERVER['REQUEST_URI'] ?? '/', '/employee/attendance') !== false) ? 'active' : '' ?>">
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
                        <a href="<?php echo BASE_URL; ?>associate/crm" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/associate/crm') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-users"></i>CRM
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>associate/team" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/associate/team') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-user-friends"></i>Team
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_URL; ?>associate/business" class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/associate/business') !== false) ? 'active' : '' ?>">
                            <i class="fas fa-chart-bar"></i>Business
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

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/css/dashboard.css">

<?php else: ?>
    <?php
    $site_title = get_setting('site_title', 'APS Dream Home') ?: 'APS Dream Home';
    ?>
    <!-- Top Bar for Public Site -->
    <div class="top-bar bg-primary text-white py-2 d-none d-lg-block">
        <div class="container">
            <div class="row g-2 align-items-center">
                <div class="col-lg-6">
                    <div class="top-contact d-flex flex-wrap align-items-center gap-3">
                        <span class="top-company text-uppercase fw-semibold d-flex align-items-center">
                            <i class="fas fa-building me-2"></i><?= htmlspecialchars($site_title); ?>
                        </span>
                        <a class="text-white text-decoration-none" href="<?php echo 'tel:' . preg_replace('/[^\d+]/', '', get_setting('contact_phone')); ?>">
                            <i class="fas fa-phone-alt me-2"></i><?php echo get_setting('contact_phone'); ?>
                        </a>
                        <a class="text-white text-decoration-none" href="mailto:<?php echo get_setting('contact_email'); ?>">
                            <i class="fas fa-envelope me-2"></i><?php echo get_setting('contact_email'); ?>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-lg-end">
                    <div class="social-links d-flex justify-content-lg-end gap-3">
                        <a href="#" class="text-white" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    $raw_logo_path = get_setting('logo_path', '');
    $logo_setting = get_setting('logo_path', '');
    $logo_url = !empty($logo_setting)
        ? (str_starts_with($logo_setting, 'http') ? $logo_setting : BASE_URL . ltrim($logo_setting, '/'))
        : BASE_URL . 'public/assets/images/logo/apslogo.png';

    if (!empty($raw_logo_path)) {
        if (str_starts_with($raw_logo_path, 'http')) {
            $logo_url = $raw_logo_path;
        } else {
            $path = ltrim($raw_logo_path, '/');
            if (str_starts_with($path, 'assets/') && !str_starts_with($path, 'public/')) {
                $path = 'public/' . $path;
            }
            $logo_url = BASE_URL . $path;
        }
    }

    $contact_phone = get_setting('contact_phone', '+91 7007444842');
    $contact_email = get_setting('contact_email', 'info@apsdreamhome.com');
    $tel_link = 'tel:' . preg_replace('/[^\d+]/', '', $contact_phone);
    ?>

    <!-- Main Header for Public Site -->
    <header class="public-header">
        <nav class="navbar navbar-expand-lg navbar-dark premium-navbar sticky-top" aria-label="Primary Navigation">
            <div class="container-fluid">
                <a class="navbar-brand premium-brand" href="<?= BASE_URL; ?>" title="<?= htmlspecialchars($site_title); ?>">
                    <div class="brand-container">
                        <img src="<?= htmlspecialchars($logo_url); ?>" alt="<?= htmlspecialchars($site_title); ?>" class="brand-logo" loading="lazy">
                    </div>
                </a>

                <span class="navbar-center-brand d-flex d-lg-none align-items-center justify-content-center mx-auto text-white">
                    <?= htmlspecialchars($site_title); ?>
                </span>

                <button class="navbar-toggler premium-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="toggler-icon"></span>
                    <span class="toggler-text">Menu</span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav premium-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?php echo is_current_page('') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>">
                                <i class="fas fa-house-chimney me-1" aria-hidden="true"></i>Home
                            </a>
                        </li>

                        <li class="nav-item dropdown mega-dropdown">
                            <a class="nav-link dropdown-toggle premium-dropdown-toggle <?php echo is_active_path('properties') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>properties" id="propertiesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-home me-1" aria-hidden="true"></i>Properties
                            </a>
                            <div class="dropdown-menu premium-mega-menu" aria-labelledby="propertiesDropdown">
                                <div class="mega-menu-container">
                                    <div class="row g-0">
                                        <div class="col-lg-8">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="mega-header"><i class="fas fa-search me-2"></i>Browse by Type</h6>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>properties"><i class="fas fa-th-large me-2"></i>All Properties</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>properties?type=apartment"><i class="fas fa-building me-2"></i>Apartments</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>properties?type=villa"><i class="fas fa-house-user me-2"></i>Villas</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>properties?type=commercial"><i class="fas fa-city me-2"></i>Commercial Spaces</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>properties?type=plots"><i class="fas fa-map me-2"></i>Plots / Land</a>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="mega-header"><i class="fas fa-fire me-2"></i>Featured Collections</h6>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>properties?featured=1"><i class="fas fa-star me-2"></i>Featured Listings</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>resell"><i class="fas fa-recycle me-2"></i>Resale Properties</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>properties?status=ready-to-move"><i class="fas fa-key me-2"></i>Ready to Move</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>properties?price=5000000-10000000"><i class="fas fa-rupee-sign me-2"></i>₹50L - ₹1Cr Homes</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mega-sidebar">
                                            <div class="mega-stats">
                                                <h6>Key Highlights</h6>
                                                <div class="stat-item">
                                                    <span class="stat-number">500+</span>
                                                    <span class="stat-label">Properties</span>
                                                </div>
                                                <div class="stat-item">
                                                    <span class="stat-number">2k+</span>
                                                    <span class="stat-label">Happy Families</span>
                                                </div>
                                                <a href="<?= BASE_URL; ?>properties" class="btn btn-light btn-sm w-100 mt-3">Explore All</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?php echo is_current_page('about') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>about">
                                <i class="fas fa-info-circle me-1" aria-hidden="true"></i>About
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?php echo is_current_page('contact') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>contact">
                                <i class="fas fa-envelope me-1" aria-hidden="true"></i>Contact
                            </a>
                        </li>
                    </ul>

                    <div class="navbar-actions d-flex align-items-center">
                        <a href="<?= BASE_URL; ?>properties" class="btn btn-outline-light me-2 d-none d-lg-block">
                            <i class="fas fa-search me-1"></i>Search
                        </a>

                        <div class="dropdown">
                            <button class="btn btn-premium-accent dropdown-toggle" type="button" id="authDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> Account
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end premium-dropdown-menu" aria-labelledby="authDropdown">
                                <li>
                                    <h6 class="dropdown-header">Login As</h6>
                                </li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>login"><i class="fas fa-sign-in-alt me-2"></i>User Login</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>associate/login"><i class="fas fa-user-tie me-2"></i>Associate Login</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>register"><i class="fas fa-user-plus me-2"></i>Register</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>associate/register"><i class="fas fa-handshake me-2"></i>Join as Associate</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <h6 class="dropdown-header">Dashboards</h6>
                                </li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>admin"><i class="fas fa-user-shield me-2"></i>Admin Panel</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>employee/login"><i class="fas fa-id-badge me-2"></i>Employee Login</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>customer/dashboard"><i class="fas fa-tachometer-alt me-2"></i>Customer Dashboard</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>associate/dashboard"><i class="fas fa-chart-line me-2"></i>Associate Dashboard</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <div class="mobile-menu-overlay" id="mobileMenuOverlay" role="presentation"></div>
    </header>

    <!-- Page Content Wrapper for Public Site -->
<?php endif; ?>
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
<div class="content-wrapper <?php echo ($is_logged_in && in_array($user_type, ['employee', 'customer', 'associate'])) ? 'main-content' : ''; ?>">
