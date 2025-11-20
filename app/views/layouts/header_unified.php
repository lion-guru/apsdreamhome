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
            : BASE_URL . 'assets/images/logo/apslogo.png';

        if (!empty($raw_logo_path)) {
            if (str_starts_with($raw_logo_path, 'http')) {
                $logo_url = $raw_logo_path;
            } else {
                $logo_url = BASE_URL . ltrim($raw_logo_path, '/');
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
                                                    <span class="stat-label">Active Listings</span>
                                                </div>
                                                <div class="stat-item">
                                                    <span class="stat-number">50+</span>
                                                    <span class="stat-label">Neighbourhoods</span>
                                                </div>
                                                <div class="mega-highlight">
                                                    <i class="fas fa-phone-volume me-2"></i>Need help? <strong>+91 7007444842</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="nav-item dropdown mega-dropdown">
                            <a class="nav-link dropdown-toggle premium-dropdown-toggle <?php echo is_active_path('projects') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>projects" id="projectsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-project-diagram me-1" aria-hidden="true"></i>Projects
                            </a>
                            <div class="dropdown-menu premium-mega-menu" aria-labelledby="projectsDropdown">
                                <div class="mega-menu-container">
                                    <div class="row g-0">
                                        <div class="col-lg-8">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <h6 class="mega-header"><i class="fas fa-list me-2"></i>By Status</h6>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects"><i class="fas fa-th me-2"></i>All Projects</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects?status=upcoming"><i class="fas fa-calendar-plus me-2"></i>Upcoming Launches</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects?status=ongoing"><i class="fas fa-business-time me-2"></i>Under Construction</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects?status=completed"><i class="fas fa-check-circle me-2"></i>Completed Projects</a>
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="mega-header"><i class="fas fa-map-marker-alt me-2"></i>By City</h6>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects?location=Gorakhpur"><i class="fas fa-location-dot me-2"></i>Gorakhpur</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects?location=Lucknow"><i class="fas fa-location-dot me-2"></i>Lucknow</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects?location=Varanasi"><i class="fas fa-location-dot me-2"></i>Varanasi</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects?location=Allahabad"><i class="fas fa-location-dot me-2"></i>Prayagraj</a>
                                                </div>
                                                <div class="col-md-4">
                                                    <h6 class="mega-header"><i class="fas fa-building me-2"></i>Discover</h6>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects?segment=luxury"><i class="fas fa-crown me-2"></i>Luxury Projects</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects?segment=affordable"><i class="fas fa-hand-holding-heart me-2"></i>Affordable Housing</a>
                                                    <a class="mega-item" href="<?= BASE_URL; ?>projects?segment=commercial"><i class="fas fa-briefcase me-2"></i>Commercial Hubs</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mega-sidebar">
                                            <div class="mega-project-card text-center">
                                                <h6>Latest Project Updates</h6>
                                                <p class="mb-3">Download brochures, floor plans, and exclusive launch offers.</p>
                                                <a href="<?= BASE_URL; ?>projects" class="btn btn-primary btn-sm">Explore Projects</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle premium-dropdown-toggle <?php echo is_active_path('services') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>services" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cogs me-1" aria-hidden="true"></i>Services
                            </a>
                            <ul class="dropdown-menu premium-dropdown" aria-labelledby="servicesDropdown">
                                <li><h6 class="dropdown-header"><i class="fas fa-handshake me-2"></i>Our Services</h6></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>services"><i class="fas fa-briefcase me-2"></i>Overview</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>property-management"><i class="fas fa-warehouse me-2"></i>Property Management</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>legal-services"><i class="fas fa-gavel me-2"></i>Legal Advisory</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>financial-services"><i class="fas fa-rupee-sign me-2"></i>Financial Guidance</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>interior-design"><i class="fas fa-palette me-2"></i>Interior Design</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle premium-dropdown-toggle <?php echo is_active_path('about') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>about" id="aboutDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-info-circle me-1" aria-hidden="true"></i>About
                            </a>
                            <ul class="dropdown-menu premium-dropdown" aria-labelledby="aboutDropdown">
                                <li><h6 class="dropdown-header"><i class="fas fa-building me-2"></i>Company</</li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>about"><i class="fas fa-landmark me-2"></i>Company Overview</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>team"><i class="fas fa-users me-2"></i>Our Team</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>testimonials"><i class="fas fa-comment-dots me-2"></i>Testimonials</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>faq"><i class="fas fa-question-circle me-2"></i>FAQs</a></li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle premium-dropdown-toggle <?php echo (is_active_path('blog') || is_active_path('gallery') || is_active_path('downloads')) ? 'active' : ''; ?>" href="<?= BASE_URL; ?>blog" id="resourcesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-folder-open me-1" aria-hidden="true"></i>Resources
                            </a>
                            <ul class="dropdown-menu premium-dropdown" aria-labelledby="resourcesDropdown">
                                <li><h6 class="dropdown-header"><i class="fas fa-newspaper me-2"></i>Content</h6></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>blog"><i class="fas fa-blog me-2"></i>Blog</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>news"><i class="fas fa-rss me-2"></i>News & Updates</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header"><i class="fas fa-images me-2"></i>Media</h6></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>gallery"><i class="fas fa-images me-2"></i>Gallery</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>downloads"><i class="fas fa-download me-2"></i>Downloads</a></li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?php echo is_active_path('contact') ? 'active' : ''; ?>" href="<?= BASE_URL; ?>contact">
                                <i class="fas fa-envelope-open-text me-1" aria-hidden="true"></i>Contact
                            </a>
                        </li>
                    </ul>

                    <div class="premium-actions">
                        <a href="<?= htmlspecialchars($tel_link); ?>" class="btn btn-success premium-btn" title="Call <?= htmlspecialchars($contact_phone); ?>">
                            <i class="fas fa-phone-alt" aria-hidden="true"></i>
                            <span class="d-none d-xl-inline ms-1">Call</span>
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-light premium-btn dropdown-toggle" type="button" id="accountDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user" aria-hidden="true"></i>
                                <span class="d-none d-lg-inline ms-1">Account</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end premium-dropdown" aria-labelledby="accountDropdown">
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>login"><i class="fas fa-sign-in-alt me-2"></i>Login</a></li>
                                <li><a class="dropdown-item premium-item" href="<?= BASE_URL; ?>register"><i class="fas fa-user-plus me-2"></i>Register</a></li>
                                <li><hr class="dropdown-divider"></li>
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
